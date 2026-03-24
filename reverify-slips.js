const path = require('path');
const fs = require('fs');
const axios = require('/opt/skjchat/node_modules/axios').default;
const NodeFormData = require('/opt/skjchat/node_modules/form-data');

const API = 'https://api.thunder.in.th/v2/verify/bank';
const apiKey = 'e7e52268-ae18-4f81-8503-b28bb3c8b119';

// Image files from skipped customers (from logs)
const slips = [
  { label: 'ANW-776', file: '/opt/skjchat/public/uploads/line-media/603777039619063897.jpg' },
  { label: 'ANW-910', file: '/opt/skjchat/public/uploads/line-media/603892796638101524.jpg' },
  { label: 'ANW-508', file: '/opt/skjchat/public/uploads/line-media/603899128963662270.jpg' },
  { label: 'ANW-583', file: '/opt/skjchat/public/uploads/line-media/603918043512832461.jpg' },
  { label: 'ANW-548', file: '/opt/skjchat/public/uploads/line-media/603918360870650163.jpg' },
  { label: 'ANW-510', file: '/opt/skjchat/public/uploads/line-media/603948510349885441.jpg' },
];

async function verify(label, filePath) {
  if (!fs.existsSync(filePath)) {
    console.log(`[${label}] FILE NOT FOUND: ${filePath}`);
    return;
  }
  const form = new NodeFormData();
  form.append('image', fs.createReadStream(filePath), {
    filename: path.basename(filePath),
    contentType: 'image/jpeg',
  });
  try {
    const res = await axios.post(API, form, {
      headers: { ...form.getHeaders(), Authorization: 'Bearer ' + apiKey },
      timeout: 15000,
    });
    const d = res.data;
    if (d.success && d.data?.rawSlip) {
      const amt = d.data.amountInSlip;
      const ref = d.data.rawSlip.transRef;
      const sender = d.data.rawSlip.sender?.account?.name?.th || '-';
      console.log(`[${label}] ✅ SLIP ฿${amt} ref=${ref} sender=${sender}`);
    } else {
      console.log(`[${label}] ❌ Not a slip: ${d.error || d.message}`);
    }
  } catch (err) {
    const status = err.response?.status;
    if (status === 404) {
      console.log(`[${label}] ❌ Not a bank slip (404)`);
    } else {
      console.log(`[${label}] ❌ Error: ${status} ${err.response?.data?.error?.message || err.message}`);
    }
  }
}

(async () => {
  for (const s of slips) {
    await verify(s.label, s.file);
  }
})();
