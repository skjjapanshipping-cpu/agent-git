<?php
/**
 * Template Name: SKJ Homepage
 * Description: Custom homepage for SKJ Japan
 */
get_template_part('template-parts/skj-head');
get_template_part('template-parts/skj-header');
?>

<main>
<div class="header-spacer"></div>

<!-- HERO SLIDER -->
<section class="hero-slider" id="heroSlider">
    <div class="slider-track" id="sliderTrack">
        <div class="slider-slide">
            <img src="https://skjjapanshipping.com/wp-content/uploads/2025/03/1.webp" alt="SKJ Japan Shipping - บริการขนส่งสินค้าจากญี่ปุ่นมาไทย" fetchpriority="high" decoding="async" width="1200" height="500">
        </div>
        <div class="slider-slide">
            <img src="https://skjjapanshipping.com/wp-content/uploads/2025/03/2.webp" alt="Fastest Express - ขนส่ง ทันใจ ปิดตู้สินค้าทุกสัปดาห์" loading="lazy" decoding="async" width="1200" height="500">
        </div>
        <div class="slider-slide">
            <img src="https://skjjapanshipping.com/wp-content/uploads/2025/03/3.webp" alt="SKJ Japan - บริการด้วยใจ พร้อมส่งมอบพัสดุอย่างปลอดภัย" loading="lazy" decoding="async" width="1200" height="500">
        </div>
    </div>
    <button class="slider-btn slider-prev" onclick="slideMove(-1)" style="width:44px;height:44px;min-width:44px;min-height:44px;max-width:44px;max-height:44px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);color:#fff;border:1px solid rgba(255,255,255,0.2);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;position:absolute;top:50%;transform:translateY(-50%);left:16px;z-index:2;"><i class="fas fa-chevron-left"></i></button>
    <button class="slider-btn slider-next" onclick="slideMove(1)" style="width:44px;height:44px;min-width:44px;min-height:44px;max-width:44px;max-height:44px;border-radius:50%;background:rgba(255,255,255,0.15);backdrop-filter:blur(8px);color:#fff;border:1px solid rgba(255,255,255,0.2);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;position:absolute;top:50%;transform:translateY(-50%);right:16px;z-index:2;"><i class="fas fa-chevron-right"></i></button>
    <div class="slider-dots">
        <div class="slider-dot active" onclick="slideTo(0)"></div>
        <div class="slider-dot" onclick="slideTo(1)"></div>
        <div class="slider-dot" onclick="slideTo(2)"></div>
    </div>
</section>

<!-- SERVICES -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <div class="eyebrow">Our Services</div>
            <h2>บริการของ SKJ JAPAN</h2>
            <p>ครบวงจรทุกความต้องการนำเข้าสินค้าจากญี่ปุ่น</p>
        </div>
        <div class="services-grid">
            <div class="service-card animate-item">
                <div class="service-icon"><i class="fas fa-ship"></i></div>
                <h3>ขนส่งทางเรือ</h3>
                <p>ส่งทางเรือจากญี่ปุ่นมาไทย ราคาประหยัด</p>
            </div>
            <div class="service-card animate-item">
                <div class="service-icon"><i class="fas fa-plane"></i></div>
                <h3>ขนส่งทางเครื่องบิน</h3>
                <p>ส่งทางอากาศ ถึงไทยเร็ว 3-7 วัน</p>
            </div>
            <div class="service-card animate-item">
                <div class="service-icon"><i class="fas fa-cart-shopping"></i></div>
                <h3>รับสั่งซื้อสินค้า</h3>
                <p>สั่งซื้อจากทุกเว็บญี่ปุ่น Yahoo, Mercari ฯลฯ</p>
            </div>
            <div class="service-card animate-item">
                <div class="service-icon"><i class="fas fa-warehouse"></i></div>
                <h3>คลังสินค้าญี่ปุ่น</h3>
                <p>เก็บรวบรวมสินค้าที่ญี่ปุ่น ประหยัดค่าขนส่ง</p>
            </div>
            <div class="service-card animate-item">
                <div class="service-icon"><i class="fas fa-location-dot"></i></div>
                <h3>เช็คสถานะ Real-time</h3>
                <p>ติดตามสถานะพัสดุเรียลไทม์ผ่าน Track & Trace</p>
            </div>
            <div class="service-card animate-item">
                <div class="service-icon"><i class="fas fa-bell"></i></div>
                <h3>LINE แจ้งเตือน</h3>
                <p>รับแจ้งเตือนอัตโนมัติผ่าน LINE เมื่อสินค้าเข้าระบบ</p>
            </div>
            <div class="service-card animate-item">
                <div class="service-icon"><i class="fas fa-box-open"></i></div>
                <h3>ฝากส่งพัสดุ</h3>
                <p>ส่งสินค้าเข้าคลังเรา เราจัดส่งกลับไทยให้</p>
            </div>
        </div>
    </div>
