import { NextRequest, NextResponse } from 'next/server'
import { getServerSession } from 'next-auth'
import { authOptions } from '@/lib/auth'
import { prisma } from '@/lib/prisma'
import { sendFacebookMessage, sendFacebookImage, sendFacebookFile } from '@/lib/platforms/facebook'
import { sendLineMessage, sendLineImage, sendLineFile, sendLineSticker } from '@/lib/platforms/line'

// POST — Send a reply message (text, image, file)
export async function POST(
  req: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const conversationId = params.id
    const { content, type = 'text', fileName, packageId, stickerId, replyTo } = await req.json()

    // Get admin session for sender tracking
    const session = await getServerSession(authOptions)
    const senderId = (session?.user as any)?.id || null

    if (!content) {
      return NextResponse.json({ error: 'Content is required' }, { status: 400 })
    }

    // Get conversation + contact
    const conversation = await prisma.conversation.findUnique({
      where: { id: conversationId },
      include: { contact: true },
    })

    if (!conversation) {
      return NextResponse.json({ error: 'Conversation not found' }, { status: 404 })
    }

    // For images/files, we need the full URL for platform APIs
    const baseUrl = process.env.NEXTAUTH_URL || 'http://localhost:3000'
    const fullUrl = content.startsWith('http') ? content : `${baseUrl}${content}`

    // Send to platform based on type
    let platformMsgId = null
    let lineQuoteToken: string | null = null
    try {
      // For LINE groups, platformId is stored as "group:{groupId}" — strip prefix for API calls
      const rawPlatformId = conversation.contact.platformId
      const platformId = rawPlatformId.startsWith('group:') ? rawPlatformId.replace('group:', '') : rawPlatformId

      if (conversation.platform === 'facebook') {
        if (type === 'image') {
          const result = await sendFacebookImage(platformId, fullUrl)
          platformMsgId = result.message_id
        } else if (type === 'file') {
          const result = await sendFacebookFile(platformId, fullUrl)
          platformMsgId = result.message_id
        } else {
          const result = await sendFacebookMessage(platformId, content)
          platformMsgId = result.message_id
        }
      } else if (conversation.platform === 'line') {
        // Look up quoteToken if replying to a specific message
        let quoteToken: string | undefined
        if (replyTo?.id) {
          try {
            const originalMsg = await prisma.message.findUnique({ where: { id: replyTo.id } })
            if (originalMsg?.metadata) {
              const meta = JSON.parse(originalMsg.metadata)
              quoteToken = meta.quoteToken || undefined
            }
          } catch {}
        }

        // Send and capture quoteToken from LINE push API response for future quoting
        let lineResult: any
        if (type === 'sticker' && packageId && stickerId) {
          lineResult = await sendLineSticker(platformId, packageId, stickerId)
        } else if (type === 'image') {
          lineResult = await sendLineImage(platformId, fullUrl)
        } else if (type === 'file') {
          lineResult = await sendLineFile(platformId, fullUrl, fileName || 'file')
        } else {
          lineResult = await sendLineMessage(platformId, content, quoteToken)
        }

        // Extract quoteToken from sentMessages response so this outbound message can be quoted later
        if (lineResult?.sentMessages?.[0]) {
          platformMsgId = lineResult.sentMessages[0].id || null
          lineQuoteToken = lineResult.sentMessages[0].quoteToken || null
        }
      }
    } catch (platformError: any) {
      console.error(`Failed to send ${conversation.platform} message:`, platformError)
      return NextResponse.json(
        { error: `Failed to send message via ${conversation.platform}`, details: platformError.message },
        { status: 502 }
      )
    }

    // Determine lastMessage text
    let lastMsg = content.substring(0, 200)
    if (type === 'sticker') lastMsg = '♥ สติกเกอร์'
    else if (type === 'image') lastMsg = '📷 รูปภาพ'
    else if (type === 'file') lastMsg = `📎 ${fileName || 'ไฟล์'}`

    // Save message (include quoteToken from LINE response so this message can be quoted later)
    const message = await prisma.message.create({
      data: {
        conversationId,
        direction: 'outbound',
        type,
        content,
        platformMsgId,
        senderId,
        status: 'sent',
        metadata: (() => {
          const meta: any = {}
          if (fileName) meta.fileName = fileName
          if (lineQuoteToken) meta.quoteToken = lineQuoteToken
          if (replyTo) {
            meta.replyToId = replyTo.id
            meta.replyToContent = replyTo.content
            meta.replyToType = replyTo.type
            meta.replyToDirection = replyTo.direction || 'inbound'
          }
          return Object.keys(meta).length > 0 ? JSON.stringify(meta) : null
        })(),
      },
    })

    // Update conversation
    await prisma.conversation.update({
      where: { id: conversationId },
      data: {
        lastMessage: lastMsg,
        lastMessageAt: new Date(),
      },
    })

    return NextResponse.json(message)
  } catch (error) {
    console.error('Send message error:', error)
    return NextResponse.json({ error: 'Internal error' }, { status: 500 })
  }
}
