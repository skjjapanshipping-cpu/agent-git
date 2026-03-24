import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { sendFacebookMessage, sendFacebookImage, sendFacebookFile } from '@/lib/platforms/facebook'
import { sendLineMessage, sendLineImage, sendLineFile } from '@/lib/platforms/line'

const API_KEY = process.env.INVOICE_API_KEY || 'skjchat-invoice-2026'

// POST — Send invoice (text + PDF + QR) to a customer via chat
export async function POST(req: NextRequest) {
  try {
    // Auth check
    const authHeader = req.headers.get('x-api-key')
    if (authHeader !== API_KEY) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 })
    }

    const body = await req.json()
    const {
      customerno,
      etd,
      itemCount,
      totalAmount,
      pickupDate,
      pickupTime,
      pdfUrl,
      qrImageUrl,
      extraMessage,
      shippingIds,
      messageTemplate,
    } = body

    if (!customerno) {
      return NextResponse.json({ error: 'customerno is required' }, { status: 400 })
    }

    // Find contact by exact customerno match (case-insensitive)
    // Supports: "ANW-500-BiRD", "ANW-500 Mei", "ANW-501 / ANW-622" (multiple codes in one chat)
    const prefix = customerno.toUpperCase()

    // Strategy 1: startsWith (covers most cases like "ANW-500-Bird")
    // Strategy 2: contains (covers "ANW-501 / ANW-622" where code is not at the start)
    const candidates = await prisma.contact.findMany({
      where: {
        name: { contains: prefix },
      },
      include: {
        conversations: {
          where: { status: 'open' },
          orderBy: { lastMessageAt: 'desc' },
          take: 1,
        },
      },
    })

    // Filter: customerno must appear as a whole token in the name
    // Before it: start of string, space, /, or other separator
    // After it: end of string, space, /, -, _ or other separator
    // This prevents "ANW-5" from matching "ANW-50-Bird"
    const contacts = candidates.filter(c => {
      const name = c.name.toUpperCase()
      const idx = name.indexOf(prefix)
      if (idx === -1) return false
      // Check char before: must be start of string or separator
      if (idx > 0) {
        const before = name[idx - 1]
        if (before !== ' ' && before !== '/' && before !== ',' && before !== '(' && before !== '_') return false
      }
      // Check char after: must be end of string or separator
      const afterIdx = idx + prefix.length
      if (afterIdx < name.length) {
        const after = name[afterIdx]
        if (after !== '-' && after !== ' ' && after !== '/' && after !== ',' && after !== ')' && after !== '_') return false
      }
      return true
    })

    if (contacts.length === 0) {
      return NextResponse.json({
        error: 'Contact not found',
        customerno,
        message: `ไม่พบลูกค้า ${prefix} ในระบบแชท (ลูกค้ายังไม่เคยทักแชท)`,
      }, { status: 404 })
    }

    // Sort by most recent conversation activity to pick the best match
    const contact = contacts.sort((a, b) => {
      const aTime = a.conversations[0]?.lastMessageAt?.getTime() || 0
      const bTime = b.conversations[0]?.lastMessageAt?.getTime() || 0
      return bTime - aTime
    })[0]

    // Find or create conversation
    let conversation = contact.conversations[0]
    if (!conversation) {
      conversation = await prisma.conversation.create({
        data: {
          contactId: contact.id,
          platform: contact.platform,
          status: 'open',
        },
      })
    }

    const platformId = contact.platformId.replace(/^group:/, '')
    const baseUrl = process.env.NEXTAUTH_URL || 'https://chat.skjjapanshipping.com'
    const results: { step: string; status: string; error?: string }[] = []

    // --- 1. Send text message ---
    const etdFormatted = etd || '-'
    const formattedCount = String(itemCount ?? '-')
    const formattedTotal = totalAmount != null ? Number(totalAmount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) : '-'

    // Use admin-edited template if provided, replacing placeholders per customer
    let text: string
    if (messageTemplate) {
      text = messageTemplate
        .replace(/\{\{จำนวน\}\}/g, formattedCount)
        .replace(/\{\{รวม\}\}/g, formattedTotal)
    } else {
      // Fallback: old hardcoded template
      text = `✨ขออนุญาตแจ้งยอดนะครับ
🚢ค่านำเข้า (รอบปิดตู้ ${etdFormatted})
📌จำนวน: ${formattedCount} ชิ้น
รวม: ${formattedTotal} บาท

📍พร้อมให้เข้ารับเอง/เรียกแมส ได้วัน${pickupDate || '-'} ตั้งแต่ เวลา ${pickupTime || '-'}

📍จัดส่งในไทยแจ้งที่อยู่จัดส่งผ่านระบบได้เลยครับ

*‼️ลูกค้าที่ต้องการส่งในไทย รบกวนชำระค่านำเข้าแยกกับค่าส่งในไทยนะครับ🙏

🙏🏻 ขอบคุณครับผม 🙏`
    }

    try {
      if (contact.platform === 'facebook') {
        await sendFacebookMessage(platformId, text)
      } else if (contact.platform === 'line') {
        await sendLineMessage(platformId, text)
      }
      // Save to DB
      await prisma.message.create({
        data: {
          conversationId: conversation.id,
          direction: 'outbound',
          type: 'text',
          content: text,
          status: 'sent',
        },
      })
      results.push({ step: 'text', status: 'sent' })
    } catch (err: any) {
      console.error(`[INVOICE] Text send failed for ${customerno}:`, err.message)
      results.push({ step: 'text', status: 'failed', error: err.message })
    }

    // --- 2. Send PDF invoice ---
    if (pdfUrl) {
      try {
        const fullPdfUrl = pdfUrl.startsWith('http') ? pdfUrl : `${baseUrl}${pdfUrl}`
        const pdfFileName = `invoice-${prefix}-${etd || 'unknown'}.pdf`

        if (contact.platform === 'facebook') {
          await sendFacebookFile(platformId, fullPdfUrl)
        } else if (contact.platform === 'line') {
          await sendLineFile(platformId, fullPdfUrl, pdfFileName)
        }
        // Save to DB
        await prisma.message.create({
          data: {
            conversationId: conversation.id,
            direction: 'outbound',
            type: 'file',
            content: pdfUrl,
            metadata: JSON.stringify({ fileName: pdfFileName }),
            status: 'sent',
          },
        })
        results.push({ step: 'pdf', status: 'sent' })
      } catch (err: any) {
        console.error(`[INVOICE] PDF send failed for ${customerno}:`, err.message)
        results.push({ step: 'pdf', status: 'failed', error: err.message })
      }
    }

    // --- 3. Send QR payment image ---
    if (qrImageUrl) {
      try {
        const fullQrUrl = qrImageUrl.startsWith('http') ? qrImageUrl : `${baseUrl}${qrImageUrl}`

        // Validate URL before sending
        if (!/^https?:\/\/.+\..+/.test(fullQrUrl)) {
          throw new Error(`Invalid QR URL: ${fullQrUrl}`)
        }

        if (contact.platform === 'facebook') {
          await sendFacebookImage(platformId, fullQrUrl)
        } else if (contact.platform === 'line') {
          await sendLineImage(platformId, fullQrUrl)
        }
        // Save to DB
        await prisma.message.create({
          data: {
            conversationId: conversation.id,
            direction: 'outbound',
            type: 'image',
            content: qrImageUrl,
            status: 'sent',
          },
        })
        results.push({ step: 'qr', status: 'sent' })
      } catch (err: any) {
        console.error(`[INVOICE] QR send failed for ${customerno}:`, err.message)
        results.push({ step: 'qr', status: 'failed', error: err.message })
      }
    }

    // Step 5: Send extra message (if provided)
    if (extraMessage) {
      try {
        if (contact.platform === 'facebook') {
          await sendFacebookMessage(contact.platformId, extraMessage)
        } else {
          await sendLineMessage(contact.platformId, extraMessage)
        }
        await prisma.message.create({
          data: {
            conversationId: conversation.id,
            direction: 'outbound',
            type: 'text',
            content: extraMessage,
            status: 'sent',
          },
        })
        results.push({ step: 'extra_msg', status: 'sent' })
      } catch (err: any) {
        console.error(`[INVOICE] Extra message failed for ${customerno}:`, err.message)
        results.push({ step: 'extra_msg', status: 'failed', error: err.message })
      }
    }

    // Update conversation lastMessage
    const sentCount = results.filter(r => r.status === 'sent').length
    if (sentCount > 0) {
      await prisma.conversation.update({
        where: { id: conversation.id },
        data: {
          lastMessage: `📄 ใบแจ้งหนี้ รอบปิดตู้ ${etdFormatted}`,
          lastMessageAt: new Date(),
          status: 'open',
        },
      })

      // Save invoice record for slip-to-invoice matching (upsert: update if pending exists)
      if (totalAmount != null && totalAmount > 0) {
        try {
          const custKey = prefix.toLowerCase()
          const etdKey = etd || ''
          // Check if a pending record already exists for this customer+etd
          const existing = await prisma.invoiceSent.findFirst({
            where: { customerno: custKey, etd: etdKey, status: 'pending' },
          })
          if (existing) {
            await prisma.invoiceSent.update({
              where: { id: existing.id },
              data: {
                contactId: contact.id,
                amount: Number(totalAmount),
                shippingIds: shippingIds ? JSON.stringify(shippingIds) : null,
              },
            })
            console.log(`[INVOICE] Updated existing invoice log: ${prefix} ฿${totalAmount}`)
          } else {
            await prisma.invoiceSent.create({
              data: {
                contactId: contact.id,
                customerno: custKey,
                etd: etdKey,
                amount: Number(totalAmount),
                shippingIds: shippingIds ? JSON.stringify(shippingIds) : null,
                status: 'pending',
              },
            })
            console.log(`[INVOICE] Saved new invoice log: ${prefix} ฿${totalAmount}`)
          }
        } catch (err: any) {
          console.error(`[INVOICE] Failed to save invoice log:`, err.message)
        }
      }
    }

    const failedSteps = results.filter(r => r.status === 'failed')
    const allFailed = sentCount === 0 && results.length > 0

    return NextResponse.json({
      success: sentCount > 0,
      customerno,
      contactName: contact.name,
      platform: contact.platform,
      results,
      failedSteps,
      warning: failedSteps.length > 0 && sentCount > 0
        ? `บางรายการส่งไม่สำเร็จ: ${failedSteps.map(f => f.step).join(', ')}`
        : undefined,
      error: allFailed
        ? `ส่งไม่สำเร็จทุกรายการ: ${failedSteps.map(f => f.error || f.step).join('; ')}`
        : undefined,
    })
  } catch (error: any) {
    console.error('[INVOICE] Error:', error)
    return NextResponse.json({ error: 'Internal error', details: error.message }, { status: 500 })
  }
}
