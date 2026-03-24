import axios from 'axios'
import * as fs from 'fs'
import * as path from 'path'
import FormData from 'form-data'

const THUNDER_API_URL = 'https://api.thunder.in.th/v2/verify/bank'

export interface ThunderResult {
  success: boolean
  data?: {
    isDuplicate: boolean
    matchedAccount: any
    amountInSlip: number
    rawSlip: {
      transRef: string
      date: string
      amount: { amount: number }
      sender: {
        bank: { id: string; name: string; short: string }
        account: { name: { th: string; en?: string }; bank: { account: string } }
      }
      receiver: {
        bank: { id: string; name: string; short: string }
        account: { name: { th: string }; bank: { account: string } }
      }
    }
  }
  message?: string
  error?: string
}

/**
 * Verify a bank slip image via Thunder API v2
 * Sends the image URL to Thunder for verification
 */
export async function verifySlip(imageUrl: string): Promise<ThunderResult> {
  const apiKey = process.env.THUNDER_API_KEY
  if (!apiKey) {
    console.error('[Thunder] THUNDER_API_KEY not set')
    return { success: false, error: 'THUNDER_API_KEY not configured' }
  }

  try {
    const res = await axios.post(
      THUNDER_API_URL,
      { url: imageUrl },
      {
        headers: {
          Authorization: `Bearer ${apiKey}`,
          'Content-Type': 'application/json',
        },
        timeout: 15000,
      }
    )

    console.log('[Thunder] verify result:', JSON.stringify(res.data).substring(0, 800))
    return res.data as ThunderResult
  } catch (err: any) {
    const status = err?.response?.status
    const data = err?.response?.data

    // 404 = slip not found (not a bank slip image)
    if (status === 404) {
      console.log('[Thunder] Not a bank slip (404)')
      return { success: false, error: 'not_slip', message: data?.message || 'ไม่พบข้อมูลสลิป' }
    }

    // 400 = validation error (bad image etc.)
    if (status === 400) {
      console.log('[Thunder] Validation error:', JSON.stringify(data))
      return { success: false, error: 'validation', message: data?.message || data?.error?.message || 'รูปภาพไม่ถูกต้อง' }
    }

    // 403 = quota exceeded
    if (status === 403) {
      console.log('[Thunder] Quota exceeded')
      return { success: false, error: 'quota', message: 'โควต้าตรวจสลิปหมด' }
    }

    console.error('[Thunder] API error:', status, JSON.stringify(data) || err.message)
    return { success: false, error: 'api_error', message: err.message }
  }
}

/**
 * Verify a bank slip by uploading the file directly (fallback when URL-based fails)
 */
export async function verifySlipFromFile(localPath: string): Promise<ThunderResult> {
  const apiKey = process.env.THUNDER_API_KEY
  if (!apiKey) {
    return { success: false, error: 'THUNDER_API_KEY not configured' }
  }

  const fullPath = path.join(process.cwd(), 'public', localPath)
  if (!fs.existsSync(fullPath)) {
    console.error('[Thunder] File not found:', fullPath)
    return { success: false, error: 'file_not_found' }
  }

  try {
    // Use eval('require') to bypass Next.js webpack bundling — forces Node.js native require
    const NodeFormData = eval('require')('form-data')
    const form = new NodeFormData()
    const filename = path.basename(fullPath)
    form.append('image', fs.createReadStream(fullPath), {
      filename,
      contentType: 'image/jpeg',
    })

    console.log('[Thunder] file upload:', filename, fs.statSync(fullPath).size, 'bytes, headers:', JSON.stringify(form.getHeaders()))

    const res = await axios.post(THUNDER_API_URL, form, {
      headers: {
        ...form.getHeaders(),
        Authorization: `Bearer ${apiKey}`,
      },
      timeout: 15000,
    })

    console.log('[Thunder] file verify result:', JSON.stringify(res.data).substring(0, 800))
    return res.data as ThunderResult
  } catch (err: any) {
    const status = err?.response?.status
    const data = err?.response?.data
    if (status === 404) return { success: false, error: 'not_slip' }
    if (status === 400) {
      console.log('[Thunder] File validation error:', JSON.stringify(data))
      return { success: false, error: 'validation', message: data?.message || data?.error?.message || 'รูปภาพไม่ถูกต้อง' }
    }
    if (status === 403) return { success: false, error: 'quota' }
    console.error('[Thunder] File API error:', status, JSON.stringify(data) || err.message)
    return { success: false, error: 'api_error', message: err.message }
  }
}
