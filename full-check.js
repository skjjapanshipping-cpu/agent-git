const { Client } = require('ssh2')
const fs = require('fs')
const path = require('path')
const https = require('https')

const SERVER = {
  host: '203.170.129.77',
  port: 2299,
  username: 'root',
  privateKey: fs.readFileSync(path.join(require('os').homedir(), '.ssh', 'id_rsa')),
  readyTimeout: 30000,
}

function ssh(conn, cmd) {
  return new Promise((resolve, reject) => {
    conn.exec(cmd, (err, stream) => {
      if (err) return reject(err)
      let out = ''
      stream.on('data', d => out += d.toString())
      stream.stderr.on('data', d => out += d.toString())
      stream.on('close', () => resolve(out.trim()))
    })
  })
}

async function main() {
  const conn = new Client()
  conn.on('ready', async () => {
    try {
      console.log('=== 1. PHP SYNTAX CHECK ===')
      const phpCheck = await ssh(conn, 'php -l /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/themes/astra-child/functions.php 2>&1')
      console.log(phpCheck)
      const phpCheck2 = await ssh(conn, 'php -l /var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/themes/astra-child/template-homepage.php 2>&1')
      console.log(phpCheck2)

      console.log('\n=== 2. HTML SECTIONS CHECK ===')
      const html = await ssh(conn, 'curl -sk "https://203.170.129.77/" -H "Host: skjjapanshipping.com" 2>/dev/null')
      
      const sections = [
        'hero-slider', 'slider-viewport', 'hero-overlay', 'hero-badge',
        'services-grid', 'service-card', 'highlight-stats', 'data-count',
        'steps-grid', 'calculator', 'shops-grid', 'testimonials',
        'faq-section', 'floating-line-btn', 'cta-section'
      ]
      sections.forEach(s => {
        const count = (html.match(new RegExp(s, 'g')) || []).length
        console.log(`  ${s}: ${count > 0 ? '✓ (' + count + ')' : '✗ MISSING'}`)
      })

      console.log('\n=== 3. CSS FILES CHECK ===')
      const cssLinks = html.match(/href=['"'][^'"]*\.css[^'"]*['"']/gi) || []
      const skjCSS = cssLinks.filter(l => l.includes('skj-') || l.includes('astra-child'))
      console.log(`  Total CSS: ${cssLinks.length}, Theme CSS: ${skjCSS.length}`)
      skjCSS.forEach(l => console.log(`  ${l}`))

      console.log('\n=== 4. JS FILES CHECK ===')
      const jsLinks = html.match(/src=['"'][^'"]*skj-custom[^'"]*['"']/gi) || []
      console.log(`  skj-custom.js: ${jsLinks.length > 0 ? '✓' : '✗ MISSING'}`)
      if (jsLinks.length) console.log(`  ${jsLinks[0]}`)

      console.log('\n=== 5. LINK CHECK (internal) ===')
      const hrefs = html.match(/href=['"'](https:\/\/skjjapanshipping\.com[^'"']*)['"']/gi) || []
      const uniqueLinks = [...new Set(hrefs.map(h => h.replace(/href=['"']/i,'').replace(/['"']$/,'')))]
      console.log(`  Found ${uniqueLinks.length} unique internal links`)
      
      for (const link of uniqueLinks.slice(0, 15)) {
        const status = await ssh(conn, `curl -sk -o /dev/null -w "%{http_code}" "${link}" 2>/dev/null`)
        console.log(`  ${status === '200' ? '✓' : status === '301' || status === '302' ? '→' : '✗'} ${status} ${link.replace('https://skjjapanshipping.com','')}`)
      }

      console.log('\n=== 6. SEO CHECK ===')
      const hasMeta = (name) => {
        const regex = new RegExp(`<meta[^>]*name=["']${name}["'][^>]*>`, 'i')
        return regex.test(html) ? '✓' : '✗'
      }
      console.log(`  description: ${hasMeta('description')}`)
      console.log(`  keywords: ${hasMeta('keywords')}`)
      console.log(`  robots: ${hasMeta('robots')}`)
      console.log(`  og:title: ${html.includes('og:title') ? '✓' : '✗'}`)
      console.log(`  og:description: ${html.includes('og:description') ? '✓' : '✗'}`)
      console.log(`  og:image: ${html.includes('og:image') ? '✓' : '✗'}`)
      console.log(`  twitter:card: ${html.includes('twitter:card') ? '✓' : '✗'}`)
      console.log(`  canonical: ${html.includes('rel="canonical"') ? '✓' : '✗'}`)
      console.log(`  JSON-LD: ${html.includes('application/ld+json') ? '✓' : '✗'}`)

      const jsonldMatch = html.match(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/i)
      if (jsonldMatch) {
        try {
          const ld = JSON.parse(jsonldMatch[1])
          const types = (ld['@graph'] || [ld]).map(s => s['@type']).filter(Boolean)
          console.log(`  JSON-LD types: ${types.join(', ')}`)
        } catch(e) { console.log(`  JSON-LD parse error: ${e.message}`) }
      }

      console.log('\n=== 7. SITEMAP CHECK ===')
      const sitemapStatus = await ssh(conn, 'curl -sk -o /dev/null -w "%{http_code}" "https://203.170.129.77/skj-sitemap.xml" -H "Host: skjjapanshipping.com" 2>/dev/null')
      console.log(`  skj-sitemap.xml: ${sitemapStatus === '200' ? '✓' : '✗'} (${sitemapStatus})`)

      console.log('\n=== 8. PHP ERROR LOG (recent) ===')
      const errors = await ssh(conn, 'grep -i "fatal\\|parse error\\|astra-child" /var/www/vhosts/skjjapanshipping.com/logs/error_log 2>/dev/null | tail -5')
      console.log(errors || '  No PHP errors found ✓')

      console.log('\n=== 9. PAGE SPEED HINTS ===')
      const hasPreload = html.includes('rel="preload"')
      const hasDefer = html.includes('defer')
      console.log(`  Preload hints: ${hasPreload ? '✓' : '✗'}`)
      console.log(`  Deferred JS: ${hasDefer ? '✓' : '✗'}`)
      console.log(`  HTML size: ${(html.length / 1024).toFixed(1)} KB`)

      conn.end()
    } catch (err) {
      console.error('Error:', err.message)
      conn.end()
    }
  })
  conn.connect(SERVER)
}
main()
