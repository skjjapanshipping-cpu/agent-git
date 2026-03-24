'use client'

import React from 'react'
import { User, Check, CheckCheck, CornerUpLeft, ShieldCheck, AlertTriangle, ArrowUpRight, ArrowDownLeft, Hash, CreditCard } from 'lucide-react'
import InitialsAvatar from '@/components/InitialsAvatar'
import { cn, formatFullTime, proxyAvatar, convertEmojiText, parseLineEmoji, getLineEmojiUrl } from '@/lib/utils'
import type { Message } from '@/lib/types'

type Props = {
  msg: Message
  contactName: string
  contactAvatar: string | null
  onReply: (msg: Message) => void
  onImageClick: (src: string) => void
}

// Auto-detect URLs in text and render as clickable links
function linkifyText(text: string, direction: string): React.ReactNode {
  if (!text) return text
  const parts = text.split(/(https?:\/\/[^\s<>"']+)/g)
  if (parts.length === 1) return text
  return parts.map((part, i) =>
    /^https?:\/\//.test(part)
      ? <a key={i} href={part} target="_blank" rel="noopener noreferrer" className={cn('underline break-all', direction === 'outbound' ? 'text-blue-100 hover:text-white' : 'text-blue-600 hover:text-blue-800')}>{part}</a>
      : part
  )
}

function MessageBubble({ msg, contactName, contactAvatar, onReply, onImageClick }: Props) {
  // Sticker: no bubble, no click
  const isSticker = (msg.type === 'image' && msg.content.includes('/stickers/')) || msg.type === 'sticker'
  // Slip verification card
  let isSlipVerification = false
  let slipMeta: any = null
  try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; if (m?.slipVerification) { isSlipVerification = true; slipMeta = m } } catch {}
  // Flex/menu card (auto-reply with buttons)
  let isFlexCard = false
  let flexButtons: string[] = []
  try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; if (m?.isFlex && m?.buttons) { isFlexCard = true; flexButtons = m.buttons } } catch {}
  // Invoice card (import cost / thai shipping bill)
  let isInvoiceCard = false
  let invoiceMeta: any = null
  try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; if (m?.invoiceCard) { isInvoiceCard = true; invoiceMeta = m } } catch {}

  return (
    <div
      id={`msg-${msg.id}`}
      className={cn('flex group/msg', msg.direction === 'outbound' ? 'justify-end' : 'justify-start')}
    >
      {/* Customer avatar for inbound */}
      {msg.direction === 'inbound' && (() => {
        let avatarUrl = contactAvatar
        try {
          const meta = msg.metadata ? JSON.parse(msg.metadata) : null
          if (meta?.senderIcon) avatarUrl = meta.senderIcon
        } catch {}
        return (
          <div className="flex-shrink-0 mr-2 self-end mb-5">
            {avatarUrl ? (
              <>
                <img src={proxyAvatar(avatarUrl)!} alt="" className="w-7 h-7 rounded-full object-cover" onError={(e) => { (e.target as HTMLImageElement).style.display='none'; (e.target as HTMLImageElement).nextElementSibling?.classList.remove('hidden') }} />
                <div className="hidden"><InitialsAvatar name={contactName} size="sm" /></div>
              </>
            ) : (
              <InitialsAvatar name={contactName} size="sm" />
            )}
          </div>
        )
      })()}

      {/* Reply button (left of outbound) */}
      {msg.direction === 'outbound' && (
        <button
          onClick={() => onReply(msg)}
          className="self-center mr-1 p-1 rounded-full hover:bg-gray-200 text-gray-300 hover:text-gray-500 opacity-0 group-hover/msg:opacity-100 transition-opacity"
          title="ตอบกลับ"
        >
          <CornerUpLeft className="w-3.5 h-3.5" />
        </button>
      )}

      {isSlipVerification ? (() => {
        const lines = msg.content.split('\n')
        const isValid = msg.content.startsWith('✅')
        const statusLine = lines[0] || ''
        const amountLine = lines.find((l: string) => l.includes('💰')) || ''
        const senderLine = lines.find((l: string) => l.includes('📤')) || ''
        const receiverLine = lines.find((l: string) => l.includes('📥')) || ''
        const refLine = lines.find((l: string) => l.includes('🔖')) || ''
        const invoiceLine = lines.find((l: string) => l.includes('💳')) || ''
        const warningLines = lines.filter((l: string) => l.match(/^(🔁|👤|⏰|❌)/))
        const amountMatch = amountLine.match(/฿[\d,.]+/)
        const amount = amountMatch ? amountMatch[0] : '-'
        const dateMatch = amountLine.match(/\|\s*(.+)/)
        const date = dateMatch ? dateMatch[1].trim() : ''
        const sender = senderLine.replace(/^📤\s*/, '')
        const receiver = receiverLine.replace(/^📥\s*/, '')
        const ref = refLine.replace(/^🔖\s*/, '')
        const invoice = invoiceLine.replace(/^💳\s*/, '')
        return (
          <div className="max-w-[320px] md:max-w-[360px]">
            <div className={cn('rounded-2xl overflow-hidden shadow-sm border', isValid ? 'border-emerald-200' : 'border-amber-200')}>
              <div className={cn('px-4 py-3 flex items-center gap-2.5', isValid ? 'bg-gradient-to-r from-emerald-500 to-emerald-600' : 'bg-gradient-to-r from-amber-500 to-amber-600')}>
                {isValid ? <ShieldCheck className="w-5 h-5 text-white flex-shrink-0" /> : <AlertTriangle className="w-5 h-5 text-white flex-shrink-0" />}
                <span className="text-white font-semibold text-sm">{isValid ? 'สลิปถูกต้อง' : statusLine.replace(/^⚠️\s*ตรวจสลิป:\s*/, '')}</span>
              </div>
              <div className="bg-white px-4 py-3 space-y-2.5">
                <div className="text-center pb-2 border-b border-gray-100">
                  <p className={cn('text-2xl font-bold', isValid ? 'text-emerald-600' : 'text-amber-600')}>{amount}</p>
                  {date && <p className="text-[11px] text-gray-400 mt-0.5">{date}</p>}
                </div>
                <div className="flex items-start gap-2">
                  <ArrowUpRight className="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" />
                  <div><p className="text-[10px] text-gray-400 leading-none mb-0.5">ผู้โอน</p><p className="text-xs text-gray-700 font-medium">{sender}</p></div>
                </div>
                <div className="flex items-start gap-2">
                  <ArrowDownLeft className="w-4 h-4 text-emerald-400 mt-0.5 flex-shrink-0" />
                  <div><p className="text-[10px] text-gray-400 leading-none mb-0.5">ผู้รับ</p><p className="text-xs text-gray-700 font-medium">{receiver}</p></div>
                </div>
                <div className="flex items-start gap-2">
                  <Hash className="w-4 h-4 text-gray-300 mt-0.5 flex-shrink-0" />
                  <div><p className="text-[10px] text-gray-400 leading-none mb-0.5">Ref</p><p className="text-[11px] text-gray-500 font-mono break-all">{ref}</p></div>
                </div>
                {invoice && (
                  <div className="flex items-start gap-2 pt-1 border-t border-gray-100">
                    <CreditCard className="w-4 h-4 text-purple-400 mt-0.5 flex-shrink-0" />
                    <p className="text-xs text-purple-600 font-medium">{invoice}</p>
                  </div>
                )}
                {warningLines.length > 0 && (
                  <div className="pt-1 border-t border-gray-100 space-y-1">
                    {warningLines.map((w: string, i: number) => (
                      <p key={i} className="text-xs text-amber-700 bg-amber-50 rounded-lg px-2.5 py-1.5">{w}</p>
                    ))}
                  </div>
                )}
              </div>
            </div>
            <p className={cn('text-[10px] mt-1 flex items-center gap-1', msg.direction === 'outbound' ? 'text-gray-400 justify-end' : 'text-gray-400')}>
              {formatFullTime(msg.createdAt)}
              {msg.direction === 'outbound' && (msg.status === 'read' ? <><CheckCheck className="w-3 h-3" /> ลูกค้าอ่านแล้ว</> : <><Check className="w-3 h-3" /> ส่งแล้ว</>)}
            </p>
          </div>
        )
      })() : isInvoiceCard ? (() => {
        const isImport = invoiceMeta.cardType === 'import'
        const headerBg = isImport ? 'from-red-600 to-red-700' : 'from-sky-500 to-sky-600'
        const headerTitle = isImport ? 'ใบแจ้งค่านำเข้า' : 'บิลค่าส่งพัสดุในไทย'
        const amountLabel = isImport ? 'ยอดรวมค่านำเข้า' : 'ยอดรวมค่าส่งในไทย'
        const amountColor = isImport ? 'text-red-500' : 'text-sky-600'
        const btnColor = isImport ? 'bg-red-600 hover:bg-red-700' : 'bg-sky-500 hover:bg-sky-600'
        const btnLabel = isImport ? 'เปิดดูใบแจ้งหนี้' : 'เปิดดูบิลค่าส่ง'
        const amt = invoiceMeta.totalAmount != null ? Number(invoiceMeta.totalAmount).toLocaleString('th-TH', { minimumFractionDigits: 2 }) : null
        return (
          <div className="max-w-[320px] md:max-w-[360px]">
            <div className="rounded-2xl overflow-hidden shadow-sm border border-gray-200">
              {/* Header */}
              <div className={cn('bg-gradient-to-r px-4 py-3 flex items-center gap-2.5', headerBg)}>
                <img src="https://skjjapanshipping.com/skjtrack/img/skj-logo-icon.png" alt="" className="w-7 h-7 rounded-full bg-white/20 p-0.5 object-contain" />
                <div>
                  <p className="text-white font-semibold text-sm">{headerTitle}</p>
                  <p className="text-blue-100 text-[10px]">SKJ JAPAN SHIPPING</p>
                </div>
              </div>
              {/* Body */}
              <div className="bg-white px-4 py-3 space-y-2.5">
                <div>
                  <p className="text-[10px] text-gray-400">รหัสลูกค้า</p>
                  <p className="text-base font-bold text-gray-800">{invoiceMeta.customerno}</p>
                </div>
                <div className="border-t border-gray-100 pt-2 space-y-1.5">
                  {isImport && invoiceMeta.etd && (
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-400">รอบปิดตู้</span>
                      <span className="font-semibold text-gray-700">{invoiceMeta.etd}</span>
                    </div>
                  )}
                  {isImport && invoiceMeta.itemCount != null && (
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-400">จำนวน</span>
                      <span className="font-semibold text-gray-700">{invoiceMeta.itemCount} ชิ้น</span>
                    </div>
                  )}
                  {!isImport && invoiceMeta.originalFilename && (
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-400">ไฟล์</span>
                      <span className="font-semibold text-gray-700 truncate max-w-[180px]">{invoiceMeta.originalFilename}</span>
                    </div>
                  )}
                </div>
                {amt && (
                  <div className="border-t border-gray-100 pt-2 text-center">
                    <p className="text-[10px] text-gray-400">{amountLabel}</p>
                    <p className={cn('text-2xl font-bold', amountColor)}>฿{amt}</p>
                  </div>
                )}
                {invoiceMeta.qrUrl && (
                  <div className="border-t border-gray-100 pt-2 text-center">
                    <p className="text-[10px] text-gray-400 mb-1.5">สแกน QR Code เพื่อชำระเงิน</p>
                    <img src={invoiceMeta.qrUrl} alt="QR" className="mx-auto max-w-[160px] rounded-lg" />
                  </div>
                )}
              </div>
              {/* Footer — show all files or single file */}
              {invoiceMeta.allFileUrls && invoiceMeta.allFileUrls.length > 1 ? (
                <div className="px-4 py-2.5 bg-gray-50 border-t border-gray-100 space-y-1.5">
                  {invoiceMeta.allFileUrls.map((url: string, idx: number) => (
                    <a key={idx} href={url} target="_blank" rel="noopener noreferrer" className={cn('block text-center text-white text-sm font-semibold py-2 rounded-lg transition-colors', btnColor)}>
                      {btnLabel} {idx + 1}
                    </a>
                  ))}
                </div>
              ) : invoiceMeta.pdfUrl ? (
                <div className="px-4 py-2.5 bg-gray-50 border-t border-gray-100">
                  <a href={invoiceMeta.pdfUrl} target="_blank" rel="noopener noreferrer" className={cn('block text-center text-white text-sm font-semibold py-2 rounded-lg transition-colors', btnColor)}>
                    {btnLabel}
                  </a>
                </div>
              ) : null}
            </div>
            <p className={cn('text-[10px] mt-1 flex items-center gap-1', msg.direction === 'outbound' ? 'text-gray-400 justify-end' : 'text-gray-400')}>
              {formatFullTime(msg.createdAt)}
              {msg.direction === 'outbound' && (msg.status === 'read' ? <><CheckCheck className="w-3 h-3" /> ลูกค้าอ่านแล้ว</> : <><Check className="w-3 h-3" /> ส่งแล้ว</>)}
            </p>
          </div>
        )
      })() : isFlexCard ? (
        <div className="max-w-[320px] md:max-w-[360px]">
          <div className="rounded-2xl overflow-hidden shadow-sm border border-blue-200">
            <div className="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
              <p className="text-[10px] text-blue-200 font-bold tracking-wider">SKJ JAPAN SHIPPING</p>
              <p className="text-white font-semibold text-sm mt-1">🙏 ยินดีต้อนรับครับ</p>
              <p className="text-blue-200 text-xs mt-0.5">บริการขนส่งสินค้าจากญี่ปุ่นถึงไทย 🇯🇵📦🇹🇭</p>
            </div>
            <div className="bg-white px-4 py-3 space-y-1.5">
              <p className="text-sm font-bold text-gray-800">สนใจบริการแบบไหนดีครับ?</p>
              <p className="text-xs text-gray-400 mb-2">👇 กดเลือกด้านล่างได้เลย</p>
              <div className="border-t border-gray-100 pt-2 space-y-1.5">
                {flexButtons.map((btn, i) => {
                  const colors = ['bg-blue-500', 'bg-emerald-600', 'bg-gray-200 !text-gray-700', 'bg-gray-200 !text-gray-700']
                  return (
                    <div key={i} className={cn('text-center text-xs font-medium py-1.5 rounded-lg text-white', colors[i] || 'bg-gray-200 !text-gray-700')}>
                      {btn}
                    </div>
                  )
                })}
              </div>
            </div>
            <div className="bg-gray-50 px-4 py-2 text-center">
              <p className="text-[10px] text-gray-400">📞 สอบถาม โทร 082-460-9940</p>
            </div>
          </div>
          <p className={cn('text-[10px] mt-1 flex items-center gap-1', msg.direction === 'outbound' ? 'text-gray-400 justify-end' : 'text-gray-400')}>
            🤖 ระบบตอบอัตโนมัติ · {formatFullTime(msg.createdAt)}
            {msg.direction === 'outbound' && (msg.status === 'read' ? <><CheckCheck className="w-3 h-3 ml-1" /> ลูกค้าอ่านแล้ว</> : <><Check className="w-3 h-3 ml-1" /> ส่งแล้ว</>)}
          </p>
        </div>
      ) : isSticker ? (
        <div className={cn('flex flex-col', msg.direction === 'outbound' ? 'items-end' : 'items-start')}>
          {(() => {
            const match = msg.content.match(/\(sticker:\s*(\d+)-(\d+)\)/)
            if (match) {
              const stickerId = match[2]
              return <img src={`https://stickershop.line-scdn.net/stickershop/v1/sticker/${stickerId}/android/sticker.png`} alt="sticker" className="w-24 h-24 object-contain" />
            }
            return <img src={msg.content} alt="sticker" className="w-20 h-20 object-contain" />
          })()}
          <p className={cn('text-[10px] mt-1 flex items-center gap-1', msg.direction === 'outbound' ? 'text-gray-400 justify-end' : 'text-gray-400')}>
            {formatFullTime(msg.createdAt)}
            {msg.direction === 'outbound' && (msg.status === 'read' ? <><CheckCheck className="w-3 h-3" /> ลูกค้าอ่านแล้ว</> : <><Check className="w-3 h-3" /> ส่งแล้ว</>)}
          </p>
        </div>
      ) : (
        <div className={cn(
          'max-w-[80%] md:max-w-[65%] px-3.5 py-2.5 text-[14px] leading-relaxed',
          msg.direction === 'outbound' ? 'bubble-out' : 'bubble-in'
        )}>
          {msg.sender && msg.direction === 'outbound' && (
            <p className="text-[10px] text-blue-200 font-medium mb-0.5">{msg.sender.name}</p>
          )}
          {msg.direction === 'inbound' && (() => { try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; return m?.senderName ? <p className="text-[10px] text-purple-500 font-bold mb-0.5">{m.senderName}</p> : null; } catch { return null; } })()}
          {msg.type === 'image' ? (
            <img src={msg.content} alt="image" className="max-w-[200px] max-h-[200px] rounded-lg cursor-pointer object-contain" onClick={() => onImageClick(msg.content)} />
          ) : msg.type === 'audio' ? (
            <div className="flex flex-col gap-1">
              <audio controls className="max-w-[240px] h-10" preload="metadata">
                <source src={msg.content} type="audio/mp4" />
                <source src={msg.content} type="audio/mpeg" />
              </audio>
              <span className={cn('text-[10px]', msg.direction === 'outbound' ? 'text-blue-200' : 'text-gray-400')}>🎵 ข้อความเสียง</span>
            </div>
          ) : msg.type === 'file' ? (
            <a href={msg.content} target="_blank" rel="noopener noreferrer" className={cn('flex items-center gap-3 py-2 px-1', msg.direction === 'outbound' ? 'text-blue-100 hover:text-white' : 'text-blue-600 hover:text-blue-800')}>
              <svg viewBox="0 0 40 40" className="w-9 h-9 flex-shrink-0">
                <rect x="4" y="2" width="26" height="36" rx="3" fill="#E53E3E" />
                <path d="M22 2v10h10" fill="#FC8181" />
                <path d="M22 2l10 10v25a3 3 0 01-3 3H7a3 3 0 01-3-3V5a3 3 0 013-3h15z" fill="#E53E3E" />
                <path d="M22 2v10h10z" fill="#FC8181" />
                <text x="17" y="29" textAnchor="middle" fill="white" fontSize="9" fontWeight="bold" fontFamily="Arial">PDF</text>
              </svg>
              <span className="text-sm underline truncate">{(() => { try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; return m?.fileName || 'ดาวน์โหลดไฟล์'; } catch { return 'ดาวน์โหลดไฟล์'; }})()}</span>
            </a>
          ) : (msg.type === 'sticker' || msg.content?.includes('(sticker:')) ? (
            (() => {
              const m = msg.content?.match(/\(sticker:\s*(\d+)-(\d+)\)/)
              return m ? (
                <img src={`https://stickershop.line-scdn.net/stickershop/v1/sticker/${m[2]}/iPhone/sticker@2x.png`} alt="sticker" className="w-24 h-24 object-contain" />
              ) : <p className="text-2xl">🩷</p>
            })()
          ) : (() => {
            let replyMeta: any = null
            try { replyMeta = msg.metadata ? JSON.parse(msg.metadata) : null } catch {}
            const hasQuote = replyMeta?.replyToId
            const legacyMatch = msg.content.match(/^💬 ตอบกลับ: "(.+?)"\n\n([\s\S]*)$/)
            const actualContent = legacyMatch ? legacyMatch[2] : msg.content
            const legacyQuote = legacyMatch ? legacyMatch[1] : null
            return (
              <>
                {(hasQuote || legacyQuote) && (
                  <div
                    className={cn(
                      'text-[12px] px-2.5 py-1.5 rounded-lg mb-1.5 cursor-pointer border-l-2 transition-colors',
                      msg.direction === 'outbound'
                        ? 'bg-blue-500/30 border-blue-300 hover:bg-blue-500/50 text-blue-100'
                        : 'bg-gray-100 border-gray-300 hover:bg-gray-200 text-gray-600'
                    )}
                    onClick={() => {
                      const targetId = replyMeta?.replyToId
                      if (targetId) {
                        const el = document.getElementById(`msg-${targetId}`)
                        if (el) {
                          el.scrollIntoView({ behavior: 'smooth', block: 'center' })
                          el.classList.add('ring-2', 'ring-blue-400', 'rounded-xl')
                          setTimeout(() => el.classList.remove('ring-2', 'ring-blue-400', 'rounded-xl'), 2000)
                        }
                      }
                    }}
                  >
                    <p className={cn('text-[10px] font-bold mb-0.5', msg.direction === 'outbound' ? 'text-blue-200' : 'text-gray-400')}>
                      ↩ {replyMeta?.replyToDirection === 'inbound' ? (contactName || 'ลูกค้า') : 'คุณ'}
                    </p>
                    <p className="truncate">
                      {replyMeta?.replyToType === 'image' ? '📷 รูปภาพ' : (replyMeta?.replyToContent || legacyQuote || '').substring(0, 60)}
                    </p>
                  </div>
                )}
                <p className="whitespace-pre-wrap break-words">{(() => {
                  const raw = hasQuote ? actualContent : (legacyMatch ? legacyMatch[2] : msg.content)
                  const segments = parseLineEmoji(raw)
                  return segments.map((seg, si) =>
                    seg.type === 'line-emoji'
                      ? <img key={si} src={getLineEmojiUrl(seg.productId, seg.emojiId)} alt="" className="inline-block w-5 h-5 align-text-bottom mx-[1px]" />
                      : <span key={si}>{linkifyText(convertEmojiText(seg.value), msg.direction)}</span>
                  )
                })()}</p>
              </>
            )
          })()}
          <p className={cn('text-[10px] mt-1 flex items-center gap-1', msg.direction === 'outbound' ? 'text-blue-200 justify-end' : 'text-gray-400')}>
            {(() => { try { const m = msg.metadata ? JSON.parse(msg.metadata) : null; if (m?.autoReply) return '🤖 '; } catch {} return null; })()}
            {formatFullTime(msg.createdAt)}
            {msg.direction === 'outbound' && (
              msg.status === 'read'
                ? <><CheckCheck className="w-3 h-3" /> ลูกค้าอ่านแล้ว</>
                : <><Check className="w-3 h-3" /> ส่งแล้ว</>
            )}
          </p>
        </div>
      )}

      {/* Reply button (right of inbound) */}
      {msg.direction === 'inbound' && (
        <button
          onClick={() => onReply(msg)}
          className="self-center ml-1 p-1 rounded-full hover:bg-gray-200 text-gray-300 hover:text-gray-500 opacity-0 group-hover/msg:opacity-100 transition-opacity"
          title="ตอบกลับ"
        >
          <CornerUpLeft className="w-3.5 h-3.5" />
        </button>
      )}
    </div>
  )
}

export default React.memo(MessageBubble)
