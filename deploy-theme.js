const { Client } = require('ssh2')
const fs = require('fs')
const path = require('path')

const SERVER = {
  host: '203.170.129.77',
  port: 2299,
  username: 'root',
  privateKey: fs.readFileSync(path.join(require('os').homedir(), '.ssh', 'id_rsa')),
  readyTimeout: 30000,
}

function ssh(conn, cmd) {
  return new Promise((resolve, reject) => {
    console.log(`\n$ ${cmd}`)
    conn.exec(cmd, (err, stream) => {
      if (err) return reject(err)
      let out = ''
      stream.on('data', d => { const s = d.toString(); out += s; process.stdout.write(s) })
      stream.stderr.on('data', d => { const s = d.toString(); out += s; process.stderr.write(s) })
      stream.on('close', () => resolve(out.trim()))
    })
  })
}

function uploadFile(conn, localPath, remotePath) {
  return new Promise((resolve, reject) => {
    conn.sftp((err, sftp) => {
      if (err) return reject(err)
      const rs = fs.createReadStream(localPath)
      const ws = sftp.createWriteStream(remotePath)
      ws.on('close', () => { console.log(`  Uploaded: ${path.basename(localPath)}`); resolve() })
      ws.on('error', reject)
      rs.pipe(ws)
    })
  })
}

function createZip(sourceDir, outPath) {
  const archiver = require('archiver')
  return new Promise((resolve, reject) => {
    const output = fs.createWriteStream(outPath)
    const archive = archiver('zip', { zlib: { level: 5 } })
    output.on('close', () => resolve(archive.pointer()))
    archive.on('error', reject)
    archive.pipe(output)
    archive.glob('**/*', { cwd: sourceDir, ignore: ['preview/**', '.git/**'], dot: false })
    archive.finalize()
  })
}

async function main() {
  const conn = new Client()
  conn.on('error', err => { console.error('SSH Error:', err.message); process.exit(1) })
  conn.on('ready', async () => {
    try {
      console.log('Connected!')
      const themePath = '/var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/themes/astra-child'
      const astraChildDir = path.resolve(__dirname, 'astra-child')
      const zipPath = path.join(process.env.TEMP || '/tmp', 'astra-child-deploy.zip')

      console.log('\n[1/4] Creating zip...')
      const bytes = await createZip(astraChildDir, zipPath)
      console.log(`  Size: ${(bytes / 1024).toFixed(1)} KB`)

      console.log('\n[2/4] Uploading and extracting...')
      await uploadFile(conn, zipPath, '/tmp/astra-child-deploy.zip')
      await ssh(conn, `unzip -o /tmp/astra-child-deploy.zip -d "${themePath}" && rm /tmp/astra-child-deploy.zip`)
      await ssh(conn, `chown -R skjjapanshipping.com_hqm6nsm5vpf:psacln "${themePath}" && chmod -R 755 "${themePath}"`)

      console.log('\n[3/4] Clearing caches...')
      await ssh(conn, 'rm -rf /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/cache/min/* 2>/dev/null; rm -rf /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/cache/wp-rocket/* 2>/dev/null; rm -rf /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/cache/used-css/* 2>/dev/null; rm -rf /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/cache/critical-css/* 2>/dev/null; find /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/cache/ -type f -name "*.html" -delete 2>/dev/null; find /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/cache/ -type f -name "*.css" -delete 2>/dev/null; echo "All caches cleared"')
      await ssh(conn, 'cd /var/www/vhosts/skjjapanshipping.com/httpdocs && wp cache flush --allow-root 2>/dev/null; echo "WP cache flushed"')

      console.log('\n[4/4] Restarting Nginx...')
      await ssh(conn, 'systemctl restart nginx && echo "Nginx restarted"')

      console.log('\n\n=== DEPLOY COMPLETE ===')
      fs.unlinkSync(zipPath)
      conn.end()
    } catch (err) {
      console.error('\nDeploy Error:', err.message || err)
      conn.end()
      process.exit(1)
    }
  })
  console.log('Connecting...')
  conn.connect(SERVER)
}
main()
