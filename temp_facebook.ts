import axios from 'axios'

const FB_API = 'https://graph.facebook.com/v19.0'

async function fbPost(data: any) {
  const token = process.env.FACEBOOK_PAGE_ACCESS_TOKEN
  if (!token) throw new Error('FACEBOOK_PAGE_ACCESS_TOKEN not set')

  try {
    const res = await axios.post(
      `${FB_API}/me/messages`,
      data,
      { params: { access_token: token } }
    )
    return res.data
  } catch (err: any) {
    const fbError = err?.response?.data?.error || {}
    console.error('[FB API Error]', JSON.stringify({
      code: fbError.code,
      subcode: fbError.error_subcode,
      type: fbError.type,
      message: fbError.message,
    }))
    // Re-throw with a clearer message for outside-window errors
    if (fbError.code === 10 || (fbError.message && fbError.message.includes('outside of allowed window'))) {
      const betterError = new Error('ลูกค้าไม่ได้ส่งข้อความมาภายใน 24 ชม. — Facebook ไม่อนุญาตให้ส่งข้อความหาลูกค้าได้')
      ;(betterError as any).response = err.response
      throw betterError
    }
    throw err
  }
}

export async function sendFacebookMessage(recipientId: string, text: string) {
  return fbPost({
    messaging_type: 'RESPONSE',
    recipient: { id: recipientId },
    message: { text },
  })
}

export async function sendFacebookImage(recipientId: string, imageUrl: string) {
  return fbPost({
    messaging_type: 'RESPONSE',
    recipient: { id: recipientId },
    message: {
      attachment: {
        type: 'image',
        payload: { url: imageUrl, is_reusable: true },
      },
    },
  })
}

export async function sendFacebookFile(recipientId: string, fileUrl: string) {
  return fbPost({
    messaging_type: 'RESPONSE',
    recipient: { id: recipientId },
    message: {
      attachment: {
        type: 'file',
        payload: { url: fileUrl, is_reusable: true },
      },
    },
  })
}

export async function markFacebookAsSeen(recipientId: string) {
  const token = process.env.FACEBOOK_PAGE_ACCESS_TOKEN
  if (!token || !recipientId) return

  try {
    await axios.post(
      `${FB_API}/me/messages`,
      {
        recipient: { id: recipientId },
        sender_action: 'mark_seen',
      },
      { params: { access_token: token } }
    )
    console.log('[FB] mark_seen success')
  } catch (err: any) {
    console.log('[FB] mark_seen failed:', err?.response?.status, err?.response?.data?.error?.message || '')
  }
}

export async function sendFacebookGenericTemplate(recipientId: string, elements: Array<{ title: string; subtitle?: string; image_url?: string; buttons?: Array<{ type: string; title: string; url?: string; payload?: string }> }>) {
  return fbPost({
    messaging_type: 'RESPONSE',
    recipient: { id: recipientId },
    message: {
      attachment: {
        type: 'template',
        payload: {
          template_type: 'generic',
          image_aspect_ratio: 'square',
          elements: elements.slice(0, 1),
        },
      },
    },
  })
}

export async function sendFacebookButtonTemplate(recipientId: string, text: string, buttons: Array<{ type: string; title: string; url?: string; payload?: string }>) {
  return fbPost({
    messaging_type: 'RESPONSE',
    recipient: { id: recipientId },
    message: {
      attachment: {
        type: 'template',
        payload: {
          template_type: 'button',
          text: text.substring(0, 640),
          buttons: buttons.slice(0, 3),
        },
      },
    },
  })
}

export async function sendFacebookQuickReplies(recipientId: string, text: string, quickReplies: Array<{ title: string; payload: string }>) {
  return fbPost({
    messaging_type: 'RESPONSE',
    recipient: { id: recipientId },
    message: {
      text: text.substring(0, 2000),
      quick_replies: quickReplies.slice(0, 13).map(qr => ({
        content_type: 'text',
        title: qr.title.substring(0, 20),
        payload: qr.payload,
      })),
    },
  })
}

export function verifyFacebookSignature(
  rawBody: Buffer,
  signature: string
): boolean {
  const crypto = require('crypto')
  const secret = process.env.FACEBOOK_APP_SECRET
  if (!secret) return false

  const expectedSig = crypto
    .createHmac('sha256', secret)
    .update(rawBody)
    .digest('hex')
  return signature === `sha256=${expectedSig}`
}
