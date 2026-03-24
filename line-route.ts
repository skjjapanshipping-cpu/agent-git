import { NextRequest, NextResponse } from 'next/server'
import { prisma, prismaRetry } from '@/lib/prisma'
import { getLineProfile, getLineGroupSummary, getLineGroupMemberProfile, verifyLineSignature, downloadLineContent } from '@/lib/platforms/line'
import { detectAndSaveOrders, detectBidPrice } from '@/lib/order-service'
import { handleAutoReply } from '@/lib/auto-reply'
import { verifySlip, verifySlipFromFile } from '@/lib/thunder'
import { notifyClients, notifyTyping } from '@/lib/sse'
import { classifyMessage } from '@/lib/auto-classify'

// ===== LINE EMOJI TEXT → UNICODE CONVERTER =====
const EMOJI_TEXT_MAP: Record<string, string> = {
  '(hands together)': '🙏', '(prayer)': '🙏', '(pray)': '🙏',
  '(happy)': '😊', '(smile)': '😊', '(grinning)': '😁',
  '(laugh)': '😂', '(lol)': '😂', '(big laugh)': '🤣',
  '(sad)': '😢', '(cry)': '😭', '(crying)': '😭', '(sob)': '😭',
  '(angry)': '😠', '(mad)': '😡',
  '(love)': '❤️', '(heart)': '❤️', '(red heart)': '❤️',
  '(broken heart)': '💔',
  '(wink)': '😉', '(tongue)': '😜',
  '(surprised)': '😮', '(shock)': '😱', '(astonished)': '😲',
  '(sleepy)': '😴', '(sleeping)': '😴', '(zzz)': '💤',
  '(cool)': '😎', '(sunglasses)': '😎',
  '(thinking)': '🤔', '(hmm)': '🤔',
  '(thumbs up)': '👍', '(good)': '👍', '(ok)': '👌', '(OK)': '👌',
  '(thumbs down)': '👎',
  '(clap)': '👏', '(clapping)': '👏',
  '(wave)': '👋', '(hi)': '👋', '(bye)': '👋',
  '(muscle)': '💪', '(strong)': '💪', '(flex)': '💪',
  '(fire)': '🔥', '(hot)': '🔥',
  '(star)': '⭐', '(sparkle)': '✨', '(sparkles)': '✨', '(glitter)': '✨',
  '(check)': '✅', '(checkmark)': '✅',
  '(cross)': '❌', '(x)': '❌',
  '(question)': '❓', '(exclamation)': '❗',
  '(sun)': '☀️', '(moon)': '🌙', '(rainbow)': '🌈',
  '(rain)': '🌧️', '(cloud)': '☁️',
  '(dog)': '🐶', '(cat)': '🐱', '(bear)': '🐻',
  '(gift)': '🎁', '(cake)': '🎂', '(party)': '🎉',
  '(music)': '🎵', '(note)': '🎵',
  '(phone)': '📱', '(camera)': '📷',
  '(money)': '💰', '(dollar)': '💵',
  '(coffee)': '☕', '(beer)': '🍺',
  '(food)': '🍽️', '(rice)': '🍚',
  '(car)': '🚗', '(airplane)': '✈️', '(ship)': '🚢',
  '(house)': '🏠', '(office)': '🏢',
  '(clock)': '⏰', '(time)': '⏰',
  '(warning)': '⚠️', '(danger)': '⚠️',
  '(100)': '💯', '(sweat)': '💦',
  '(poop)': '💩', '(ghost)': '👻', '(skull)': '💀',
  '(kiss)': '😘', '(blush)': '☺️', '(shy)': '😳',
  '(angel)': '😇', '(devil)': '😈', '(alien)': '👽',
  '(robot)': '🤖', '(monkey)': '🐵',
  '(eyes)': '👀', '(eye)': '👁️',
  '(hand)': '✋', '(fist)': '✊', '(v)': '✌️', '(peace)': '✌️',
  '(point up)': '☝️', '(point down)': '👇', '(point left)': '👈', '(point right)': '👉',
  '(raising hand)': '🙋', '(bow)': '🙇',
  '(heart eyes)': '😍', '(star eyes)': '🤩',
  '(sick)': '🤒', '(injured)': '🤕', '(mask)': '😷',
  '(yummy)': '😋', '(drool)': '🤤',
  '(hug)': '🤗', '(shrug)': '🤷',
  '(facepalm)': '🤦', '(rolling eyes)': '🙄',
  '(zip)': '🤐', '(silence)': '🤫', '(shh)': '🤫',
  '(nervous)': '😰', '(sweat smile)': '😅', '(phew)': '😅',
  '(dizzy)': '😵', '(confused)': '😕',
  '(celebrate)': '🥳', '(tada)': '🎉',
  '(rose)': '🌹', '(flower)': '🌸', '(cherry blossom)': '🌸',
  '(four leaf clover)': '🍀', '(clover)': '🍀',
  '(ring)': '💍', '(gem)': '💎', '(diamond)': '💎',
  '(trophy)': '🏆', '(medal)': '🏅', '(crown)': '👑',
  '(balloon)': '🎈', '(confetti)': '🎊',
  '(envelope)': '✉️', '(mail)': '📩', '(letter)': '💌',
  '(pin)': '📌', '(key)': '🔑', '(lock)': '🔒',
  '(light bulb)': '💡', '(idea)': '💡',
  '(bomb)': '💣', '(boom)': '💥', '(collision)': '💥',
  '(speech)': '💬', '(thought)': '💭',
}

function convertLineEmojiText(text: string): string {
  // Replace emoji text patterns like (hands together) → 🙏, unknown ones → ☺
  return text.replace(/\([a-zA-Z\s]+\)/g, (match) => {
    const lower = match.toLowerCase()
    return EMOJI_TEXT_MAP[lower] || '☺'
  })
}

// Process LINE emoji: use emojis array for image markers, fallback to text conversion
function processLineEmoji(message: any): string {
  let text = message.text || ''
  
  // If LINE provides emojis array, replace placeholders with image markers
  if (message.emojis?.length) {
    console.log('[LINE emoji] emojis array:', JSON.stringify(message.emojis))
    // Process from end to start so indices don't shift
    const sorted = [...message.emojis].sort((a: any, b: any) => b.index - a.index)
    for (const emoji of sorted) {
      const marker = `[line-emoji:${emoji.productId}:${emoji.emojiId}]`
      text = text.substring(0, emoji.index) + marker + text.substring(emoji.index + emoji.length)
    }
    console.log('[LINE emoji] processed:', text.substring(0, 80))
    return text
  }

  // No emojis array — check for text descriptions like (hands together) and replace with markers
  // LINE emoji text pattern: content in parentheses
  if (text.includes('(') && /\([a-zA-Z\s]+\)/.test(text)) {
    console.log('[LINE emoji-text] raw:', text.substring(0, 80))
  }

  // Try to convert known text descriptions to Unicode, leave unknown ones for frontend
  return convertLineEmojiText(text)
}

