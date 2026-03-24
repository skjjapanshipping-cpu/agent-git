'use client'

import { useState, useEffect } from 'react'
import { BarChart3, MessageCircle, Users, TrendingUp, ArrowLeft, Facebook, Loader2, UserCheck } from 'lucide-react'
import Link from 'next/link'
import { cn } from '@/lib/utils'

type AdminStat = {
  id: string
  name: string
  today: number
  week: number
  month: number
  daily: number[]
}

type Stats = {
  totalConversations: number
  totalContacts: number
  totalMessages: number
  messagesToday: number
  newContactsToday: number
  inboundToday: number
  outboundToday: number
  unreadConversations: number
  platform: { lineContacts: number; fbContacts: number; lineConvos: number; fbConvos: number }
  dailyMessages: { date: string; inbound: number; outbound: number }[]
  dailyContacts: { date: string; count: number }[]
  messagesWeek: number
  messagesMonth: number
  contactsWeek: number
  contactsMonth: number
  adminStats?: AdminStat[]
  dailyLabels?: string[]
}

export default function AnalyticsPage() {
  const [stats, setStats] = useState<Stats | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch('/api/analytics')
      .then(r => r.json())
      .then(data => { setStats(data); setLoading(false) })
      .catch(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-blue-500" />
      </div>
    )
  }

  if (!stats) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <p className="text-gray-500">ไม่สามารถโหลดข้อมูลได้</p>
      </div>
    )
  }

  const maxMsg = Math.max(...stats.dailyMessages.map(d => d.inbound + d.outbound), 1)
  const maxContact = Math.max(...stats.dailyContacts.map(d => d.count), 1)

  // Admin chart colors
  const adminColors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#F97316']

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 px-4 md:px-8 py-4">
        <div className="max-w-6xl mx-auto flex items-center gap-3">
          <Link href="/" className="p-2 hover:bg-gray-100 rounded-xl">
            <ArrowLeft className="w-5 h-5 text-gray-600" />
          </Link>
          <BarChart3 className="w-6 h-6 text-blue-600" />
          <h1 className="text-lg font-bold text-gray-900">สถิติแชท</h1>
        </div>
      </div>

      <div className="max-w-6xl mx-auto px-4 md:px-8 py-6 space-y-6">
        {/* Summary cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard icon={<MessageCircle className="w-5 h-5" />} label="ข้อความวันนี้" value={stats.messagesToday} color="blue" sub={`รับ ${stats.inboundToday} / ส่ง ${stats.outboundToday}`} />
          <StatCard icon={<Users className="w-5 h-5" />} label="ลูกค้าใหม่วันนี้" value={stats.newContactsToday} color="green" />
          <StatCard icon={<TrendingUp className="w-5 h-5" />} label="แชทยังไม่อ่าน" value={stats.unreadConversations} color="red" />
          <StatCard icon={<MessageCircle className="w-5 h-5" />} label="แชททั้งหมด" value={stats.totalConversations} color="purple" />
        </div>

        {/* Period stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <MiniCard label="ข้อความ 7 วัน" value={stats.messagesWeek.toLocaleString()} />
          <MiniCard label="ข้อความ 30 วัน" value={stats.messagesMonth.toLocaleString()} />
          <MiniCard label="ลูกค้าใหม่ 7 วัน" value={stats.contactsWeek.toLocaleString()} />
          <MiniCard label="ลูกค้าใหม่ 30 วัน" value={stats.contactsMonth.toLocaleString()} />
        </div>

        {/* Platform breakdown */}
        <div className="bg-white rounded-2xl border border-gray-200 p-5">
          <h3 className="text-sm font-bold text-gray-800 mb-4">แพลตฟอร์ม</h3>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <PlatformCard icon={<svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" /></svg>} label="LINE ลูกค้า" value={stats.platform.lineContacts} color="green" />
            <PlatformCard icon={<Facebook className="w-5 h-5" />} label="Facebook ลูกค้า" value={stats.platform.fbContacts} color="blue" />
            <PlatformCard icon={<svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63h2.386c.349 0 .63.285.63.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.627-.63.349 0 .631.285.631.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" /></svg>} label="LINE แชท" value={stats.platform.lineConvos} color="green" />
            <PlatformCard icon={<Facebook className="w-5 h-5" />} label="Facebook แชท" value={stats.platform.fbConvos} color="blue" />
          </div>
        </div>

        {/* Daily messages chart */}
        <div className="bg-white rounded-2xl border border-gray-200 p-5">
          <h3 className="text-sm font-bold text-gray-800 mb-4">ข้อความ 7 วันล่าสุด</h3>
          <div className="flex items-end gap-2 h-40">
            {stats.dailyMessages.map((d, i) => {
              const total = d.inbound + d.outbound
              const h = Math.max((total / maxMsg) * 100, 4)
              const inH = total > 0 ? (d.inbound / total) * h : 0
              const outH = total > 0 ? (d.outbound / total) * h : 0
              return (
                <div key={i} className="flex-1 flex flex-col items-center gap-1">
                  <span className="text-[10px] text-gray-500 font-medium">{total}</span>
                  <div className="w-full flex flex-col items-center" style={{ height: `${h}%` }}>
                    <div className="w-full max-w-8 bg-blue-400 rounded-t" style={{ height: `${outH}%` }} title={`ส่ง ${d.outbound}`} />
                    <div className="w-full max-w-8 bg-blue-600 rounded-b" style={{ height: `${inH}%` }} title={`รับ ${d.inbound}`} />
                  </div>
                  <span className="text-[9px] text-gray-400 truncate w-full text-center">{d.date}</span>
                </div>
              )
            })}
          </div>
          <div className="flex items-center gap-4 mt-3 justify-center">
            <span className="flex items-center gap-1 text-[10px] text-gray-500"><span className="w-2.5 h-2.5 rounded bg-blue-600" /> รับเข้า</span>
            <span className="flex items-center gap-1 text-[10px] text-gray-500"><span className="w-2.5 h-2.5 rounded bg-blue-400" /> ส่งออก</span>
          </div>
        </div>

        {/* Daily new contacts chart */}
        <div className="bg-white rounded-2xl border border-gray-200 p-5">
          <h3 className="text-sm font-bold text-gray-800 mb-4">ลูกค้าใหม่ 7 วันล่าสุด</h3>
          <div className="flex items-end gap-2 h-32">
            {stats.dailyContacts.map((d, i) => {
              const h = Math.max((d.count / maxContact) * 100, 4)
              return (
                <div key={i} className="flex-1 flex flex-col items-center gap-1">
                  <span className="text-[10px] text-gray-500 font-medium">{d.count}</span>
                  <div className="w-full max-w-8 bg-green-500 rounded" style={{ height: `${h}%` }} />
                  <span className="text-[9px] text-gray-400 truncate w-full text-center">{d.date}</span>
                </div>
              )
            })}
          </div>
        </div>

        {/* ===== Admin KPI Stats ===== */}
        {stats.adminStats && stats.adminStats.length > 0 && (
          <>
            <div className="bg-white rounded-2xl border border-gray-200 p-5">
              <div className="flex items-center gap-2 mb-4">
                <UserCheck className="w-5 h-5 text-blue-600" />
                <h3 className="text-sm font-bold text-gray-800">สถิติการตอบแชทรายแอดมิน (KPI)</h3>
              </div>

              {/* Summary table */}
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="border-b border-gray-200">
                      <th className="text-left py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">แอดมิน</th>
                      <th className="text-center py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">วันนี้</th>
                      <th className="text-center py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">7 วัน</th>
                      <th className="text-center py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">30 วัน</th>
                      <th className="text-center py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">เฉลี่ย/วัน (7d)</th>
                    </tr>
                  </thead>
                  <tbody>
                    {stats.adminStats
                      .sort((a, b) => b.week - a.week)
                      .map((admin, idx) => {
                        const avgPerDay = admin.week > 0 ? Math.round(admin.week / 7) : 0
                        const maxWeek = Math.max(...stats.adminStats!.map(a => a.week), 1)
                        const barWidth = Math.max((admin.week / maxWeek) * 100, 2)
                        return (
                          <tr key={admin.id} className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td className="py-3 px-3">
                              <div className="flex items-center gap-2">
                                <div className="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style={{ background: adminColors[idx % adminColors.length] }}>
                                  {admin.name.charAt(0)}
                                </div>
                                <div>
                                  <span className="font-semibold text-gray-800">{admin.name}</span>
                                  <div className="w-24 h-1.5 bg-gray-100 rounded-full mt-1 hidden md:block">
                                    <div className="h-full rounded-full transition-all" style={{ width: `${barWidth}%`, background: adminColors[idx % adminColors.length] }} />
                                  </div>
                                </div>
                              </div>
                            </td>
                            <td className="text-center py-3 px-3">
                              <span className={cn('text-lg font-bold', admin.today > 0 ? 'text-blue-600' : 'text-gray-300')}>
                                {admin.today.toLocaleString()}
                              </span>
                            </td>
                            <td className="text-center py-3 px-3">
                              <span className="text-lg font-bold text-gray-800">{admin.week.toLocaleString()}</span>
                            </td>
                            <td className="text-center py-3 px-3">
                              <span className="text-lg font-bold text-gray-800">{admin.month.toLocaleString()}</span>
                            </td>
                            <td className="text-center py-3 px-3 hidden md:table-cell">
                              <span className="text-sm font-semibold text-gray-600">{avgPerDay} ข้อความ</span>
                            </td>
                          </tr>
                        )
                      })}
                  </tbody>
                  <tfoot>
                    <tr className="bg-gray-50">
                      <td className="py-2.5 px-3 font-bold text-gray-700 text-xs">รวมทั้งหมด</td>
                      <td className="text-center py-2.5 px-3 font-bold text-blue-600">{stats.adminStats.reduce((s, a) => s + a.today, 0).toLocaleString()}</td>
                      <td className="text-center py-2.5 px-3 font-bold text-gray-800">{stats.adminStats.reduce((s, a) => s + a.week, 0).toLocaleString()}</td>
                      <td className="text-center py-2.5 px-3 font-bold text-gray-800">{stats.adminStats.reduce((s, a) => s + a.month, 0).toLocaleString()}</td>
                      <td className="text-center py-2.5 px-3 font-bold text-gray-600 hidden md:table-cell">{Math.round(stats.adminStats.reduce((s, a) => s + a.week, 0) / 7)} ข้อความ</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            {/* Admin daily chart */}
            <div className="bg-white rounded-2xl border border-gray-200 p-5">
              <h3 className="text-sm font-bold text-gray-800 mb-4">ข้อความตอบรายวัน (7 วัน) — แต่ละแอดมิน</h3>
              <div className="flex items-end gap-2 h-44">
                {(stats.dailyLabels || []).map((label, dayIdx) => {
                  const dayTotals = stats.adminStats!.map(a => a.daily[dayIdx] || 0)
                  const dayTotal = dayTotals.reduce((s, v) => s + v, 0)
                  const maxDayTotal = Math.max(
                    ...(stats.dailyLabels || []).map((_, di) =>
                      stats.adminStats!.reduce((s, a) => s + (a.daily[di] || 0), 0)
                    ),
                    1
                  )
                  const barH = Math.max((dayTotal / maxDayTotal) * 100, 4)

                  return (
                    <div key={dayIdx} className="flex-1 flex flex-col items-center gap-1">
                      <span className="text-[10px] text-gray-500 font-medium">{dayTotal}</span>
                      <div className="w-full flex flex-col items-center" style={{ height: `${barH}%` }}>
                        {stats.adminStats!
                          .sort((a, b) => b.week - a.week)
                          .map((admin, aIdx) => {
                            const val = admin.daily[dayIdx] || 0
                            const segH = dayTotal > 0 ? (val / dayTotal) * 100 : 0
                            if (val === 0) return null
                            return (
                              <div
                                key={admin.id}
                                className="w-full max-w-10"
                                style={{
                                  height: `${segH}%`,
                                  background: adminColors[aIdx % adminColors.length],
                                  borderRadius: aIdx === 0 ? '4px 4px 0 0' : aIdx === stats.adminStats!.length - 1 ? '0 0 4px 4px' : '0',
                                }}
                                title={`${admin.name}: ${val}`}
                              />
                            )
                          })}
                      </div>
                      <span className="text-[9px] text-gray-400 truncate w-full text-center">{label}</span>
                    </div>
                  )
                })}
              </div>
              <div className="flex flex-wrap items-center gap-3 mt-3 justify-center">
                {stats.adminStats
                  .sort((a, b) => b.week - a.week)
                  .map((admin, idx) => (
                    <span key={admin.id} className="flex items-center gap-1 text-[10px] text-gray-500">
                      <span className="w-2.5 h-2.5 rounded" style={{ background: adminColors[idx % adminColors.length] }} />
                      {admin.name}
                    </span>
                  ))}
              </div>
            </div>
          </>
        )}

        {/* Totals */}
        <div className="bg-white rounded-2xl border border-gray-200 p-5">
          <h3 className="text-sm font-bold text-gray-800 mb-3">ยอดรวมทั้งหมด</h3>
          <div className="grid grid-cols-3 gap-4 text-center">
            <div>
              <p className="text-2xl font-bold text-gray-900">{stats.totalContacts.toLocaleString()}</p>
              <p className="text-xs text-gray-500">ลูกค้า</p>
            </div>
            <div>
              <p className="text-2xl font-bold text-gray-900">{stats.totalConversations.toLocaleString()}</p>
              <p className="text-xs text-gray-500">แชท</p>
            </div>
            <div>
              <p className="text-2xl font-bold text-gray-900">{stats.totalMessages.toLocaleString()}</p>
              <p className="text-xs text-gray-500">ข้อความ</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

function StatCard({ icon, label, value, color, sub }: { icon: React.ReactNode; label: string; value: number; color: string; sub?: string }) {
  const colors: Record<string, string> = {
    blue: 'bg-blue-50 text-blue-600',
    green: 'bg-green-50 text-green-600',
    red: 'bg-red-50 text-red-600',
    purple: 'bg-purple-50 text-purple-600',
  }
  return (
    <div className="bg-white rounded-2xl border border-gray-200 p-4">
      <div className={cn('w-9 h-9 rounded-xl flex items-center justify-center mb-2', colors[color])}>{icon}</div>
      <p className="text-2xl font-bold text-gray-900">{value.toLocaleString()}</p>
      <p className="text-xs text-gray-500 mt-0.5">{label}</p>
      {sub && <p className="text-[10px] text-gray-400 mt-0.5">{sub}</p>}
    </div>
  )
}

function MiniCard({ label, value }: { label: string; value: string }) {
  return (
    <div className="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center justify-between">
      <span className="text-xs text-gray-500">{label}</span>
      <span className="text-sm font-bold text-gray-800">{value}</span>
    </div>
  )
}

function PlatformCard({ icon, label, value, color }: { icon: React.ReactNode; label: string; value: number; color: string }) {
  return (
    <div className="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
      <div className={cn('w-8 h-8 rounded-lg flex items-center justify-center', color === 'green' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600')}>{icon}</div>
      <div>
        <p className="text-lg font-bold text-gray-900">{value}</p>
        <p className="text-[10px] text-gray-500">{label}</p>
      </div>
    </div>
  )
}
