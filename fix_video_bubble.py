#!/usr/bin/env python3
import sys

filepath = '/opt/skjchat/src/components/MessageBubble.tsx'

with open(filepath, 'rb') as f:
    raw = f.read()
content = raw.decode('utf-8', errors='surrogateescape')

old_block = """          ) : msg.type === 'file' ? (
            <a href={msg.content} target="_blank" rel="noopener noreferrer" className={cn('flex items-center gap-3 py-2 px-1', msg.direction === 'outbound' ? 'text-blue-100 hover:text-white' : 'text-blue-600 hover:text-blue-800')}>
              <svg viewBox="0 0 40 40" className="w-9 h-9 flex-shrink-0">
                <rect x="4" y="2" width="26" height="36" rx="3" fill="#E53E3E" />
                <path d="M22 2v10h10" fill="#FC8181" />
                <path d="M22 2l10 10v25a3 3 0 01-3 3H7a3 3 0 01-3-3V5a3 3 0 013-3h15z" fill="#E53E3E" />
                <path d="M22 2v10h10z" fill="#FC8181" />
                <text x="17" y="29" textAnchor="middle" fill="white" fontSize="9" fontWeight="bold" fontFamily="Arial">PDF</text>
              </svg>
              <span className="text-sm underline truncate">{(() => { try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; return m?.fileName || '\u0e14\u0e32\u0e27\u0e19\u0e4c\u0e42\u0e2b\u0e25\u0e14\u0e44\u0e1f\u0e25\u0e4c'; } catch { return '\u0e14\u0e32\u0e27\u0e19\u0e4c\u0e42\u0e2b\u0e25\u0e14\u0e44\u0e1f\u0e25\u0e4c'; }})()}</span>
            </a>"""

new_block = """          ) : (msg.type === 'video' || (msg.type === 'file' && /\\.(mp4|mov|avi|webm)([?#]|$)/i.test(msg.content))) ? (
            <div className="flex flex-col gap-1">
              <video controls className="max-w-[240px] max-h-[200px] rounded-lg" preload="metadata">
                <source src={msg.content} />
              </video>
              <span className={cn('text-[10px]', msg.direction === 'outbound' ? 'text-blue-200' : 'text-gray-400')}>\ud83c\udfac \u0e27\u0e34\u0e14\u0e35\u0e42\u0e2d</span>
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
              <span className="text-sm underline truncate">{(() => { try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; return m?.fileName || '\u0e14\u0e32\u0e27\u0e19\u0e4c\u0e42\u0e2b\u0e25\u0e14\u0e44\u0e1f\u0e25\u0e4c'; } catch { return '\u0e14\u0e32\u0e27\u0e19\u0e4c\u0e42\u0e2b\u0e25\u0e14\u0e44\u0e1f\u0e25\u0e4c'; }})()}</span>
            </a>"""

if old_block in content:
    content = content.replace(old_block, new_block)
    with open(filepath, 'wb') as f:
        f.write(content.encode('utf-8', errors='surrogateescape'))
    print('OK: replaced file block with video+file handling')
else:
    print('ERROR: old block not found, trying to find it...')
    lines = content.split('\n')
    for i, line in enumerate(lines):
        if "msg.type === 'file'" in line:
            print(f'  Line {i+1}: {line.strip()[:100]}')