// POST — Receive messages from LINE OA
export async function POST(req: NextRequest) {
  try {
    const rawBody = await req.text()
    const signature = req.headers.get('x-line-signature') || ''

    // Verify signature
    if (process.env.LINE_CHANNEL_SECRET && !verifyLineSignature(rawBody, signature)) {
      return NextResponse.json({ error: 'Invalid signature' }, { status: 403 })
    }

    const body = JSON.parse(rawBody)
    const events = body.events || []
    console.log(`[LINE webhook] received ${events.length} events`)

    // Return 200 IMMEDIATELY — process events in background
    // LINE requires fast response, otherwise it retries and causes duplicates
    processLineEvents(events).catch(err => console.error('[LINE] bg processing error:', err))

    return NextResponse.json({ status: 'ok' })
  } catch (error) {
    console.error('LINE webhook error:', error)
    return NextResponse.json({ error: 'Internal error' }, { status: 500 })
  }
}

async function processLineEvents(events: any[]) {
  for (const event of events) {
    const sourceType = event.source?.type || 'user'
    console.log('[LINE event]', event.type, sourceType, event.message?.type || '', 'mid:', event.message?.id || '-')
    // Log webhook event
    try { await prisma.webhookLog.create({ data: { platform: 'line', eventType: event.type || 'unknown', payload: JSON.stringify(event) } }) } catch {}
    try {
      await prismaRetry(async () => {
        if (event.type === 'message') {
          if (sourceType === 'group') {
            await handleGroupMessage(event)
          } else {
            await handleLineMessage(event)
          }
        } else if (event.type === 'follow') {
          await handleLineFollow(event)
        } else if (event.type === 'join') {
          await handleGroupJoin(event)
        } else if (event.type === 'postback') {
          await handlePostback(event)
        } else if (event.type === 'beacon' || event.type === 'unsend') {
          // skip
        }
      })
      // Typing indicators — LINE doesn't send explicit typing events via webhook,
      // but we push "typing started" SSE when a message arrives (user was typing)
    } catch (e) {
      console.error('[LINE handler error]', e)
    }
  }
}

async function handleLineMessage(event: any) {
  const userId = event.source?.userId
  if (!userId) return

  const message = event.message

  // Dedup: skip if this platformMsgId already saved (LINE retry protection)
  if (message.id) {
    const existing = await prisma.message.findFirst({
      where: { platformMsgId: message.id },
    })
    if (existing) {
      console.log('[LINE dedup] skip already-saved msg:', message.id)
      return
    }
  }

  // Check if contact already exists (for auto-reply: new vs returning)
  const existingContact = await prisma.contact.findUnique({
    where: { platform_platformId: { platform: 'line', platformId: userId } },
  })
  const isNewContact = !existingContact

  // Find or create contact — use upsert to avoid race conditions
  let contact = await prisma.contact.upsert({
    where: { platform_platformId: { platform: 'line', platformId: userId } },
    update: {}, // don't update on race — avatar refresh done separately
    create: { name: 'LINE User', platform: 'line', platformId: userId },
  })

  // Fetch profile only for new contacts (no avatar yet)
  if (!contact.avatar || contact.name === 'LINE User') {
    try {
      const profile = await getLineProfile(userId)
      contact = await prisma.contact.update({
        where: { id: contact.id },
        data: {
          name: profile.displayName || contact.name,
          avatar: profile.pictureUrl || contact.avatar,
        },
      })
    } catch (e) {
      // Non-critical
    }
  }

  // Find or create conversation — with retry for race conditions
  let conversation: any = null
  for (let attempt = 0; attempt < 3; attempt++) {
    conversation = await prisma.conversation.findFirst({
      where: { contactId: contact.id, platform: 'line', status: 'open' },
    })
    if (!conversation) {
      try {
        conversation = await prisma.conversation.create({
          data: { contactId: contact.id, platform: 'line', status: 'open' },
        })
      } catch {
        // Race: another request created it — retry find
        await new Promise(r => setTimeout(r, 50 * (attempt + 1)))
        continue
      }
    }
    break
  }
  if (!conversation) return

  // Parse message
  let msgType = 'text'
  let content = ''

  switch (message.type) {
    case 'text':
      content = processLineEmoji(message)
      break
    case 'image':
      msgType = 'image'
      try {
        content = await downloadLineContent(message.id, 'jpg')
      } catch (e) {
        console.error('[LINE] Failed to download image:', e)
        content = `https://api-data.line.me/v2/bot/message/${message.id}/content`
      }
      break
    case 'video':
      msgType = 'file'
      try {
        content = await downloadLineContent(message.id, 'mp4')
      } catch (e) {
        content = `(video)`
      }
      break
    case 'audio':
      msgType = 'audio'
      try {
        content = await downloadLineContent(message.id, 'm4a')
      } catch (e) {
        content = `(audio)`
      }
      break
    case 'file':
      msgType = 'file'
      try {
        const ext = (message.fileName || '').split('.').pop() || 'pdf'
        content = await downloadLineContent(message.id, ext)
      } catch (e) {
        console.error('[LINE] Failed to download file:', e)
        content = `(file: ${message.fileName || 'unknown'})`
      }
      break
    case 'sticker':
      msgType = 'sticker'
      content = `(sticker: ${message.packageId}-${message.stickerId})`
      break
    default:
      msgType = message.type || 'text'
      content = `(${message.type})`
  }

  // Start Thunder API call IMMEDIATELY after image download (parallel with DB save)
  // This keeps the reply token fresh — don't wait for DB operations
  // For local images: use file upload first (Thunder can't fetch from our URL → 500)
  let thunderPromise: Promise<any> | null = null
  if (msgType === 'image' && content) {
    if (!content.startsWith('http')) {
      console.log('[Thunder] Starting file-based verify early for', contact.name)
      thunderPromise = verifySlipFromFile(content).catch((e: any) => {
        console.error('[Thunder] early file verify error:', e.message)
        return null
      })
    } else {
      console.log('[Thunder] Starting URL verify early for', contact.name)
      thunderPromise = verifySlip(content).catch((e: any) => {
        console.error('[Thunder] early verify error:', e.message)
        return null
      })
    }
  }

  // Push typing indicator before saving (UI shows "กำลังพิมพ์..." briefly)
  try { notifyTyping(conversation.id, contact.name) } catch {}

  // Save message + update conversation — retry on deadlock
  // Look up quoted message if customer is replying to a specific message
  let replyToMeta: Record<string, any> = {}
  if (message.quotedMessageId) {
    try {
      const quotedMsg = await prisma.message.findFirst({
        where: { platformMsgId: message.quotedMessageId, conversationId: conversation.id },
      })
      if (quotedMsg) {
        replyToMeta = {
          replyToId: quotedMsg.id,
          replyToContent: quotedMsg.content?.substring(0, 200) || '',
          replyToType: quotedMsg.type,
          replyToDirection: quotedMsg.direction,
        }
      }
    } catch {}
  }

  for (let attempt = 0; attempt < 3; attempt++) {
    try {
      await prisma.$transaction([
        prisma.message.create({
          data: {
            conversationId: conversation.id,
            direction: 'inbound',
            type: msgType,
            content,
            platformMsgId: message.id,
            metadata: JSON.stringify({ replyToken: event.replyToken, markAsReadToken: message.markAsReadToken || null, quoteToken: message.quoteToken || null, ...replyToMeta }),
          },
        }),
        prisma.message.updateMany({
          where: {
            conversationId: conversation.id,
            direction: 'outbound',
            status: { not: 'read' },
          },
          data: { status: 'read' },
        }),
        prisma.conversation.update({
          where: { id: conversation.id },
          data: {
            lastMessage: msgType === 'image' ? '📷 รูปภาพ'
              : msgType === 'sticker' ? '🩷 สติกเกอร์'
              : msgType === 'file' ? '📎 ไฟล์'
              : content.substring(0, 2000),
            lastMessageAt: new Date(),
            unreadCount: { increment: 1 },
          },
        }),
      ])
      break // success
    } catch (e: any) {
      if (attempt < 2 && (e.code === 'P2034' || e.message?.includes('deadlock') || e.message?.includes('Lock'))) {
        console.warn(`[LINE tx retry ${attempt + 1}] mid:${message.id}`)
        await new Promise(r => setTimeout(r, 100 * (attempt + 1)))
      } else {
        throw e
      }
    }
  }

  console.log('[LINE saved]', message.id, msgType, content.substring(0, 30))

  // Push real-time SSE event to connected clients
  try {
    notifyClients('new_message', {
      conversationId: conversation.id,
      contactName: contact.name,
      platform: 'line',
      type: msgType,
      content: msgType === 'image' ? '📷 รูปภาพ' : msgType === 'sticker' ? '🩷 สติกเกอร์' : content.substring(0, 100),
    })
  } catch {}

  // Auto-classify and tag conversation
  if (msgType === 'text' && content) {
    try {
      const classification = classifyMessage(content)
      if (classification.tag && classification.confidence > 0) {
        const existing = conversation.tags ? JSON.parse(conversation.tags) : []
        if (!existing.includes(classification.tag)) {
          existing.push(classification.tag)
          await prisma.conversation.update({
            where: { id: conversation.id },
            data: { tags: JSON.stringify(existing) },
          })
        }
      }
    } catch {}
  }

  // Detect product URLs (async, non-blocking)
  if (msgType === 'text' && content) {
    detectAndSaveOrders(content, contact.id, conversation.id).catch(err =>
      console.error('URL detection error (LINE):', err)
    )
    // Detect bid price in separate message (e.g. "5000เยน")
    detectBidPrice(content, contact.id).catch(err =>
      console.error('Bid price detection error (LINE):', err)
    )
  }

  // Auto-reply for new contacts or keyword matches (1:1 DM only)
  // Uses Reply API (free) — await to know if replyToken was consumed
  let replyTokenUsed = false
  if (msgType === 'text') {
    try {
      const result = await handleAutoReply(userId, content, isNewContact, conversation.id, event.replyToken)
      replyTokenUsed = result.replied
    } catch (err) {
      console.error('[LINE] auto-reply error:', err)
    }
  } else if (isNewContact) {
    try {
      const result = await handleAutoReply(userId, '', true, conversation.id, event.replyToken)
      replyTokenUsed = result.replied
    } catch (err) {
      console.error('[LINE] auto-reply welcome error:', err)
    }
  }

  // Notify team + verify slip when customer sends an image
  if (msgType === 'image' && content) {
    const tokenForSlip = replyTokenUsed ? undefined : event.replyToken
    // Wait for pre-fetched Thunder result (started before DB save)
    const preResult = thunderPromise ? await thunderPromise : null
    notifyTeamImage(contact.name, content, userId, conversation.id, tokenForSlip, preResult).catch(err =>
      console.error('[LINE] team image notify error:', err)
    )
  }
}