</section>

<!-- HIGHLIGHT STATS -->
<section class="highlight-section">
    <div class="highlight-inner">
        <h2>Fastest Express ส่งไว ทันใจ ทุกสัปดาห์</h2>
        <p>ด้วยความเร็วเพียง 14-25 วัน หลังปิดตู้ พร้อมเช็คสถานะ Real-time</p>
        <div class="highlight-stats">
            <div class="h-stat"><div class="num">14-25</div><div class="label">วันถึงไทย</div></div>
            <div class="h-stat"><div class="num">5+</div><div class="label">ปีประสบการณ์</div></div>
            <div class="h-stat"><div class="num">1,000+</div><div class="label">ลูกค้าไว้วางใจ</div></div>
            <div class="h-stat"><div class="num">200,000+</div><div class="label">พัสดุที่จัดส่งสำเร็จ</div></div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section steps-section">
    <div class="section-inner">
        <div class="section-header">
            <div class="eyebrow">How It Works</div>
            <h2>วิธีสั่งซื้อสินค้าจากญี่ปุ่น</h2>
            <p>ขั้นตอนง่ายๆ เพียง 4 ขั้นตอน</p>
        </div>
        <div class="steps-grid">
            <div class="step-card animate-item"><div class="step-number">1</div><h3>สมัครสมาชิก</h3><p>ลงทะเบียนเข้าสู่ระบบ SKJ Japan</p></div>
            <div class="step-card animate-item"><div class="step-number">2</div><h3>แจ้งสั่งซื้อสินค้า</h3><p>ส่งลิงก์สินค้าให้ทีมงานจัดซื้อ</p></div>
            <div class="step-card animate-item"><div class="step-number">3</div><h3>สินค้าเข้าคลัง</h3><p>เข้าคลังญี่ปุ่น เลือกส่งทางเรือหรือเครื่องบิน</p></div>
            <div class="step-card animate-item"><div class="step-number">4</div><h3>ได้รับสินค้า</h3><p>สินค้าถึงไทย ส่งถึงบ้านคุณ!</p></div>
        </div>
    </div>
</section>

