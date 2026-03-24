#!/usr/bin/env python3
"""Fix LINE webhook group chat lastMessage to include video type"""

filepath = '/opt/skjchat/src/app/api/webhooks/line/route.ts'

with open(filepath, 'rb') as f:
    raw = f.read()

# Look for the pattern on line 610: ... 'sticker' ... 'file' ...
# We need to insert video between sticker and file checks
target = b": msgType === 'file' ? '\xf0\x9f\x93\x8e \xe0\xb8\x84\xe0\xb8\xb3\xe0\xb8\xa5\xe0\xb8\xb1\xe0\xb8\x87'"
# 📎 ไฟล์

replace = b": msgType === 'video' ? '\xf0\x9f\x8e\xac \xe0\xb8\xa7\xe0\xb8\xb4\xe0\xb8\x94\xe0\xb8\xb5\xe0\xb9\x82\xe0\xb8\xad' : msgType === 'file' ? '\xf0\x9f\x93\x8e \xe0\xb8\x84\xe0\xb8\xb3\xe0\xb8\xa5\xe0\xb8\xb1\xe0\xb8\x87'"
# 🎬 วิดีโอ : ... 📎 ไฟล์

count = raw.count(target)
print(f'Found {count} occurrence(s) of target pattern')

if count > 0:
    # Only replace the one that hasn't been fixed yet (the group chat one)
    # Check if already fixed
    if b"'video' ? '\xf0\x9f\x8e\xac" in raw:
        # Count existing video fixes
        existing = raw.count(b"'video' ? '\xf0\x9f\x8e\xac")
        print(f'Already have {existing} video fix(es)')
        if existing >= 2:
            print('All occurrences already fixed')
        else:
            raw = raw.replace(target, replace, 1)
            with open(filepath, 'wb') as f:
                f.write(raw)
            print('OK: Fixed 1 more occurrence')
    else:
        raw = raw.replace(target, replace)
        with open(filepath, 'wb') as f:
            f.write(raw)
        print(f'OK: Fixed all {count} occurrences')
else:
    print('Target not found, checking actual bytes around line 610...')
    lines = raw.split(b'\n')
    if len(lines) > 609:
        line610 = lines[609]
        print(f'Line 610 hex: {line610[:200].hex()}')
        print(f'Line 610 text: {line610.decode("utf-8", errors="replace")[:200]}')