// ===== GROUP MESSAGE HANDLER =====
async function handleGroupMessage(event: any) {
  const groupId = event.source?.groupId
  const userId = event.source?.userId
  if (!groupId) return

  const message = event.message

  // Dedup
  if (message.id) {
    const existing = await prisma.message.findFirst({
      where: { platformMsgId: message.id },
    })
    if (existing) {
      console.log('[LINE group dedup] skip:', message.id)
      return
    }
  }

  // Find or create contact for the GROUP (platformId = group:{groupId})
  const groupPlatformId = `group:${groupId}`
  let contact = await prisma.contact.upsert({
    where: { platform_platformId: { platform: 'line', platformId: groupPlatformId } },
    update: {},
    create: { name: `กลุ่ม LINE`, platform: 'line', platformId: groupPlatformId },
  })

  // Fetch group info if name is default
  if (contact.name === 'กลุ่ม LINE') {
    try {
      const groupInfo = await getLineGroupSummary(groupId)
      contact = await prisma.contact.update({
        where: { id: contact.id },
        data: {
          name: `👥 ${groupInfo.groupName || 'กลุ่ม LINE'}`,
          avatar: groupInfo.pictureUrl || contact.avatar,
        },
      })
    } catch (e) {
      console.error('[LINE] Failed to get group summary:', e)
    }
  }

  // Get sender name + avatar (who sent the message in the group)
  let senderName = 'สมาชิก'
  let senderIcon = ''
  if (userId) {
    try {
      const memberProfile = await getLineGroupMemberProfile(groupId, userId)
      senderName = memberProfile.displayName || senderName
      senderIcon = memberProfile.pictureUrl || ''
    } catch (e) {
      // Non-critical — might fail for some users
    }
  }

  // Find or create conversation — with retry for race conditions
  let conversation: any = null
  for (let attempt = 0; attempt < 3; attempt++) {
    conversation = await prisma.conversation.findFirst({
      where: { contactId: contact.id, platform: 'line', status: 'open' },
    })
    if (!conversation) {
      try {
        conversation = await prisma.conversation.create({
          data: { contactId: contact.id, platform: 'line', status: 'open' },
        })
      } catch {
        await new Promise(r => setTimeout(r, 50 * (attempt + 1)))
        continue
      }
    }
    break
  }
  if (!conversation) return

  // Parse message
  let msgType = 'text'
  let content = ''

  switch (message.type) {
    case 'text':
      content = processLineEmoji(message)
      break
    case 'image':
      msgType = 'image'
      try {
        content = await downloadLineContent(message.id, 'jpg')
      } catch (e) {
        console.error('[LINE group] Failed to download image:', e)
        content = `https://api-data.line.me/v2/bot/message/${message.id}/content`
      }
      break
    case 'video':
      msgType = 'file'
      try {
        content = await downloadLineContent(message.id, 'mp4')
      } catch (e) {
        content = `(video)`
      }
      break
    case 'audio':
      msgType = 'audio'
      try {
        content = await downloadLineContent(message.id, 'm4a')
      } catch (e) {
        content = `(audio)`
      }
      break
    case 'file':
      msgType = 'file'
      try {
        const ext = (message.fileName || '').split('.').pop() || 'pdf'
        content = await downloadLineContent(message.id, ext)
      } catch (e) {
        console.error('[LINE group] Failed to download file:', e)
        content = `(file: ${message.fileName || 'unknown'})`
      }
      break
    case 'sticker':
      msgType = 'sticker'
      content = `(sticker: ${message.packageId}-${message.stickerId})`
      break
    default:
      msgType = message.type || 'text'
      content = `(${message.type})`
  }

  // Look up quoted message if user is replying to a specific message in group
  let grpReplyToMeta: Record<string, any> = {}
  if (message.quotedMessageId) {
    try {
      const quotedMsg = await prisma.message.findFirst({
        where: { platformMsgId: message.quotedMessageId, conversationId: conversation.id },
      })
      if (quotedMsg) {
        grpReplyToMeta = {
          replyToId: quotedMsg.id,
          replyToContent: quotedMsg.content?.substring(0, 200) || '',
          replyToType: quotedMsg.type,
          replyToDirection: quotedMsg.direction,
        }
      }
    } catch {}
  }

  // Save message with sender info in metadata — retry on deadlock
  for (let attempt = 0; attempt < 3; attempt++) {
    try {
      await prisma.$transaction([
        prisma.message.create({
          data: {
            conversationId: conversation.id,
            direction: 'inbound',
            type: msgType,
            content,
            platformMsgId: message.id,
            metadata: JSON.stringify({ replyToken: event.replyToken, markAsReadToken: message.markAsReadToken || null, senderName, senderId: userId, senderIcon, quoteToken: message.quoteToken || null, ...grpReplyToMeta }),
          },
        }),
        prisma.conversation.update({
          where: { id: conversation.id },
          data: {
            lastMessage: `${senderName}: ${msgType === 'image' ? '📷 รูปภาพ' : msgType === 'sticker' ? '🩷 สติกเกอร์' : msgType === 'file' ? '📎 ไฟล์' : content.substring(0, 1500)}`,
            lastMessageAt: new Date(),
            unreadCount: { increment: 1 },
          },
        }),
      ])
      break
    } catch (e: any) {
      if (attempt < 2 && (e.code === 'P2034' || e.message?.includes('deadlock') || e.message?.includes('Lock'))) {
        console.warn(`[LINE group tx retry ${attempt + 1}] mid:${message.id}`)
        await new Promise(r => setTimeout(r, 100 * (attempt + 1)))
      } else {
        throw e
      }
    }
  }

  console.log('[LINE group saved]', message.id, senderName, content.substring(0, 30))

  // Verify slip when someone sends an image in group
  if (msgType === 'image' && content) {
    // If this is the SKJ-BUYER group, treat image as proof for pending orders
    const buyerGroupId = process.env.BUYER_GROUP_ID
    if (buyerGroupId && groupId === buyerGroupId) {
      handleBuyerProofImage(content, groupId).catch(err =>
        console.error('[LINE group] buyer proof error:', err)
      )
    } else {
      notifyTeamImage(senderName, content, groupId, conversation.id, event.replyToken).catch(err =>
        console.error('[LINE group] slip verify error:', err)
      )
    }
  }

  // Detect product URLs
  if (msgType === 'text' && content) {
    detectAndSaveOrders(content, contact.id, conversation.id).catch(err =>
      console.error('URL detection error (LINE group):', err)
    )
  }
}

