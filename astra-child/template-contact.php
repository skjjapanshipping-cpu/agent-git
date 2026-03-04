<?php
/**
 * Template Name: SKJ Contact
 * Description: Contact page for SKJ Japan
 */
get_template_part('template-parts/skj-head');
get_template_part('template-parts/skj-header');
?>
<main>
<div class="header-spacer"></div>
<section class="page-hero">
    <div class="eyebrow">Contact Us</div>
    <h1>ติดต่อเรา</h1>
    <p>มีคำถาม? ต้องการสอบถามข้อมูลเพิ่มเติม? ติดต่อทีมงาน SKJ JAPAN ได้ทุกช่องทาง</p>
    <div class="breadcrumb"><a href="<?php echo home_url('/'); ?>">หน้าแรก</a><span>/</span><span style="color:rgba(255,255,255,0.8);">ติดต่อเรา</span></div>
</section>

<!-- CONTACT CARDS -->
<section class="section" style="padding-bottom:0;">
<div class="section-inner">
<div class="contact-cards">
    <div class="contact-card animate-item">
        <div class="card-icon"><i class="fab fa-line"></i></div>
        <h3>LINE Official</h3>
        <p>แชทกับเราผ่าน LINE</p>
        <a href="https://lin.ee/whDh44F" target="_blank">@skj.japan</a>
    </div>
    <div class="contact-card animate-item">
        <div class="card-icon"><i class="fas fa-envelope"></i></div>
        <h3>อีเมล</h3>
        <p>ส่งอีเมลถึงเรา</p>
        <a href="mailto:skj.japanshipping@gmail.com">skj.japanshipping@gmail.com</a>
    </div>
    <div class="contact-card animate-item">
        <div class="card-icon"><i class="fas fa-phone"></i></div>
        <h3>โทรศัพท์</h3>
        <p>โทรหาเราได้เลย</p>
        <a href="tel:+66-82-460-9940">082-460-9940</a>
    </div>
    <div class="contact-card animate-item">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <h3>เวลาทำการ</h3>
        <p>เปิดให้บริการทุกวัน</p>
        <span style="font-weight:600;color:var(--gray-900);">09:00 - 18:00</span>
    </div>
</div>
</div>
</section>

<!-- CONTACT FORM + SIDEBAR -->
<section class="section">
    <div class="section-inner">
        <div class="contact-grid">
            <div class="contact-form">
                <h2>ส่งข้อความถึงเรา</h2>
                <p class="form-subtitle">กรอกแบบฟอร์มด้านล่าง ทีมงานจะติดต่อกลับโดยเร็ว</p>
                <div id="form-msg" style="display:none;border-radius:12px;padding:20px;margin-bottom:24px;text-align:center;"></div>
                <form id="skjContactForm" onsubmit="return skjSubmitContact(event)">
                    <input type="hidden" name="action" value="skj_contact">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('skj_contact_form'); ?>">
                    <div class="form-row">
                        <div class="form-group"><label>ชื่อ - นามสกุล <span class="required">*</span></label><input type="text" name="name" required placeholder="กรอกชื่อ-นามสกุล"></div>
                        <div class="form-group"><label>อีเมล <span class="required">*</span></label><input type="email" name="email" required placeholder="example@email.com"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>เบอร์โทรศัพท์</label><input type="tel" name="phone" placeholder="0xx-xxx-xxxx"></div>
                        <div class="form-group"><label>LINE ID</label><input type="text" name="line_id" placeholder="@line_id"></div>
                    </div>
                    <div class="form-group"><label>หัวข้อ <span class="required">*</span></label>
                        <select name="subject" required>
                            <option value="">-- เลือกหัวข้อ --</option>
                            <option>สอบถามบริการขนส่ง</option>
                            <option>สอบถามบริการสั่งซื้อสินค้า</option>
                            <option>แจ้งปัญหาการใช้งาน</option>
                            <option>เสนอแนะ / ร้องเรียน</option>
                            <option>อื่นๆ</option>
                        </select>
                    </div>
                    <div class="form-group"><label>ข้อความ <span class="required">*</span></label><textarea name="message" required placeholder="พิมพ์ข้อความของคุณที่นี่..."></textarea></div>
                    <button type="submit" class="form-submit" id="submitBtn"><i class="fas fa-paper-plane"></i> ส่งข้อความ</button>
                </form>
            </div>

            <div class="contact-sidebar">
                <div class="sidebar-card">
                    <h3><i class="fas fa-share-nodes"></i> ช่องทางโซเชียลมีเดีย</h3>
                    <div class="social-grid">
                        <a href="https://www.facebook.com/SKJ.Japan" target="_blank" class="social-item facebook"><i class="fab fa-facebook-f"></i> Facebook</a>
                        <a href="https://lin.ee/whDh44F" target="_blank" class="social-item line"><i class="fab fa-line"></i> LINE</a>
                        <a href="https://www.instagram.com/skj.japan" target="_blank" class="social-item instagram"><i class="fab fa-instagram"></i> Instagram</a>
                        <a href="https://www.tiktok.com/@skj.japan" target="_blank" class="social-item tiktok"><i class="fab fa-tiktok"></i> TikTok</a>
                        <a href="https://www.youtube.com/@SKJJAPANShipping" target="_blank" class="social-item youtube"><i class="fab fa-youtube"></i> YouTube</a>
                    </div>
                </div>
                <div class="sidebar-card">
                    <h3><i class="fas fa-warehouse"></i> ที่อยู่คลังสินค้า (ญี่ปุ่น)</h3>
                    <p>สมัครสมาชิกเพื่อรับที่อยู่คลังสินค้าในญี่ปุ่น สำหรับจัดส่งสินค้าเข้าคลัง</p>
                    <a href="<?php echo home_url('/skjtrack/register'); ?>" class="btn btn-red" style="margin-top:12px;width:100%;justify-content:center;"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
                </div>
                <div class="sidebar-card">
                    <h3><i class="fas fa-question-circle"></i> ต้องการความช่วยเหลือ?</h3>
                    <p>ทีมงานพร้อมให้บริการทุกวัน 09:00-18:00 ติดต่อผ่าน LINE ได้เลยครับ!</p>
                    <a href="https://lin.ee/whDh44F" target="_blank" class="btn btn-outline" style="margin-top:12px;width:100%;justify-content:center;"><i class="fab fa-line"></i> แชทผ่าน LINE</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section" style="background:var(--gray-50);">
    <div class="section-inner">
        <div class="section-header"><div class="eyebrow">FAQ</div><h2>คำถามที่พบบ่อย</h2></div>
        <div class="faq-list">
            <div class="faq-item open">
                <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">SKJ Japan Shipping เชื่อถือได้ไหม? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-a">SKJ Japan Shipping เป็นบริษัทที่ให้บริการขนส่งสินค้าจากญี่ปุ่นมาไทยมากกว่า 5 ปี มีลูกค้ามากกว่า 1,000 ราย และจัดส่งพัสดุกว่า 200,000 ชิ้น</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">การจัดส่งสินค้าใช้เวลานานเท่าไหร่? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-a">สินค้าจะถึงไทยภายใน 14-25 วัน หลังจากปิดตู้ โดยเราปิดตู้ทุกสัปดาห์</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">ค่าบริการเท่าไหร่? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-a">ค่าขนส่งทางเรือเริ่มต้น กก. ละ 150 บาท สินค้าขนาดใหญ่คิดตาม CBM ที่ 10,000 บาท/คิว สอบถามรายละเอียดเพิ่มเติมได้ทาง LINE</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">มีบริการรับสั่งซื้อสินค้าให้ไหม? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-a">มีครับ! เรามีทีมงานที่ญี่ปุ่นคอยช่วยจัดซื้อสินค้าจากทุกเว็บไซต์ เพียงส่งลิงก์สินค้าที่ต้องการให้ทีมงาน</div>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('template-parts/skj-footer'); ?>
