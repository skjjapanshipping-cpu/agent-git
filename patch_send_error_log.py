#!/usr/bin/env python3
"""Add detailed error logging for LINE API errors in send route and line.ts"""

# 1. Fix send route - log full error response
path1 = '/opt/skjchat/src/app/api/conversations/[id]/send/route.ts'
with open(path1, 'r') as f:
    content = f.read()

old = """    } catch (platformError: any) {
      console.error(`Failed to send ${conversation.platform} message:`, platformError)
      return NextResponse.json(
        { error: `Failed to send message via ${conversation.platform}`, details: platformError.message },
        { status: 502 }
      )
    }"""

new = """    } catch (platformError: any) {
      const errData = platformError?.response?.data
      console.error(`Failed to send ${conversation.platform} message:`, platformError.message, 'status:', platformError?.response?.status, 'data:', JSON.stringify(errData))
      return NextResponse.json(
        { error: `Failed to send message via ${conversation.platform}`, details: platformError.message, lineError: errData },
        { status: 502 }
      )
    }"""

if old in content:
    content = content.replace(old, new)
    with open(path1, 'w') as f:
        f.write(content)
    print('Updated send route error logging')
else:
    print('send route pattern not found')

# 2. Fix line.ts sendLineImage - log the URL being sent and error details
path2 = '/opt/skjchat/src/lib/platforms/line.ts'
with open(path2, 'r') as f:
    content2 = f.read()

# Add logging to sendLineImage
old2 = """export async function sendLineImage(userId: string, imageUrl: string) {
  const token = process.env.LINE_CHANNEL_ACCESS_TOKEN
  if (!token) throw new Error('LINE_CHANNEL_ACCESS_TOKEN not set')

  const res = await axios.post(
    `${LINE_API}/message/push`,
    {
      to: userId,
      messages: [{
        type: 'image',
        originalContentUrl: imageUrl,
        previewImageUrl: imageUrl,
      }],
    },
    {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    }
  )
  return res.data
}"""

new2 = """export async function sendLineImage(userId: string, imageUrl: string) {
  const token = process.env.LINE_CHANNEL_ACCESS_TOKEN
  if (!token) throw new Error('LINE_CHANNEL_ACCESS_TOKEN not set')

  console.log('[LINE sendImage] to:', userId, 'url:', imageUrl)
  try {
    const res = await axios.post(
      `${LINE_API}/message/push`,
      {
        to: userId,
        messages: [{
          type: 'image',
          originalContentUrl: imageUrl,
          previewImageUrl: imageUrl,
        }],
      },
      {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      }
    )
    return res.data
  } catch (err: any) {
    console.error('[LINE sendImage] ERROR:', err?.response?.status, JSON.stringify(err?.response?.data))
    throw err
  }
}"""

if old2 in content2:
    content2 = content2.replace(old2, new2)
    print('Updated sendLineImage logging')
else:
    print('sendLineImage pattern not found')

# Add logging to sendLineFile
old3 = """  const res = await axios.post(
    `${LINE_API}/message/push`,
    {
      to: userId,
      messages: [flexMessage],
    },
    {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    }
  )
  return res.data
}

export async function sendLineSticker"""

new3 = """  console.log('[LINE sendFile] to:', userId, 'url:', fileUrl, 'name:', fileName)
  try {
    const res = await axios.post(
      `${LINE_API}/message/push`,
      {
        to: userId,
        messages: [flexMessage],
      },
      {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      }
    )
    return res.data
  } catch (err: any) {
    console.error('[LINE sendFile] ERROR:', err?.response?.status, JSON.stringify(err?.response?.data))
    throw err
  }
}

export async function sendLineSticker"""

if old3 in content2:
    content2 = content2.replace(old3, new3)
    print('Updated sendLineFile logging')
else:
    print('sendLineFile pattern not found')

with open(path2, 'w') as f:
    f.write(content2)
print('Done - rebuild needed')