// ===== GROUP JOIN HANDLER =====
async function handleGroupJoin(event: any) {
  const groupId = event.source?.groupId
  if (!groupId) return

  const groupPlatformId = `group:${groupId}`

  let name = '👥 กลุ่ม LINE'
  let avatar = null
  try {
    const groupInfo = await getLineGroupSummary(groupId)
    name = `👥 ${groupInfo.groupName || 'กลุ่ม LINE'}`
    avatar = groupInfo.pictureUrl || null
  } catch (e) {
    console.error('[LINE] Failed to get group info on join:', e)
  }

  await prisma.contact.upsert({
    where: { platform_platformId: { platform: 'line', platformId: groupPlatformId } },
    update: { name, avatar },
    create: { name, avatar, platform: 'line', platformId: groupPlatformId },
  })

  console.log('[LINE] Joined group:', name)
}

async function handleLineFollow(event: any) {
  const userId = event.source?.userId
  if (!userId) return

  const existing = await prisma.contact.findUnique({
    where: { platform_platformId: { platform: 'line', platformId: userId } },
  })
  if (existing) return

  let name = 'LINE User'
  let avatar = null
  try {
    const profile = await getLineProfile(userId)
    name = profile.displayName || name
    avatar = profile.pictureUrl || null
  } catch (e) {
    console.error('Failed to fetch LINE profile on follow:', e)
  }

  await prisma.contact.create({
    data: { name, avatar, platform: 'line', platformId: userId },
  })
}

// ===== NOTIFY TEAM — IMAGE + THUNDER SLIP VERIFICATION =====
const POWER_TEAM_GROUP_ID = 'C192c7f95ebcef2c3e23e70f0a3d4df54'

