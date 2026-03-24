const { PrismaClient } = require('/opt/skjchat/node_modules/@prisma/client');
const axios = require('/opt/skjchat/node_modules/axios').default;
const p = new PrismaClient();

const LARAVEL_API_URL = 'https://skjjapanshipping.com/skjtrack/api/update-pay-status';
const LARAVEL_KEY = 'skjchat-invoice-2026';

(async () => {
  // 1. Check current invoiceSent status for ANW-830
  const invoices = await p.invoiceSent.findMany({
    where: { customerno: 'anw-830' },
    orderBy: { createdAt: 'desc' },
    take: 10,
  });
  console.log('=== ANW-830 invoices ===');
  invoices.forEach(i => console.log(
    'id:', i.id,
    'ETD:', i.etd,
    'amount:', i.amount,
    'status:', i.status,
    'paidAt:', i.paidAt?.toISOString() || '-',
    'paidTransRef:', i.paidTransRef || '-'
  ));

  // 2. Call Laravel API with etd=thai-bill to update thai_bill_status
  // (Now that the fix is deployed, it should go to MODE 3)
  try {
    const res = await axios.post(LARAVEL_API_URL, {
      customerno: 'anw-830',
      amount: 215,
      trans_ref: 'manual-fix-anw830',
      etd: 'thai-bill',
    }, {
      headers: { 'X-API-Key': LARAVEL_KEY, 'Content-Type': 'application/json' },
      timeout: 15000,
    });
    console.log('\n=== Laravel response ===');
    console.log(JSON.stringify(res.data, null, 2));
  } catch (err) {
    console.log('Laravel error:', err.response?.data || err.message);
  }

  await p.$disconnect();
})();
