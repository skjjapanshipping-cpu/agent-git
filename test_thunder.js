var fs = require('fs');
var path = require('path');
var FormData = require('form-data');
var axios = require('axios');

// Load .env manually
var envContent = fs.readFileSync('/opt/skjchat/.env.local', 'utf8');
var keyLine = envContent.split('\n').find(function(l) { return l.startsWith('THUNDER_API_KEY='); });
var key = keyLine ? keyLine.split('=').slice(1).join('=').trim().replace(/['"]/g, '') : '';

var testFile = 'public/uploads/line-media/604606881625276863.jpg';

console.log('Key present:', !!key, 'Key length:', key ? key.length : 0);
console.log('File exists:', fs.existsSync(testFile), 'Size:', fs.existsSync(testFile) ? fs.statSync(testFile).size : 0);

// Use Buffer (readFileSync) instead of createReadStream - this is the fix we applied
var fileBuffer = fs.readFileSync(testFile);
var form = new FormData();
form.append('image', fileBuffer, { filename: 'test.jpg', contentType: 'image/jpeg' });

console.log('Buffer length:', fileBuffer.length);

axios.post('https://api.thunder.in.th/v2/verify/bank', form, {
  headers: Object.assign({}, form.getHeaders(), { Authorization: 'Bearer ' + key }),
  timeout: 15000
}).then(function(r) {
  console.log('SUCCESS:', JSON.stringify(r.data).substring(0, 300));
}).catch(function(e) {
  console.log('ERR status:', e.response ? e.response.status : 'no response');
  console.log('ERR data:', JSON.stringify(e.response ? e.response.data : e.message));
});