// === CONFIG: ปรับแก้ได้ตามต้องการ ===
const VALID_ACCOUNTS: Array<{
  nameTh: string      // ชื่อไทย (partial match)
  nameEn: string      // ชื่ออังกฤษ (partial match, case-insensitive)
  accounts: string[]  // เลขบัญชี / เบอร์โทร / proxy / เลขร้านค้า (partial match)
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
const MAX_SLIP_AGE_MINUTES = 15  // สลิปต้องไม่เก่าเกินกี่นาที

async function notifyTeamImage(customerName: string, imageUrl: string, customerId: string, conversationId: string, replyToken?: string, preThunderResult?: any) {
  const startMs = Date.now()
  const { pushLineMessages, replyLineMessages } = await import('@/lib/platforms/line')
  const appUrl = process.env.NEXTAUTH_URL || process.env.NEXT_PUBLIC_APP_URL || 'https://chat.skjjapanshipping.com'
  const fullImageUrl = imageUrl.startsWith('http') ? imageUrl : `${appUrl}${imageUrl}`

  // Step 1: Use pre-fetched Thunder result, or call Thunder API
  // For local images: prefer file upload (Thunder can't fetch our URL → 500)
  let thunderResult: any = preThunderResult || null
  if (!thunderResult) {
    if (!imageUrl.startsWith('http')) {
      // Local image → file upload first
      try {
        thunderResult = await verifySlipFromFile(imageUrl)
      } catch (err: any) {
        console.error('[Thunder] file verify error:', err.message)
      }
    }
    if (!thunderResult || (!thunderResult.success && thunderResult.error === 'file_not_found')) {
      // Fallback to URL-based
      try {
        thunderResult = await verifySlip(fullImageUrl)
      } catch (err: any) {
        console.error('[Thunder] URL verify error:', err.message)
      }
    }
  }
  console.log('[Thunder] result for', customerName, ':', thunderResult?.success, thunderResult?.error, `(${Date.now() - startMs}ms, token: ${replyToken ? 'yes' : 'no'})`)

  // Step 2: Determine slip status
  const isSlip = thunderResult?.success === true && thunderResult?.data?.rawSlip

  // === Not a slip / can't verify ===
  if (!isSlip) {
    const err = thunderResult?.error
    // quota = system issue → warn admin only
    if (err === 'quota') {
      console.log('[NOTIFY] Thunder quota exceeded for', customerName)
      try {
        await prisma.message.create({
          data: {
            conversationId, direction: 'outbound', type: 'text',
            content: `⚡ โควต้า Thunder หมด — ตรวจสลิปไม่ได้ กรุณาเติมโควต้า`,
            status: 'sent',
            metadata: JSON.stringify({ isSystem: true, slipVerification: true, error: 'quota' }),
          },
        })
        await prisma.conversation.update({
          where: { id: conversationId },
          data: { lastMessage: `⚡ โควต้า Thunder หมด`, lastMessageAt: new Date() },
        })
      } catch (_) {}
      return
    }
    // not_slip / validation / other = not a bank slip or can't read
    console.log('[NOTIFY] Non-slip image from', customerName, '- skipped (', err, ')')

    // === Check if customer has pending import fee invoice → notify POWER TEAM ===
    try {
      // Extract customer number from name (e.g. "ANW-548-🍀Name" → "ANW-548")
      const custMatch = customerName.match(/\b(ANW-\d+)\b/i)
      if (custMatch) {
        const custNo = custMatch[1].toLowerCase()
        const thirtyDaysAgo = new Date()
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)

        // Find pending import fee invoices (etd is a date, NOT 'thai-bill')
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
          const totalAmount = pendingImportInvoices.reduce((sum, i) => sum + i.amount, 0)
          const etdDisplay = inv.etd || '-'

          console.log(`[NOTIFY] Import invoice pending for ${custNo.toUpperCase()} ETD=${etdDisplay} ฿${totalAmount} — notifying POWER TEAM`)

          const alertMsg = [
            `🔔 แจ้งเตือน: สลิปค่านำเข้า`,
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
          console.log(`[NOTIFY] Sent import invoice alert to POWER TEAM for ${custNo.toUpperCase()}`)
        }
      }
    } catch (alertErr: any) {
      console.error('[NOTIFY] Import invoice alert error:', alertErr.message)
    }

    return
  }

  // Step 3: Parse slip data
  const slip = thunderResult.data.rawSlip
  const amount = slip.amount?.amount || 0
  const amountStr = `฿${amount.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`
  const senderName = slip.sender?.account?.name?.th || 'ไม่ทราบ'
  const senderBank = slip.sender?.bank?.name || slip.sender?.bank?.short || ''
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

  // Step 4: Check all conditions → collect warnings
  const warnings: { icon: string; text: string; detail: string }[] = []

  // Condition 1: สลิปซ้ำ — เช็คจาก transRef ใน DB
  let isDuplicate = false
  if (transRef && transRef !== '-') {
    const existing = await prisma.verifiedSlip.findUnique({ where: { transRef } })
    if (existing) {
      isDuplicate = true
      warnings.push({ icon: '�', text: 'สลิปซ้ำ!', detail: `เคยใช้เมื่อ ${existing.createdAt.toLocaleString('th-TH', { timeZone: 'Asia/Bangkok', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}` })
    }
  }

  // Condition 2: ตรวจผู้รับเงิน (ชื่อ TH/EN + เลขบัญชี/proxy)
  if (VALID_ACCOUNTS.length > 0) {
    const matchedAccount = VALID_ACCOUNTS.some(va => {
      // เช็คชื่อไทย
      if (va.nameTh && receiverName.includes(va.nameTh)) return true
      // เช็คชื่ออังกฤษ (case-insensitive)
      if (va.nameEn && receiverNameEn.toUpperCase().includes(va.nameEn.toUpperCase())) return true
      // เช็คเลขบัญชี / proxy
      const acctStr = `${receiverAccountNo} ${receiverProxyNo}`.replace(/[\s-x]/g, '')
      if (va.accounts.some(a => acctStr.includes(a.replace(/[\s-]/g, '')))) return true
      return false
    })
    if (!matchedAccount) {
      warnings.push({ icon: '👤', text: 'ผู้รับไม่ตรง', detail: `${receiverName} ${receiverNameEn ? `(${receiverNameEn})` : ''} ${receiverAccountNo || receiverProxyNo}`.trim() })
    }
  }

  // Condition 3: สลิปช้าเกิน 15 นาที
  if (slip.date) {
    const slipTime = new Date(slip.date).getTime()
    const now = Date.now()
    const diffMin = (now - slipTime) / 60000
    if (diffMin > MAX_SLIP_AGE_MINUTES) {
      const mins = Math.round(diffMin)
      warnings.push({ icon: '⏰', text: `สลิปช้าเกิน ${MAX_SLIP_AGE_MINUTES} นาที`, detail: `ส่งมาแล้ว ${mins} นาที` })
    }
  }

  // Condition 4: ยอดขั้นต่ำ (ปิดใช้งาน — ไม่มียอดขั้นต่ำ)
  // if (amount < MIN_AMOUNT) {
  //   warnings.push({ icon: '💰', text: 'ยอดต่ำกว่ากำหนด', detail: `ขั้นต่ำ ฿${MIN_AMOUNT} / ได้รับ ${amountStr}` })
  // }

  const hasWarnings = warnings.length > 0

  console.log('[NOTIFY] Slip', hasWarnings ? 'WARNING' : 'OK', ':', customerName, amountStr, warnings.map(w => w.text).join(', '))

  // === BUILD BEAUTIFUL CUSTOMER FLEX MESSAGE ===
  let statusEmoji = '✅'
  let statusText = 'สลิปถูกต้อง'
  let statusColor = '#059669' // emerald-600
  let gradientTop = '#059669'
  let gradientBot = '#10B981'
  let statusSubtext = 'ตรวจสอบเรียบร้อย'
  let footerText = 'ได้รับการยืนยันแล้ว ขอบคุณค่ะ 🙏'
  let footerBg = '#ECFDF5'
  let footerColor = '#065F46'

  if (isDuplicate) {
    statusEmoji = '🚫'
    statusText = 'สลิปซ้ำ!'
    statusColor = '#DC2626'
    gradientTop = '#DC2626'
    gradientBot = '#EF4444'
    statusSubtext = 'เคยใช้แล้ว'
    footerText = 'สลิปนี้เคยใช้แล้ว กรุณาติดต่อเจ้าหน้าที่'
    footerBg = '#FEF2F2'
    footerColor = '#991B1B'
  } else if (hasWarnings) {
    statusEmoji = '⚠️'
    statusText = 'พบข้อสังเกต'
    statusColor = '#D97706'
    gradientTop = '#D97706'
    gradientBot = '#F59E0B'
    statusSubtext = warnings.map(w => w.text).join(', ')
    footerText = 'กรุณาตรวจสอบหรือติดต่อเจ้าหน้าที่'
    footerBg = '#FFFBEB'
    footerColor = '#92400E'
  }

  // Warning detail rows for customer
  const custWarningContents: any[] = []
  if (hasWarnings) {
    custWarningContents.push({ type: 'separator', margin: 'lg', color: '#F3F4F6' })
    for (const w of warnings) {
      custWarningContents.push({
        type: 'box', layout: 'horizontal', margin: 'sm', paddingStart: '4px', paddingEnd: '4px',
        contents: [
          { type: 'text', text: `${w.icon}`, size: 'sm', flex: 0 },
          { type: 'text', text: w.text, size: 'xs', color: '#B45309', weight: 'bold', flex: 3, margin: '8px' },
          { type: 'text', text: w.detail, size: 'xxs', color: '#78716C', flex: 4, wrap: true, align: 'end' },
        ],
      })
    }
  }

  const custFlex: any = {
    type: 'flex',
    altText: `${statusEmoji} ${statusText} — ${amountStr}`,
    contents: {
      type: 'bubble',
      size: 'mega',
      header: {
        type: 'box', layout: 'vertical',
        backgroundColor: gradientTop, paddingAll: '20px', paddingBottom: '24px',
        contents: [
          // Brand bar
          { type: 'box', layout: 'horizontal', contents: [
            { type: 'text', text: 'SKJ JAPAN SHIPPING', size: 'xxs', color: '#FFFFFFAA', weight: 'bold', flex: 1 },
            { type: 'text', text: 'Thunder ⚡', size: 'xxs', color: '#FFFFFFAA', flex: 0 },
          ]},
          // Big status icon
          { type: 'text', text: statusEmoji, size: '3xl', align: 'center', margin: 'lg' },
          // Status text
          { type: 'text', text: statusText, weight: 'bold', size: 'xl', color: '#FFFFFF', align: 'center', margin: 'md' },
          { type: 'text', text: statusSubtext, size: 'xs', color: '#FFFFFFCC', align: 'center', margin: 'xs' },
        ],
      },
      body: {
        type: 'box', layout: 'vertical', paddingAll: '20px', spacing: 'none',
        contents: [
          // Amount
          { type: 'text', text: amountStr, weight: 'bold', size: '3xl', color: '#111827', align: 'center' },
          { type: 'text', text: slipDate, size: 'xs', color: '#9CA3AF', align: 'center', margin: 'sm' },
          // Divider
          { type: 'separator', margin: 'xl', color: '#E5E7EB' },
          // Sender info
          {
            type: 'box', layout: 'horizontal', margin: 'xl',
            contents: [
              { type: 'box', layout: 'vertical', flex: 0, width: '40px', height: '40px', cornerRadius: '20px', backgroundColor: '#EFF6FF', justifyContent: 'center', alignItems: 'center',
                contents: [{ type: 'text', text: '📤', size: 'md', align: 'center' }] },
              { type: 'box', layout: 'vertical', flex: 1, margin: '12px',
                contents: [
                  { type: 'text', text: 'ผู้โอน', size: 'xxs', color: '#9CA3AF' },
                  { type: 'text', text: senderName, size: 'sm', weight: 'bold', color: '#111827' },
                  { type: 'text', text: `${senderBankShort} ${senderAccount}`, size: 'xxs', color: '#6B7280', margin: 'xs' },
                ] },
            ],
          },
          // Receiver info
          {
            type: 'box', layout: 'horizontal', margin: 'lg',
            contents: [
              { type: 'box', layout: 'vertical', flex: 0, width: '40px', height: '40px', cornerRadius: '20px', backgroundColor: '#F0FDF4', justifyContent: 'center', alignItems: 'center',
                contents: [{ type: 'text', text: '�', size: 'md', align: 'center' }] },
              { type: 'box', layout: 'vertical', flex: 1, margin: '12px',
                contents: [
                  { type: 'text', text: 'ผู้รับ', size: 'xxs', color: '#9CA3AF' },
                  { type: 'text', text: receiverName, size: 'sm', weight: 'bold', color: '#111827' },
                  { type: 'text', text: receiverProxy, size: 'xxs', color: '#6B7280', margin: 'xs' },
                ] },
            ],
          },
          // Ref
          { type: 'separator', margin: 'lg', color: '#F3F4F6' },
          {
            type: 'box', layout: 'horizontal', margin: 'md',
            contents: [
              { type: 'text', text: 'เลขอ้างอิง', size: 'xxs', color: '#9CA3AF', flex: 2 },
              { type: 'text', text: transRef, size: 'xxs', color: '#6B7280', flex: 5, align: 'end', wrap: true },
            ],
          },
          // Warning rows (if any)
          ...custWarningContents,
        ],
      },
      footer: {
        type: 'box', layout: 'vertical', backgroundColor: footerBg, paddingAll: '16px',
        contents: [
          { type: 'text', text: footerText, size: 'xs', color: footerColor, align: 'center', wrap: true, weight: 'bold' },
        ],
      },
    },
  }

  // Try Reply API first (free, no quota), fall back to Push API
  try {
    if (replyToken) {
      try {
        await replyLineMessages(replyToken, [custFlex])
        console.log('[NOTIFY] Slip result → replied (reply API) to:', customerId)
      } catch (replyErr: any) {
        console.log('[NOTIFY] Reply failed:', replyErr.message, JSON.stringify(replyErr.response?.data))
        await pushLineMessages(customerId, [custFlex])
        console.log('[NOTIFY] Slip result → replied (push API) to:', customerId)
      }
    } else {
      await pushLineMessages(customerId, [custFlex])
      console.log('[NOTIFY] Slip result → replied (push API) to:', customerId)
    }
  } catch (err: any) {
    console.error('[NOTIFY] Failed to reply to customer:', err.message)
  }

  // === SAVE VERIFIED SLIP TO DB (for duplicate detection) ===
  if (!isDuplicate && transRef && transRef !== '-') {
    try {
      await prisma.verifiedSlip.create({
        data: { transRef, amount, senderName, receiverName, contactId: customerId },
      })
      console.log('[SLIP] Saved transRef:', transRef)
    } catch (err: any) {
      // Ignore unique constraint error (race condition)
      if (!err.message?.includes('Unique')) {
        console.error('[SLIP] Failed to save:', err.message)
      }
    }
  }

  // === INVOICE MATCHING: auto-update pay_status if slip matches invoice ===
  let invoiceMatchMsg = ''
  if (!hasWarnings && !isDuplicate && amount >= MIN_AMOUNT) {
    try {
      // customerId here is LINE platformId (userId or groupId) — lookup the DB contact id
      let lineContact = await prisma.contact.findUnique({
        where: { platform_platformId: { platform: 'line', platformId: customerId } },
      })
      // For groups: customerId is raw groupId, but DB stores as "group:{groupId}"
      if (!lineContact) {
        lineContact = await prisma.contact.findUnique({
          where: { platform_platformId: { platform: 'line', platformId: `group:${customerId}` } },
        })
      }
      const dbContactId = lineContact?.id || customerId

      // Extract customer number from contact/group name (e.g. "ANW-539 (8)" → "ANW-539")
      let extractedCustomerNo: string | undefined
      if (lineContact?.name) {
        const custMatch = lineContact.name.match(/\b(ANW-\d+)\b/i)
        if (custMatch) extractedCustomerNo = custMatch[1].toUpperCase()
      }
      // Also try from customerName parameter (sender display name in group might contain it)
      if (!extractedCustomerNo && customerName) {
        const custMatch2 = customerName.match(/\b(ANW-\d+)\b/i)
        if (custMatch2) extractedCustomerNo = custMatch2[1].toUpperCase()
      }

      const { matchSlipToInvoice } = await import('@/lib/invoice-match')
      const matchResult = await matchSlipToInvoice(dbContactId, amount, transRef, extractedCustomerNo)
      if (matchResult?.matched) {
        invoiceMatchMsg = `\n💳 ${matchResult.message}`
        console.log('[NOTIFY] Invoice match:', matchResult.message)
      }
    } catch (err: any) {
      console.error('[NOTIFY] Invoice match error:', err.message)
    }
  }

  // === SAVE TO ADMIN CHAT (so admin sees slip result in conversation) ===
  try {
    const adminMsg = hasWarnings
      ? `⚠️ ตรวจสลิป: ${statusText}\n💰 ${amountStr} | ${slipDate}\n📤 ${senderName} (${senderBankShort})\n📥 ${receiverName}\n🔖 ${transRef}\n${warnings.map(w => `${w.icon} ${w.text}: ${w.detail}`).join('\n')}`
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
    console.log('[NOTIFY] Slip result saved to admin chat:', conversationId)
  } catch (err: any) {
    console.error('[NOTIFY] Failed to save admin chat msg:', err.message)
  }
}

