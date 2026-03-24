import { NextRequest, NextResponse } from 'next/server'
import { prisma, prismaRetry } from '@/lib/prisma'
import { detectAndSaveOrders, detectBidPrice } from '@/lib/order-service'
import { handleAutoReply } from '@/lib/auto-reply'
import { notifyClients, notifyTyping } from '@/lib/sse'
import { verifySlip, verifySlipFromFile } from '@/lib/thunder'
import axios from 'axios'
import * as fs from 'fs'
import * as path from 'path'
import { sendFacebookMessage, sendFacebookGenericTemplate } from '@/lib/platforms/facebook'
import { pushLineMessages } from '@/lib/platforms/line'

// GET — Facebook webhook verification
export async function GET(req: NextRequest) {
  const searchParams = req.nextUrl.searchParams
  const mode = searchParams.get('hub.mode')
  const token = searchParams.get('hub.verify_token')
  const challenge = searchParams.get('hub.challenge')

  if (mode === 'subscribe' && token === process.env.FACEBOOK_VERIFY_TOKEN) {
    return new NextResponse(challenge, { status: 200 })
  }
  return NextResponse.json({ error: 'Forbidden' }, { status: 403 })
}

// POST — Receive messages from Facebook Messenger
export async function POST(req: NextRequest) {
  try {
    const body = await req.json()

    if (body.object !== 'page') {
      return NextResponse.json({ status: 'ignored' })
    }

    // Collect all events
    const allEvents: any[] = []
    for (const entry of body.entry || []) {
      for (const event of entry.messaging || []) {
        allEvents.push(event)
      }
    }
    console.log(`[FB webhook] received ${allEvents.length} events`)

    // Return 200 IMMEDIATELY — process events in background
    processFbEvents(allEvents).catch(err => console.error('[FB] bg processing error:', err))

    return NextResponse.json({ status: 'ok' })
  } catch (error) {
    console.error('Facebook webhook error:', error)
    return NextResponse.json({ error: 'Internal error' }, { status: 500 })
  }
}

async function processFbEvents(events: any[]) {
  for (const event of events) {
    const evtType = event.read ? 'read' : event.message ? 'message' : event.delivery ? 'delivery' : 'other'
    try { await prisma.webhookLog.create({ data: { platform: 'facebook', eventType: evtType, payload: JSON.stringify(event) } }) } catch {}
    try {
      await prismaRetry(async () => {
        if (event.read) {
          await handleReadReceipt(event)
        } else if (event.message && event.message.is_echo) {
          await handleEchoMessage(event)
        } else if (event.message && !event.message.is_echo) {
          await handleIncomingMessage(event)
        }
      })
    } catch (e) {
      console.error('[FB handler error]', e)
    }
  }
}

async function handleReadReceipt(event: any) {
  const senderId = event.sender.id
  const watermark = event.read.watermark // timestamp in ms — all messages before this are read

  try {
    const contact = await prisma.contact.findUnique({
      where: { platform_platformId: { platform: 'facebook', platformId: senderId } },
    })
    if (!contact) return

    const conversation = await prisma.conversation.findFirst({
      where: { contactId: contact.id, platform: 'facebook', status: 'open' },
    })
    if (!conversation) return

    // Mark all outbound messages sent before watermark as "read"
    const watermarkDate = new Date(watermark)
    const result = await prisma.message.updateMany({
      where: {
        conversationId: conversation.id,
        direction: 'outbound',
        status: { not: 'read' },
        createdAt: { lte: watermarkDate },
      },
      data: { status: 'read' },
    })

    // Push SSE event so UI updates read status in real-time
    if (result.count > 0) {
      try { notifyClients('read_receipt', { conversationId: conversation.id }) } catch {}
    }
  } catch (e) {
    console.error('FB read receipt error:', e)
  }
}

