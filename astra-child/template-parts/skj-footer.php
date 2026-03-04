<?php
/**
 * SKJ Japan - Shared Footer Template Part
 * Includes: CTA, footer, scroll-to-top, cookie banner
 */
$home_url = home_url('/');
$about_url = home_url('/about/');
$services_url = home_url('/services/');
$blog_url = home_url('/blog/');
$contact_url = home_url('/contact-us/');
$login_url = home_url('/skjtrack/login');
$register_url = home_url('/skjtrack/register');
$track_url = home_url('/skjtrack/tracking');
$logo_url = 'https://skjjapanshipping.com/wp-content/uploads/2026/02/skj-logo-white.png';
?>
<!-- CTA -->
<section class="cta-section">
    <h2>พร้อมเริ่มส่งสินค้าจากญี่ปุ่นกับเราแล้วหรือยัง?</h2>
    <p>สมัครสมาชิกวันนี้ เริ่มต้นใช้บริการขนส่งสินค้าจากญี่ปุ่นที่ดีที่สุด</p>
    <a href="<?php echo esc_url($register_url); ?>" class="btn btn-white btn-lg" style="position: relative;"><i class="fas fa-user-plus"></i> สมัครสมาชิกฟรี</a>
</section>

</main>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-inner">
        <div>
            <div class="footer-logo">
                <img src="<?php echo esc_url($logo_url); ?>" alt="SKJ Japan">
            </div>
            <p>SKJ JAPAN SHIPPING<br>"รวดเร็ว ปลอดภัย ใส่ใจคุณ"<br>บริการขนส่งสินค้าจากญี่ปุ่นมาไทย</p>
            <div class="social-links">
                <a href="https://www.facebook.com/SKJ.Japan" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://lin.ee/whDh44F" target="_blank"><i class="fab fa-line"></i></a>
                <a href="https://www.instagram.com/skj.japan" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@skj.japan" target="_blank"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        <div>
            <h4>เมนู</h4>
            <a href="<?php echo esc_url($home_url); ?>">หน้าแรก</a>
            <a href="<?php echo esc_url($about_url); ?>">เกี่ยวกับเรา</a>
            <a href="<?php echo esc_url($services_url); ?>">บริการของเรา</a>
            <a href="<?php echo esc_url($blog_url); ?>">บทความ</a>
            <a href="<?php echo esc_url($contact_url); ?>">ติดต่อเรา</a>
        </div>
        <div>
            <h4>บริการ</h4>
            <a href="<?php echo esc_url($home_url); ?>">ขนส่งทางเรือ</a>
            <a href="<?php echo esc_url($home_url); ?>">รับสั่งซื้อสินค้า</a>
            <a href="<?php echo esc_url($track_url); ?>">Track & Trace</a>
            <a href="<?php echo esc_url($home_url); ?>">คลังสินค้าญี่ปุ่น</a>
        </div>
        <div>
            <h4>ติดต่อเรา</h4>
            <p><i class="fas fa-phone" style="color: var(--red-light); margin-right: 8px;"></i> 082-460-9940</p>
            <p><i class="fab fa-line" style="color: var(--red-light); margin-right: 8px;"></i> @skj.japan</p>
            <p style="margin-top: 8px;"><i class="fas fa-clock" style="color: var(--red-light); margin-right: 8px;"></i> เปิดให้บริการทุกวัน 09:00-18:00</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date('Y'); ?> SKJ Japan Shipping Company. All rights reserved.
    </div>
</footer>

<!-- SCROLL TO TOP -->
<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- COOKIE CONSENT BANNER -->
<div id="skjCookieBanner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:99998;background:rgba(30,41,59,0.97);backdrop-filter:blur(10px);padding:16px 24px;font-size:0.82rem;color:#cbd5e1;flex-wrap:wrap;align-items:center;justify-content:center;gap:12px;border-top:1px solid rgba(255,255,255,0.1);text-align:center;">
    <p style="margin:0;line-height:1.6;width:100%;max-width:680px;">เว็บไซต์นี้มีการใช้คุกกี้เพื่อเพิ่มประสิทธิภาพและประสบการณ์ที่ดีในการใช้งาน <a href="#" style="color:#f87171;text-decoration:underline;">นโยบายความเป็นส่วนตัว</a> และ <a href="#" style="color:#f87171;text-decoration:underline;">นโยบายคุกกี้</a></p>
    <div style="display:flex;gap:10px;justify-content:center;">
        <button onclick="skjCookieAccept()" style="background:transparent;color:#cbd5e1;border:1px solid #64748b;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:0.82rem;font-family:inherit;font-weight:600;transition:all 0.2s;white-space:nowrap;" onmouseover="this.style.borderColor='#fff';this.style.color='#fff';" onmouseout="this.style.borderColor='#64748b';this.style.color='#cbd5e1';">ตั้งค่าคุกกี้</button>
        <button onclick="skjCookieAccept()" style="background:#C9301D;color:#fff;border:none;padding:8px 24px;border-radius:6px;cursor:pointer;font-size:0.82rem;font-family:inherit;font-weight:700;transition:all 0.2s;white-space:nowrap;" onmouseover="this.style.background='#e53e2e';" onmouseout="this.style.background='#C9301D';">ยอมรับทั้งหมด</button>
    </div>
</div>
<script>
(function(){
    if(!localStorage.getItem('skj_cookie_accepted')){
        document.getElementById('skjCookieBanner').style.display='flex';
    }
})();
function skjCookieAccept(){
    localStorage.setItem('skj_cookie_accepted','1');
    document.getElementById('skjCookieBanner').style.display='none';
}
</script>
