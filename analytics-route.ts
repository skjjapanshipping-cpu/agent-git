import { NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

export const dynamic = 'force-dynamic'

export async function GET() {
  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000)
  const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000)

  // Total counts
  const [totalConversations, totalContacts, totalMessages] = await Promise.all([
    prisma.conversation.count(),
    prisma.contact.count(),
    prisma.message.count(),
  ])

  // Today's stats
  const [messagesToday, newContactsToday, inboundToday, outboundToday] = await Promise.all([
    prisma.message.count({ where: { createdAt: { gte: today } } }),
    prisma.contact.count({ where: { createdAt: { gte: today } } }),
    prisma.message.count({ where: { createdAt: { gte: today }, direction: 'inbound' } }),
    prisma.message.count({ where: { createdAt: { gte: today }, direction: 'outbound' } }),
  ])

  // Unread conversations
  const unreadConversations = await prisma.conversation.count({
    where: { unreadCount: { gt: 0 } },
  })

  // Platform breakdown
  const [lineContacts, fbContacts, lineConvos, fbConvos] = await Promise.all([
    prisma.contact.count({ where: { platform: 'line' } }),
    prisma.contact.count({ where: { platform: 'facebook' } }),
    prisma.conversation.count({ where: { platform: 'line' } }),
    prisma.conversation.count({ where: { platform: 'facebook' } }),
  ])

  // Messages per day (last 7 days)
  const dailyMessages: { date: string; inbound: number; outbound: number }[] = []
  for (let i = 6; i >= 0; i--) {
    const dayStart = new Date(today.getTime() - i * 24 * 60 * 60 * 1000)
    const dayEnd = new Date(dayStart.getTime() + 24 * 60 * 60 * 1000)
    const [inb, outb] = await Promise.all([
      prisma.message.count({ where: { createdAt: { gte: dayStart, lt: dayEnd }, direction: 'inbound' } }),
      prisma.message.count({ where: { createdAt: { gte: dayStart, lt: dayEnd }, direction: 'outbound' } }),
    ])
    dailyMessages.push({
      date: dayStart.toLocaleDateString('th-TH', { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'Asia/Bangkok' }),
      inbound: inb,
      outbound: outb,
    })
  }

  // New contacts per day (last 7 days)
  const dailyContacts: { date: string; count: number }[] = []
  for (let i = 6; i >= 0; i--) {
    const dayStart = new Date(today.getTime() - i * 24 * 60 * 60 * 1000)
    const dayEnd = new Date(dayStart.getTime() + 24 * 60 * 60 * 1000)
    const count = await prisma.contact.count({ where: { createdAt: { gte: dayStart, lt: dayEnd } } })
    dailyContacts.push({
      date: dayStart.toLocaleDateString('th-TH', { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'Asia/Bangkok' }),
      count,
    })
  }

  // Week / month totals
  const [messagesWeek, messagesMonth, contactsWeek, contactsMonth] = await Promise.all([
    prisma.message.count({ where: { createdAt: { gte: weekAgo } } }),
    prisma.message.count({ where: { createdAt: { gte: monthAgo } } }),
    prisma.contact.count({ where: { createdAt: { gte: weekAgo } } }),
    prisma.contact.count({ where: { createdAt: { gte: monthAgo } } }),
  ])

  // ===== Admin KPI Stats =====
  const admins = await prisma.user.findMany({
    where: { role: 'admin' },
    select: { id: true, name: true },
    orderBy: { name: 'asc' },
  })

  // Filter out placeholder admins
  const realAdmins = admins.filter(a => !a.name.includes('รอดำเนินการ'))

  const adminStats = await Promise.all(
    realAdmins.map(async (admin) => {
      const [todayCount, weekCount, monthCount] = await Promise.all([
        prisma.message.count({
          where: { senderId: admin.id, direction: 'outbound', createdAt: { gte: today } },
        }),
        prisma.message.count({
          where: { senderId: admin.id, direction: 'outbound', createdAt: { gte: weekAgo } },
        }),
        prisma.message.count({
          where: { senderId: admin.id, direction: 'outbound', createdAt: { gte: monthAgo } },
        }),
      ])

      // Daily breakdown for last 7 days
      const daily: number[] = []
      for (let i = 6; i >= 0; i--) {
        const dayStart = new Date(today.getTime() - i * 24 * 60 * 60 * 1000)
        const dayEnd = new Date(dayStart.getTime() + 24 * 60 * 60 * 1000)
        const count = await prisma.message.count({
          where: { senderId: admin.id, direction: 'outbound', createdAt: { gte: dayStart, lt: dayEnd } },
        })
        daily.push(count)
      }

      return {
        id: admin.id,
        name: admin.name,
        today: todayCount,
        week: weekCount,
        month: monthCount,
        daily,
      }
    })
  )

  // Daily labels for admin chart
  const dailyLabels: string[] = []
  for (let i = 6; i >= 0; i--) {
    const dayStart = new Date(today.getTime() - i * 24 * 60 * 60 * 1000)
    dailyLabels.push(dayStart.toLocaleDateString('th-TH', { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'Asia/Bangkok' }))
  }

  return NextResponse.json({
    totalConversations,
    totalContacts,
    totalMessages,
    messagesToday,
    newContactsToday,
    inboundToday,
    outboundToday,
    unreadConversations,
    platform: { lineContacts, fbContacts, lineConvos, fbConvos },
    dailyMessages,
    dailyContacts,
    messagesWeek,
    messagesMonth,
    contactsWeek,
    contactsMonth,
    adminStats,
    dailyLabels,
  })
}
