#!/usr/bin/env python3
"""Fix LINE webhook group chat lastMessage to include video type"""

filepath = '/opt/skjchat/src/app/api/webhooks/line/route.ts'

with open(filepath, 'rb') as f:
    raw = f.read()

# From hex dump: msgType === 'file' ? '📎 ไฟล์'
# 📎 = f09f938e, ไฟล์ = e0b984 e0b89f e0b8a5 e0b98c
target = b"msgType === 'file' ? '\xf0\x9f\x93\x8e \xe0\xb8\x84\xe0\xb8\x9f\xe0\xb8\xa5\xe0\xb9\x8c'"

# Wait, let me just use the string directly
target_str = "msgType === 'file' ? '📎 ไฟล์'"
replace_str = "msgType === 'video' ? '🎬 วิดีโอ' : msgType === 'file' ? '📎 ไฟล์'"

target_bytes = target_str.encode('utf-8')
replace_bytes = replace_str.encode('utf-8')

count = raw.count(target_bytes)
print(f'Found {count} occurrence(s)')

if count > 0:
    # Check how many already have video fix
    video_check = "msgType === 'video' ? '🎬 วิดีโอ'".encode('utf-8')
    existing = raw.count(video_check)
    print(f'Existing video fixes: {existing}')
    
    if existing >= count:
        print('All already fixed')
    else:
        # Replace only occurrences that don't already have video before them
        # Simple approach: replace all, since the replacement is idempotent
        # Actually we need to be careful - if we already fixed some, the target won't match those
        raw = raw.replace(target_bytes, replace_bytes)
        with open(filepath, 'wb') as f:
            f.write(raw)
        new_count = raw.count(video_check)
        print(f'OK: Now have {new_count} video fix(es)')
else:
    print('Target not found')
