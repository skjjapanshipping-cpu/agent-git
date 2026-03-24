const { PrismaClient } = require('/opt/skjchat/node_modules/@prisma/client');
const axios = require('/opt/skjchat/node_modules/axios').default;
const fs = require('fs');
const path = require('path');
const p = new PrismaClient();

const THUNDER_API_URL = 'https://api.thunder.in.th/v2/verify/bank';
const apiKey = 'e7e52268-ae18-4f81-8503-b28bb3c8b119';
const LARAVEL_API_URL = 'https://skjjapanshipping.com/skjtrack/api/update-pay-status';
const LARAVEL_KEY = 'skjchat-invoice-2026';

const slips = [
  { file: '/opt/skjchat/public/uploads/line-media/604036570349305859.jpg', custNo: 'anw-636' },
  { file: '/opt/skjchat/public/uploads/line-media/604035914343907755.jpg', custNo: 'anw-578' },
];

async function verifyAndUpdate(slip) {
  console.log('\n=== Processing', slip.custNo.toUpperCase(), '===');
  
  // 1. Verify with Thunder
  const NodeFormData = require('/opt/skjchat/node_modules/form-data');
  const form = new NodeFormData();
  form.append('image', fs.createReadStream(slip.file), {
    filename: path.basename(slip.file),
    contentType: 'image/jpeg',
  });
  
  const thunderRes = await axios.post(THUNDER_API_URL, form, {
    headers: { ...form.getHeaders(), Authorization: 'Bearer ' + apiKey },
    timeout: 15000,
  });
  
  const data = thunderRes.data;
  if (!data.success) {
    console.log('Thunder failed:', data);
    return;
  }
  
  const rawSlip = data.data.rawSlip;
  const amount = rawSlip.amount.amount;
  const transRef = rawSlip.transRef;
  const senderName = rawSlip.sender?.account?.name?.th || '-';
  const receiverName = rawSlip.receiver?.account?.name?.th || '-';
  console.log('Verified:', amount, 'THB, transRef:', transRef, 'sender:', senderName, 'receiver:', receiverName);
  
  // 2. Find pending invoice
  const thirtyDaysAgo = new Date();
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
  
  const invoices = await p.invoiceSent.findMany({
    where: {
      customerno: slip.custNo,
      status: 'pending',
      createdAt: { gte: thirtyDaysAgo },
    },
    orderBy: { createdAt: 'desc' },
  });
  
  console.log('Pending invoices:', invoices.length, invoices.map(i => `ETD=${i.etd} ฿${i.amount}`).join(', '));
  
  // 3. Match amount
  const tolerance = 1.0;
  const match = invoices.find(inv => Math.abs(inv.amount - amount) <= tolerance);
  const totalMatch = invoices.length > 1 && Math.abs(invoices.reduce((s, i) => s + i.amount, 0) - amount) <= tolerance;
  const matchedInvoices = match ? [match] : totalMatch ? invoices : [];
  
  if (matchedInvoices.length === 0) {
    console.log('No amount match for slip:', amount);
    return;
  }
  
  console.log('Matched', matchedInvoices.length, 'invoice(s)');
  
  // 4. Update invoiceSent status
  let allShippingIds = [];
  const etdList = [];
  for (const inv of matchedInvoices) {
    await p.invoiceSent.update({
      where: { id: inv.id },
      data: { status: 'paid', paidAt: new Date(), paidTransRef: transRef },
    });
    if (inv.shippingIds) {
      try {
        const ids = JSON.parse(inv.shippingIds);
        if (Array.isArray(ids)) allShippingIds.push(...ids);
      } catch {}
    }
    if (inv.etd && !etdList.includes(inv.etd)) etdList.push(inv.etd);
  }
  console.log('Invoice updated to paid, ETDs:', etdList.join(','));
  
  // 5. Call Laravel API
  try {
    const laravelRes = await axios.post(LARAVEL_API_URL, {
      customerno: slip.custNo,
      amount,
      trans_ref: transRef,
      etd: etdList.join(','),
      shipping_ids: allShippingIds.length > 0 ? allShippingIds : null,
    }, {
      headers: { 'X-API-Key': LARAVEL_KEY, 'Content-Type': 'application/json' },
      timeout: 15000,
    });
    console.log('Laravel response:', JSON.stringify(laravelRes.data));
  } catch (err) {
    console.log('Laravel error:', err.response?.data || err.message);
  }
}

(async () => {
  for (const slip of slips) {
    await verifyAndUpdate(slip);
  }
  await p.$disconnect();
})();
