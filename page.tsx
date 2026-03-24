'use client'

import { useEffect, useState, useRef, useCallback } from 'react'
import { MessageCircle, Search, Send, Facebook, X, Paperclip, Eye, CheckSquare, Trash2, LogOut, UserCircle } from 'lucide-react'
import ContactPanel from '@/components/ContactPanel'
import ImageLightbox from '@/components/ImageLightbox'
import MessageBubble from '@/components/MessageBubble'
import AlbumBubble from '@/components/AlbumBubble'
import ConversationItem, { ConversationContextMenu } from '@/components/ConversationItem'
import ChatHeader from '@/components/ChatHeader'
import ChatInput from '@/components/ChatInput'
import CommentsPanel from '@/components/CommentsPanel'
import AutoReplySettings from '@/components/AutoReplySettings'
import GlobalSearch from '@/components/GlobalSearch'
import ScheduleMessage from '@/components/ScheduleMessage'
import MessageTemplates from '@/components/MessageTemplates'
import CustomerInsights from '@/components/CustomerInsights'
import RichMenuManager from '@/components/RichMenuManager'
import ScreenCapture from '@/components/ScreenCapture'
import ToastContainer, { showToast } from '@/components/Toast'
import NotificationCenter from '@/components/NotificationCenter'
import SidebarMoreMenu from '@/components/SidebarMoreMenu'
import { cn, formatTime, proxyAvatar } from '@/lib/utils'
import type { Contact, Conversation, Message, MobileView } from '@/lib/types'