// ===== POSTBACK HANDLER — SKJ-BUYER group button actions =====
async function handlePostback(event: any) {
  const data = event.postback?.data || ''
  const groupId = event.source?.groupId
  const userId = event.source?.userId
  console.log('[LINE postback]', data, 'group:', groupId, 'user:', userId)

  const buyerGroupId = process.env.BUYER_GROUP_ID
  // Only process postbacks from SKJ-BUYER group
  if (!groupId || groupId !== buyerGroupId) return

  const { sendLineMessage, sendLineImage, pushLineMessages } = await import('@/lib/platforms/line')

  // Parse action:orderId
  const [action, orderId] = data.split(':')
  if (!orderId) return

  if (action === 'order_done' || action === 'order_fail') {
    const order = await prisma.purchaseOrder.findUnique({
      where: { id: orderId },
      include: { contact: true, conversation: true },
    })
    if (!order) {
      await sendLineMessage(groupId, `⚠️ ไม่พบออเดอร์ ${orderId}`)
      return
    }

    const newStatus = action === 'order_done' ? 'received' : 'unavailable'
    await prisma.purchaseOrder.update({
      where: { id: orderId },
      data: {
        status: newStatus,
        // Reset proofImageUrl when "สั่งแล้ว" so proof images can be (re)sent
        ...(action === 'order_done' ? { proofImageUrl: null } : {}),
      },
    })

    // Notify customer via LINE/FB
    const contact = order.contact
    const recipientId = contact.platformId.startsWith('group:') ? contact.platformId.replace('group:', '') : contact.platformId

    if (action === 'order_done') {
      const customerMsg = `✅ สินค้ารายการนี้กดให้เรียบร้อยแล้วค่ะ\n🔗 ${order.url}`
      try {
        if (contact.platform === 'line') {
          await sendLineMessage(recipientId, customerMsg)
        } else if (contact.platform === 'facebook') {
          const { sendFacebookMessage } = await import('@/lib/platforms/facebook')
          await sendFacebookMessage(recipientId, customerMsg)
        }
        await prisma.message.create({
          data: { conversationId: order.conversationId, direction: 'outbound', type: 'text', content: customerMsg, status: 'sent' },
        })
        await prisma.conversation.update({
          where: { id: order.conversationId },
          data: { lastMessage: customerMsg.substring(0, 200), lastMessageAt: new Date() },
        })
      } catch (err) {
        console.error('[POSTBACK] customer notify error:', err)
      }

      // Send updated Flex in group showing "สำเร็จ" status
      try {
        const { pushLineMessages } = await import('@/lib/platforms/line')
        const doneFlex = {
          type: 'flex',
          altText: `✅ สำเร็จ — ${contact.name}`,
          contents: {
            type: 'bubble',
            size: 'kilo',
            header: {
              type: 'box', layout: 'vertical',
              contents: [{ type: 'text', text: '✅ สำเร็จ', size: 'md', weight: 'bold', color: '#FFFFFF' }],
              backgroundColor: '#22C55E', paddingAll: '12px',
            },
            body: {
              type: 'box', layout: 'vertical', paddingAll: '14px',
              contents: [
                { type: 'text', text: `👤 ${contact.name}`, size: 'sm', weight: 'bold' },
                { type: 'text', text: order.url, size: 'xs', color: '#666666', wrap: true, margin: 'sm' },
              ],
            },
          },
        }
        await pushLineMessages(groupId, [doneFlex])
      } catch (err) {
        console.error('[POSTBACK] group flex update error:', err)
      }
    } else {
      const customerMsg = `❌ ลิงก์นี้ไม่ทันค่ะ\n🔗 ${order.url}`
      try {
        if (contact.platform === 'line') {
          await sendLineMessage(recipientId, customerMsg)
        } else if (contact.platform === 'facebook') {
          const { sendFacebookMessage } = await import('@/lib/platforms/facebook')
          await sendFacebookMessage(recipientId, customerMsg)
        }
        await prisma.message.create({
          data: { conversationId: order.conversationId, direction: 'outbound', type: 'text', content: customerMsg, status: 'sent' },
        })
        await prisma.conversation.update({
          where: { id: order.conversationId },
          data: { lastMessage: customerMsg.substring(0, 200), lastMessageAt: new Date() },
        })
      } catch (err) {
        console.error('[POSTBACK] customer notify error:', err)
      }
    }

    console.log(`[POSTBACK] ${action} order ${orderId} → ${newStatus}`)
  }

  // Auction Bid — Admin กด Bid ในกลุ่ม → แจ้งลูกค้า + ส่ง Flex ใหม่ให้กด Bid ซ้ำได้
  if (action === 'auction_bid') {
    const order = await prisma.purchaseOrder.findUnique({
      where: { id: orderId },
      include: {
        contact: true,
        conversation: true,
        bids: { orderBy: { round: 'desc' }, take: 1 },
      },
    })
    if (!order || !order.isAuction) return

    const lastBid = order.bids[0]
    const currentRound = lastBid ? lastBid.round : 0
    const bidYen = order.currentBidYen || 0

    // Mark previous bid as outbid if exists
    if (lastBid && lastBid.result === 'placed') {
      await prisma.auctionBid.update({ where: { id: lastBid.id }, data: { result: 'outbid' } })
    }

    // Create new bid record
    await prisma.auctionBid.create({
      data: {
        purchaseOrderId: orderId,
        round: currentRound + 1,
        bidAmountYen: bidYen,
        result: 'placed',
      },
    })

    // Update order status to bidding
    await prisma.purchaseOrder.update({
      where: { id: orderId },
      data: { auctionStatus: 'bidding' },
    })

    // Notify CUSTOMER only (not visible to admin chat)
    const contact = order.contact
    const recipientId = contact.platformId.startsWith('group:') ? contact.platformId.replace('group:', '') : contact.platformId
    const yenStr = bidYen > 0 ? `¥${bidYen.toLocaleString()}` : ''
    const customerMsg = `🔨 Bid ให้แล้วค่ะ🙏${yenStr ? ' ราคา ' + yenStr : ''}\n🔗 ${order.url}`

    try {
      if (contact.platform === 'line') {
        await sendLineMessage(recipientId, customerMsg)
      } else if (contact.platform === 'facebook') {
        const { sendFacebookMessage } = await import('@/lib/platforms/facebook')
        await sendFacebookMessage(recipientId, customerMsg)
      }
      await prisma.message.create({ data: { conversationId: order.conversationId, direction: 'outbound', type: 'text', content: customerMsg, status: 'sent' } })
      await prisma.conversation.update({ where: { id: order.conversationId }, data: { lastMessage: customerMsg.substring(0, 200), lastMessageAt: new Date() } })
    } catch (err) {
      console.error('[POSTBACK] auction bid customer notify error:', err)
    }

    // ไม่ส่ง Flex card ใหม่ในกลุ่ม — ใช้การ์ดเดิมกด Bid ซ้ำได้เลย

    console.log(`[POSTBACK] auction_bid order ${orderId} round ${currentRound + 1} ¥${bidYen}`)
  }

  // Auction actions: won / lost / outbid
  if (action === 'auction_won' || action === 'auction_lost' || action === 'auction_outbid') {
    const order = await prisma.purchaseOrder.findUnique({
      where: { id: orderId },
      include: {
        contact: true,
        conversation: true,
        bids: { orderBy: { round: 'desc' }, take: 1 },
      },
    })
    if (!order || !order.isAuction) return

    const lastBid = order.bids[0]
    const contact = order.contact
    const recipientId = contact.platformId.startsWith('group:') ? contact.platformId.replace('group:', '') : contact.platformId
    const yenStr = order.currentBidYen ? `¥${order.currentBidYen.toLocaleString()}` : ''

    if (action === 'auction_won') {
      if (lastBid) await prisma.auctionBid.update({ where: { id: lastBid.id }, data: { result: 'won' } })
      await prisma.purchaseOrder.update({ where: { id: orderId }, data: { auctionStatus: 'won', status: 'received' } })

      const customerMsg = `🎉 ชนะประมูลแล้วค่ะ! ราคาสุดท้าย ${yenStr}\n🔗 ${order.url}`
      try {
        if (contact.platform === 'line') await sendLineMessage(recipientId, customerMsg)
        else if (contact.platform === 'facebook') {
          const { sendFacebookMessage } = await import('@/lib/platforms/facebook')
          await sendFacebookMessage(recipientId, customerMsg)
        }
        await prisma.message.create({ data: { conversationId: order.conversationId, direction: 'outbound', type: 'text', content: customerMsg, status: 'sent' } })
        await prisma.conversation.update({ where: { id: order.conversationId }, data: { lastMessage: customerMsg.substring(0, 200), lastMessageAt: new Date() } })
      } catch (err) { console.error('[POSTBACK] auction won notify error:', err) }

      // Send "ชนะประมูล" Flex card in BUYER group
      try {
        const wonFlex = {
          type: 'flex',
          altText: `🏆 ชนะประมูล — ${contact.name}`,
          contents: {
            type: 'bubble',
            size: 'kilo',
            header: {
              type: 'box', layout: 'vertical',
              contents: [{ type: 'text', text: '🏆 ชนะประมูล', size: 'md', weight: 'bold', color: '#FFFFFF' }],
              backgroundColor: '#22C55E', paddingAll: '12px',
            },
            body: {
              type: 'box', layout: 'vertical', paddingAll: '14px',
              contents: [
                { type: 'text', text: `👤 ${contact.name}`, size: 'sm', weight: 'bold' },
                { type: 'text', text: order.url, size: 'xs', color: '#666666', wrap: true, margin: 'sm' },
              ],
            },
          },
        }
        await pushLineMessages(groupId, [wonFlex])
      } catch (err) { console.error('[POSTBACK] auction won group flex error:', err) }
    } else if (action === 'auction_outbid') {
      // โดนนำ — แจ้งลูกค้าว่าโดนนำราคา
      await prisma.purchaseOrder.update({ where: { id: orderId }, data: { auctionStatus: 'outbid' } })

      const customerMsg = `⚡ สินค้ารายการนี้โดนคู่แข่งนำค่ะ\n🔗 ${order.url}\n\nหากต้องการเพิ่มราคาบิด กรุณาแจ้งราคาใหม่มาได้เลยค่ะ`
      try {
        if (contact.platform === 'line') await sendLineMessage(recipientId, customerMsg)
        else if (contact.platform === 'facebook') {
          const { sendFacebookMessage } = await import('@/lib/platforms/facebook')
          await sendFacebookMessage(recipientId, customerMsg)
        }
        await prisma.message.create({ data: { conversationId: order.conversationId, direction: 'outbound', type: 'text', content: customerMsg, status: 'sent' } })
        await prisma.conversation.update({ where: { id: order.conversationId }, data: { lastMessage: customerMsg.substring(0, 200), lastMessageAt: new Date() } })
      } catch (err) { console.error('[POSTBACK] auction outbid notify error:', err) }
    } else {
      if (lastBid) await prisma.auctionBid.update({ where: { id: lastBid.id }, data: { result: 'lost' } })
      await prisma.purchaseOrder.update({ where: { id: orderId }, data: { auctionStatus: 'lost', status: 'unavailable' } })

      const customerMsg = `😔 ไม่ได้สินค้าค่ะ ราคาเกินที่สู้ไว้\n🔗 ${order.url}`
      try {
        if (contact.platform === 'line') await sendLineMessage(recipientId, customerMsg)
        else if (contact.platform === 'facebook') {
          const { sendFacebookMessage } = await import('@/lib/platforms/facebook')
          await sendFacebookMessage(recipientId, customerMsg)
        }
        await prisma.message.create({ data: { conversationId: order.conversationId, direction: 'outbound', type: 'text', content: customerMsg, status: 'sent' } })
        await prisma.conversation.update({ where: { id: order.conversationId }, data: { lastMessage: customerMsg.substring(0, 200), lastMessageAt: new Date() } })
      } catch (err) { console.error('[POSTBACK] auction lost notify error:', err) }
    }

    console.log(`[POSTBACK] ${action} order ${orderId}`)
  }
}

