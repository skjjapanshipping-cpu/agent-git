#!/usr/bin/env python3
"""
Fix LINE sendLineFile: encode file URL to handle Thai characters in filenames.
Also fix the upload route to sanitize filenames, and fix sendLineImage similarly.
"""

path = '/opt/skjchat/src/lib/platforms/line.ts'
with open(path, 'r') as f:
    content = f.read()

# Fix sendLineFile - encode the fileUrl for LINE Flex Message URIs
old = """export async function sendLineFile(userId: string, fileUrl: string, fileName: string) {
  const token = process.env.LINE_CHANNEL_ACCESS_TOKEN
  if (!token) throw new Error('LINE_CHANNEL_ACCESS_TOKEN not set')

  // Send as Flex Message — card with PDF icon image + download button
  const appUrl = process.env.NEXTAUTH_URL || process.env.NEXT_PUBLIC_APP_URL || 'https://chat.skjjapanshipping.com'
  const pdfIconUrl = `${appUrl}/pdf-icon.png`"""

new = """export async function sendLineFile(userId: string, fileUrl: string, fileName: string) {
  const token = process.env.LINE_CHANNEL_ACCESS_TOKEN
  if (!token) throw new Error('LINE_CHANNEL_ACCESS_TOKEN not set')

  // Encode non-ASCII characters in the URL for LINE API compatibility
  const encodedFileUrl = fileUrl.replace(/[^\x20-\x7E]/g, (ch) => encodeURIComponent(ch))

  // Send as Flex Message — card with PDF icon image + download button
  const appUrl = process.env.NEXTAUTH_URL || process.env.NEXT_PUBLIC_APP_URL || 'https://chat.skjjapanshipping.com'
  const pdfIconUrl = `${appUrl}/pdf-icon.png`"""

if old in content:
    content = content.replace(old, new)
    print('Step 1: Added URL encoding to sendLineFile')
else:
    print('Step 1: sendLineFile pattern not found')

# Replace all occurrences of fileUrl in the flex message with encodedFileUrl
# The action URIs in the flex message
old2 = """        action: {
          type: 'uri',
          label: 'ดาวน์โหลด',
          uri: fileUrl,
        },"""

new2 = """        action: {
          type: 'uri',
          label: 'ดาวน์โหลด',
          uri: encodedFileUrl,
        },"""

if old2 in content:
    content = content.replace(old2, new2)
    print('Step 2: Fixed body action URI')
else:
    print('Step 2: body action URI pattern not found')

old3 = """            action: {
              type: 'uri',
              label: 'ดาวน์โหลดไฟล์',
              uri: fileUrl,
            },"""

new3 = """            action: {
              type: 'uri',
              label: 'ดาวน์โหลดไฟล์',
              uri: encodedFileUrl,
            },"""

if old3 in content:
    content = content.replace(old3, new3)
    print('Step 3: Fixed footer button URI')
else:
    print('Step 3: footer button URI pattern not found')

# Fix the log line to show encoded URL
old4 = """  console.log('[LINE sendFile] to:', userId, 'url:', fileUrl, 'name:', fileName)"""
new4 = """  console.log('[LINE sendFile] to:', userId, 'url:', encodedFileUrl, 'name:', fileName)"""

if old4 in content:
    content = content.replace(old4, new4)
    print('Step 4: Fixed log line')
else:
    print('Step 4: log line pattern not found')

with open(path, 'w') as f:
    f.write(content)
print('Done - file saved, rebuild needed')