async function handleEchoMessage(event: any) {
  // Echo = message sent FROM the page (via Meta Business Suite, API, etc.)
  // recipient.id = the customer's PSID
  const recipientId = event.recipient.id
  const message = event.message
  const text = message.text || ''
  const attachments = message.attachments || []

  // Find the contact by recipient ID (the customer we sent to)
  const contact = await prisma.contact.findUnique({
    where: { platform_platformId: { platform: 'facebook', platformId: recipientId } },
  })
  if (!contact) {
    console.log('[FB echo] No contact found for recipient:', recipientId)
    return
  }

  // Find open conversation
  const conversation = await prisma.conversation.findFirst({
    where: { contactId: contact.id, platform: 'facebook', status: 'open' },
  })
  if (!conversation) {
    console.log('[FB echo] No open conversation for contact:', contact.name)
    return
  }

  // Check for duplicate (same platformMsgId)
  if (message.mid) {
    const existing = await prisma.message.findFirst({
      where: { conversationId: conversation.id, platformMsgId: message.mid },
    })
    if (existing) return // Already saved (sent from SKJ Chat)
  }

  // Determine message type and content
  let msgType = 'text'
  let content = text

  if (attachments.length > 0) {
    const att = attachments[0]
    if (att.type === 'image') {
      msgType = 'image'
      content = att.payload?.url || ''
    } else if (att.type === 'template') {
      msgType = 'text'
      content = text || '(template message)'
    } else {
      msgType = att.type || 'file'
      content = att.payload?.url || text || '(attachment)'
    }
  }

  if (!content) return

  // Save as outbound message
  await prisma.message.create({
    data: {
      conversationId: conversation.id,
      direction: 'outbound',
      type: msgType,
      content,
      platformMsgId: message.mid,
      status: 'sent',
      metadata: JSON.stringify({ source: 'meta_business_suite', isEcho: true }),
    },
  })

  // Update conversation
  await prisma.conversation.update({
    where: { id: conversation.id },
    data: {
      lastMessage: content.substring(0, 200),
      lastMessageAt: new Date(),
    },
  })

  // Push SSE update
  try {
    notifyClients('new_message', {
      conversationId: conversation.id,
      contactName: contact.name,
      platform: 'facebook',
      type: msgType,
      content: msgType === 'image' ? '📷 รูปภาพ' : content.substring(0, 100),
      direction: 'outbound',
    })
  } catch {}

  console.log('[FB echo] Saved outbound message from Meta Business Suite for', contact.name)
}

async function fetchFbProfile(senderId: string): Promise<{ name: string; avatar: string | null }> {
  const token = process.env.FACEBOOK_PAGE_ACCESS_TOKEN
  let name = 'Facebook User'
  let avatar: string | null = null

  // Method 1: User Profile API (works for most users)
  try {
    const profileRes = await fetch(
      `https://graph.facebook.com/v19.0/${senderId}?fields=name,profile_pic&access_token=${token}`
    )
    const profile = await profileRes.json()
    if (profile.name) {
      name = profile.name
      avatar = profile.profile_pic || null
      return { name, avatar }
    }
  } catch (e) {
    console.log('[FB] Profile API failed for', senderId)
  }

  // Method 2: Conversations API fallback (works when Profile API is restricted)
  try {
    const convRes = await fetch(
      `https://graph.facebook.com/v19.0/me/conversations?user_id=${senderId}&fields=participants&access_token=${token}`
    )
    const convData = await convRes.json()
    const conversations = convData?.data || []
    for (const conv of conversations) {
      const participants = conv?.participants?.data || []
      const user = participants.find((p: any) => p.id === senderId)
      if (user?.name) {
        name = user.name
        console.log('[FB] Got name from Conversations API:', name)
        break
      }
    }
  } catch (e) {
    console.log('[FB] Conversations API fallback also failed for', senderId)
  }

  return { name, avatar }
}

