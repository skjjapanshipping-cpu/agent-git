const { PrismaClient } = require('/opt/skjchat/node_modules/@prisma/client');
const prisma = new PrismaClient();

async function main() {
  // Find all invoiceSent records for ETD 09/02/2026
  const invoices = await prisma.invoiceSent.findMany({
    where: {
      etd: '09/02/2026',
    },
    orderBy: { customerno: 'asc' },
  });

  console.log('=== invoiceSent ETD 09/02/2026 ===');
  console.log(`Total: ${invoices.length}\n`);

  const pending = invoices.filter(i => i.status === 'pending');
  const paid = invoices.filter(i => i.status === 'paid');

  console.log(`Paid: ${paid.length}`);
  paid.forEach(i => console.log(`  ✅ ${i.customerno} ฿${i.amount} paid=${i.paidAt?.toISOString()?.substring(0,10) || '-'} ref=${i.paidTransRef || '-'}`));

  console.log(`\nPending: ${pending.length}`);
  pending.forEach(i => console.log(`  ❌ ${i.customerno} ฿${i.amount} created=${i.createdAt?.toISOString()?.substring(0,10) || '-'}`));

  // Also check slipVerified records that were skipped
  console.log('\n=== Recent skipped slips (last 24h) ===');
  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);
  
  const slips = await prisma.slipVerified.findMany({
    where: {
      createdAt: { gte: yesterday },
    },
    orderBy: { createdAt: 'desc' },
    take: 50,
  });
  
  console.log(`Total slips verified (24h): ${slips.length}`);
  slips.forEach(s => {
    const name = s.customerName || '-';
    console.log(`  ${s.createdAt?.toISOString()?.substring(0,16)} ${name} ฿${s.amount} ref=${s.transRef?.substring(0,20) || '-'}`);
  });

  await prisma.$disconnect();
}

main().catch(e => { console.error(e); process.exit(1); });
