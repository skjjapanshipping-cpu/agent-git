import { prisma } from '@/lib/prisma'
import axios from 'axios'

const LARAVEL_API_URL = 'https://skjjapanshipping.com/skjtrack/api/update-pay-status'
const API_KEY = 'skjchat-invoice-2026'

/**
 * Check if a verified slip amount matches a pending invoice for this contact.
 * If matched → call Laravel API to update pay_status to "ชำระเงินแล้ว"
 * Returns match info or null if no match.
 */
export async function matchSlipToInvoice(
  contactId: string,
  slipAmount: number,
  transRef: string,
  customerNo?: string,
): Promise<{ matched: boolean; customerno?: string; etd?: string; message?: string } | null> {
  try {
    // Find pending invoices for this contact (within last 30 days)
    const thirtyDaysAgo = new Date()
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)

    let pendingInvoices = await prisma.invoiceSent.findMany({
      where: {
        contactId,
        status: 'pending',
        createdAt: { gte: thirtyDaysAgo },
      },
      orderBy: { createdAt: 'desc' },
    })

    // Fallback: if no invoices by contactId and customerNo provided, search by customerno
    if (pendingInvoices.length === 0 && customerNo) {
      const custKey = customerNo.toLowerCase()
      console.log(`[INVOICE-MATCH] No invoices for contactId, trying customerno fallback: ${custKey}`)
      pendingInvoices = await prisma.invoiceSent.findMany({
        where: {
          customerno: custKey,
          status: 'pending',
          createdAt: { gte: thirtyDaysAgo },
        },
        orderBy: { createdAt: 'desc' },
      })
      if (pendingInvoices.length > 0) {
        console.log(`[INVOICE-MATCH] Found ${pendingInvoices.length} invoice(s) via customerno fallback: ${custKey}`)
      }
    }

    if (pendingInvoices.length === 0) {
      console.log(`[INVOICE-MATCH] No pending invoices for contact ${contactId}${customerNo ? ` (customerNo: ${customerNo})` : ''}`)
      return null
    }

    // Try to match amount (±1 baht tolerance)
    const tolerance = 1.0

    // === Strategy 1: Single invoice match ===
    const singleMatch = pendingInvoices.find(inv => Math.abs(inv.amount - slipAmount) <= tolerance)

    // === Strategy 2: Combined invoices match (sum of all pending ≈ slip amount) ===
    const totalPending = pendingInvoices.reduce((sum, inv) => sum + inv.amount, 0)
    const isCombinedMatch = !singleMatch && pendingInvoices.length > 1 && Math.abs(totalPending - slipAmount) <= tolerance

    // Determine which invoices to mark as paid
    const matchedInvoices = singleMatch ? [singleMatch] : isCombinedMatch ? pendingInvoices : []

    if (matchedInvoices.length === 0) {
      console.log(`[INVOICE-MATCH] No amount match for contact ${contactId}, slip=฿${slipAmount}, invoices=[${pendingInvoices.map(i => `฿${i.amount}`).join(',')}], total=฿${totalPending}`)
      return null
    }

    const matchType = singleMatch ? 'single' : 'combined'
    console.log(`[INVOICE-MATCH] ✅ ${matchType} match! ${matchedInvoices.length} invoice(s), slip=฿${slipAmount}`, matchedInvoices.map(i => `${i.customerno} ETD=${i.etd} ฿${i.amount}`).join(', '))

    // Mark all matched invoices as paid in SKJ Chat DB
    let allShippingIds: number[] = []
    const etdList: string[] = []
    for (const inv of matchedInvoices) {
      await prisma.invoiceSent.update({
        where: { id: inv.id },
        data: {
          status: 'paid',
          paidAt: new Date(),
          paidTransRef: transRef,
        },
      })
      if (inv.shippingIds) {
        try {
          const ids = JSON.parse(inv.shippingIds)
          if (Array.isArray(ids)) allShippingIds.push(...ids)
        } catch {}
      }
      if (inv.etd && !etdList.includes(inv.etd)) etdList.push(inv.etd)
    }

    const customerno = matchedInvoices[0].customerno

    // Call Laravel API to update pay_status for ALL matched shipping IDs
    try {
      const res = await axios.post(LARAVEL_API_URL, {
        customerno,
        amount: slipAmount,
        trans_ref: transRef,
        etd: etdList.join(','),
        shipping_ids: allShippingIds.length > 0 ? allShippingIds : null,
      }, {
        headers: {
          'X-API-Key': API_KEY,
          'Content-Type': 'application/json',
        },
        timeout: 15000,
      })

      const data = res.data
      console.log(`[INVOICE-MATCH] Laravel response:`, JSON.stringify(data))

      const etdDisplay = etdList.length > 1 ? `${etdList.length} รอบ` : `รอบ ${etdList[0] || '-'}`

      if (data.success) {
        return {
          matched: true,
          customerno,
          etd: etdList.join(','),
          message: `✅ อัพเดทสถานะชำระเงินแล้ว (${customerno.toUpperCase()} ${etdDisplay})`,
        }
      } else {
        return {
          matched: true,
          customerno,
          etd: etdList.join(','),
          message: `⚠️ ยอดตรงกับบิล แต่ไม่สามารถอัพเดทสถานะได้: ${data.message}`,
        }
      }
    } catch (apiErr: any) {
      console.error(`[INVOICE-MATCH] Laravel API error:`, apiErr.message)
      return {
        matched: true,
        customerno,
        etd: etdList.join(','),
        message: `⚠️ ยอดตรงกับบิล (${customerno.toUpperCase()}) แต่เชื่อมต่อ My Shipping ไม่ได้`,
      }
    }
  } catch (err: any) {
    console.error(`[INVOICE-MATCH] Error:`, err.message)
    return null
  }
}
