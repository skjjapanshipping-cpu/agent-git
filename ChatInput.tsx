'use client'

import React, { useRef } from 'react'
import { Send, Smile, ImageIcon, Paperclip, X, CornerUpLeft, Sticker, MonitorUp } from 'lucide-react'
import { cn } from '@/lib/utils'
import EmojiPicker from '@/components/EmojiPicker'
import StickerPicker from '@/components/StickerPicker'
import QuickReply from '@/components/QuickReply'
import type { Message } from '@/lib/types'

type Props = {
  platform: string
  inputText: string
  sending: boolean
  uploading: boolean
  replyTo: Message | null
  contactName: string
  showEmojiPicker: boolean
  showStickerPicker: boolean
  onInputChange: (val: string) => void
  onSend: () => void
  onTyping: () => void
  onPaste: (e: React.ClipboardEvent) => void
  onImageClick: () => void
  onFileClick: () => void
  onSetReplyTo: (msg: Message | null) => void
  onToggleEmoji: () => void
  onToggleSticker: () => void
  onCloseEmoji: () => void
  onCloseSticker: () => void
  onEmojiSelect: (emoji: string) => void
  onStickerSelect: (pkgId: string, stkId: string) => void
  onQuickReplySelect: (text: string) => void
  inputRef: React.Ref<HTMLTextAreaElement>
  imageInputRef: React.Ref<HTMLInputElement>
  fileInputRef: React.Ref<HTMLInputElement>
  onFileUpload: (e: React.ChangeEvent<HTMLInputElement>, type: 'image' | 'file') => void
  onScreenCapture?: () => void
}

function ChatInput({
  platform, inputText, sending, uploading, replyTo, contactName,
  showEmojiPicker: emojiOpen, showStickerPicker: stickerOpen,
  onInputChange, onSend, onTyping, onPaste,
  onImageClick, onFileClick, onSetReplyTo,
  onToggleEmoji, onToggleSticker, onCloseEmoji, onCloseSticker,
  onEmojiSelect, onStickerSelect, onQuickReplySelect,
  inputRef, imageInputRef, fileInputRef, onFileUpload, onScreenCapture,
}: Props) {
  return (
    <>
      <div className="flex-shrink-0 bg-white border-t border-gray-200 p-2.5 md:p-3 safe-bottom">
        {/* Hidden file inputs */}
        <input ref={imageInputRef} type="file" accept="image/*" multiple className="hidden" onChange={(e) => onFileUpload(e, 'image')} />
        <input ref={fileInputRef} type="file" className="hidden" onChange={(e) => onFileUpload(e, 'file')} />

        {/* Reply preview bar */}
        {replyTo && (
          <div className="flex items-center gap-2 px-3 py-2 mb-2 bg-blue-50 rounded-xl border-l-4 border-blue-400">
            <CornerUpLeft className="w-4 h-4 text-blue-500 flex-shrink-0" />
            <div className="flex-1 min-w-0">
              <p className="text-[10px] text-blue-500 font-bold">
                ตอบกลับ {replyTo.direction === 'inbound' ? (contactName || 'ลูกค้า') : 'ข้อความของคุณ'}
              </p>
              <p className="text-xs text-gray-600 truncate">
                {replyTo.type === 'image' ? '📷 รูปภาพ' : replyTo.type === 'sticker' ? '♥ สติกเกอร์' : replyTo.content.substring(0, 80)}
              </p>
            </div>
            {replyTo.type === 'image' && !replyTo.content.includes('sticker') && (
              <img src={replyTo.content} alt="" className="w-10 h-10 rounded object-cover flex-shrink-0" />
            )}
            <button onClick={() => onSetReplyTo(null)} className="p-1 hover:bg-blue-100 rounded-lg flex-shrink-0">
              <X className="w-3.5 h-3.5 text-blue-400" />
            </button>
          </div>
        )}

        {uploading && (
          <div className="flex items-center gap-2 px-3 py-2 mb-2 bg-blue-50 rounded-xl text-xs text-blue-600">
            <span className="animate-spin w-4 h-4 border-2 border-blue-300 border-t-blue-600 rounded-full" />
            กำลังอัพโหลด...
          </div>
        )}

        <div className="flex items-end gap-1.5">
          <div className="relative">
            <button
              onClick={onToggleEmoji}
              className={cn('p-2.5 hover:bg-gray-100 active:bg-gray-200 rounded-xl transition-colors', emojiOpen ? 'text-blue-500 bg-blue-50' : 'text-gray-500')}
              title="อิโมจิ / สติกเกอร์"
            >
              <Smile className="w-5 h-5" />
            </button>
            {emojiOpen && (
              <EmojiPicker
                onSelect={onEmojiSelect}
                onClose={onCloseEmoji}
              />
            )}
          </div>
          {/* Sticker picker — LINE only */}
          {platform === 'line' && (
            <div className="relative">
              <button
                onClick={onToggleSticker}
                className={cn('p-2.5 hover:bg-gray-100 active:bg-gray-200 rounded-xl transition-colors', stickerOpen ? 'bg-purple-50 text-purple-500' : 'text-gray-500')}
                title="สติกเกอร์"
              >
                <Sticker className="w-5 h-5" />
              </button>
              {stickerOpen && (
                <StickerPicker
                  onSelect={onStickerSelect}
                  onClose={onCloseSticker}
                />
              )}
            </div>
          )}
          <button
            onClick={onImageClick}
            disabled={uploading}
            className="p-2.5 hover:bg-gray-100 active:bg-gray-200 rounded-xl text-gray-500 transition-colors disabled:opacity-40"
            title="ส่งรูปภาพ"
          >
            <ImageIcon className="w-5 h-5" />
          </button>
          <button
            onClick={onFileClick}
            disabled={uploading}
            className="p-2.5 hover:bg-gray-100 active:bg-gray-200 rounded-xl text-gray-500 transition-colors disabled:opacity-40"
            title="ส่งไฟล์"
          >
            <Paperclip className="w-5 h-5" />
          </button>
          {onScreenCapture && (
            <button
              onClick={onScreenCapture}
              disabled={uploading}
              className="p-2.5 hover:bg-gray-100 active:bg-gray-200 rounded-xl text-gray-500 transition-colors disabled:opacity-40"
              title="แคปหน้าจอ"
            >
              <MonitorUp className="w-5 h-5" />
            </button>
          )}
          <textarea
            ref={inputRef}
            value={inputText}
            onChange={(e) => { onInputChange(e.target.value); onTyping() }}
            onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); onSend() } }}
            onPaste={onPaste}
            placeholder="พิมพ์ข้อความ..."
            rows={1}
            className="flex-1 bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-[15px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none max-h-32 overflow-y-auto"
            style={{ minHeight: '48px' }}
            onInput={(e) => {
              const target = e.target as HTMLTextAreaElement
              target.style.height = 'auto'
              target.style.height = Math.min(target.scrollHeight, 128) + 'px'
            }}
          />
          <button
            onClick={onSend}
            disabled={!inputText.trim() || sending}
            className="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-40 text-white rounded-2xl p-3 transition-colors flex-shrink-0"
          >
            <Send className="w-5 h-5" />
          </button>
        </div>
      </div>
      {/* Quick Reply buttons */}
      <QuickReply onSelect={onQuickReplySelect} />
    </>
  )
}

export default React.memo(ChatInput)
