# SKJ Japan Shipping — Agent Guide

> ระบบครบวงจรของ SKJ Japan Shipping (ขนส่งสินค้าจากญี่ปุ่นมาไทย) ประกอบด้วย 3 sub-projects ที่ต้องทำงานร่วมกัน ทั้งหมด deploy ไปที่ domain `skjjapanshipping.com`
>
> 🔒 **Security note**: secrets/IPs ใน guide นี้ใช้ placeholder (`<ORIGIN_IP>`, `<SSH_KEY_PATH>` ฯลฯ) — ค่าจริงอยู่ใน `.cursor/secrets.local.md` (gitignored). อย่า paste ค่าจริงลงไฟล์ที่ committable

## Overview

| Sub-project | Path | Tech | บทบาท |
|---|---|---|---|
| **Backoffice** | `backoffice/` | Laravel 5.x + Blade + jQuery + DataTables | ระบบหลังบ้านจัดการลูกค้า/ขนส่ง + ระบบหน้าบ้านลูกค้า "My Shipping" + Scanner app |
| **WordPress Theme** | `astra-child/` | Astra child theme (PHP) | Marketing pages (homepage, about, services, blog, contact) + SEO |
| **Chat App** | `C:\skjchat` *(แยก repo)* | Next.js 14 + Prisma + NextAuth + Tailwind | Chat ระหว่าง admin ↔ ลูกค้า (รวม LINE bridge) — มี [AGENTS.md ของตัวเอง](../skjchat/AGENTS.md) |

## Production

- **Domain**: `https://skjjapanshipping.com`
- **Origin IP / SSH**: ดู `.cursor/secrets.local.md` (Cloudflare proxy อยู่ด้านหน้า)
- **Hosting**: Plesk panel, Apache + Nginx, PHP 7.4.33

### Paths บน server
| Component | Path |
|---|---|
| WordPress | `/var/www/vhosts/skjjapanshipping.com/httpdocs/` |
| Laravel backoffice | `/var/www/vhosts/skjjapanshipping.com/backoffice/` |
| WP child theme | `/var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/themes/astra-child/` |

### Deploy command pattern
> รายละเอียดเต็มและ command พร้อมใช้: ดู `.cursor/rules/deployment.mdc` + `.cursor/secrets.local.md`

```powershell
# Upload file ไป server (port <SSH_PORT>, ตรง origin IP — bypass Cloudflare)
scp -i <SSH_KEY_PATH> -P <SSH_PORT> -o StrictHostKeyChecking=no `
    "C:\skjtrack\backoffice\<file>" `
    <SSH_USER>@<ORIGIN_IP>:/var/www/vhosts/skjjapanshipping.com/backoffice/<path>

# Clear Laravel cache หลัง deploy view/config
ssh -i <SSH_KEY_PATH> -p <SSH_PORT> <SSH_USER>@<ORIGIN_IP> `
    "cd /var/www/vhosts/skjjapanshipping.com/backoffice && php artisan view:clear"
