import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'

const API_KEY = process.env.INVOICE_API_KEY || 'skjchat-invoice-2026'

const CORS_HEADERS = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'POST, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type, X-API-Key',
}

export async function OPTIONS() {
  return new NextResponse(null, { status: 204, headers: CORS_HEADERS })
}

// POST — Update invoiceSent status (e.g. pending → paid)
export async function POST(req: NextRequest) {
  try {
    const authHeader = req.headers.get('x-api-key')
    if (authHeader !== API_KEY) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401, headers: CORS_HEADERS })
    }

    const { customerno, etd, status } = await req.json()
    if (!customerno || !status) {
      return NextResponse.json({ error: 'customerno and status are required' }, { status: 400, headers: CORS_HEADERS })
    }

    const custKey = String(customerno).toLowerCase()
    const validStatuses = ['pending', 'paid', 'cancelled']
    if (!validStatuses.includes(status)) {
      return NextResponse.json({ error: `Invalid status. Must be one of: ${validStatuses.join(', ')}` }, { status: 400, headers: CORS_HEADERS })
    }

    // Find and update invoiceSent records
    const whereClause: any = { customerno: custKey }
    if (etd) whereClause.etd = String(etd)

    const records = await prisma.invoiceSent.findMany({ where: whereClause })

    if (records.length === 0) {
      return NextResponse.json({ success: false, message: 'No invoice records found', customerno, etd }, { headers: CORS_HEADERS })
    }

    const updated = await prisma.invoiceSent.updateMany({
      where: whereClause,
      data: { status },
    })

    return NextResponse.json({
      success: true,
      message: `Updated ${updated.count} record(s) to status: ${status}`,
      customerno,
      etd: etd || 'all',
      updatedCount: updated.count,
    }, { headers: CORS_HEADERS })

  } catch (error: any) {
    console.error('[INVOICE-UPDATE-STATUS] Error:', error)
    return NextResponse.json({ error: 'Internal error', details: error.message }, { status: 500, headers: CORS_HEADERS })
  }
}
