<?php
/**
 * Template Name: SKJ Services
 * Description: Services page for SKJ Japan
 */
get_template_part('template-parts/skj-head');
get_template_part('template-parts/skj-header');
$register_url = home_url('/skjtrack/register');
?>
<style>
.service-tag { display:inline-flex;align-items:center;gap:6px;background:#fef2f2;color:var(--red);padding:4px 14px;border-radius:20px;font-size:0.78rem;font-weight:700;margin-bottom:10px; }
.service-detail-content h3 { font-size:1.3rem;font-weight:800;margin-bottom:10px;color:var(--gray-900); }
.service-detail-content ul { list-style:none;padding:0;margin:16px 0; }
.service-detail-content li { padding:6px 0;font-size:0.88rem;color:var(--gray-600);display:flex;align-items:center;gap:8px; }
.service-detail-content li i { color:var(--red);font-size:0.75rem; }
.pricing-card .price { font-size:2.5rem;font-weight:900;color:var(--red);margin:12px 0 4px; }
.pricing-card .price-unit { font-size:0.82rem;color:var(--gray-400);margin-bottom:16px; }
.pricing-card .features { list-style:none;padding:0;margin:0 0 20px; }
.pricing-card .features li { padding:6px 0;font-size:0.85rem;color:var(--gray-600);display:flex;align-items:center;gap:8px; }
.pricing-card .features li i { color:var(--red);font-size:0.75rem; }
</style>
<main>
<div class="header-spacer"></div>
<section class="page-hero">
    <div class="eyebrow">Our Services</div>
    <h1>บริการของเรา</h1>
    <p>ครบวงจรทุกความต้องการนำเข้าสินค้าจากญี่ปุ่น ด้วยบริการที่หลากหลาย</p>
    <div class="breadcrumb"><a href="<?php echo home_url('/'); ?>">หน้าแรก</a><span>/</span><span style="color:rgba(255,255,255,0.8);">บริการของเรา</span></div>
</section>

<!-- SERVICE 1 -->
<section class="section">
    <div class="section-inner">
        <div class="service-detail animate-item">
            <div class="service-detail-content">
                <span class="service-tag"><i class="fas fa-ship"></i> บริการหลัก</span>
                <h3>ขนส่งสินค้าทางเรือ</h3>
                <p>บริการขนส่งสินค้าจากญี่ปุ่นมาไทยทางเรือ ราคาประหยัด ปิดตู้ทุกสัปดาห์ สินค้าถึงไทยภายใน 14-25 วัน</p>
                <ul><li><i class="fas fa-check"></i> ปิดตู้สินค้าทุกสัปดาห์</li><li><i class="fas fa-check"></i> ราคาเริ่มต้น กก. ละ 150 บาท</li><li><i class="fas fa-check"></i> สินค้าถึงไทยภายใน 14-25 วัน</li><li><i class="fas fa-check"></i> แพ็คสินค้าอย่างดี ปลอดภัย</li><li><i class="fas fa-check"></i> ระบบ Track & Trace ติดตามสถานะ</li></ul>
                <a href="<?php echo esc_url($register_url); ?>" class="btn btn-red"><i class="fas fa-user-plus"></i> เริ่มต้นใช้งาน</a>
            </div>
            <div class="service-detail-image"><img src="https://skjjapanshipping.com/wp-content/uploads/2025/03/7.webp" alt="ขนส่งสินค้าทางเรือ" loading="lazy" decoding="async"></div>
        </div>
    </div>
</section>

<!-- SERVICE 1.5 - AIR SHIPPING -->
<section class="section" style="background:var(--gray-50);">
    <div class="section-inner">
        <div class="service-detail reverse animate-item">
            <div class="service-detail-content">
                <span class="service-tag" style="background:#eff6ff;color:#2563eb;"><i class="fas fa-plane"></i> บริการใหม่</span>
                <h3>ขนส่งสินค้าทางเครื่องบิน</h3>
                <p>บริการขนส่งสินค้าทางอากาศจากญี่ปุ่นมาไทย รวดเร็วทันใจ ถึงไทยภายใน 3-7 วัน เหมาะสำหรับสินค้าที่ต้องการความรวดเร็ว</p>
                <ul><li><i class="fas fa-check"></i> ถึงไทยเร็ว 3-7 วัน</li><li><i class="fas fa-check"></i> ราคาเริ่มต้น กก. ละ 339 บาท</li><li><i class="fas fa-check"></i> รองรับ CBM สำหรับสินค้าขนาดใหญ่</li><li><i class="fas fa-check"></i> แพ็คสินค้าอย่างดี ปลอดภัย</li><li><i class="fas fa-check"></i> ระบบ Track & Trace ติดตามสถานะ</li></ul>
                <a href="<?php echo esc_url($register_url); ?>" class="btn btn-red" style="background:#2563eb;"><i class="fas fa-user-plus"></i> เริ่มต้นใช้งาน</a>
            </div>
            <div class="service-detail-image"><img src="https://skjjapanshipping.com/wp-content/uploads/2025/03/7.webp" alt="ขนส่งสินค้าทางเครื่องบิน" loading="lazy" decoding="async"></div>
        </div>
    </div>
</section>

<!-- SERVICE 2 -->
<section class="section">
    <div class="section-inner">
        <div class="service-detail animate-item">
            <div class="service-detail-content">
                <span class="service-tag"><i class="fas fa-cart-shopping"></i> บริการเสริม</span>
                <h3>รับสั่งซื้อสินค้า</h3>
                <p>บริการรับสั่งซื้อสินค้าจากทุกเว็บไซต์ในญี่ปุ่น ไม่ว่าจะเป็น Yahoo Auctions, Mercari, Amazon JP, Rakuten และอื่นๆ</p>
                <ul><li><i class="fas fa-check"></i> สั่งซื้อจากทุกเว็บไซต์ญี่ปุ่น</li><li><i class="fas fa-check"></i> ทีมงานคนไทยช่วยจัดซื้อ</li><li><i class="fas fa-check"></i> ส่งลิงก์สินค้ามา เราจัดการให้</li><li><i class="fas fa-check"></i> ตรวจสอบสินค้าก่อนจัดส่ง</li><li><i class="fas fa-check"></i> ค่าบริการสมเหตุสมผล</li></ul>
                <a href="https://lin.ee/whDh44F" target="_blank" class="btn btn-red"><i class="fab fa-line"></i> สอบถามทาง LINE</a>
            </div>
            <div class="service-detail-image"><img src="https://skjjapanshipping.com/wp-content/uploads/2025/03/6.webp" alt="รับสั่งซื้อสินค้า" loading="lazy" decoding="async"></div>
        </div>
    </div>
</section>

<!-- SERVICE 3 -->
<section class="section">
    <div class="section-inner">
        <div class="service-detail animate-item">
            <div class="service-detail-content">
                <span class="service-tag"><i class="fas fa-warehouse"></i> คลังสินค้า</span>
                <h3>คลังสินค้าญี่ปุ่น</h3>
                <p>มีคลังสินค้าที่ญี่ปุ่นพร้อมรองรับสินค้าของลูกค้า เก็บรวบรวมก่อนจัดส่ง ช่วยประหยัดค่าขนส่ง</p>
                <ul><li><i class="fas fa-check"></i> คลังสินค้าพร้อมรองรับ</li><li><i class="fas fa-check"></i> เก็บรวบรวมก่อนส่ง ประหยัดค่าขนส่ง</li><li><i class="fas fa-check"></i> ตรวจสอบสภาพสินค้า</li><li><i class="fas fa-check"></i> ถ่ายรูปสินค้าให้ตรวจสอบ</li><li><i class="fas fa-check"></i> แพ็คสินค้าอย่างปลอดภัย</li></ul>
                <a href="<?php echo esc_url($register_url); ?>" class="btn btn-red"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
            </div>
            <div class="service-detail-image"><img src="https://skjjapanshipping.com/wp-content/uploads/2025/03/%E0%B8%A0%E0%B8%B2%E0%B8%9E%E0%B8%97%E0%B8%B5%E0%B9%885.webp" alt="คลังสินค้าญี่ปุ่น" loading="lazy" decoding="async"></div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" style="background:var(--gray-50);">
    <div class="section-inner">
        <div class="section-header"><div class="eyebrow">How It Works</div><h2>ขั้นตอนการใช้บริการ</h2><p>ง่ายเพียง 4 ขั้นตอน ก็รับสินค้าจากญี่ปุ่นถึงบ้าน</p></div>
        <div class="steps-grid">
            <div class="step-card animate-item"><div class="step-number">1</div><h3>สมัครสมาชิก</h3><p>สมัครฟรี รับที่อยู่คลังญี่ปุ่น</p></div>
            <div class="step-card animate-item"><div class="step-number">2</div><h3>สั่งซื้อสินค้า</h3><p>สั่งซื้อจากเว็บญี่ปุ่น ส่งมาที่คลัง</p></div>
            <div class="step-card animate-item"><div class="step-number">3</div><h3>แจ้งส่งสินค้า</h3><p>แจ้งผ่านระบบ รอปิดตู้ทุกสัปดาห์</p></div>
            <div class="step-card animate-item"><div class="step-number">4</div><h3>รับสินค้าที่ไทย</h3><p>สินค้าถึงไทย ส่งถึงบ้านคุณ!</p></div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section class="section">
    <div class="section-inner">
        <div class="section-header"><div class="eyebrow">Pricing</div><h2>อัตราค่าบริการขนส่ง</h2><p>ราคาคุ้มค่า โปร่งใส ไม่มีค่าใช้จ่ายแอบแฝง</p></div>
        <div class="pricing-grid">
            <div class="pricing-card animate-item">
                <h3>ขนส่งทางเรือ</h3><p style="color:var(--gray-400);font-size:0.82rem;">สินค้าทั่วไป</p>
                <div class="price">150<span style="font-size:1rem;font-weight:600;">฿</span></div><div class="price-unit">ต่อ กิโลกรัม</div>
                <ul class="features"><li><i class="fas fa-check"></i> ปิดตู้ทุกสัปดาห์</li><li><i class="fas fa-check"></i> ถึงไทย 14-25 วัน</li><li><i class="fas fa-check"></i> Track & Trace</li><li><i class="fas fa-check"></i> แพ็คสินค้าให้ฟรี</li></ul>
                <a href="<?php echo esc_url($register_url); ?>" class="btn btn-outline" style="width:100%;justify-content:center;">สมัครเลย</a>
            </div>
            <div class="pricing-card popular animate-item">
                <h3>ขนส่งทางเรือ</h3><p style="color:var(--gray-400);font-size:0.82rem;">สินค้าขนาดใหญ่ / คิดตาม CBM</p>
                <div class="price">10,000<span style="font-size:1rem;font-weight:600;">฿</span></div><div class="price-unit">ต่อ คิวบิกเมตร (CBM)</div>
                <ul class="features"><li><i class="fas fa-check"></i> เหมาะกับสินค้าหนักและใหญ่</li><li><i class="fas fa-check"></i> ถึงไทย 14-25 วัน</li><li><i class="fas fa-check"></i> Track & Trace</li><li><i class="fas fa-check"></i> ประกันสินค้า</li></ul>
                <a href="<?php echo esc_url($register_url); ?>" class="btn btn-red" style="width:100%;justify-content:center;">สมัครเลย</a>
            </div>
            <div class="pricing-card animate-item">
                <h3>ขนส่งทางเครื่องบิน ✈️</h3><p style="color:var(--gray-400);font-size:0.82rem;">สินค้าทั่วไป — เร็วทันใจ</p>
                <div class="price" style="color:#2563eb;">339<span style="font-size:1rem;font-weight:600;">฿</span></div><div class="price-unit">ต่อ กิโลกรัม</div>
                <ul class="features"><li><i class="fas fa-check"></i> ถึงไทยเร็ว 3-7 วัน</li><li><i class="fas fa-check"></i> Track & Trace</li><li><i class="fas fa-check"></i> รองรับ CBM</li><li><i class="fas fa-check"></i> แพ็คสินค้าให้ฟรี</li></ul>
                <a href="<?php echo esc_url($register_url); ?>" class="btn btn-outline" style="width:100%;justify-content:center;border-color:#2563eb;color:#2563eb;">สมัครเลย</a>
            </div>
            <div class="pricing-card animate-item">
                <h3>รับสั่งซื้อสินค้า</h3><p style="color:var(--gray-400);font-size:0.82rem;">บริการจัดซื้อจากทุกเว็บ</p>
                <div class="price">ฟรี</div><div class="price-unit">ค่าบริการจัดซื้อ</div>
                <ul class="features"><li><i class="fas fa-check"></i> สั่งซื้อจากทุกเว็บญี่ปุ่น</li><li><i class="fas fa-check"></i> ตรวจสอบสินค้าให้</li><li><i class="fas fa-check"></i> ถ่ายรูปยืนยัน</li><li><i class="fas fa-check"></i> รวมค่าขนส่งในราคา</li></ul>
                <a href="https://lin.ee/whDh44F" target="_blank" class="btn btn-outline" style="width:100%;justify-content:center;">สอบถาม LINE</a>
            </div>
        </div>
        <p style="text-align:center;margin-top:24px;color:var(--gray-400);font-size:0.82rem;">* ราคาอาจเปลี่ยนแปลงตามประเภทสินค้าและเงื่อนไข กรุณาสอบถามทีมงานเพื่อรับใบเสนอราคาที่แม่นยำ</p>
    </div>
</section>

<!-- SUPPORTED SITES -->
<section class="section" style="background:var(--gray-50);">
    <div class="section-inner">
        <div class="section-header"><div class="eyebrow">Partner Sites</div><h2>รองรับทุกเว็บชอปปิ้งญี่ปุ่น</h2><p>สั่งซื้อสินค้าได้จากทุกเว็บไซต์ยอดนิยมในญี่ปุ่น</p></div>
        <div class="shops-grid">
            <?php
            $shops = array(
                '2024/02/1-1.jpg','2024/02/2-1.jpg','2024/02/3-1.jpg','2024/02/4-1.jpg',
                '2024/02/5-1.jpg','2024/02/6-1.jpg','2024/02/7-1.jpg','2024/02/8.jpg',
                '2023/12/9.jpg','2023/12/10.jpg','2023/12/11.jpg','2023/12/12.jpg',
                '2023/12/13.jpg','2023/12/14.jpg','2023/12/15.jpg','2023/12/16.jpg',
                '2023/12/17.jpg','2023/12/18.jpg'
            );
            foreach ($shops as $s) : ?>
            <div class="shop-item animate-item"><img src="https://skjjapanshipping.com/wp-content/uploads/<?php echo $s; ?>" alt="Shop" loading="lazy" decoding="async" width="120" height="120"></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_template_part('template-parts/skj-footer'); ?>
<script>
window.addEventListener('scroll',function(){document.getElementById('header').classList.toggle('scrolled',window.scrollY>50);document.getElementById('scrollTop').classList.toggle('show',window.scrollY>400);},{passive:true});
var obs=new IntersectionObserver(function(e){e.forEach(function(e){if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});},{threshold:0.05,rootMargin:'0px 0px 200px 0px'});
document.querySelectorAll('.animate-item').forEach(function(el,i){el.style.transitionDelay=(i%6)*0.08+'s';obs.observe(el);});
setTimeout(function(){document.querySelectorAll('.animate-item:not(.visible)').forEach(function(el){el.classList.add('visible');});},150);
</script>
<?php wp_footer(); ?>
</body></html>