<!-- PRICE CALCULATOR -->
<section class="section" id="calculator" style="background: var(--gray-50, #f8fafc);">
    <div class="section-inner">
        <div class="section-header">
            <div class="eyebrow">Price Calculator</div>
            <h2>คำนวณค่าสินค้าและค่าขนส่ง</h2>
            <p>ประมาณค่าใช้จ่ายทั้งหมดก่อนสั่งซื้อ วาง URL สินค้า หรือกรอกราคาเอง</p>
        </div>

        <div class="calc-wrapper animate-item">
            <!-- URL Fetch -->
            <div class="calc-card">
                <div class="calc-card-head calc-red"><i class="fas fa-link"></i> วาง URL สินค้า — ดึงราคาอัตโนมัติ</div>
                <div class="calc-card-body">
                    <div class="calc-url-row">
                        <input type="url" id="calcUrl" class="calc-input calc-url-input" placeholder="วาง URL จาก Yahoo Auctions, Mercari, Rakuten, Amazon JP...">
                        <button class="calc-btn-fetch" id="calcBtnFetch" onclick="calcFetch()">
                            <span class="calc-spinner"></span>
                            <span class="calc-btn-text"><i class="fas fa-search"></i> ดึงราคา</span>
                        </button>
                    </div>
                    <div class="calc-sites">
                        <span>รองรับ:</span>
                        <span class="calc-site-tag"><i class="fas fa-gavel"></i> Yahoo Auctions</span>
                        <span class="calc-site-tag"><i class="fas fa-store"></i> Mercari</span>
                        <span class="calc-site-tag"><i class="fas fa-shopping-bag"></i> Rakuten</span>
                        <span class="calc-site-tag"><i class="fas fa-box"></i> Amazon JP</span>
                    </div>
                    <div class="calc-error" id="calcError"></div>
                    <div class="calc-preview" id="calcPreview">
                        <img id="calcPreviewImg" src="" alt="">
                        <div class="calc-preview-info">
                            <span class="calc-badge" id="calcPreviewSite"></span>
                            <h4 id="calcPreviewTitle"></h4>
                            <div class="calc-preview-prices">
                                <div><small>ราคาสินค้า</small><strong id="calcPreviewPrice" class="calc-price-red"></strong></div>
                                <div><small>ค่าส่งในญี่ปุ่น</small><strong id="calcPreviewShip"></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Fields -->
            <div class="calc-card">
                <div class="calc-card-head calc-blue"><i class="fas fa-sliders-h"></i> กรอกรายละเอียด</div>
                <div class="calc-card-body">
                    <div class="calc-grid">
                        <div class="calc-field" style="grid-column: 1 / -1;">
                            <label>ประเภทขนส่ง</label>
                            <select id="calcMethod" class="calc-input" onchange="calcMethodChange()">
                                <option value="sea">🚢 ทางเรือ (150 ฿/กก. | 15-25 วัน)</option>
                                <option value="air">✈️ ทางเครื่องบิน (339 ฿/กก. | 3-7 วัน)</option>
                            </select>
                        </div>
                        <div class="calc-field">
                            <label>ราคาสินค้า (เยน)</label>
                            <input type="number" id="calcPrice" class="calc-input" value="0" min="0" oninput="calcRun()">
                        </div>
                        <div class="calc-field">
                            <label>ค่าส่งในญี่ปุ่น (เยน)</label>
                            <input type="number" id="calcShipJP" class="calc-input" value="0" min="0" oninput="calcRun()">
                        </div>
                        <div class="calc-field">
                            <label>เรทเยน (บาท/เยน)</label>
                            <input type="number" id="calcRate" class="calc-input" value="0.235" min="0" step="0.001" oninput="calcRun()">
                        </div>
                        <div class="calc-field">
                            <label>น้ำหนัก (กก.)</label>
                            <input type="number" id="calcWeight" class="calc-input" value="1" min="0.1" step="0.1" oninput="calcRun()">
                        </div>
                        <div class="calc-field">
                            <label id="calcImportLabel">ค่านำเข้า (บาท/กก.)</label>
                            <input type="number" id="calcImport" class="calc-input" value="150" min="0" oninput="calcRun()">
                        </div>
                        <div class="calc-field">
                            <label>ค่าส่งในไทย (บาท)</label>
                            <input type="number" id="calcThaiShip" class="calc-input" value="25" min="0" oninput="calcRun()">
                        </div>
                    </div>
                    <button class="calc-btn-run" onclick="calcRun()"><i class="fas fa-calculator"></i> คำนวณ</button>
                </div>
            </div>

            <!-- Results -->
            <div class="calc-card">
                <div class="calc-card-head calc-green"><i class="fas fa-receipt"></i> สรุปค่าใช้จ่ายโดยประมาณ</div>
                <div class="calc-card-body">
                    <div class="calc-section-label" style="color:var(--skj-red,#C9301D);"><i class="fas fa-yen-sign"></i> ฝั่งญี่ปุ่น</div>
                    <div class="calc-row"><span>ราคาสินค้า</span><strong><span id="cr_price">0</span> เยน</strong></div>
                    <div class="calc-row"><span>ค่าส่งในญี่ปุ่น (มาโกดัง SKJ)</span><strong><span id="cr_shipjp">0</span> เยน</strong></div>
                    <div class="calc-row calc-row-highlight"><span>รวมเยน</span><strong style="color:var(--skj-red,#C9301D);"><span id="cr_totalyen">0</span> เยน</strong></div>
                    <div class="calc-row"><span>คิดเป็นเงินบาท <small>(เรท <span id="cr_rate">0.235</span>)</small></span><strong><span id="cr_totalbaht">0</span> บาท</strong></div>

                    <div class="calc-divider"></div>
                    <div class="calc-section-label" style="color:var(--skj-red-dark,#2b6cb0);"><i class="fas fa-truck"></i> ฝั่งไทย</div>
                    <div class="calc-row"><span>ค่านำเข้า ญี่ปุ่น→ไทย <small>(<span id="cr_imprate">150</span> × <span id="cr_wt">1</span> กก.)</small></span><strong><span id="cr_impcost">150.00</span> บาท</strong></div>
                    <div class="calc-row"><span>ค่าส่งพัสดุในไทย</span><strong><span id="cr_thship">25</span> บาท</strong></div>

                    <div class="calc-divider"></div>
                    <div class="calc-total-box">
                        <div>
                            <div class="calc-total-label"><i class="fas fa-check-circle"></i> รวมทั้งหมดโดยประมาณ</div>
                        </div>
                        <div class="calc-total-value" id="cr_grand">0 บาท</div>
                    </div>
                    <div class="calc-note"><i class="fas fa-info-circle"></i> <strong>หมายเหตุ:</strong> เป็นการคำนวณเพื่อประเมินค่าใช้จ่ายเบื้องต้นเท่านั้น</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SHOPPING SITES -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <div class="eyebrow">Partner Sites</div>
            <h2>รวมเว็บไซต์ชอปปิ้งญี่ปุ่น</h2>
            <p>สั่งซื้อได้จากทุกเว็บไซต์ยอดนิยมในญี่ปุ่น</p>
        </div>
        <div class="shops-grid">
            <?php
            $shops = array(
                '2024/02/1-1.jpg','2024/02/2-1.jpg','2024/02/3-1.jpg','2024/02/4-1.jpg',
                '2024/02/5-1.jpg','2024/02/6-1.jpg','2024/02/7-1.jpg','2024/02/8.jpg',
                '2023/12/9.jpg','2023/12/10.jpg','2023/12/11.jpg','2023/12/12.jpg',
                '2023/12/13.jpg','2023/12/14.jpg','2023/12/15.jpg','2023/12/16.jpg',
                '2023/12/17.jpg','2023/12/18.jpg'
            );
            foreach ($shops as $shop) :
            ?>
            <div class="shop-item animate-item"><img src="https://skjjapanshipping.com/wp-content/uploads/<?php echo $shop; ?>" alt="Shop" loading="lazy" decoding="async" width="120" height="120"></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section" style="background: var(--gray-50);">
    <div class="section-inner">
        <div class="section-header">
            <div class="eyebrow">Testimonials</div>
            <h2>ส่งต่อความประทับใจจากลูกค้า</h2>
        </div>
        <div class="testimonials-wrap">
            <div class="testimonials-scroll">
                <?php for ($loop = 0; $loop < 2; $loop++) : for ($i = 11; $i >= 1; $i--) : ?>
                <img class="testimonial-img" src="https://skjjapanshipping.com/wp-content/uploads/2021/02/11_<?php echo $i; ?>.png" alt="รีวิวจากลูกค้า" loading="lazy" decoding="async">
                <?php endfor; endfor; ?>
            </div>
        </div>
    </div>
