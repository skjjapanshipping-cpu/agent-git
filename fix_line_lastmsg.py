#!/usr/bin/env python3
"""Fix LINE webhook lastMessage and SSE to include video type"""

filepath = '/opt/skjchat/src/app/api/webhooks/line/route.ts'

with open(filepath, 'rb') as f:
    content = f.read().decode('utf-8')

# Fix group chat lastMessage (line ~610)
old = "msgType === 'sticker' ? '\U0001f9e1 สติกเกอร์' : msgType === 'file'"
new = "msgType === 'sticker' ? '\U0001f9e1 สติกเกอร์' : msgType === 'video' ? '\U0001f3ac วิดีโอ' : msgType === 'file'"

if old in content:
    content = content.replace(old, new)
    print('OK: Fixed group lastMessage')
else:
    print('SKIP: group lastMessage already fixed or not found')

with open(filepath, 'wb') as f:
    f.write(content.encode('utf-8'))

print('Done')
