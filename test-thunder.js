const path = require('path');
const fs = require('fs');
process.env.THUNDER_API_KEY = 'e7e52268-ae18-4f81-8503-b28bb3c8b119';
const axios = require('/opt/skjchat/node_modules/axios').default;

const API = 'https://api.thunder.in.th/v2/verify/bank';
const apiKey = process.env.THUNDER_API_KEY;
const NodeFormData = require('/opt/skjchat/node_modules/form-data');

async function testSlip(label, filePath) {
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
    console.log(`[${label}] SUCCESS: amount=${res.data?.data?.amountInSlip}`);
  } catch (err) {
    console.log(`[${label}] FAIL: ${err.response?.status} ${err.response?.data?.error?.message || err.message}`);
  }
}

(async () => {
  // Known good slip (ANW-603, ฿1,886)
  await testSlip('ANW-603-good', '/opt/skjchat/public/uploads/line-media/603943668965179495.jpg');
  // Failed slip (ANW-548)
  await testSlip('ANW-548-fail', '/opt/skjchat/public/uploads/line-media/603918360870650163.jpg');
})();