async function handleIncomingMessage(event: any) {
  const senderId = event.sender.id
  const message = event.message
  const text = message.text || ''
  const attachments = message.attachments || []

  // Skip Facebook Commerce / system-generated messages
  if (attachments.length > 0) {
    const attTypes = attachments.map((a: any) => a.type)
    // template = Share address, Confirm payment, Payment confirmed, etc.
    // fallback = Facebook built-in feature cards
    if (attTypes.includes('template') || attTypes.includes('fallback')) {
      console.log('[FB] Skipping commerce/template message from', senderId, '- types:', attTypes.join(','))
      return
    }
  }
  // Skip known Facebook Commerce text patterns
  if (text && /^(Tap .*(Confirm|confirm)|Share address|Payment confirmed|Shared with)/i.test(text)) {
    console.log('[FB] Skipping commerce text from', senderId, ':', text.substring(0, 60))
    return
  }

  // Find or create contact (track if new for auto-reply)
  let isNewContact = false
  let contact = await prisma.contact.findUnique({
    where: { platform_platformId: { platform: 'facebook', platformId: senderId } },
  })

  if (!contact) {
    isNewContact = true
    // Fetch profile from Facebook
    const { name, avatar } = await fetchFbProfile(senderId)

    contact = await prisma.contact.create({
      data: {
        name,
        avatar,
        platform: 'facebook',
        platformId: senderId,
      },
    })
  } else {
    // Refresh name (only if still default) or avatar (if missing)
    if (contact.name === 'Facebook User' || !contact.avatar) {
      const { name, avatar } = await fetchFbProfile(senderId)
      const updateData: any = {}
      // Only update name if still default "Facebook User" — never overwrite admin rename
      if (contact.name === 'Facebook User' && name !== 'Facebook User') {
        updateData.name = name
      }
      // Only update avatar if missing
      if (!contact.avatar && avatar) {
        updateData.avatar = avatar
      }
      if (Object.keys(updateData).length > 0) {
        contact = await prisma.contact.update({
          where: { id: contact.id },
          data: updateData,
        })
      }
    } else {
      // Just refresh avatar
      try {
        const profileRes = await fetch(
          `https://graph.facebook.com/v19.0/${senderId}?fields=profile_pic&access_token=${process.env.FACEBOOK_PAGE_ACCESS_TOKEN}`
        )
        const profile = await profileRes.json()
        const newAvatar = profile.profile_pic || null
        if (newAvatar && newAvatar !== contact.avatar) {
          contact = await prisma.contact.update({
            where: { id: contact.id },
            data: { avatar: newAvatar },
          })
        }
      } catch (e) {
        // Non-critical
      }
    }
  }

  // Find or create conversation
  let conversation = await prisma.conversation.findFirst({
    where: { contactId: contact.id, platform: 'facebook', status: 'open' },
  })

  if (!conversation) {
    conversation = await prisma.conversation.create({
      data: {
        contactId: contact.id,
        platform: 'facebook',
        status: 'open',
      },
    })
  }

  // Determine message type and content
  let msgType = 'text'
  let content = text

  if (attachments.length > 0) {
    const att = attachments[0]
    if (att.type === 'image') {
      msgType = 'image'
      content = att.payload?.url || ''
    } else if (att.type === 'sticker') {
      msgType = 'sticker'
      content = att.payload?.url || '(sticker)'
    } else {
      msgType = att.type || 'file'
      content = att.payload?.url || text || '(attachment)'
    }
  }

  // Push typing indicator before saving
  try { notifyTyping(conversation.id, contact.name) } catch {}

  // Save message
  await prisma.message.create({
    data: {
      conversationId: conversation.id,
      direction: 'inbound',
      type: msgType,
      content,
      platformMsgId: message.mid,
      metadata: attachments.length > 0 ? JSON.stringify(attachments) : undefined,
    },
  })

  // Update conversation
  await prisma.conversation.update({
    where: { id: conversation.id },
    data: {
      lastMessage: content.substring(0, 200),
      lastMessageAt: new Date(),
      unreadCount: { increment: 1 },
    },
  })

  // Push real-time SSE event
  try {
    notifyClients('new_message', {
      conversationId: conversation.id,
      contactName: contact.name,
      platform: 'facebook',
      type: msgType,
      content: msgType === 'image' ? '📷 รูปภาพ' : content.substring(0, 100),
    })
  } catch {}

  // Auto-reply (welcome for new contacts, keyword matching, off-hours)
  if (msgType === 'text') {
    handleAutoReply(senderId, content, isNewContact, conversation.id, undefined, 'facebook').catch(err =>
      console.error('[FB] auto-reply error:', err)
    )
  } else if (isNewContact) {
    // New contact sent image/sticker first — still send welcome
    handleAutoReply(senderId, '', true, conversation.id, undefined, 'facebook').catch(err =>
      console.error('[FB] auto-reply welcome error:', err)
    )
  }

  // Detect product URLs and create purchase orders
  if (msgType === 'text' && content) {
    detectAndSaveOrders(content, contact.id, conversation.id).catch(err =>
      console.error('URL detection error (Facebook):', err)
    )
    // Detect bid price in separate message (e.g. "5000เยน")
    detectBidPrice(content, contact.id).catch(err =>
      console.error('Bid price detection error (Facebook):', err)
    )
  }

  // Verify slip when customer sends an image
  if (msgType === 'image' && content) {
    notifyFbSlip(contact.name, content, senderId, conversation.id).catch(err =>
      console.error('[FB] slip verify error:', err)
    )
  }
}