// ===== BUYER GROUP PROOF IMAGE HANDLER =====
// ส่งรูปให้ลูกค้า ไม่จำกัดจำนวน — ยึดจากออเดอร์ล่าสุดที่กด "สั่งแล้ว"
// Admin กดสั่งแล้ว → ออเดอร์นั้นเป็น target → รูปทุกรูปที่ส่งมาจะไปหาลูกค้าคนนั้น
// ถ้า Admin กดสั่งแล้วให้คนใหม่ → target เปลี่ยนเป็นคนใหม่
async function handleBuyerProofImage(imageUrl: string, groupId: string) {
  // Find the most recent order marked as "received" (last one admin clicked "สั่งแล้ว")
  // No proofImageUrl filter — allows unlimited images per order
  const order = await prisma.purchaseOrder.findFirst({
    where: {
      status: 'received',
    },
    include: { contact: true, conversation: true },
    orderBy: { updatedAt: 'desc' },
  })

  if (!order) {
    console.log('[BUYER-PROOF] No active order found (no "สั่งแล้ว" orders)')
    return
  }

  const appUrl = process.env.NEXTAUTH_URL || process.env.NEXT_PUBLIC_APP_URL || 'https://chat.skjjapanshipping.com'
  const fullImageUrl = imageUrl.startsWith('http') ? imageUrl : `${appUrl}${imageUrl}`

  // Update proof image (keeps track of the latest proof sent)
  await prisma.purchaseOrder.update({
    where: { id: order.id },
    data: { proofImageUrl: imageUrl },
  })

  // Send ONLY image to customer — no text message
  const contact = order.contact
  const recipientId = contact.platformId.startsWith('group:') ? contact.platformId.replace('group:', '') : contact.platformId
  console.log(`[BUYER-PROOF] sending to ${contact.name} (${contact.platform}:${recipientId}) order:${order.id} url: ${fullImageUrl}`)

  try {
    if (contact.platform === 'line') {
      const { sendLineImage } = await import('@/lib/platforms/line')
      const result = await sendLineImage(recipientId, fullImageUrl)
      console.log(`[BUYER-PROOF] LINE API response:`, JSON.stringify(result))
    } else if (contact.platform === 'facebook') {
      const { sendFacebookImage } = await import('@/lib/platforms/facebook')
      await sendFacebookImage(recipientId, fullImageUrl)
    }

    // Save image message to conversation
    await prisma.message.create({ data: { conversationId: order.conversationId, direction: 'outbound', type: 'image', content: imageUrl, status: 'sent' } })
    await prisma.conversation.update({ where: { id: order.conversationId }, data: { lastMessage: '📷 รูปหลักฐาน', lastMessageAt: new Date() } })

    // No reply in group — quiet for batch sending unlimited images
    console.log(`[BUYER-PROOF] sent proof for order ${order.id} to ${contact.name}`)
  } catch (err: any) {
    console.error(`[BUYER-PROOF] error sending to ${contact.name}:`, err?.response?.data || err?.message || err)
  }
}