<script>
window.addEventListener('scroll',function(){document.getElementById('header').classList.toggle('scrolled',window.scrollY>50);document.getElementById('scrollTop').classList.toggle('show',window.scrollY>400);},{passive:true});
var obs=new IntersectionObserver(function(e){e.forEach(function(e){if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});},{threshold:0.05,rootMargin:'0px 0px 200px 0px'});
document.querySelectorAll('.animate-item').forEach(function(el,i){el.style.transitionDelay=(i%6)*0.08+'s';obs.observe(el);});
setTimeout(function(){document.querySelectorAll('.animate-item:not(.visible)').forEach(function(el){el.classList.add('visible');});},150);

function skjSubmitContact(e){
    e.preventDefault();
    var form=document.getElementById('skjContactForm');
    var btn=document.getElementById('submitBtn');
    var msg=document.getElementById('form-msg');
    btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';
    var fd=new FormData(form);
    fetch('<?php echo admin_url("admin-ajax.php"); ?>',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(res){
        msg.style.display='block';
        if(res.success){
            msg.style.background='#f0fdf4';msg.style.border='1px solid #bbf7d0';msg.style.color='#15803d';
            msg.innerHTML='<i class="fas fa-check-circle" style="font-size:2rem;display:block;margin-bottom:8px;color:#22c55e;"></i><strong>ส่งข้อความสำเร็จ!</strong><br>ทีมงานจะติดต่อกลับโดยเร็วที่สุด ขอบคุณครับ';
            form.reset();
        } else {
            msg.style.background='#fef2f2';msg.style.border='1px solid #fecaca';msg.style.color='#dc2626';
            msg.innerHTML='<i class="fas fa-exclamation-circle"></i> '+(res.data||'เกิดข้อผิดพลาด กรุณาลองใหม่');
        }
        btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> ส่งข้อความ';
        msg.scrollIntoView({behavior:'smooth',block:'center'});
    })
    .catch(function(){
        msg.style.display='block';msg.style.background='#fef2f2';msg.style.border='1px solid #fecaca';msg.style.color='#dc2626';
        msg.innerHTML='<i class="fas fa-exclamation-circle"></i> เกิดข้อผิดพลาด กรุณาติดต่อผ่าน LINE';
        btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> ส่งข้อความ';
    });
    return false;
}
</script>
<?php wp_footer(); ?>
</body></html>