```

> ⚠️ ห้าม `scp` ผ่าน hostname `skjjapanshipping.com` — จะชี้ไป Cloudflare แล้ว timeout ต้องใช้ origin IP ตรงๆ

## Backoffice (Laravel)

### Key Models
- `App\User` — มี accessor `customercode` (ANW-XXXX uppercase จาก `customerno` lowercase)
- `App\Models\Customershipping` — ตารางหลัก แต่ละ row = 1 box; field `etd`, `box_no`, `thai_tracking_no`, `thai_shipping_price`, etc.
- `App\Models\ExtraShippingCharge` — ค่าบริการเพิ่มเติม (ไม่มี box เช่นค่ากล่อง Repack) — มี `sequence_no` กัน dedup ซ้ำใน batch
- `App\Models\SystemSetting` — config ที่ admin แก้ผ่าน UI

### Key Controllers
- `CustomershippingController` — Admin "My Shipping" page (จัดการ box, ETD, รูป)
- `CustomerShippingViewController` — Customer-facing "My Shipping" page (DataTable + summary cards + recipient filter)
- `ShippopController` — รับ PDF บิล Shippop → parse text → save `thai_shipping_price` ต่อ box + `ExtraShippingCharge` สำหรับ items ที่ไม่มี box
- `QrScanController` + `ScannerAuthController` — Scanner app (มือถือ scan QR ทำ pickup)
- `InvoiceController` — ออก invoice PDF/Excel

### Key Views (Blade)
- `customershipping/index.blade.php` — **Admin** My Shipping (~5000 lines, มี modal "แจ้งค่าส่งไทย")
- `customershippingview/index.blade.php` — **Customer** My Shipping (~4100 lines, custom dropdown UI, summary cards, Quick View, สรุปบิลค่าส่งในไทย modal)
- `scanner/pickup.blade.php` — Scanner UI
- `layouts/app.blade.php` — Admin layout (FontAwesome 4, Paper Dashboard)

### Conventions
- Frontend ใช้ **FontAwesome 4** (`fa fa-truck`, `fa fa-money` ฯลฯ) — ไม่มี `fa-truck-fast` หรือ `fa-shipping-fast`
- Custom dropdown UI: class `.modern-dropdown` + sub-classes `.dd-toggle`, `.dd-menu`, `.dd-list`, `.dd-item`, `.dd-scroll-btn` — ใช้ร่วมกันระหว่าง recipient + ETD dropdown (mobile-friendly, ทำงานบน LINE LIFF webview)
- ภาษา UI: **ไทย** (label, modal title, toast)
- Date format: `dd/MM/yyyy` (เช่น `20/04/2026`) สำหรับ display, `Y-m-d` สำหรับ DB
- Customer code format: lowercase `anw-NNNN` ใน DB, แสดงเป็น uppercase `ANW-NNNN` ผ่าน `$user->customercode`

### Migrations
- ทำใน `backoffice/database/migrations/` ตั้งชื่อแบบ `YYYY_MM_DD_HHMMSS_description.php`
- รัน production: `php artisan migrate --force`
- ห้ามแก้ migration ที่ run แล้ว — สร้างไฟล์ใหม่เสมอ

## astra-child (WordPress)

- ใช้ Astra parent theme + child theme custom
- หน้า homepage = `template-homepage.php` (template assigned to home page)
- Partials: `template-parts/skj-head.php`, `skj-header.php`, `skj-footer.php`
- CSS: `assets/css/skj-theme.css`, `skj-pages.css`
- SEO + sitemap logic อยู่ใน `functions.php` (XML sitemap ที่ `/skj-sitemap.xml`)
- WP admin login URL: `/skj-admin-login` (hidden ด้วย plugin `skj-hide-login`) — `wp-login.php` ตรงๆ จะ 404
- WP cache: LiteSpeed/W3 Total Cache — clear ผ่าน `wp-content/cache/` หลัง deploy

## skjchat (Next.js)

- Repo แยกที่ `C:\skjchat` — ใช้ Next.js 14 + Prisma + NextAuth + Tailwind
- ทำหน้าที่ chat ระหว่าง admin ↔ customer + LINE Bridge
- Deploy script: `scripts/provision-prod-env.mjs` (ใช้ ssh2 + basic-ftp)
- Backoffice เรียก skjchat API ผ่าน `LineMessagingService` หรือ Chat API endpoint
- รายละเอียดเต็ม → `C:\skjchat\AGENTS.md` + `C:\skjchat\.cursor\rules\*.mdc`

## Workflow / Best Practices

1. **แก้ Laravel view** → edit `backoffice/resources/views/...` → `scp` ไป server → `php artisan view:clear`
2. **แก้ controller/model/migration** → `scp` + `php artisan config:clear cache:clear` + รัน migrate ถ้ามี schema change
3. **แก้ WP theme** → `scp` ไป `wp-content/themes/astra-child/` + clear WP cache
4. **ห้าม commit credentials** — `.env`, `id_rsa`, custom passwords, real IP ใน committable files
5. **Test บน mobile** ก่อน deploy — ลูกค้าส่วนใหญ่เข้าผ่าน LINE LIFF webview → ใช้ custom dropdown แทน native `<select>` ในกรณีที่ต้องการ reliability

## Common Gotchas

### Shell / PowerShell
- ❌ `&&` ไม่รองรับใน PowerShell — ใช้ `;` หรือ split เป็นหลาย calls
- ❌ Quoting bash command ผ่าน `ssh` ตรงๆยาก — สร้าง `.sh` script local → `scp` upload → `ssh ... bash script.sh`
- ❌ `php artisan tinker --execute='...'` ผ่าน ssh + PowerShell → parse error เพราะ quote ซ้อนกัน → ใช้ bash script แทน
- ✅ `$()` ใน PowerShell ต้อง escape เป็น `\$()` ตอน pass ผ่าน ssh

### Network / Deploy
- ❌ SSH/SCP ผ่าน hostname `skjjapanshipping.com` → Cloudflare timeout (port 22 ถูก proxy block) → ใช้ origin IP จริงเท่านั้น
- ✅ หลัง deploy view ต้อง `php artisan view:clear` มิฉะนั้นเห็นเวอร์ชั่นเก่า (compiled view cache)
- ✅ Migration ต้องใส่ `--force` flag ใน production (`php artisan migrate --force`)

### Frontend / Mobile
- ❌ iOS/LINE LIFF webview ไม่เปิด native `<select>` picker บางครั้ง → ใช้ `.modern-dropdown` แทน (ดู `customershippingview/index.blade.php`)
- ❌ Touch event ต้อง bind ทั้ง `click` + `touchend` ใน LIFF (กัน 300ms delay)
- ✅ `scrollbar-gutter: stable` จำเป็นบน custom dropdown list เพื่อกัน scrollbar ทับ text
- ✅ Min touch target 44×44px (Apple HIG) สำหรับ scroll arrow ใน dropdown

### PDF Parsing (Smalot)
- ❌ Smalot PdfParser ใส่ space ใน Thai words (เช่น `กล่ องเบอร์` แทน `กล่องเบอร์`) → regex ต้อง `\s*` ระหว่างตัวอักษร
- ❌ "ค่ากล่อง" หรือ "ค่าบริการเพิ่มเติม" ไม่มี box number → ห้าม filter ทิ้งใน frontend ก่อน save (เคยเป็น bug)
- ✅ Multi-PDF upload: ใช้ "replace batch" strategy + `sequence_no` แทน `updateOrCreate` (กัน dedup รายการที่เหมือนกัน)
- ✅ Debug: `php artisan tinker` + เรียก `extractShippopItems()` ดู intermediate output

### Database
- ❌ `Carbon::now()->subMonths(N)` ที่ปลายเดือนอาจ overflow ไปเดือนถัดไป → `Carbon::now()->startOfMonth()->subMonths(N)` เสมอ
- ✅ `selectRaw()` ปลอดภัยกว่า `whereRaw` กับ user input (ต้องใช้ `bindings`)
- ✅ Eager load relations ผ่าน `->with()` กัน N+1 queries บน DataTable

### LINE / Chat Integration
- ❌ LINE webhook timeout = 1 sec → ตอบ 200 OK ทันที + process async
- ❌ LINE push quota 1,000/เดือน (free tier) → ใช้ skjchat fallback ก่อนเสมอ
- ✅ Verify signature ก่อน process webhook (ใช้ raw body, ไม่ใช่ parsed JSON)

### Maintenance Note
- Refactor ใหญ่ใน view (`customershipping/index.blade.php` 5000 lines) → แยก partial blade ทีละส่วน ห้ามทำทีเดียวทั้งหมด

## Maintenance Policy

### เมื่อไหร่ต้องอัพเดท AGENTS.md / .cursor/rules
| Trigger | Action |
|---|---|
| Schema change ใน DB (model, migration ใหม่) | Update "Key Models" section + ตัวอย่างใน `laravel-patterns.mdc` |
| Controller/View ใหม่ที่สำคัญ | เพิ่มใน "Key Controllers/Views" |
| Server migration (IP/port/path เปลี่ยน) | Update `.cursor/secrets.local.md` + verify rules ที่อ้าง placeholder ถูกต้อง |
| Library version bump (Laravel/PHP major) | Update header tech stack + ตรวจ syntax patterns |
| Bug ที่ใช้เวลาเกิน 30 นาทีแก้ | เพิ่ม 1 บรรทัดใน "Common Gotchas" |
| Workflow ใหม่ (deploy step เพิ่ม) | Update "Workflow / Best Practices" |
| ทุกๆ 3 เดือน | Review ทั้งหมด — ลบที่ outdated, รวบ section ที่ซ้ำ |

### Format ของ Changelog entry
```
- YYYY-MM-DD: <module> — <change summary> (PR/commit ref ถ้ามี)
```

## Changelog

- **2026-05-27** — Secret hygiene pass: scrub origin IP + SSH key path ออกจาก AGENTS.md / `deployment.mdc` → ย้ายไป `.cursor/secrets.local.md`. เพิ่ม Maintenance Policy + Changelog section. ขยาย Common Gotchas ให้ครอบคลุม bug ที่เคยแก้
- **2026-05-26** — Initial creation: รวบรวม system overview, 3 sub-projects, deploy pattern, key models/controllers/views, conventions. แตก scope rules 12 ไฟล์ใน `.cursor/rules/`
- **2026-05-24** — `ExtraShippingCharge.sequence_no` added (migration `2026_05_24_200000_*`) — เปลี่ยน save strategy เป็น "replace batch"
- **2026-05-23** — Recipient + ETD dropdown รวมเป็น `.modern-dropdown` pattern (mobile-friendly, LIFF compatible)

## See Also

- **skjchat AGENTS.md**: `C:\skjchat\AGENTS.md`
- **Local secrets**: `.cursor/secrets.local.md` (gitignored)
- **Scope rules**: `.cursor/rules/*.mdc` (12 ไฟล์ — load อัตโนมัติตาม glob)
  - `conventions.mdc` (always) — UI lang, date, money, customer code
  - `deployment.mdc` (always) — SSH/SCP patterns
  - `laravel-patterns.mdc` — Laravel 5.x, Eloquent, Auth
  - `blade-ui.mdc` — `.modern-dropdown`, summary cards, modal
  - `migrations.mdc` — schema patterns
  - `pdf-parsing.mdc` — Smalot quirks, regex tolerance
  - `datatable.mdc` — jQuery DataTables (client/server-side)
  - `exports.mdc` — Excel/PDF (Maatwebsite, DomPDF, Thai font)
  - `line-messaging.mdc` — LINE push/Flex/quota
  - `chat-integration.mdc` — Laravel → skjchat API
  - `scanner.mdc` — Scanner mobile + guard
  - `wordpress.mdc` — Astra child + SEO