// ===== FACEBOOK SLIP VERIFICATION (Thunder API) =====
const VALID_ACCOUNTS: Array<{
  nameTh: string
  nameEn: string
  accounts: string[]
}> = [
  {
    nameTh: 'อนุวัตร',
    nameEn: 'ANUWAT',
    accounts: [
      '9940',           // PromptPay เบอร์ 0824609940
      '9145533',        // แม่มณี 014000009145533
      '2642440375',     // SCB
      '020425695911',   // ออมสิน
      '5695911',        // ออมสิน (สั้น)
      '0173811063',     // KBANK
    ],
  },
  {
    nameTh: 'นัทธี',
    nameEn: 'NATTEE',
    accounts: [
      '6637136786',     // KTB
    ],
  },
]
const MIN_AMOUNT = 0  // ไม่มียอดขั้นต่ำ
const MAX_SLIP_AGE_MINUTES = 15

async function notifyFbSlip(customerName: string, imageUrl: string, fbSenderId: string, conversationId: string) {
  const appUrl = process.env.NEXTAUTH_URL || process.env.NEXT_PUBLIC_APP_URL || 'https://chat.skjjapanshipping.com'
  const fullImageUrl = imageUrl.startsWith('http') ? imageUrl : `${appUrl}${imageUrl}`

  // Step 1: Download FB image locally and use file upload first (more reliable)
  let thunderResult: any = null
  let tmpPath = ''
  try {
    const uploadsDir = path.join(process.cwd(), 'public', 'uploads')
    if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true })
    const tmpFile = `fb-slip-${Date.now()}.jpg`
    tmpPath = path.join(uploadsDir, tmpFile)
    const imgRes = await axios.get(fullImageUrl, { responseType: 'arraybuffer', timeout: 10000 })
    fs.writeFileSync(tmpPath, imgRes.data)
    console.log('[FB Thunder] Downloaded FB image to', tmpFile, '- size:', imgRes.data.length)
    thunderResult = await verifySlipFromFile(`/uploads/${tmpFile}`)
    console.log('[FB Thunder] file result for', customerName, ':', thunderResult?.success, thunderResult?.error)
  } catch (dlErr: any) {
    console.error('[FB Thunder] File download/verify error:', dlErr.message)
  }

  // Step 1b: Fallback to URL-based if file upload didn't work
  if (!thunderResult?.success && thunderResult?.error !== 'quota') {
    try {
      const urlResult = await verifySlip(fullImageUrl)
      if (urlResult?.success || (!thunderResult || urlResult?.error !== thunderResult?.error)) {
        thunderResult = urlResult
      }
      console.log('[FB Thunder] URL fallback for', customerName, ':', thunderResult?.success, thunderResult?.error)
    } catch (err: any) {
      console.error('[FB Thunder] URL verify error:', err.message)
    }
  }

  // Clean up temp file
  if (tmpPath) { try { fs.unlinkSync(tmpPath) } catch (_) {} }

  const isSlip = thunderResult?.success === true && thunderResult?.data?.rawSlip

  if (!isSlip) {
    const err = thunderResult?.error
    if (err === 'quota') {
      console.log('[FB NOTIFY] Thunder quota exceeded for', customerName)
      try {
        await prisma.message.create({
          data: {
            conversationId, direction: 'outbound', type: 'text',
            content: `⚡ โควต้า Thunder หมด — ตรวจสลิปไม่ได้ กรุณาเติมโควต้า`,
            status: 'sent',
            metadata: JSON.stringify({ isSystem: true, slipVerification: true, error: 'quota' }),
          },
        })
      } catch (_) {}
      return
    }
    console.log('[FB NOTIFY] Non-slip image from', customerName, '- skipped (', err, ')')

    // === Check if customer has pending import fee invoice → notify POWER TEAM ===
    try {
      const custMatch = customerName.match(/\b(ANW-\d+)\b/i)
      if (custMatch) {
        const custNo = custMatch[1].toLowerCase()
        const thirtyDaysAgo = new Date()
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)

        const pendingImportInvoices = await prisma.invoiceSent.findMany({
          where: {
            customerno: custNo,
            status: 'pending',
            createdAt: { gte: thirtyDaysAgo },
            NOT: { etd: 'thai-bill' },
          },
          orderBy: { createdAt: 'desc' },
        })

        if (pendingImportInvoices.length > 0) {
          const inv = pendingImportInvoices[0]
          const totalAmount = pendingImportInvoices.reduce((sum: number, i: any) => sum + i.amount, 0)
          const etdDisplay = inv.etd || '-'
          const POWER_TEAM_GROUP_ID = 'C192c7f95ebcef2c3e23e70f0a3d4df54'

          console.log(`[FB NOTIFY] Import invoice pending for ${custNo.toUpperCase()} ETD=${etdDisplay} ฿${totalAmount} — notifying POWER TEAM`)

          const alertMsg = [
            `🔔 แจ้งเตือน: สลิปค่านำเข้า (Facebook)`,
            `━━━━━━━━━━━━━━━`,
            `👤 ลูกค้า: ${custNo.toUpperCase()}`,
            `📦 รอบปิดตู้: ${etdDisplay}`,
            `💰 ยอดค่านำเข้า: ฿${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`,
            ``,
            `⚠️ ระบบไม่สามารถอ่าน QR สลิปได้`,
            `กรุณาตรวจสอบสลิปผ่าน App ธนาคารโดยตรง`,
            `แล้วอัพเดทสถานะ "ชำระเงินแล้ว" ใน My Shipping หลังบ้าน`,
          ].join('\n')

          await pushLineMessages(POWER_TEAM_GROUP_ID, [{ type: 'text', text: alertMsg }])
          console.log(`[FB NOTIFY] Sent import invoice alert to POWER TEAM for ${custNo.toUpperCase()}`)
        }
      }
    } catch (alertErr: any) {
      console.error('[FB NOTIFY] Import invoice alert error:', alertErr.message)
    }

    return
  }

  // Step 2: Parse slip data
  const slip = thunderResult.data.rawSlip
  const amount = slip.amount?.amount || 0
  const amountStr = `฿${amount.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`
  const senderName = slip.sender?.account?.name?.th || 'ไม่ทราบ'
  const senderBankShort = slip.sender?.bank?.short || '-'
  const senderAccount = slip.sender?.account?.bank?.account || '-'
  const receiverName = slip.receiver?.account?.name?.th || 'ไม่ทราบ'
  const receiverNameEn = slip.receiver?.account?.name?.en || ''
  const receiverAccountNo = slip.receiver?.account?.bank?.account || ''
  const receiverProxyNo = slip.receiver?.account?.proxy?.account || ''
  const receiverProxy = receiverProxyNo
    || (slip.receiver?.account?.bank ? `${slip.receiver.account.bank.short || slip.receiver.bank?.short || ''} ${receiverAccountNo}`.trim() : '')
    || '-'
  const transRef = slip.transRef || '-'
  const slipDate = slip.date
    ? new Date(slip.date).toLocaleString('th-TH', { timeZone: 'Asia/Bangkok', day: 'numeric', month: 'short', year: '2-digit', hour: '2-digit', minute: '2-digit' })
    : '-'

  // Step 3: Validate — collect warnings
  const warnings: { icon: string; text: string; detail: string }[] = []

  // Duplicate check
  let isDuplicate = false
  if (transRef && transRef !== '-') {
    const existing = await prisma.verifiedSlip.findUnique({ where: { transRef } })
    if (existing) {
      isDuplicate = true
      warnings.push({ icon: '🚫', text: 'สลิปซ้ำ!', detail: `เคยใช้เมื่อ ${existing.createdAt.toLocaleString('th-TH', { timeZone: 'Asia/Bangkok', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}` })
    }
  }

  // Receiver check
  if (VALID_ACCOUNTS.length > 0) {
    const matchedAccount = VALID_ACCOUNTS.some(va => {
      if (va.nameTh && receiverName.includes(va.nameTh)) return true
      if (va.nameEn && receiverNameEn.toUpperCase().includes(va.nameEn.toUpperCase())) return true
      const acctStr = `${receiverAccountNo} ${receiverProxyNo}`.replace(/[\s-x]/g, '')
      if (va.accounts.some(a => acctStr.includes(a.replace(/[\s-]/g, '')))) return true
      return false
    })
    if (!matchedAccount) {
      warnings.push({ icon: '👤', text: 'ผู้รับไม่ตรง', detail: `${receiverName} ${receiverNameEn ? `(${receiverNameEn})` : ''} ${receiverAccountNo || receiverProxyNo}`.trim() })
    }
  }

  // Slip age check
  if (slip.date) {
    const diffMin = (Date.now() - new Date(slip.date).getTime()) / 60000
    if (diffMin > MAX_SLIP_AGE_MINUTES) {
      warnings.push({ icon: '⏰', text: `สลิปช้าเกิน ${MAX_SLIP_AGE_MINUTES} นาที`, detail: `ส่งมาแล้ว ${Math.round(diffMin)} นาที` })
    }
  }

  // Amount check (ปิดใช้งาน — ไม่มียอดขั้นต่ำ)
  // if (amount < MIN_AMOUNT) {
  //   warnings.push({ icon: '💰', text: 'ยอดต่ำกว่ากำหนด', detail: `ขั้นต่ำ ฿${MIN_AMOUNT} / ได้รับ ${amountStr}` })
  // }

  const hasWarnings = warnings.length > 0
  console.log('[FB NOTIFY] Slip', hasWarnings ? 'WARNING' : 'OK', ':', customerName, amountStr)

  // Step 4: Send beautiful Generic Template card with image header
  const cardStatus = isDuplicate ? 'duplicate' : hasWarnings ? 'warning' : 'ok'
  const cardParams = new URLSearchParams({
    status: cardStatus,
    amount: String(amount),
    date: slipDate,
    sn: senderName,
    sb: senderBankShort,
    sa: senderAccount,
    rn: receiverName,
    ra: receiverProxy,
    ref: transRef,
    ...(hasWarnings ? { w: warnings.map(w => w.text).join('|') } : {}),
  })
  const cardImageUrl = `${appUrl}/api/webhooks/slip-card?${cardParams.toString()}&_t=${Date.now()}`

  const cardTitle = isDuplicate
    ? `\u{1F6AB} \u0e2a\u0e25\u0e34\u0e1b\u0e0b\u0e49\u0e33! \u2014 ${amountStr}`
    : hasWarnings
      ? `\u26a0\ufe0f \u0e1e\u0e1a\u0e02\u0e49\u0e2d\u0e2a\u0e31\u0e07\u0e40\u0e01\u0e15 \u2014 ${amountStr}`
      : `\u2705 \u0e2a\u0e25\u0e34\u0e1b\u0e16\u0e39\u0e01\u0e15\u0e49\u0e2d\u0e07 \u2014 ${amountStr}`
  const cardSubtitle = `${senderName} > ${receiverName} | ${slipDate}`

  try {
    await sendFacebookGenericTemplate(fbSenderId, [{
      title: cardTitle.substring(0, 80),
      subtitle: cardSubtitle.substring(0, 80),
      image_url: cardImageUrl,
      buttons: [
        { type: 'web_url', title: 'SKJ JAPAN Shipping', url: 'https://skjjapanshipping.com/skjtrack/login' },
      ],
    }])
    console.log('[FB NOTIFY] Slip generic template sent to:', fbSenderId)
  } catch (err: any) {
    // Fallback to plain text
    console.error('[FB NOTIFY] Generic template failed:', err.message, err?.response?.data)
    const refShort = transRef.length > 22 ? transRef.substring(0, 22) + '...' : transRef
    const fallbackText = `${cardTitle}\n${senderName} (${senderBankShort}) > ${receiverName}\n${slipDate} | Ref: ${refShort}`
    try { await sendFacebookMessage(fbSenderId, fallbackText) } catch (_) {}
  }

  // Save verified slip to DB (for duplicate detection)
  if (!isDuplicate && transRef && transRef !== '-') {
    try {
      await prisma.verifiedSlip.create({
        data: { transRef, amount, senderName, receiverName, contactId: fbSenderId },
      })
      console.log('[FB SLIP] Saved transRef:', transRef)
    } catch (err: any) {
      if (!err.message?.includes('Unique')) {
        console.error('[FB SLIP] Failed to save:', err.message)
      }
    }
  }

  // === INVOICE MATCHING: auto-update pay_status if slip matches invoice ===
  let invoiceMatchMsg = ''
  if (!hasWarnings && !isDuplicate && amount >= MIN_AMOUNT) {
    try {
      // For Facebook, contactId is the contact DB id, not fbSenderId
      const fbContact = await prisma.contact.findUnique({
        where: { platform_platformId: { platform: 'facebook', platformId: fbSenderId } },
      })
      if (fbContact) {
        // Extract customer number from contact name (e.g. "ANW-684-Mi Eiei" → "ANW-684")
        let fbCustomerNo: string | undefined
        if (fbContact.name) {
          const custMatch = fbContact.name.match(/\b(ANW-\d+)\b/i)
          if (custMatch) fbCustomerNo = custMatch[1].toUpperCase()
        }
        const { matchSlipToInvoice } = await import('@/lib/invoice-match')
        const matchResult = await matchSlipToInvoice(fbContact.id, amount, transRef, fbCustomerNo)
        if (matchResult?.matched) {
          invoiceMatchMsg = `\n💳 ${matchResult.message}`
          console.log('[FB NOTIFY] Invoice match:', matchResult.message)
        }
      }
    } catch (err: any) {
      console.error('[FB NOTIFY] Invoice match error:', err.message)
    }
  }

  // Save to admin chat
  try {
    const adminMsg = hasWarnings
      ? `⚠️ ตรวจสลิป: ${isDuplicate ? 'สลิปซ้ำ!' : 'พบข้อสังเกต'}\n💰 ${amountStr} | ${slipDate}\n📤 ${senderName} (${senderBankShort})\n📥 ${receiverName}\n🔖 ${transRef}\n${warnings.map(w => `${w.icon} ${w.text}: ${w.detail}`).join('\n')}`
      : `✅ สลิปถูกต้อง\n💰 ${amountStr} | ${slipDate}\n📤 ${senderName} (${senderBankShort})\n📥 ${receiverName}\n🔖 ${transRef}${invoiceMatchMsg}`

    await prisma.message.create({
      data: {
        conversationId,
        direction: 'outbound',
        type: 'text',
        content: adminMsg,
        status: 'sent',
        metadata: JSON.stringify({ isSystem: true, slipVerification: true, thunderResult: { amount, senderName, receiverName, transRef, hasWarnings, warnings: warnings.map(w => w.text) } }),
      },
    })
    await prisma.conversation.update({
      where: { id: conversationId },
      data: {
        lastMessage: hasWarnings ? `⚠️ สลิป ${amountStr} — ${warnings[0].text}` : `✅ สลิป ${amountStr} ถูกต้อง`,
        lastMessageAt: new Date(),
      },
    })
    console.log('[FB NOTIFY] Slip result saved to admin chat:', conversationId)
  } catch (err: any) {
    console.error('[FB NOTIFY] Failed to save admin msg:', err.message)
  }
}
