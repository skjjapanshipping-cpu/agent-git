#!/usr/bin/env python3
"""Replace lines 292-302 in MessageBubble.tsx to add video support"""

filepath = '/opt/skjchat/src/components/MessageBubble.tsx'

with open(filepath, 'rb') as f:
    lines = f.readlines()

# Line 292 is index 291 (0-based), through line 302 is index 301
# Verify line 292 contains "msg.type === 'file'"
line292 = lines[291].decode('utf-8', errors='replace').strip()
print(f'Line 292: {line292[:80]}')

if "msg.type === 'file'" not in line292:
    print('ERROR: Line 292 does not contain expected content')
    exit(1)

# Get the fileName display logic from original line 301
origFileNameLine = lines[300].decode('utf-8', errors='replace')
print(f'Line 301 (fileName): {origFileNameLine.strip()[:80]}')

# Build replacement lines (keeping original indentation)
replacement = b"""          ) : (msg.type === 'video' || (msg.type === 'file' && /\\.(mp4|mov|avi|webm)([?#]|$)/i.test(msg.content))) ? (
            <div className="flex flex-col gap-1">
              <video controls className="max-w-[240px] max-h-[200px] rounded-lg" preload="metadata">
                <source src={msg.content} />
              </video>
              <span className={cn('text-[10px]', msg.direction === 'outbound' ? 'text-blue-200' : 'text-gray-400')}>\xf0\x9f\x8e\xac \xe0\xb8\xa7\xe0\xb8\xb4\xe0\xb8\x94\xe0\xb8\xb5\xe0\xb9\x82\xe0\xb8\xad</span>
            </div>
          ) : msg.type === 'file' ? (
            <a href={msg.content} target="_blank" rel="noopener noreferrer" className={cn('flex items-center gap-3 py-2 px-1', msg.direction === 'outbound' ? 'text-blue-100 hover:text-white' : 'text-blue-600 hover:text-blue-800')}>
              {/\\.pdf([?#]|$)/i.test(msg.content) ? (
                <svg viewBox="0 0 40 40" className="w-9 h-9 flex-shrink-0">
                  <rect x="4" y="2" width="26" height="36" rx="3" fill="#E53E3E" />
                  <path d="M22 2v10h10" fill="#FC8181" />
                  <path d="M22 2l10 10v25a3 3 0 01-3 3H7a3 3 0 01-3-3V5a3 3 0 013-3h15z" fill="#E53E3E" />
                  <path d="M22 2v10h10z" fill="#FC8181" />
                  <text x="17" y="29" textAnchor="middle" fill="white" fontSize="9" fontWeight="bold" fontFamily="Arial">PDF</text>
                </svg>
              ) : (
                <svg viewBox="0 0 40 40" className="w-9 h-9 flex-shrink-0">
                  <rect x="4" y="2" width="26" height="36" rx="3" fill="#4299E1" />
                  <path d="M22 2l10 10v25a3 3 0 01-3 3H7a3 3 0 01-3-3V5a3 3 0 013-3h15z" fill="#4299E1" />
                  <path d="M22 2v10h10z" fill="#90CDF4" />
                  <text x="17" y="29" textAnchor="middle" fill="white" fontSize="8" fontWeight="bold" fontFamily="Arial">FILE</text>
                </svg>
              )}
"""

# Keep original fileName span line (line 301) and closing </a> (line 302)
new_lines = lines[:291] + [replacement] + lines[300:]

with open(filepath, 'wb') as f:
    f.writelines(new_lines)

# Verify
with open(filepath, 'rb') as f:
    verify = f.readlines()
print(f'Total lines: {len(lines)} -> {len(verify)}')
print(f'New line 292: {verify[291].decode("utf-8", errors="replace").strip()[:80]}')
print('OK: Video + file handling added successfully')
