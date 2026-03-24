import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { sendFacebookMessage, sendFacebookImage, sendFacebookFile } from '@/lib/platforms/facebook'
import { sendLineMessage, sendLineImage, sendLineFile, pushLineMessages } from '@/lib/platforms/line'

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
      flexMessages,
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

    const etdFormatted = etd || '-'
    const formattedCount = String(itemCount ?? '-')
    const formattedTotal = totalAmount != null ? Number(totalAmount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) : '-'

    // === LINE + flexMessages: send Flex Message card ===
    if (contact.platform === 'line' && flexMessages && Array.isArray(flexMessages) && flexMessages.length > 0) {
      try {
        const flexResult = await pushLineMessages(platformId, flexMessages)
        // Extract quoteToken from LINE response
        const flexQuoteToken = flexResult?.sentMessages?.[0]?.quoteToken || null

        // Build summary text + card metadata for SKJ Chat DB
        let summaryText: string
        if (messageTemplate) {
          summaryText = messageTemplate
            .replace(/\{\{\u0E08\u0E33\u0E19\u0E27\u0E19\}\}/g, formattedCount)
            .replace(/\{\{\u0E23\u0E27\u0E21\}\}/g, formattedTotal)
        } else {
          summaryText = `\u{1F6A2} \u0E04\u0E48\u0E32\u0E19\u0E33\u0E40\u0E02\u0E49\u0E32 (\u0E23\u0E2D\u0E1A\u0E1B\u0E34\u0E14\u0E15\u0E39\u0E49 ${etdFormatted})\n\u0E08\u0E33\u0E19\u0E27\u0E19: ${formattedCount} \u0E0A\u0E34\u0E49\u0E19\n\u0E23\u0E27\u0E21: \u0E3F${formattedTotal}`
        }

        const cardMetaObj: any = {
          invoiceCard: true,
          cardType: 'import',
          customerno: prefix,
          totalAmount: totalAmount != null ? Number(totalAmount) : null,
          itemCount: itemCount != null ? Number(itemCount) : null,
          etd: etdFormatted,
          pdfUrl: pdfUrl || null,
          qrUrl: qrImageUrl || 'https://chat.skjjapanshipping.com/uploads/qr-payment.jpg',
        }
        if (flexQuoteToken) cardMetaObj.quoteToken = flexQuoteToken
        const cardMeta = JSON.stringify(cardMetaObj)

        await prisma.message.create({
          data: { conversationId: conversation.id, direction: 'outbound', type: 'text', content: summaryText, metadata: cardMeta, status: 'sent' },
        })
        results.push({ step: 'flex', status: 'sent' })
      } catch (err: any) {
        console.error(`[INVOICE] Flex send failed for ${customerno}:`, err.message)
        results.push({ step: 'flex', status: 'failed', error: err.message })
      }

      // Send extra message if provided (separate push)
      if (extraMessage) {
        try {
          const extraResult = await pushLineMessages(platformId, [{ type: 'text', text: extraMessage }])
          const extraQt = extraResult?.sentMessages?.[0]?.quoteToken || null
          await prisma.message.create({
            data: { conversationId: conversation.id, direction: 'outbound', type: 'text', content: extraMessage, metadata: extraQt ? JSON.stringify({ quoteToken: extraQt }) : null, status: 'sent' },
          })
          results.push({ step: 'extra_msg', status: 'sent' })
        } catch (err: any) {
          console.error(`[INVOICE] Extra message failed for ${customerno}:`, err.message)
          results.push({ step: 'extra_msg', status: 'failed', error: err.message })
        }
      }
    } else {
      // === Facebook or no flexMessages: send text + PDF + QR separately ===

      // Use admin-edited template if provided
      let text: string
      if (messageTemplate) {
        text = messageTemplate
          .replace(/\{\{\u0E08\u0E33\u0E19\u0E27\u0E19\}\}/g, formattedCount)
          .replace(/\{\{\u0E23\u0E27\u0E21\}\}/g, formattedTotal)
      } else {
        text = `\u2728\u0E02\u0E2D\u0E2D\u0E19\u0E38\u0E0D\u0E32\u0E15\u0E41\u0E08\u0E49\u0E07\u0E22\u0E2D\u0E14\u0E19\u0E30\u0E04\u0E23\u0E31\u0E1A\n\u{1F6A2}\u0E04\u0E48\u0E32\u0E19\u0E33\u0E40\u0E02\u0E49\u0E32 (\u0E23\u0E2D\u0E1A\u0E1B\u0E34\u0E14\u0E15\u0E39\u0E49 ${etdFormatted})\n\u{1F4CC}\u0E08\u0E33\u0E19\u0E27\u0E19: ${formattedCount} \u0E0A\u0E34\u0E49\u0E19\n\u0E23\u0E27\u0E21: ${formattedTotal} \u0E1A\u0E32\u0E17\n\n\u{1F4CD}\u0E1E\u0E23\u0E49\u0E2D\u0E21\u0E43\u0E2B\u0E49\u0E40\u0E02\u0E49\u0E32\u0E23\u0E31\u0E1A\u0E40\u0E2D\u0E07/\u0E40\u0E23\u0E35\u0E22\u0E01\u0E41\u0E21\u0E2A \u0E44\u0E14\u0E49\u0E27\u0E31\u0E19${pickupDate || '-'} \u0E15\u0E31\u0E49\u0E07\u0E41\u0E15\u0E48 \u0E40\u0E27\u0E25\u0E32 ${pickupTime || '-'}\n\n\u{1F4CD}\u0E08\u0E31\u0E14\u0E2A\u0E48\u0E07\u0E43\u0E19\u0E44\u0E17\u0E22\u0E41\u0E08\u0E49\u0E07\u0E17\u0E35\u0E48\u0E2D\u0E22\u0E39\u0E48\u0E08\u0E31\u0E14\u0E2A\u0E48\u0E07\u0E1C\u0E48\u0E32\u0E19\u0E23\u0E30\u0E1A\u0E1A\u0E44\u0E14\u0E49\u0E40\u0E25\u0E22\u0E04\u0E23\u0E31\u0E1A\n\n*\u203C\uFE0F\u0E25\u0E39\u0E01\u0E04\u0E49\u0E32\u0E17\u0E35\u0E48\u0E15\u0E49\u0E2D\u0E07\u0E01\u0E32\u0E23\u0E2A\u0E48\u0E07\u0E43\u0E19\u0E44\u0E17\u0E22 \u0E23\u0E1A\u0E01\u0E27\u0E19\u0E0A\u0E33\u0E23\u0E30\u0E04\u0E48\u0E32\u0E19\u0E33\u0E40\u0E02\u0E49\u0E32\u0E41\u0E22\u0E01\u0E01\u0E31\u0E1A\u0E04\u0E48\u0E32\u0E2A\u0E48\u0E07\u0E43\u0E19\u0E44\u0E17\u0E22\u0E19\u0E30\u0E04\u0E23\u0E31\u0E1A\u{1F64F}\n\n\u{1F64F}\u{1F3FB} \u0E02\u0E2D\u0E1A\u0E04\u0E38\u0E13\u0E04\u0E23\u0E31\u0E1A\u0E1C\u0E21 \u{1F64F}`
      }

      try {
        let textQt: string | null = null
        if (contact.platform === 'facebook') {
          await sendFacebookMessage(platformId, text)
        } else if (contact.platform === 'line') {
          const textResult = await sendLineMessage(platformId, text)
          textQt = textResult?.sentMessages?.[0]?.quoteToken || null
        }
        await prisma.message.create({
          data: { conversationId: conversation.id, direction: 'outbound', type: 'text', content: text, metadata: textQt ? JSON.stringify({ quoteToken: textQt }) : null, status: 'sent' },
        })
        results.push({ step: 'text', status: 'sent' })
      } catch (err: any) {
        console.error(`[INVOICE] Text send failed for ${customerno}:`, err.message)
        results.push({ step: 'text', status: 'failed', error: err.message })
      }

      // PDF
      if (pdfUrl) {
        try {
          const fullPdfUrl = pdfUrl.startsWith('http') ? pdfUrl : `${baseUrl}${pdfUrl}`
          const pdfFileName = `invoice-${prefix}-${etd || 'unknown'}.pdf`
          let pdfQt: string | null = null
          if (contact.platform === 'facebook') {
            await sendFacebookFile(platformId, fullPdfUrl)
          } else if (contact.platform === 'line') {
            const pdfResult = await sendLineFile(platformId, fullPdfUrl, pdfFileName)
            pdfQt = pdfResult?.sentMessages?.[0]?.quoteToken || null
          }
          const pdfMeta: any = { fileName: pdfFileName }
          if (pdfQt) pdfMeta.quoteToken = pdfQt
          await prisma.message.create({
            data: { conversationId: conversation.id, direction: 'outbound', type: 'file', content: pdfUrl, metadata: JSON.stringify(pdfMeta), status: 'sent' },
          })
          results.push({ step: 'pdf', status: 'sent' })
        } catch (err: any) {
          console.error(`[INVOICE] PDF send failed for ${customerno}:`, err.message)
          results.push({ step: 'pdf', status: 'failed', error: err.message })
        }
      }

      // QR
      if (qrImageUrl) {
        try {
          const fullQrUrl = qrImageUrl.startsWith('http') ? qrImageUrl : `${baseUrl}${qrImageUrl}`
          if (!/^https?:\/\/.+\..+/.test(fullQrUrl)) throw new Error(`Invalid QR URL: ${fullQrUrl}`)
          let qrQt: string | null = null
          if (contact.platform === 'facebook') {
            await sendFacebookImage(platformId, fullQrUrl)
          } else if (contact.platform === 'line') {
            const qrResult = await sendLineImage(platformId, fullQrUrl)
            qrQt = qrResult?.sentMessages?.[0]?.quoteToken || null
          }
          await prisma.message.create({
            data: { conversationId: conversation.id, direction: 'outbound', type: 'image', content: qrImageUrl, metadata: qrQt ? JSON.stringify({ quoteToken: qrQt }) : null, status: 'sent' },
          })
          results.push({ step: 'qr', status: 'sent' })
        } catch (err: any) {
          console.error(`[INVOICE] QR send failed for ${customerno}:`, err.message)
          results.push({ step: 'qr', status: 'failed', error: err.message })
        }
      }

      // Extra message
      if (extraMessage) {
        try {
          let extraQt2: string | null = null
          if (contact.platform === 'facebook') {
            await sendFacebookMessage(contact.platformId, extraMessage)
          } else {
            const extraResult2 = await sendLineMessage(contact.platformId, extraMessage)
            extraQt2 = extraResult2?.sentMessages?.[0]?.quoteToken || null
          }
          await prisma.message.create({
            data: { conversationId: conversation.id, direction: 'outbound', type: 'text', content: extraMessage, metadata: extraQt2 ? JSON.stringify({ quoteToken: extraQt2 }) : null, status: 'sent' },
          })
          results.push({ step: 'extra_msg', status: 'sent' })
        } catch (err: any) {
          console.error(`[INVOICE] Extra message failed for ${customerno}:`, err.message)
          results.push({ step: 'extra_msg', status: 'failed', error: err.message })
        }
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
