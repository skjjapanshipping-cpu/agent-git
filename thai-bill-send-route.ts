import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { sendFacebookMessage, sendFacebookImage, sendFacebookFile } from '@/lib/platforms/facebook'
import { pushLineMessages } from '@/lib/platforms/line'

const API_KEY = process.env.INVOICE_API_KEY || 'skjchat-invoice-2026'

// POST — Send Thai shipping bill to customer via chat
// LINE: sends Flex Message (card UI) if flexMessages provided
// Facebook: sends plain text + PDF + QR separately
export async function POST(req: NextRequest) {
  try {
    const authHeader = req.headers.get('x-api-key')
    if (authHeader !== API_KEY) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const body = await req.json()
    const { customerno, totalAmount, pdfUrl, originalFilename, message: extraText, qrImageUrl, flexMessages, allFileUrls } = body

    if (!customerno) {
      return NextResponse.json({ error: 'customerno is required' }, { status: 400 })
    }

    const prefix = String(customerno).toUpperCase()

    // Find contact
    const allContacts = await prisma.contact.findMany({ select: { id: true, name: true, platform: true, platformId: true } })
    const contact = allContacts.find((c: any) => {
      const name = c.name.toUpperCase()
      const idx = name.indexOf(prefix)
      if (idx === -1) return false
      if (idx > 0) {
        const before = name[idx - 1]
        if (!' /,(_'.includes(before)) return false
      }
      const afterIdx = idx + prefix.length
      if (afterIdx < name.length) {
        const after = name[afterIdx]
        if (!'- /,)_'.includes(after)) return false
      }
      return true
    })

    if (!contact) {
      return NextResponse.json({ error: 'Customer not found in chat', customerno }, { status: 404 })
    }

    // Find or create conversation
    let conversation = await prisma.conversation.findFirst({
      where: { contactId: contact.id },
      orderBy: { lastMessageAt: 'desc' },
    })
    if (!conversation) {
      conversation = await prisma.conversation.create({
        data: { contactId: contact.id, platform: contact.platform, status: 'open' },
      })
    }

    const platformId = contact.platformId.replace(/^group:/, '')
    const results: { step: string; status: string; error?: string }[] = []

    const formattedTotal = totalAmount != null && totalAmount > 0
      ? Number(totalAmount).toLocaleString('th-TH', { minimumFractionDigits: 2 })
      : null

    // === LINE: send Flex Message (card UI) via pushLineMessages ===
    if (contact.platform === 'line' && flexMessages && Array.isArray(flexMessages) && flexMessages.length > 0) {
      try {
        await pushLineMessages(platformId, flexMessages)
        // Save summary text + card metadata to DB
        const summaryParts = ['\u{1F4E6} \u0E1A\u0E34\u0E25\u0E04\u0E48\u0E32\u0E2A\u0E48\u0E07\u0E1E\u0E31\u0E2A\u0E14\u0E38\u0E43\u0E19\u0E44\u0E17\u0E22']
        summaryParts.push('\u0E23\u0E2B\u0E31\u0E2A\u0E25\u0E39\u0E01\u0E04\u0E49\u0E32: ' + prefix)
        if (formattedTotal) summaryParts.push('\u0E22\u0E2D\u0E14\u0E23\u0E27\u0E21: \u0E3F' + formattedTotal)
        if (originalFilename) summaryParts.push('\u0E44\u0E1F\u0E25\u0E4C: ' + originalFilename)
        const summaryText = summaryParts.join('\n')

        const cardMeta = JSON.stringify({
          invoiceCard: true,
          cardType: 'thai_bill',
          customerno: prefix,
          totalAmount: totalAmount != null ? Number(totalAmount) : null,
          pdfUrl: pdfUrl || null,
          originalFilename: originalFilename || null,
          qrUrl: qrImageUrl || 'https://chat.skjjapanshipping.com/uploads/qr-payment.jpg',
          allFileUrls: allFileUrls || (pdfUrl ? [pdfUrl] : []),
        })

        await prisma.message.create({
          data: { conversationId: conversation.id, direction: 'outbound', type: 'text', content: summaryText, metadata: cardMeta, status: 'sent' },
        })
        results.push({ step: 'flex', status: 'sent' })
      } catch (err: any) {
        console.error(`[THAI-BILL] Flex send failed for ${customerno}:`, err.message)
        results.push({ step: 'flex', status: 'failed', error: err.message })
      }
    } else {
      // === Facebook or no flexMessages: send plain text + PDF + QR separately ===

      // 1. Text
      let text = extraText || ''
      if (!text) {
        const lines = []
        lines.push('\u{1F4E6} \u0E1A\u0E34\u0E25\u0E04\u0E48\u0E32\u0E2A\u0E48\u0E07\u0E1E\u0E31\u0E2A\u0E14\u0E38\u0E43\u0E19\u0E44\u0E17\u0E22')
        lines.push('\u0E23\u0E2B\u0E31\u0E2A\u0E25\u0E39\u0E01\u0E04\u0E49\u0E32: ' + prefix)
        if (formattedTotal) lines.push('\u0E22\u0E2D\u0E14\u0E23\u0E27\u0E21: ' + formattedTotal + ' \u0E1A\u0E32\u0E17')
        if (originalFilename) lines.push('\u0E44\u0E1F\u0E25\u0E4C: ' + originalFilename)
        lines.push('')
        lines.push('\u0E01\u0E23\u0E38\u0E13\u0E32\u0E0A\u0E33\u0E23\u0E30\u0E40\u0E07\u0E34\u0E19\u0E15\u0E32\u0E21 QR Code \u0E14\u0E49\u0E32\u0E19\u0E25\u0E48\u0E32\u0E07 \u{1F64F}')
        text = lines.join('\n')
      }
      try {
        await sendFacebookMessage(platformId, text)
        await prisma.message.create({
          data: { conversationId: conversation.id, direction: 'outbound', type: 'text', content: text, status: 'sent' },
        })
        results.push({ step: 'text', status: 'sent' })
      } catch (err: any) {
        console.error(`[THAI-BILL] Text failed for ${customerno}:`, err.message)
        results.push({ step: 'text', status: 'failed', error: err.message })
      }

      // 2. PDF
      if (pdfUrl) {
        try {
          const fullPdfUrl = pdfUrl.startsWith('http') ? pdfUrl : `https://chat.skjjapanshipping.com${pdfUrl}`
          await sendFacebookFile(platformId, fullPdfUrl)
          await prisma.message.create({
            data: {
              conversationId: conversation.id, direction: 'outbound', type: 'file',
              content: pdfUrl, metadata: JSON.stringify({ fileName: originalFilename || `thai-bill-${prefix}.pdf` }), status: 'sent',
            },
          })
          results.push({ step: 'pdf', status: 'sent' })
        } catch (err: any) {
          console.error(`[THAI-BILL] PDF failed for ${customerno}:`, err.message)
          results.push({ step: 'pdf', status: 'failed', error: err.message })
        }
      }

      // 3. QR
      const qrUrl = qrImageUrl || 'https://chat.skjjapanshipping.com/uploads/qr-payment.jpg'
      try {
        await sendFacebookImage(platformId, qrUrl)
        await prisma.message.create({
          data: { conversationId: conversation.id, direction: 'outbound', type: 'image', content: qrUrl, status: 'sent' },
        })
        results.push({ step: 'qr', status: 'sent' })
      } catch (err: any) {
        console.error(`[THAI-BILL] QR failed for ${customerno}:`, err.message)
        results.push({ step: 'qr', status: 'failed', error: err.message })
      }
    }

    // Update conversation
    const sentCount = results.filter(r => r.status === 'sent').length
    if (sentCount > 0) {
      const lastMsg = '\u{1F4E6} \u0E1A\u0E34\u0E25\u0E04\u0E48\u0E32\u0E2A\u0E48\u0E07\u0E1E\u0E31\u0E2A\u0E14\u0E38\u0E43\u0E19\u0E44\u0E17\u0E22' + (formattedTotal ? ' \u0E3F' + formattedTotal : '')
      await prisma.conversation.update({
        where: { id: conversation.id },
        data: { lastMessage: lastMsg, lastMessageAt: new Date(), status: 'open' },
      })

      // Save invoiceSent record for slip-to-invoice matching (ค่าส่งไทย)
      if (totalAmount != null && totalAmount > 0) {
        try {
          const custKey = prefix.toLowerCase()
          const existing = await prisma.invoiceSent.findFirst({
            where: { customerno: custKey, etd: 'thai-bill', status: 'pending' },
          })
          if (existing) {
            await prisma.invoiceSent.update({
              where: { id: existing.id },
              data: {
                contactId: contact.id,
                amount: Number(totalAmount),
              },
            })
            console.log(`[THAI-BILL] Updated existing invoice log: ${prefix} ฿${totalAmount}`)
          } else {
            await prisma.invoiceSent.create({
              data: {
                contactId: contact.id,
                customerno: custKey,
                etd: 'thai-bill',
                amount: Number(totalAmount),
                status: 'pending',
              },
            })
            console.log(`[THAI-BILL] Saved new invoice log: ${prefix} ฿${totalAmount}`)
          }
        } catch (err: any) {
          console.error(`[THAI-BILL] Failed to save invoice log:`, err.message)
        }
      }
    }

    const failedSteps = results.filter(r => r.status === 'failed')
    return NextResponse.json({
      success: sentCount > 0,
      customerno,
      contactName: contact.name,
      platform: contact.platform,
      results,
      failedSteps,
    })
  } catch (error: any) {
    console.error('[THAI-BILL] Error:', error)
    return NextResponse.json({ error: 'Internal error', details: error.message }, { status: 500 })
  }
}