</section>

<!-- BLOG -->
<section class="section">
    <div class="section-inner">
        <div class="section-header">
            <div class="eyebrow">Blog</div>
            <h2>บทความล่าสุด</h2>
            <p>เรื่องน่ารู้เกี่ยวกับการสั่งซื้อสินค้าจากญี่ปุ่น</p>
        </div>
        <div class="blog-grid">
            <?php
            $blog_posts = new WP_Query(array(
                'posts_per_page' => 3,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            if ($blog_posts->have_posts()) : while ($blog_posts->have_posts()) : $blog_posts->the_post();
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                if (!$thumb) $thumb = 'https://skjjapanshipping.com/wp-content/uploads/2023/12/1-7.png';
            ?>
            <a href="<?php the_permalink(); ?>" class="blog-card animate-item" style="text-decoration:none;color:inherit;">
                <img class="thumb" src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async" width="400" height="250">
                <div class="content">
                    <span class="tag">บทความ</span>
                    <h3><?php the_title(); ?></h3>
                    <div class="date"><i class="far fa-calendar"></i> <?php echo get_the_date('j M Y'); ?></div>
                </div>
            </a>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section" style="background: var(--gray-50);">
    <div class="section-inner">
        <div class="section-header">
            <div class="eyebrow">FAQ</div>
            <h2>คำถามที่พบบ่อย</h2>
        </div>
        <div class="faq-list">
            <div class="faq-item open">
                <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">SKJ Japan Shipping เชื่อถือได้ไหม? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-a">SKJ Japan Shipping เป็นบริษัทที่ให้บริการขนส่งสินค้าจากญี่ปุ่นมาไทยมากกว่า 5 ปี มีลูกค้ามากกว่า 1,000 ราย และจัดส่งพัสดุกว่า 200,000 ชิ้น</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">การจัดส่งสินค้าใช้เวลานานเท่าไหร่? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-a">สินค้าจะถึงไทยภายใน 14-25 วัน หลังจากปิดตู้ โดยเราปิดตู้ทุกสัปดาห์ ลูกค้าสามารถเช็คสถานะการจัดส่งได้ตลอดเวลาผ่านระบบ Track & Trace</div>
            </div>
            <div class="faq-item">
                <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">สินค้าต้องห้ามมีอะไรบ้าง? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-a">สินค้าต้องห้ามได้แก่ วัตถุไวไฟ วัตถุระเบิด สารเคมีอันตราย ยาเสพติด อาวุธ สิ่งผิดกฎหมาย และสินค้าละเมิดลิขสิทธิ์</div>
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
    // Hero Slider
    var currentSlide = 0, totalSlides = 3, track = document.getElementById('sliderTrack'), dots = document.querySelectorAll('.slider-dot');
    function slideTo(n) { currentSlide = n; track.style.transform = 'translateX(-' + (currentSlide * 100) + '%)'; dots.forEach(function(d, i) { d.classList.toggle('active', i === currentSlide); }); }
    function slideMove(dir) { slideTo((currentSlide + dir + totalSlides) % totalSlides); }
    var sliderPaused = false;
    var heroSlider = document.getElementById('heroSlider');
    setInterval(function() { if (!sliderPaused) slideMove(1); }, 5000);
    heroSlider.addEventListener('mouseenter', function() { sliderPaused = true; });
    heroSlider.addEventListener('mouseleave', function() { sliderPaused = false; });

    // Sticky header + scroll top
    window.addEventListener('scroll', function() {
        document.getElementById('header').classList.toggle('scrolled', window.scrollY > 50);
        document.getElementById('scrollTop').classList.toggle('show', window.scrollY > 400);
    }, { passive: true });

    // Scroll animation
    var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
    }, { threshold: 0.05, rootMargin: '0px 0px 200px 0px' });
    document.querySelectorAll('.animate-item').forEach(function(el, i) { el.style.transitionDelay = (i % 6) * 0.08 + 's'; obs.observe(el); });
    setTimeout(function(){ document.querySelectorAll('.animate-item:not(.visible)').forEach(function(el){ el.classList.add('visible'); }); }, 150);

    // ===== PRICE CALCULATOR =====
    var CALC_API = '/calc.php?action=scrape';

    function calcFetch() {
        var url = document.getElementById('calcUrl').value.trim();
        if (!url) { calcShowError('กรุณาวาง URL สินค้า'); return; }
        var btn = document.getElementById('calcBtnFetch');
        btn.classList.add('loading');
        btn.disabled = true;
        calcHideError();
        document.getElementById('calcPreview').classList.remove('show');

        fetch(CALC_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ url: url })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.classList.remove('loading'); btn.disabled = false;
            if (d.success) {
                document.getElementById('calcPreviewSite').textContent = d.site;
                document.getElementById('calcPreviewTitle').textContent = d.title || 'ไม่พบชื่อสินค้า';
                document.getElementById('calcPreviewPrice').textContent = cNf(d.price) + ' เยน';
                document.getElementById('calcPreviewShip').textContent = d.shipping > 0 ? cNf(d.shipping) + ' เยน' : (d.shipping_text || 'ไม่ระบุ');
                var img = document.getElementById('calcPreviewImg');
                if (d.image) { img.src = d.image; img.style.display = 'block'; } else { img.style.display = 'none'; }
                document.getElementById('calcPreview').classList.add('show');
                document.getElementById('calcPrice').value = d.price;
                document.getElementById('calcShipJP').value = d.shipping || 0;
                if (d.shipping_note) {
                    var el = document.getElementById('calcError');
                    el.innerHTML = '<i class="fas fa-info-circle"></i> ' + d.shipping_note;
                    el.classList.add('show');
                    el.style.background = '#fffbeb';
                    el.style.borderColor = '#f6e05e';
                    el.style.color = '#744210';
                }
                calcRun();
            } else {
                if (d.shipping_note) {
                    var el = document.getElementById('calcError');
                    el.innerHTML = '<i class="fas fa-info-circle"></i> ' + d.shipping_note;
                    el.classList.add('show');
                    el.style.background = '#fffbeb';
                    el.style.borderColor = '#f6e05e';
                    el.style.color = '#744210';
                } else {
                    calcShowError(d.error || 'ไม่สามารถดึงข้อมูลราคาได้ คุณสามารถกรอกราคาด้วยตนเองได้');
                }
            }
        })
        .catch(function(e) {
            btn.classList.remove('loading'); btn.disabled = false;
            calcShowError('เกิดข้อผิดพลาด: ' + e.message);
        });
    }

    function calcMethodChange() {
        var method = document.getElementById('calcMethod').value;
        var importEl = document.getElementById('calcImport');
        if (method === 'air') {
            importEl.value = 339;
        } else {
            importEl.value = 150;
        }
        calcRun();
    }

    function calcRun() {
        var p = parseFloat(document.getElementById('calcPrice').value) || 0;
        var s = parseFloat(document.getElementById('calcShipJP').value) || 0;
        var r = parseFloat(document.getElementById('calcRate').value) || 0.235;
        var w = parseFloat(document.getElementById('calcWeight').value) || 1;
        var ir = parseFloat(document.getElementById('calcImport').value) || 150;
        var ts = parseFloat(document.getElementById('calcThaiShip').value) || 25;
        var tYen = p + s, tBaht = tYen * r, impC = ir * w, grand = tBaht + impC + ts;
        document.getElementById('cr_price').textContent = cNf(p);
        document.getElementById('cr_shipjp').textContent = cNf(s);
        document.getElementById('cr_totalyen').textContent = cNf(tYen);
        document.getElementById('cr_rate').textContent = r;
        document.getElementById('cr_totalbaht').textContent = cNf2(tBaht);
        document.getElementById('cr_imprate').textContent = cNf(ir);
        document.getElementById('cr_wt').textContent = w;
        document.getElementById('cr_impcost').textContent = cNf2(impC);
        document.getElementById('cr_thship').textContent = cNf(ts);
        document.getElementById('cr_grand').textContent = cNf2(grand) + ' บาท';
    }

    function cNf(n) { return Math.round(n).toLocaleString('en-US'); }
    function cNf2(n) { return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function calcShowError(m) { var e = document.getElementById('calcError'); e.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + m; e.style.background='#fed7d7'; e.style.borderColor='#fc8181'; e.style.color='#9b2c2c'; e.classList.add('show'); }
    function calcHideError() { var e = document.getElementById('calcError'); e.classList.remove('show'); e.style.background=''; e.style.borderColor=''; e.style.color=''; }

    document.getElementById('calcUrl').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); calcFetch(); } });
    calcRun();
</script>
<?php wp_footer(); ?>
</body>
</html>