export default function InboxPage() {
  const [conversations, setConversations] = useState<Conversation[]>([])
  const [activeConvo, setActiveConvo] = useState<Conversation | null>(null)
  const [messages, setMessages] = useState<Message[]>([])
  const [inputText, setInputText] = useState('')
  const [searchText, setSearchText] = useState('')
  const [filterPlatform, setFilterPlatform] = useState<string>('')
  const [filterStatus, setFilterStatus] = useState('all')
  const [sending, setSending] = useState(false)
  const [mobileView, setMobileView] = useState<MobileView>('list')
  const [editingName, setEditingName] = useState(false)
  const [editNameValue, setEditNameValue] = useState('')
  const [showContactPanel, setShowContactPanel] = useState(false)
  const [uploading, setUploading] = useState(false)
  const [chatSearch, setChatSearch] = useState('')
  const [chatSearchOpen, setChatSearchOpen] = useState(false)
  const [chatSearchResults, setChatSearchResults] = useState<number>(0)
  const [dragOver, setDragOver] = useState(false)
  const [loadingMessages, setLoadingMessages] = useState(false)
  const [showEmojiPicker, setShowEmojiPicker] = useState(false)
  const [showStickerPicker, setShowStickerPicker] = useState(false)
  const [showComments, setShowComments] = useState(false)
  const [showAutoReply, setShowAutoReply] = useState(false)
  const [showGlobalSearch, setShowGlobalSearch] = useState(false)
  const [showSchedule, setShowSchedule] = useState(false)
  const [deferredPrompt, setDeferredPrompt] = useState<any>(null)
  const [showTemplates, setShowTemplates] = useState(false)
  const [showInsights, setShowInsights] = useState(false)
  const [showRichMenu, setShowRichMenu] = useState(false)
  const [pendingFiles, setPendingFiles] = useState<File[]>([])
  const [pendingFilePreviews, setPendingFilePreviews] = useState<string[]>([])
  const [contextMenu, setContextMenu] = useState<{ x: number; y: number; convo: Conversation } | null>(null)
  const [previewMessages, setPreviewMessages] = useState<any[] | null>(null)
  const [previewConvo, setPreviewConvo] = useState<Conversation | null>(null)
  const [replyTo, setReplyTo] = useState<Message | null>(null)
  const [adminName, setAdminName] = useState<string>('')
  const [hasMoreMessages, setHasMoreMessages] = useState(false)
  const [loadingOlder, setLoadingOlder] = useState(false)
  const [lightboxOpen, setLightboxOpen] = useState(false)
  const [lightboxImages, setLightboxImages] = useState<string[]>([])
  const [lightboxIndex, setLightboxIndex] = useState(0)
  const [sidebarWidth, setSidebarWidth] = useState(380)
  const isResizingRef = useRef(false)
  const sidebarRef = useRef<HTMLDivElement>(null)
  const [typingNames, setTypingNames] = useState<string[]>([])
  const [bulkMode, setBulkMode] = useState(false)
  const [bulkSelected, setBulkSelected] = useState<Set<string>>(new Set())
  const [hasMoreConvos, setHasMoreConvos] = useState(false)
  const [loadingMoreConvos, setLoadingMoreConvos] = useState(false)
  const nextConvoCursorRef = useRef<string | null>(null)
  const msgCacheRef = useRef<Record<string, Message[]>>({})
  const lastMsgCountRef = useRef<number>(0)
  const lastMsgIdRef = useRef<string>('')
  const messagesEndRef = useRef<HTMLDivElement>(null)
  const inputRef = useRef<HTMLTextAreaElement>(null)
  const chatContainerRef = useRef<HTMLDivElement>(null)
  const editNameRef = useRef<HTMLInputElement>(null)
  const fileInputRef = useRef<HTMLInputElement>(null)
  const imageInputRef = useRef<HTMLInputElement>(null)
  const prevUnreadRef = useRef<number>(0)
  const audioRef = useRef<HTMLAudioElement | null>(null)
  const notifPermRef = useRef<boolean>(false)

  // Sidebar resize drag handler
  useEffect(() => {
    const saved = localStorage.getItem('skjchat-sidebar-width')
    if (saved) setSidebarWidth(Math.max(280, Math.min(600, parseInt(saved))))
    const onMouseMove = (e: MouseEvent) => {
      if (!isResizingRef.current) return
      e.preventDefault()
      const newW = Math.max(280, Math.min(600, e.clientX))
      setSidebarWidth(newW)
    }
    const onMouseUp = () => {
      if (isResizingRef.current) {
        isResizingRef.current = false
        document.body.style.cursor = ''
        document.body.style.userSelect = ''
        localStorage.setItem('skjchat-sidebar-width', String(sidebarRef.current ? sidebarRef.current.offsetWidth : 380))
      }
    }
    window.addEventListener('mousemove', onMouseMove)
    window.addEventListener('mouseup', onMouseUp)
    return () => { window.removeEventListener('mousemove', onMouseMove); window.removeEventListener('mouseup', onMouseUp) }
  }, [])

  const startResize = useCallback(() => {
    isResizingRef.current = true
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'
  }, [])

  // Init: audio + notification permission + fetch admin name + service worker + scheduled cron
  useEffect(() => {
    // Create notification sound
    audioRef.current = new Audio('/notification.wav')
    audioRef.current.volume = 0.7

    // Request browser notification permission
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission().then(p => { notifPermRef.current = p === 'granted' })
    } else if ('Notification' in window && Notification.permission === 'granted') {
      notifPermRef.current = true
    }

    // Fetch admin session name
    fetch('/api/auth/session').then(r => r.json()).then(s => {
      if (s?.user?.name) setAdminName(s.user.name)
    }).catch(() => {})

    // Register service worker for PWA
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch(() => {})
    }

    // PWA install prompt
    const handleInstall = (e: Event) => { e.preventDefault(); setDeferredPrompt(e) }
    window.addEventListener('beforeinstallprompt', handleInstall)

    // Cron: process scheduled messages every 30s
    const cronInterval = setInterval(() => {
      fetch('/api/cron/send-scheduled').catch(() => {})
    }, 30000)

    return () => {
      window.removeEventListener('beforeinstallprompt', handleInstall)
      clearInterval(cronInterval)
    }
  }, [])

  // SSE real-time listener — instant updates when webhook receives messages
  useEffect(() => {
    let es: EventSource | null = null
    try {
      es = new EventSource('/api/sse')
      es.addEventListener('new_message', (e) => {
        const data = JSON.parse(e.data)
        // Clear typing indicator for this conversation
        setTypingNames([])
        // Refresh conversation list immediately
        loadConversations()
        // If viewing this conversation, refresh messages too
        if (activeConvo?.id === data.conversationId) {
          loadMessages(data.conversationId, false)
        }
      })
      es.addEventListener('typing', (e) => {
        const data = JSON.parse(e.data)
        if (activeConvo?.id === data.conversationId) {
          setTypingNames([data.contactName])
          setTimeout(() => setTypingNames([]), 3000)
        }
      })
      es.addEventListener('read_receipt', (e) => {
        const data = JSON.parse(e.data)
        if (activeConvo?.id === data.conversationId) {
          // Refresh messages to show ✓✓ read status
          loadMessages(data.conversationId, false)
        }
      })
      es.onerror = () => {
        // Auto-reconnect handled by EventSource
      }
    } catch {}
    return () => { es?.close() }
  }, [activeConvo?.id])

  // Update tab title with unread count
  function updateTabTitle(unread: number) {
    document.title = unread > 0 ? `(${unread}) SKJ Chat — Unified Inbox` : 'SKJ Chat — Unified Inbox'
  }

  // Play sound + show browser notification
  function notifyNewMessage(count: number) {
    // Play sound
    if (audioRef.current) {
      audioRef.current.currentTime = 0
      audioRef.current.play().catch(() => {})
    }

    // Browser notification
    if (notifPermRef.current) {
      const n = new Notification('SKJ Chat — ข้อความใหม่', {
        body: `มี ${count} ข้อความใหม่ที่ยังไม่อ่าน`,
        icon: '/icon-192.png',
        tag: 'skjchat-new-msg',
      })
      n.onclick = () => { window.focus(); n.close() }
    }
  }

  // Load conversations
  useEffect(() => {
    loadConversations()
    const interval = setInterval(loadConversations, 3000) // poll every 3s
    return () => clearInterval(interval)
  }, [filterPlatform, filterStatus, searchText])

  async function loadConversations() {
    try {
      const params = new URLSearchParams()
      if (filterStatus) params.set('status', filterStatus)
      if (filterPlatform) params.set('platform', filterPlatform)
      if (searchText) params.set('search', searchText)
      params.set('_t', Date.now().toString())
      const res = await fetch(`/api/conversations?${params}`, { cache: 'no-store' })
      const json = await res.json()
      const data: Conversation[] = json.conversations ?? json // support both old/new API shape
      const hasMore = json.hasMore ?? false
      const nextCursor = json.nextCursor ?? null

      // Check for new unread messages
      const totalUnread = data.reduce((sum: number, c: any) => sum + (c.unreadCount || 0), 0)
      if (totalUnread > prevUnreadRef.current && prevUnreadRef.current >= 0) {
        notifyNewMessage(totalUnread)
      }
      prevUnreadRef.current = totalUnread
      updateTabTitle(totalUnread)

      // On poll refresh: preserve any extra conversations loaded via infinite scroll
      // Only merge extras when showing 'all' without search/platform filters
      setConversations(prev => {
        if (prev.length > data.length && !searchText && filterStatus === 'all' && !filterPlatform) {
          const freshIds = new Set(data.map(c => c.id))
          const extra = prev.filter(c => !freshIds.has(c.id))
          return [...data, ...extra]
        }
        return data
      })
      setHasMoreConvos(hasMore)
      nextConvoCursorRef.current = nextCursor
    } catch (e) {
      console.error('Failed to load conversations:', e)
    }
  }

  async function loadMoreConversations() {
    if (!hasMoreConvos || loadingMoreConvos || !nextConvoCursorRef.current) return
    setLoadingMoreConvos(true)
    try {
      const params = new URLSearchParams()
      if (filterStatus) params.set('status', filterStatus)
      if (filterPlatform) params.set('platform', filterPlatform)
      if (searchText) params.set('search', searchText)
      params.set('cursor', nextConvoCursorRef.current)
      const res = await fetch(`/api/conversations?${params}`, { cache: 'no-store' })
      const json = await res.json()
      const more: Conversation[] = json.conversations ?? json
      const hasMore = json.hasMore ?? false
      const nextCursor = json.nextCursor ?? null

      if (more.length > 0) {
        setConversations(prev => {
          const existingIds = new Set(prev.map(c => c.id))
          const newOnes = more.filter(c => !existingIds.has(c.id))
          return [...prev, ...newOnes]
        })
      }
      setHasMoreConvos(hasMore)
      nextConvoCursorRef.current = nextCursor
    } catch (e) {
      console.error('Failed to load more conversations:', e)
    } finally {
      setLoadingMoreConvos(false)
    }
  }

  // Poll typing indicator for active conversation
  useEffect(() => {
    if (!activeConvo) { setTypingNames([]); return }
    const pollTyping = () => {
      fetch(`/api/conversations/${activeConvo.id}/typing`).then(r => r.json()).then(d => {
        setTypingNames((d.typers || []).filter((n: string) => n !== adminName))
      }).catch(() => {})
    }
    pollTyping()
    const iv = setInterval(pollTyping, 2000)
    return () => clearInterval(iv)
  }, [activeConvo?.id, adminName])

  // Send typing event (throttled — send every 2s while typing)
  const lastTypingSentRef = useRef<number>(0)
  function handleTyping() {
    if (!activeConvo || !adminName) return
    const now = Date.now()
    if (now - lastTypingSentRef.current < 2000) return
    lastTypingSentRef.current = now
    fetch(`/api/conversations/${activeConvo.id}/typing`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: adminName }),
    }).catch(() => {})
  }

  // Load messages when active conversation changes
  useEffect(() => {
    setReplyTo(null)
    setHasMoreMessages(false)
    if (activeConvo) {
      // Show cached messages instantly
      const cached = msgCacheRef.current[activeConvo.id]
      if (cached) {
        setMessages(cached)
        lastMsgIdRef.current = cached.map((m: any) => m.id + (m.status || '')).join(',')
        lastMsgCountRef.current = cached.length
        setTimeout(() => scrollToBottom(true), 30)
      } else {
        setMessages([])
        setLoadingMessages(true)
        lastMsgCountRef.current = 0
        lastMsgIdRef.current = ''
      }
      // Sync Facebook messages (catches outbound from Meta Business Suite)
      if (activeConvo.platform === 'facebook') {
        fetch(`/api/conversations/${activeConvo.id}/sync-fb`, { method: 'POST' })
          .then(r => r.json())
          .then(d => { if (d.synced > 0) loadMessages(activeConvo.id, false) })
          .catch(() => {})
      }
      // Fetch fresh data
      loadMessages(activeConvo.id, !cached)
      const interval = setInterval(() => loadMessages(activeConvo.id, false), 2000)
      return () => clearInterval(interval)
    }
  }, [activeConvo?.id])

  async function loadMessages(convoId: string, isInitial: boolean) {
    try {
      const res = await fetch(`/api/conversations/${convoId}/messages`)
      const json = await res.json()
      const data: Message[] = json.messages || json // support both old/new API shape
      const hasMore = json.hasMore ?? false
      setLoadingMessages(false)
      setHasMoreMessages(hasMore)
      // Build fingerprint: id+status of every message to detect any change
      const fp = data.map((m: any) => m.id + (m.status || '')).join(',')
      const prevFp = lastMsgIdRef.current
      const prevCount = lastMsgCountRef.current
      lastMsgCountRef.current = data.length
      lastMsgIdRef.current = fp
      if (!isInitial && fp === prevFp) return // nothing changed, skip re-render
      // Preserve older messages that were loaded via infinite scroll
      setMessages(prev => {
        if (!isInitial && prev.length > data.length) {
          // We have older messages loaded — keep them, update/append new ones
          const oldIds = new Set(data.map(m => m.id))
          const olderMsgs = prev.filter(m => !oldIds.has(m.id) && new Date(m.createdAt) < new Date(data[0]?.createdAt))
          const merged = [...olderMsgs, ...data]
          msgCacheRef.current[convoId] = merged
          return merged
        }
        msgCacheRef.current[convoId] = data
        return data
      })
      if (isInitial || data.length > prevCount) {
        setTimeout(scrollToBottom, 50)
      }
    } catch (e) {
      console.error('Failed to load messages:', e)
      setLoadingMessages(false)
    }
  }

  async function loadOlderMessages() {
    if (!activeConvo || loadingOlder || !hasMoreMessages) return
    const oldest = messages[0]
    if (!oldest) return
    setLoadingOlder(true)
    try {
      const res = await fetch(`/api/conversations/${activeConvo.id}/messages?cursor=${oldest.id}`)
      const json = await res.json()
      const olderMsgs: Message[] = json.messages || json
      const hasMore = json.hasMore ?? false
      setHasMoreMessages(hasMore)
      if (olderMsgs.length > 0) {
        // Remember scroll position to restore after prepending
        const container = chatContainerRef.current
        const prevScrollHeight = container?.scrollHeight || 0
        setMessages(prev => {
          const merged = [...olderMsgs, ...prev]
          msgCacheRef.current[activeConvo.id] = merged
          return merged
        })
        // Restore scroll position after DOM update
        requestAnimationFrame(() => {
          if (container) {
            container.scrollTop = container.scrollHeight - prevScrollHeight
          }
        })
      }
    } catch (e) {
      console.error('Failed to load older messages:', e)
    } finally {
      setLoadingOlder(false)
    }
  }

  function scrollToBottom(instant?: boolean) {
    messagesEndRef.current?.scrollIntoView({ behavior: instant ? 'instant' as any : 'smooth' })
  }

  async function sendMessage() {
    if (!inputText.trim() || !activeConvo || sending) return
    const text = inputText.trim()
    const quotedMsg = replyTo
    setInputText('')
    setReplyTo(null)
    setSending(true)

    // Optimistic update — store quote info in metadata only (don't embed in content)
    const optimistic: Message = {
      id: `temp-${Date.now()}`,
      conversationId: activeConvo.id,
      direction: 'outbound',
      type: 'text',
      content: text,
      metadata: quotedMsg ? JSON.stringify({ replyToId: quotedMsg.id, replyToContent: quotedMsg.content, replyToType: quotedMsg.type, replyToDirection: quotedMsg.direction }) : null,
      createdAt: new Date().toISOString(),
    }
    setMessages((prev) => [...prev, optimistic])
    setTimeout(scrollToBottom, 50)

    const convoId = activeConvo.id
    const payload = {
      content: text,
      ...(quotedMsg && { replyTo: { id: quotedMsg.id, content: quotedMsg.content, type: quotedMsg.type, direction: quotedMsg.direction } }),
    }
    let success = false
    for (let attempt = 0; attempt < 3; attempt++) {
      try {
        const res = await fetch(`/api/conversations/${convoId}/send`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        })
        if (!res.ok) {
          const err = await res.text()
          throw new Error(err || `HTTP ${res.status}`)
        }
        success = true
        loadMessages(convoId, false)
        loadConversations()
        break
      } catch (e: any) {
        console.error(`Send attempt ${attempt + 1} failed:`, e)
        if (attempt < 2) await new Promise(r => setTimeout(r, 1000 * (attempt + 1)))
      }
    }
    if (!success) {
      showToast('error', 'ส่งข้อความไม่สำเร็จ กรุณาลองใหม่', {
        label: 'ลองอีกครั้ง',
        onClick: () => {
          setInputText(text)
          if (quotedMsg) setReplyTo(quotedMsg)
        },
      })
      setMessages(prev => prev.filter(m => m.id !== optimistic.id))
    }
    setSending(false)
    inputRef.current?.focus()
  }

  async function uploadAndSendFile(file: File) {
    if (!activeConvo) return
    const isImage = file.type.startsWith('image/')
    const sendType = isImage ? 'image' : 'file'

    setUploading(true)
    try {
      const formData = new FormData()
      formData.append('file', file)
      const uploadRes = await fetch('/api/upload', { method: 'POST', body: formData })
      const { url } = await uploadRes.json()

      // Optimistic update
      const optimistic: Message = {
        id: `temp-${Date.now()}`,
        conversationId: activeConvo.id,
        direction: 'outbound',
        type: sendType,
        content: url,
        createdAt: new Date().toISOString(),
      }
      setMessages(prev => [...prev, optimistic])
      setTimeout(scrollToBottom, 50)

      // Send via API with retry
      const convoId = activeConvo.id
      let sent = false
      for (let attempt = 0; attempt < 3; attempt++) {
        try {
          const res = await fetch(`/api/conversations/${convoId}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content: url, type: sendType, fileName: file.name }),
          })
          if (!res.ok) throw new Error(`HTTP ${res.status}`)
          sent = true
          break
        } catch (e) {
          if (attempt < 2) await new Promise(r => setTimeout(r, 1000 * (attempt + 1)))
        }
      }
      if (sent) {
        loadMessages(convoId, false)
        loadConversations()
      } else {
        showToast('error', 'ส่งไฟล์ไม่สำเร็จ กรุณาลองใหม่')
        setMessages(prev => prev.filter(m => m.id !== optimistic.id))
      }
    } catch (err) {
      console.error('Upload/send failed:', err)
      showToast('error', 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาลองใหม่')
    } finally {
      setUploading(false)
    }
  }

  function stageFiles(files: File[]) {
    setPendingFiles(prev => [...prev, ...files])
    for (const file of files) {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (ev) => setPendingFilePreviews(prev => [...prev, ev.target?.result as string])
        reader.readAsDataURL(file)
      } else {
        setPendingFilePreviews(prev => [...prev, ''])
      }
    }
  }

  function removeStaged(idx: number) {
    setPendingFiles(prev => prev.filter((_, i) => i !== idx))
    setPendingFilePreviews(prev => prev.filter((_, i) => i !== idx))
  }

  function cancelPendingFile() {
    setPendingFiles([])
    setPendingFilePreviews([])
  }

  async function confirmSendFile() {
    const files = [...pendingFiles]
    setPendingFiles([])
    setPendingFilePreviews([])
    if (!activeConvo) return

    // Single file or non-image files → send one by one
    const allImages = files.every(f => f.type.startsWith('image/'))
    if (!allImages || files.length === 1) {
      for (const file of files) {
        await uploadAndSendFile(file)
      }
      return
    }

    // Multiple images → upload all, then send as batch (LINE album)
    setUploading(true)
    try {
      const urls: string[] = []
      for (const file of files) {
        const formData = new FormData()
        formData.append('file', file)
        const uploadRes = await fetch('/api/upload', { method: 'POST', body: formData })
        const { url } = await uploadRes.json()
        urls.push(url)
      }

      // Optimistic update
      const optimistics: Message[] = urls.map((url, i) => ({
        id: `temp-batch-${Date.now()}-${i}`,
        conversationId: activeConvo!.id,
        direction: 'outbound' as const,
        type: 'image',
        content: url,
        createdAt: new Date().toISOString(),
      }))
      setMessages(prev => [...prev, ...optimistics])
      setTimeout(scrollToBottom, 50)

      // Send batch
      const res = await fetch(`/api/conversations/${activeConvo!.id}/send-batch`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ urls }),
      })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      loadMessages(activeConvo!.id, false)
      loadConversations()
    } catch (err) {
      console.error('Batch send failed:', err)
      showToast('error', 'ส่งรูปภาพไม่สำเร็จ กรุณาลองใหม่')
    } finally {
      setUploading(false)
    }
  }

  async function handleFileUpload(e: React.ChangeEvent<HTMLInputElement>, sendType: 'image' | 'file') {
    const files = Array.from(e.target.files || [])
    if (!files.length || !activeConvo) return
    e.target.value = '' // reset input
    stageFiles(files)
  }

  async function handleDrop(e: React.DragEvent) {
    e.preventDefault()
    setDragOver(false)
    if (!activeConvo) return
    const files = Array.from(e.dataTransfer.files)
    if (files.length > 0) stageFiles(files)
  }

  const handlePaste = useCallback((e: React.ClipboardEvent) => {
    if (!activeConvo) return
    const items = Array.from(e.clipboardData.items)
    const files: File[] = []
    for (const item of items) {
      if (item.kind === 'file') {
        const file = item.getAsFile()
        if (file) files.push(file)
      }
    }
    if (files.length) {
      e.preventDefault()
      stageFiles(files)
    }
  }, [activeConvo])

  async function searchInChat(query: string) {
    setChatSearch(query)
    if (!query.trim() || !activeConvo) {
      // Reset — reload all messages
      loadMessages(activeConvo?.id || '', false)
      setChatSearchResults(0)
      return
    }
    try {
      const res = await fetch(`/api/conversations/${activeConvo.id}/messages?search=${encodeURIComponent(query.trim())}`)
      const data = await res.json()
      setMessages(data)
      setChatSearchResults(data.length)
    } catch (e) {
      console.error('Search failed:', e)
    }
  }

  const closeChatSearch = useCallback(() => {
    setChatSearchOpen(false)
    setChatSearch('')
    setChatSearchResults(0)
    if (activeConvo) loadMessages(activeConvo.id, false)
  }, [activeConvo])

  const openLightbox = useCallback((clickedSrc: string) => {
    const imgs = messages
      .filter(m => m.type === 'image' && !m.content.includes('/stickers/') && !m.content.match(/\(sticker:/))
      .map(m => m.content)
    const idx = imgs.indexOf(clickedSrc)
    setLightboxImages(imgs)
    setLightboxIndex(idx >= 0 ? idx : 0)
    setLightboxOpen(true)
  }, [messages])

  const selectConversation = useCallback(function selectConversation(convo: Conversation) {
    // Instantly show cached messages or clear old ones
    const cached = msgCacheRef.current[convo.id]
    if (cached) {
      setMessages(cached)
    } else {
      setMessages([])
      setLoadingMessages(true)
    }
    // Immediately mark as read locally + call dedicated markread API (syncs DB + platform)
    if (convo.unreadCount > 0) {
      setConversations(prev => prev.map(c => c.id === convo.id ? { ...c, unreadCount: 0 } : c))
      convo = { ...convo, unreadCount: 0 }
      fetch(`/api/conversations/${convo.id}/markread`, { method: 'POST' }).catch(() => {})
    }
    setActiveConvo(convo)
    setMobileView('chat')
    setShowContactPanel(false)
    setEditingName(false)
    setChatSearchOpen(false)
    setChatSearch('')
    setChatSearchResults(0)
    setTimeout(() => inputRef.current?.focus(), 200)
  }, [])

  const goBackToList = useCallback(function goBackToList() {
    setMobileView('list')
    setEditingName(false)
  }, [])

  // Keyboard shortcuts: Ctrl+K = global search, Alt+↑↓ = switch conversations
  useEffect(() => {
    const handleKey = (e: KeyboardEvent) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault()
        setShowGlobalSearch(true)
      }
      if (e.altKey && e.key === 'ArrowUp') {
        e.preventDefault()
        setConversations(prev => {
          const idx = prev.findIndex(c => c.id === activeConvo?.id)
          if (idx > 0) selectConversation(prev[idx - 1])
          return prev
        })
      }
      if (e.altKey && e.key === 'ArrowDown') {
        e.preventDefault()
        setConversations(prev => {
          const idx = prev.findIndex(c => c.id === activeConvo?.id)
          if (idx >= 0 && idx < prev.length - 1) selectConversation(prev[idx + 1])
          return prev
        })
      }
    }
    window.addEventListener('keydown', handleKey)
    return () => window.removeEventListener('keydown', handleKey)
  }, [activeConvo, selectConversation])

  function startEditName() {
    if (!activeConvo) return
    setEditNameValue(activeConvo.contact.name)
    setEditingName(true)
    setTimeout(() => editNameRef.current?.select(), 100)
  }

  async function saveContactName() {
    if (!activeConvo || !editNameValue.trim()) {
      setEditingName(false)
      return
    }
    const newName = editNameValue.trim()
    try {
      await fetch(`/api/contacts/${activeConvo.contact.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: newName }),
      })
      // Update local state — both header and sidebar instantly
      setActiveConvo(prev => prev ? { ...prev, contact: { ...prev.contact, name: newName } } : null)
      setConversations(prev => prev.map(c =>
        c.id === activeConvo.id ? { ...c, contact: { ...c.contact, name: newName } } : c
      ))
    } catch (e) {
      console.error('Failed to rename contact:', e)
    }
    setEditingName(false)
  }

  // ===== CONTEXT MENU ACTIONS =====
  const handleConvoAction = useCallback(async (action: string, convo: Conversation) => {
    setContextMenu(null)
    if (action === 'pin') {
      setConversations(prev => prev.map(c => c.id === convo.id ? { ...c, isPinned: !c.isPinned } : c))
      try { await fetch(`/api/conversations/${convo.id}/pin`, { method: 'PUT' }) } catch {}
      loadConversations()
      return
    }
    if (action === 'delete') {
      if (!confirm(`ลบแชท "${convo.contact.name}" จะลบข้อความทั้งหมด ยืนยัน?`)) return
    }
    if (action === 'preview') {
      try {
        const res = await fetch(`/api/conversations/${convo.id}/actions`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'preview' }),
        })
        const data = await res.json()
        setPreviewMessages(data.messages || [])
        setPreviewConvo(convo)
      } catch (e) { console.error(e) }
      return
    }
    // Instant local update for read/unread actions
    if (action === 'markRead') {
      setConversations(prev => prev.map(c => c.id === convo.id ? { ...c, unreadCount: 0 } : c))
      try {
        await fetch(`/api/conversations/${convo.id}/markread`, { method: 'POST' })
        loadConversations()
      } catch (e) { console.error(e) }
      return
    } else if (action === 'markUnread') {
      setConversations(prev => prev.map(c => c.id === convo.id ? { ...c, unreadCount: 1 } : c))
    }
    try {
      await fetch(`/api/conversations/${convo.id}/actions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action }),
      })
      if (action === 'delete' && activeConvo?.id === convo.id) {
        setActiveConvo(null)
        setMessages([])
      }
      loadConversations()
    } catch (e) { console.error(e) }
  }, [activeConvo])

  // ===== BULK ACTIONS =====
  function toggleBulkSelect(id: string) {
    setBulkSelected(prev => {
      const next = new Set(prev)
      if (next.has(id)) next.delete(id); else next.add(id)
      return next
    })
  }

  async function bulkAction(action: 'markRead' | 'close' | 'delete') {
    const ids = Array.from(bulkSelected)
    if (ids.length === 0) return
    if (action === 'delete' && !confirm(`ลบ ${ids.length} แชทที่เลือก ยืนยัน?`)) return

    for (const id of ids) {
      try {
        await fetch(`/api/conversations/${id}/actions`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action }),
        })
      } catch {}
    }
    showToast('success', `ดำเนินการ ${ids.length} แชทสำเร็จ`)
    setBulkSelected(new Set())
    setBulkMode(false)
    loadConversations()
  }

  // Extracted stable callbacks for memoized child components
  const handleCancelEditName = useCallback(() => setEditingName(false), [])
  const handleToggleChatSearch = useCallback(() => {
    setChatSearchOpen(prev => {
      if (prev) closeChatSearch()
      return !prev
    })
  }, [closeChatSearch])
  const handleToggleOrderPanel = useCallback(() => {}, [])
  const handleToggleComments = useCallback(() => {
    setShowComments(prev => { if (!prev) { setShowContactPanel(false) }; return !prev })
  }, [])
  const handleToggleContactPanel = useCallback(() => {
    setShowContactPanel(prev => { if (!prev) { setShowComments(false) }; return !prev })
  }, [])
  const handleReply = useCallback((m: Message) => { setReplyTo(m); inputRef.current?.focus() }, [])
  const handleImageClick = useCallback(() => imageInputRef.current?.click(), [])
  const handleFileClick = useCallback(() => fileInputRef.current?.click(), [])
  const [showScreenCapture, setShowScreenCapture] = useState(false)
  const handleScreenCapture = useCallback(() => setShowScreenCapture(true), [])
  const handleScreenCaptureResult = useCallback((file: File, previewUrl: string) => {
    setShowScreenCapture(false)
    stageFiles([file])
  }, [])
  const handleScreenCaptureCancel = useCallback(() => setShowScreenCapture(false), [])
  const handleCloseEmoji = useCallback(() => setShowEmojiPicker(false), [])
  const handleCloseSticker = useCallback(() => setShowStickerPicker(false), [])
  const handleToggleEmoji = useCallback(() => setShowEmojiPicker(prev => !prev), [])
  const handleToggleSticker = useCallback(() => { setShowStickerPicker(prev => !prev); setShowEmojiPicker(false) }, [])
  const handleEmojiSelect = useCallback((emoji: string) => { setInputText(prev => prev + emoji); inputRef.current?.focus() }, [])
  const handleQuickReplySelect = useCallback((text: string) => { setInputText(text); inputRef.current?.focus() }, [])
  const handleStickerSelect = useCallback(async (pkgId: string, stkId: string) => {
    setShowStickerPicker(false)
    if (!activeConvo) return
    try {
      await fetch(`/api/conversations/${activeConvo.id}/send`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: `(sticker: ${pkgId}-${stkId})`, type: 'sticker', packageId: pkgId, stickerId: stkId }),
      })
      loadMessages(activeConvo.id, false)
    } catch (err) { console.error('Sticker send error:', err) }
  }, [activeConvo])

  // Close context menu on click outside
  useEffect(() => {
    if (!contextMenu) return
    const close = () => setContextMenu(null)
    window.addEventListener('click', close)
    return () => window.removeEventListener('click', close)
  }, [contextMenu])

  // ===== RENDER =====
  return (
    <div className="flex h-[100dvh] bg-gray-50 dark:bg-gray-900 overflow-hidden">
      <ToastContainer />
      <audio ref={audioRef} src="/noti.mp3" preload="auto" />
      <div
        ref={sidebarRef}
        style={{ width: sidebarWidth }}
        className={cn(
          'max-md:!w-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col flex-shrink-0',
          mobileView === 'chat' ? 'hidden md:flex' : 'flex'
        )}
      >
        {/* Admin bar */}
        {adminName && (
          <div className="flex-shrink-0 bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <UserCircle className="w-5 h-5 text-blue-200" />
              <span className="text-sm text-white font-semibold">{adminName}</span>
            </div>
            <button
              onClick={() => { if (confirm('ออกจากระบบ?')) window.location.href = '/api/auth/signout' }}
              className="flex items-center gap-1.5 text-xs text-blue-200 hover:text-white transition-colors bg-blue-500/30 hover:bg-blue-500/50 px-3 py-1.5 rounded-lg"
            >
              <LogOut className="w-4 h-4" />
              ออกจากระบบ
            </button>
          </div>
        )}
        {/* Header — LINE OA style */}
        <div className="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
          <div className="px-4 pt-3 pb-2.5">
            {/* Row 1: Title + essential icons */}
            <div className="flex items-center justify-between mb-2.5">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                  <MessageCircle className="w-4.5 h-4.5 text-white" />
                </div>
                <div>
                  <h1 className="text-base font-bold text-gray-900 dark:text-white leading-tight">SKJ Chat</h1>
                  {conversations.reduce((sum, c) => sum + (c.unreadCount || 0), 0) > 0 && (
                    <p className="text-[10px] text-red-500 font-semibold leading-tight">
                      {conversations.reduce((sum, c) => sum + (c.unreadCount || 0), 0)} ข้อความใหม่
                    </p>
                  )}
                </div>
              </div>
              <div className="flex items-center gap-1">
                <button
                  onClick={() => setShowGlobalSearch(true)}
                  className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 rounded-xl text-gray-500 transition-colors"
                  title="ค้นหา (Ctrl+K)"
                >
                  <Search className="w-5 h-5" />
                </button>
                <button
                  onClick={() => { setBulkMode(p => !p); setBulkSelected(new Set()) }}
                  className={cn('p-2 rounded-xl transition-colors', bulkMode ? 'bg-blue-100 text-blue-600' : 'hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200 text-gray-500')}
                  title="เลือกหลายแชท"
                >
                  <CheckSquare className="w-5 h-5" />
                </button>
                <NotificationCenter onSelectConversation={(id) => {
                  const convo = conversations.find(c => c.id === id)
                  if (convo) selectConversation(convo)
                }} />
                <SidebarMoreMenu
                  onAutoReply={() => setShowAutoReply(true)}
                  onRichMenu={() => setShowRichMenu(true)}
                  deferredPrompt={deferredPrompt}
                  onInstall={async () => {
                    const p = deferredPrompt as any
                    p.prompt()
                    await p.userChoice
                    setDeferredPrompt(null)
                  }}
                />
              </div>
            </div>

            {/* Search — touch friendly */}
            <div className="relative mb-2.5">
              <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                type="text"
                placeholder="ค้นหาชื่อลูกค้า..."
                value={searchText}
                onChange={(e) => setSearchText(e.target.value)}
                className="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              {searchText && (
                <button onClick={() => setSearchText('')} className="absolute right-3 top-1/2 -translate-y-1/2 p-0.5">
                  <X className="w-4 h-4 text-gray-400" />
                </button>
              )}
            </div>

            {/* Filters — pill style for mobile */}
            <div className="flex gap-2">
              {['all', 'inbox', 'unread'].map(s => (
                <button
                  key={s}
                  onClick={() => setFilterStatus(s)}
                  className={cn(
                    'px-3 py-1.5 rounded-full text-xs font-semibold transition-colors',
                    filterStatus === s ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 active:bg-gray-200'
                  )}
                >
                  {s === 'all' ? 'ทั้งหมด' : s === 'inbox' ? 'อินบ็อกซ์' : `ยังไม่อ่าน${conversations.reduce((sum, c) => sum + (c.unreadCount || 0), 0) > 0 ? ` (${conversations.reduce((sum, c) => sum + (c.unreadCount || 0), 0)})` : ''}`}
                </button>
              ))}
              <div className="ml-auto flex gap-1">
                <button
                  onClick={() => setFilterPlatform(filterPlatform === 'facebook' ? '' : 'facebook')}
                  className={cn(
                    'w-8 h-8 rounded-full flex items-center justify-center transition-colors',
                    filterPlatform === 'facebook' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-400'
                  )}
                >
                  <Facebook className="w-4 h-4" />
                </button>
                <button
                  onClick={() => setFilterPlatform(filterPlatform === 'line' ? '' : 'line')}
                  className={cn(
                    'w-8 h-8 rounded-full flex items-center justify-center transition-colors',
                    filterPlatform === 'line' ? 'bg-green-100 text-green-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-400'
                  )}
                >
                  <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" /></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Conversation List — scrollable */}
        <div
          className="flex-1 overflow-y-auto overscroll-contain relative"
          onScroll={(e) => {
            const el = e.currentTarget
            if (el.scrollHeight - el.scrollTop - el.clientHeight < 120 && hasMoreConvos && !loadingMoreConvos) {
              loadMoreConversations()
            }
          }}
        >
          {conversations.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-full text-gray-400 p-8">
              <MessageCircle className="w-14 h-14 mb-3 opacity-20" />
              <p className="text-sm font-medium">ยังไม่มีแชท</p>
              <p className="text-xs mt-1 text-center">เชื่อมต่อ Facebook / LINE<br/>แล้วรอข้อความจากลูกค้า</p>
            </div>
          ) : (
            [...conversations].sort((a, b) => (b.isPinned ? 1 : 0) - (a.isPinned ? 1 : 0)).map((convo) => (
              <div key={`${convo.id}-${convo.lastMessageAt || ''}`} className="flex items-stretch">
                {bulkMode && (
                  <label className="flex items-center px-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                    <input
                      type="checkbox"
                      checked={bulkSelected.has(convo.id)}
                      onChange={() => toggleBulkSelect(convo.id)}
                      className="w-4 h-4 rounded text-blue-600 focus:ring-blue-500"
                    />
                  </label>
                )}
                <div className="flex-1 min-w-0">
                  <ConversationItem
                    convo={convo}
                    isActive={activeConvo?.id === convo.id}
                    onClick={() => bulkMode ? toggleBulkSelect(convo.id) : selectConversation(convo)}
                    onContextMenu={(e) => { e.preventDefault(); setContextMenu({ x: e.clientX, y: e.clientY, convo }) }}
                  />
                </div>
              </div>
            ))
          )}
          {loadingMoreConvos && (
            <div className="flex items-center justify-center py-4 gap-2">
              <span className="animate-spin w-4 h-4 border-2 border-gray-300 border-t-blue-500 rounded-full" />
              <p className="text-xs text-gray-400">กำลังโหลดเพิ่ม...</p>
            </div>
          )}

          {/* Context Menu */}
          {contextMenu && (
            <ConversationContextMenu
              menu={contextMenu}
              onAction={handleConvoAction}
            />
          )}
        </div>

        {/* Bulk Action Bar */}
        {bulkMode && (
          <div className="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 flex items-center gap-2">
            <span className="text-xs text-gray-500 font-medium mr-auto">
              เลือก {bulkSelected.size} แชท
            </span>
            <button
              onClick={() => bulkAction('markRead')}
              disabled={bulkSelected.size === 0}
              className="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 disabled:opacity-40"
            >
              อ่านแล้ว
            </button>
            <button
              onClick={() => bulkAction('close')}
              disabled={bulkSelected.size === 0}
              className="px-3 py-1.5 bg-gray-600 text-white text-xs font-bold rounded-lg hover:bg-gray-700 disabled:opacity-40"
            >
              ปิดแชท
            </button>
            <button
              onClick={() => bulkAction('delete')}
              disabled={bulkSelected.size === 0}
              className="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 disabled:opacity-40"
            >
              <Trash2 className="w-3.5 h-3.5" />
            </button>
            <button
              onClick={() => { setBulkMode(false); setBulkSelected(new Set()) }}
              className="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-xs font-bold rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              ยกเลิก
            </button>
          </div>
        )}
      </div>

      {/* ===== RESIZE HANDLE ===== */}
      <div
        onMouseDown={startResize}
        className="hidden md:flex w-1.5 cursor-col-resize items-center justify-center flex-shrink-0 group hover:bg-blue-100 active:bg-blue-200 transition-colors"
      >
        <div className="w-0.5 h-8 bg-gray-300 rounded-full group-hover:bg-blue-400 group-active:bg-blue-500 transition-colors" />
      </div>

      {/* ===== CHAT AREA ===== */}
      <div
        className={cn(
          'flex-1 flex flex-col min-w-0 relative',
          mobileView === 'list' && 'hidden md:flex'
        )}
        onDragOver={(e) => { e.preventDefault(); if (activeConvo) setDragOver(true) }}
        onDragLeave={(e) => { if (e.currentTarget === e.target || !e.currentTarget.contains(e.relatedTarget as Node)) setDragOver(false) }}
        onDrop={handleDrop}
      >
        {/* Drag overlay */}
        {dragOver && activeConvo && (
          <div className="absolute inset-0 z-50 bg-blue-500/20 backdrop-blur-sm flex items-center justify-center border-4 border-dashed border-blue-400 rounded-xl m-2 pointer-events-none">
            <div className="bg-white rounded-2xl shadow-xl px-8 py-6 text-center">
              <Paperclip className="w-10 h-10 text-blue-500 mx-auto mb-2" />
              <p className="text-lg font-bold text-gray-800">วางไฟล์ที่นี่</p>
              <p className="text-sm text-gray-500">รูปภาพ, PDF, เอกสาร</p>
            </div>
          </div>
        )}
        {activeConvo ? (
          <>
            <ChatHeader
              convo={activeConvo}
              editingName={editingName}
              editNameValue={editNameValue}
              chatSearchOpen={chatSearchOpen}
              showOrderPanel={false}
              showComments={showComments}
              showContactPanel={showContactPanel}
              onBack={goBackToList}
              onStartEditName={startEditName}
              onEditNameChange={setEditNameValue}
              onSaveContactName={saveContactName}
              onCancelEditName={handleCancelEditName}
              onToggleChatSearch={handleToggleChatSearch}
              onToggleOrderPanel={handleToggleOrderPanel}
              onToggleComments={handleToggleComments}
              onToggleContactPanel={handleToggleContactPanel}
              onSchedule={() => setShowSchedule(true)}
              onTemplates={() => setShowTemplates(true)}
              onInsights={() => setShowInsights(true)}
              editNameRef={editNameRef}
            />

            {/* Chat Search Bar */}
            {chatSearchOpen && (
              <div className="flex-shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-3 py-2 flex items-center gap-2">
                <Search className="w-4 h-4 text-gray-400 flex-shrink-0" />
                <input
                  type="text"
                  value={chatSearch}
                  onChange={(e) => searchInChat(e.target.value)}
                  placeholder="ค้นหาในแชทนี้..."
                  className="flex-1 text-sm bg-transparent focus:outline-none"
                  autoFocus
                />
                {chatSearch && (
                  <span className="text-[11px] text-gray-400 flex-shrink-0">
                    {chatSearchResults} ผลลัพธ์
                  </span>
                )}
                <button onClick={closeChatSearch} className="p-1 hover:bg-gray-100 rounded-lg">
                  <X className="w-4 h-4 text-gray-400" />
                </button>
              </div>
            )}

            {/* Messages — scrollable area */}
            <div
              ref={chatContainerRef}
              className="flex-1 overflow-y-auto overscroll-contain p-3 pb-2 md:p-4 md:pb-2 space-y-3 bg-gray-50 dark:bg-gray-900"
              onScroll={(e) => {
                const el = e.currentTarget
                if (el.scrollTop < 80 && hasMoreMessages && !loadingOlder) {
                  loadOlderMessages()
                }
              }}
            >
              {loadingMessages ? (
                <div className="flex items-center justify-center h-full gap-2">
                  <span className="animate-spin w-5 h-5 border-2 border-gray-300 border-t-blue-500 rounded-full" />
                  <p className="text-xs text-gray-400">กำลังโหลดข้อความ...</p>
                </div>
              ) : messages.length === 0 ? (
                <div className="flex items-center justify-center h-full">
                  <p className="text-xs text-gray-400">ยังไม่มีข้อความ</p>
                </div>
              ) : null}
              {loadingOlder && (
                <div className="flex items-center justify-center py-3 gap-2">
                  <span className="animate-spin w-4 h-4 border-2 border-gray-300 border-t-blue-500 rounded-full" />
                  <p className="text-xs text-gray-400">กำลังโหลดข้อความเก่า...</p>
                </div>
              )}
              {!loadingMessages && !hasMoreMessages && messages.length > 0 && (
                <p className="text-center text-[11px] text-gray-300 py-2">— จุดเริ่มต้นของแชท —</p>
              )}
              {(() => {
                // Group consecutive images from same direction within 60s into albums
                const groups: { type: 'single', msg: Message }[] | { type: 'album', msgs: Message[] }[] = []
                let i = 0
                while (i < messages.length) {
                  const msg = messages[i]
                  const isImg = msg.type === 'image' && !msg.content.includes('/stickers/')
                  if (isImg) {
                    const album: Message[] = [msg]
                    let j = i + 1
                    while (j < messages.length) {
                      const next = messages[j]
                      const nextIsImg = next.type === 'image' && !next.content.includes('/stickers/')
                      if (!nextIsImg || next.direction !== msg.direction) break
                      const dt = Math.abs(new Date(next.createdAt).getTime() - new Date(album[album.length - 1].createdAt).getTime())
                      if (dt > 60000) break
                      album.push(next)
                      j++
                    }
                    if (album.length >= 2) {
                      (groups as any[]).push({ type: 'album', msgs: album })
                    } else {
                      (groups as any[]).push({ type: 'single', msg })
                    }
                    i = j
                  } else {
                    (groups as any[]).push({ type: 'single', msg })
                    i++
                  }
                }
                // Date separator helper
                const toDateKey = (d: string) => new Date(d).toLocaleDateString('th-TH', { timeZone: 'Asia/Bangkok' })
                const formatDateSep = (d: string) => {
                  const msgDate = new Date(d)
                  const now = new Date()
                  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
                  const yesterday = new Date(today.getTime() - 86400000)
                  const msgDay = new Date(msgDate.getFullYear(), msgDate.getMonth(), msgDate.getDate())
                  if (msgDay.getTime() === today.getTime()) return 'วันนี้'
                  if (msgDay.getTime() === yesterday.getTime()) return 'เมื่อวาน'
                  return msgDate.toLocaleDateString('th-TH', { timeZone: 'Asia/Bangkok', day: 'numeric', month: 'long', year: 'numeric' })
                }
                let lastDateKey = ''
                return groups.map((g: any) => {
                  const firstMsg = g.type === 'album' ? g.msgs[0] : g.msg
                  const dateKey = toDateKey(firstMsg.createdAt)
                  let dateSep = null
                  if (dateKey !== lastDateKey) {
                    lastDateKey = dateKey
                    dateSep = (
                      <div key={`date-${dateKey}`} className="flex items-center gap-3 py-3 px-4">
                        <div className="flex-1 h-px bg-gray-200" />
                        <span className="text-[11px] font-semibold text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-200 whitespace-nowrap">{formatDateSep(firstMsg.createdAt)}</span>
                        <div className="flex-1 h-px bg-gray-200" />
                      </div>
                    )
                  }
                  const bubble = g.type === 'album' ? (
                    <AlbumBubble
                      key={g.msgs[0].id}
                      messages={g.msgs}
                      contactName={activeConvo?.contact.name || ''}
                      contactAvatar={activeConvo?.contact.avatar || null}
                      onReply={handleReply}
                      onImageClick={openLightbox}
                    />
                  ) : (
                    <MessageBubble
                      key={g.msg.id}
                      msg={g.msg}
                      contactName={activeConvo?.contact.name || ''}
                      contactAvatar={activeConvo?.contact.avatar || null}
                      onReply={handleReply}
                      onImageClick={openLightbox}
                    />
                  )
                  return dateSep ? <>{dateSep}{bubble}</> : bubble
                })
              })()}
              {/* Typing indicator */}
              {typingNames.length > 0 && (
                <div className="flex items-center gap-2 px-2 py-1">
                  <div className="flex items-center gap-1 px-3 py-2 bg-gray-100 rounded-2xl rounded-bl-sm">
                    <span className="flex gap-0.5">
                      <span className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                      <span className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                      <span className="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
                    </span>
                    <span className="text-[11px] text-gray-500 ml-1.5 font-medium">
                      {typingNames.join(', ')} กำลังพิมพ์...
                    </span>
                  </div>
                </div>
              )}
              <div ref={messagesEndRef} className="h-4 shrink-0" />
            </div>

            {activeConvo && (
              <ChatInput
                platform={activeConvo.platform}
                inputText={inputText}
                sending={sending}
                uploading={uploading}
                replyTo={replyTo}
                contactName={activeConvo.contact.name}
                showEmojiPicker={showEmojiPicker}
                showStickerPicker={showStickerPicker}
                onInputChange={setInputText}
                onSend={sendMessage}
                onTyping={handleTyping}
                onPaste={handlePaste}
                onImageClick={handleImageClick}
                onFileClick={handleFileClick}
                onSetReplyTo={setReplyTo}
                onToggleEmoji={handleToggleEmoji}
                onToggleSticker={handleToggleSticker}
                onCloseEmoji={handleCloseEmoji}
                onCloseSticker={handleCloseSticker}
                onEmojiSelect={handleEmojiSelect}
                onStickerSelect={handleStickerSelect}
                onQuickReplySelect={handleQuickReplySelect}
                inputRef={inputRef}
                imageInputRef={imageInputRef}
                fileInputRef={fileInputRef}
                onFileUpload={handleFileUpload}
                onScreenCapture={handleScreenCapture}
              />
            )}
          </>
        ) : (
          /* Empty State — desktop only (mobile always shows list) */
          <div className="flex-1 hidden md:flex flex-col items-center justify-center text-gray-400 bg-gray-50">
            <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <MessageCircle className="w-10 h-10 text-gray-300" />
            </div>
            <h3 className="text-lg font-semibold text-gray-500 mb-1">SKJ Chat Inbox</h3>
            <p className="text-sm">เลือกแชทจากด้านซ้ายเพื่อเริ่มตอบข้อความ</p>
            <div className="flex items-center gap-4 mt-6">
              <div className="flex items-center gap-1.5 text-xs text-blue-500">
                <Facebook className="w-4 h-4" /> Facebook
              </div>
              <div className="flex items-center gap-1.5 text-xs text-green-500">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" /></svg> LINE OA
              </div>
            </div>
          </div>
        )}
      </div>

      {/* ===== CONTACT INFO PANEL ===== */}
      {activeConvo && (
        <ContactPanel
          contactId={activeConvo.contact.id}
          visible={showContactPanel}
          onClose={() => setShowContactPanel(false)}
        />
      )}

      {/* ===== COMMENTS PANEL — แจ้งทีมงาน ===== */}
      {activeConvo && showComments && (
        <CommentsPanel
          contactName={activeConvo.contact.name}
          visible={showComments}
          onClose={() => setShowComments(false)}
        />
      )}
      {/* ===== AUTO-REPLY SETTINGS ===== */}
      <AutoReplySettings visible={showAutoReply} onClose={() => setShowAutoReply(false)} />

      {/* ===== GLOBAL SEARCH ===== */}
      <GlobalSearch
        visible={showGlobalSearch}
        onClose={() => setShowGlobalSearch(false)}
        onSelectConversation={(convoId) => {
          const convo = conversations.find(c => c.id === convoId)
          if (convo) selectConversation(convo)
          else {
            fetch(`/api/conversations/${convoId}`).then(r => r.json()).then(c => {
              if (c?.id) selectConversation(c)
            }).catch(() => {})
          }
        }}
      />

      {/* ===== SCHEDULE MESSAGE ===== */}
      {activeConvo && (
        <ScheduleMessage
          conversationId={activeConvo.id}
          visible={showSchedule}
          onClose={() => setShowSchedule(false)}
        />
      )}

      {/* ===== MESSAGE TEMPLATES ===== */}
      <MessageTemplates
        visible={showTemplates}
        onClose={() => setShowTemplates(false)}
        onUseTemplate={(text) => setInputText(text)}
        contactName={activeConvo?.contact.name}
      />

      {/* ===== CUSTOMER INSIGHTS ===== */}
      {activeConvo && (
        <CustomerInsights
          contactId={activeConvo.contact.id}
          visible={showInsights}
          onClose={() => setShowInsights(false)}
        />
      )}

      {/* ===== RICH MENU MANAGER ===== */}
      <RichMenuManager
        visible={showRichMenu}
        onClose={() => setShowRichMenu(false)}
      />

      {/* ===== FILE CONFIRM DIALOG (multi-file) ===== */}
      {pendingFiles.length > 0 && (
        <div className="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4" onClick={cancelPendingFile}>
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 space-y-4" onClick={e => e.stopPropagation()}>
            <h3 className="text-sm font-bold text-gray-800 text-center">
              {pendingFiles.every(f => f.type.startsWith('image/'))
                ? `📷 ยืนยันส่งรูปภาพ${pendingFiles.length > 1 ? ` ${pendingFiles.length} รูป` : ''}?`
                : `📎 ยืนยันส่งไฟล์${pendingFiles.length > 1 ? ` ${pendingFiles.length} ไฟล์` : ''}?`
              }
            </h3>

            {/* Preview grid */}
            {pendingFiles.length === 1 ? (
              pendingFilePreviews[0] ? (
                <img src={pendingFilePreviews[0]} alt="preview" className="max-h-48 mx-auto rounded-xl border border-gray-200 object-contain" />
              ) : (
                <div className="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                  <svg viewBox="0 0 40 40" className="w-10 h-10 flex-shrink-0">
                    <rect x="4" y="2" width="26" height="36" rx="3" fill="#E53E3E" />
                    <path d="M22 2l10 10v25a3 3 0 01-3 3H7a3 3 0 01-3-3V5a3 3 0 013-3h15z" fill="#E53E3E" />
                    <path d="M22 2v10h10z" fill="#FC8181" />
                    <text x="17" y="29" textAnchor="middle" fill="white" fontSize="9" fontWeight="bold" fontFamily="Arial">FILE</text>
                  </svg>
                  <div className="min-w-0">
                    <p className="text-sm font-semibold text-gray-800 truncate">{pendingFiles[0].name}</p>
                    <p className="text-[11px] text-gray-400">{(pendingFiles[0].size / 1024).toFixed(0)} KB</p>
                  </div>
                </div>
              )
            ) : (
              <div className={cn('grid gap-1.5 max-h-60 overflow-y-auto', pendingFiles.length <= 4 ? 'grid-cols-2' : 'grid-cols-3')}>
                {pendingFiles.map((f, i) => (
                  <div key={i} className="relative group/thumb">
                    {pendingFilePreviews[i] ? (
                      <img src={pendingFilePreviews[i]} alt="" className="w-full aspect-square object-cover rounded-lg border border-gray-200" />
                    ) : (
                      <div className="w-full aspect-square bg-gray-100 rounded-lg flex items-center justify-center text-xs text-gray-500 p-1 text-center break-all">{f.name}</div>
                    )}
                    <button
                      onClick={() => removeStaged(i)}
                      className="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center opacity-0 group-hover/thumb:opacity-100 transition-opacity shadow"
                    >×</button>
                  </div>
                ))}
              </div>
            )}

            {pendingFiles.length === 1 && pendingFilePreviews[0] && (
              <p className="text-xs text-gray-500 text-center truncate">{pendingFiles[0].name} ({(pendingFiles[0].size / 1024).toFixed(0)} KB)</p>
            )}
            {pendingFiles.length > 1 && (
              <p className="text-xs text-gray-400 text-center">{(pendingFiles.reduce((s, f) => s + f.size, 0) / 1024).toFixed(0)} KB รวม</p>
            )}

            {/* Buttons */}
            <div className="flex gap-2">
              <button
                onClick={cancelPendingFile}
                className="flex-1 border border-gray-300 text-gray-600 font-semibold py-2.5 rounded-xl active:bg-gray-100"
              >
                ยกเลิก
              </button>
              <button
                onClick={confirmSendFile}
                className="flex-[2] bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-2.5 rounded-xl flex items-center justify-center gap-2"
              >
                <Send className="w-4 h-4" /> ส่งเลย{pendingFiles.length > 1 ? ` (${pendingFiles.length})` : ''}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ===== PREVIEW MESSAGES MODAL ===== */}
      {previewMessages && previewConvo && (
        <div className="fixed inset-0 z-[200] bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" onClick={() => { setPreviewMessages(null); setPreviewConvo(null) }}>
          <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[70vh] flex flex-col" onClick={(e) => e.stopPropagation()}>
            <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
              <div className="flex items-center gap-3">
                <Eye className="w-5 h-5 text-blue-500" />
                <div>
                  <h3 className="text-sm font-bold text-gray-800">{previewConvo.contact.name}</h3>
                  <p className="text-xs text-gray-400">ข้อความล่าสุดที่ได้รับ</p>
                </div>
              </div>
              <button onClick={() => { setPreviewMessages(null); setPreviewConvo(null) }} className="p-1.5 hover:bg-gray-100 rounded-lg">
                <X className="w-4 h-4 text-gray-400" />
              </button>
            </div>
            <div className="flex-1 overflow-y-auto p-4 space-y-3">
              {previewMessages.length === 0 ? (
                <p className="text-sm text-gray-400 text-center py-8">ยังไม่มีข้อความจากลูกค้า</p>
              ) : (
                previewMessages.map((msg: any) => (
                  <div key={msg.id} className="bg-gray-50 rounded-xl p-3">
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-[10px] text-gray-400">
                        {msg.type === 'image' ? '📷 รูปภาพ' : msg.type === 'sticker' ? '♥ สติกเกอร์' : '💬 ข้อความ'}
                      </span>
                      <span className="text-[10px] text-gray-400">{new Date(msg.createdAt).toLocaleString('th-TH', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })}</span>
                    </div>
                    {msg.type === 'image' ? (
                      <img src={msg.content} alt="" className="max-h-32 rounded-lg" />
                    ) : (
                      <p className="text-sm text-gray-700 whitespace-pre-wrap break-words">
                        {msg.content.length > 200 ? msg.content.substring(0, 200) + '...' : msg.content}
                      </p>
                    )}
                  </div>
                ))
              )}
            </div>
            <div className="px-5 py-3 border-t border-gray-100">
              <button
                onClick={() => { selectConversation(previewConvo); setPreviewMessages(null); setPreviewConvo(null) }}
                className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl text-sm"
              >
                เปิดแชทนี้
              </button>
            </div>
          </div>
        </div>
      )}

      {/* ===== SCREEN CAPTURE ===== */}
      {showScreenCapture && (
        <ScreenCapture
          onCapture={handleScreenCaptureResult}
          onCancel={handleScreenCaptureCancel}
        />
      )}

      {/* ===== IMAGE LIGHTBOX ===== */}
      {lightboxOpen && lightboxImages.length > 0 && (
        <ImageLightbox
          images={lightboxImages}
          currentIndex={lightboxIndex}
          onClose={() => setLightboxOpen(false)}
          onNavigate={setLightboxIndex}
        />
      )}
    </div>
  )
}
