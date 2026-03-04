<?php
/**
 * SKJ Japan - Shared Header Template Part
 * Includes: topbar, header, mobile nav
 */
$current_page = basename(get_page_template_slug());
$logo_url = 'https://skjjapanshipping.com/wp-content/uploads/2026/02/skj-logo-white.png';
$home_url = home_url('/');
$track_url = home_url('/skjtrack/tracking');
$login_url = home_url('/skjtrack/login');
$register_url = home_url('/skjtrack/register');

// Page URLs - use home_url for reliability
$about_url = home_url('/about/');
$services_url = home_url('/services/');
$blog_url = home_url('/blog/');
$contact_url = home_url('/contact-us/');

// Active page detection
$is_home = is_front_page();
$is_about = (strpos($current_page, 'about') !== false);
$is_services = (strpos($current_page, 'services') !== false);
$is_blog = (strpos($current_page, 'blog') !== false) || is_home() && get_option('show_on_front') == 'posts' || is_archive() || is_single();
$is_contact = (strpos($current_page, 'contact') !== false);
?>
<!-- TOP BAR -->
<div class="skj-topbar" id="skjTopbar" style="display:flex !important;position:fixed !important;top:0;left:0;right:0;z-index:99999;background:#C9301D;color:#fff;height:38px;align-items:center;justify-content:space-between;padding:0 24px;font-size:0.8rem;font-weight:500;width:100%;visibility:visible;opacity:1;">
    <div class="skj-topbar-left" style="display:flex;align-items:center;gap:6px;">
        <span>เปิดให้บริการทุกวัน ปิดตู้ทุกสัปดาห์</span>
        <a href="<?php echo esc_url($track_url); ?>" class="track-btn" style="background:#fff !important;color:#C9301D !important;padding:3px 14px;border-radius:20px;font-weight:700;font-size:0.75rem;display:inline-flex;align-items:center;gap:5px;margin-left:10px;text-decoration:none;white-space:nowrap;"><i class="fas fa-search" style="color:#C9301D;"></i> Track &amp; Trace</a>
    </div>
    <div class="skj-topbar-right" style="display:flex;align-items:center;gap:12px;">
        <a href="https://www.facebook.com/SKJ.Japan" target="_blank" style="color:#fff;opacity:0.8;"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/skj.japan" target="_blank" style="color:#fff;opacity:0.8;"><i class="fab fa-instagram"></i></a>
        <a href="https://lin.ee/whDh44F" target="_blank" style="color:#fff;opacity:0.8;"><i class="fab fa-line"></i></a>
        <a href="https://www.tiktok.com/@skj.japan" target="_blank" style="color:#fff;opacity:0.8;"><i class="fab fa-tiktok"></i></a>
        <a href="https://www.youtube.com/@SKJJAPANShipping" target="_blank" style="color:#fff;opacity:0.8;"><i class="fab fa-youtube"></i></a>
        <span style="opacity: 0.5;color:#fff;">|</span>
        <a href="#" style="font-size: 0.78rem; font-weight: 700; color:#fff;">TH</a>
    </div>
</div>

<style>
.nav a{border-style:solid!important;border-color:transparent!important;border-width:0 0 2px 0!important;border-radius:6px!important;box-shadow:none!important;-webkit-box-shadow:none!important}
.nav a:hover,.nav a.active{border-color:transparent transparent #C9301D transparent!important}
</style>
<!-- HEADER -->
<header class="header" id="header">
    <div class="header-inner">
        <a href="<?php echo esc_url($home_url); ?>" class="logo">
            <img src="<?php echo esc_url($logo_url); ?>" alt="SKJ Japan">
        </a>
        <div class="nav-wrap">
            <nav class="nav">
                <a href="<?php echo esc_url($home_url); ?>" <?php if($is_home) echo 'class="active"'; ?>>หน้าแรก</a>
                <a href="<?php echo esc_url($about_url); ?>" <?php if($is_about) echo 'class="active"'; ?>>เกี่ยวกับเรา</a>
                <a href="<?php echo esc_url($services_url); ?>" <?php if($is_services) echo 'class="active"'; ?>>บริการของเรา</a>
                <a href="<?php echo esc_url($blog_url); ?>" <?php if($is_blog) echo 'class="active"'; ?>>บทความ</a>
                <a href="<?php echo esc_url($contact_url); ?>" <?php if($is_contact) echo 'class="active"'; ?>>ติดต่อเรา</a>
            </nav>
            <div class="nav-actions">
                <a href="<?php echo esc_url($login_url); ?>" class="btn btn-outline" style="padding: 8px 18px; font-size: 0.82rem;"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
                <a href="<?php echo esc_url($register_url); ?>" class="btn btn-red" style="padding: 8px 18px; font-size: 0.82rem;"><i class="fas fa-user-plus" style="color:inherit;"></i> สมัครสมาชิก</a>
            </div>
        </div>
        <button class="mobile-toggle" onclick="document.getElementById('mobileNav').classList.add('open')"><i class="fas fa-bars"></i></button>
    </div>
</header>

<!-- MOBILE NAV -->
<div class="mobile-nav-overlay" id="mobileNav" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="mobile-nav-panel">
        <div class="mobile-nav-header">
            <img src="<?php echo esc_url($logo_url); ?>" alt="SKJ Japan">
            <button class="mobile-nav-close" onclick="document.getElementById('mobileNav').classList.remove('open')"><i class="fas fa-times"></i></button>
        </div>
        <div class="mobile-nav-links">
            <a href="<?php echo esc_url($home_url); ?>" <?php if($is_home) echo 'class="active"'; ?>><i class="fas fa-home"></i> หน้าแรก</a>
            <a href="<?php echo esc_url($about_url); ?>" <?php if($is_about) echo 'class="active"'; ?>><i class="fas fa-info-circle"></i> เกี่ยวกับเรา</a>
            <a href="<?php echo esc_url($services_url); ?>" <?php if($is_services) echo 'class="active"'; ?>><i class="fas fa-concierge-bell"></i> บริการของเรา</a>
            <a href="<?php echo esc_url($blog_url); ?>" <?php if($is_blog) echo 'class="active"'; ?>><i class="fas fa-newspaper"></i> บทความ</a>
            <a href="<?php echo esc_url($contact_url); ?>" <?php if($is_contact) echo 'class="active"'; ?>><i class="fas fa-envelope"></i> ติดต่อเรา</a>
            <a href="<?php echo esc_url($track_url); ?>"><i class="fas fa-search"></i> Track & Trace</a>
        </div>
        <div class="mobile-nav-actions">
            <a href="<?php echo esc_url($login_url); ?>" class="btn btn-outline"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
            <a href="<?php echo esc_url($register_url); ?>" class="btn btn-red"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
        </div>
        <div class="mobile-nav-social">
            <a href="https://www.facebook.com/SKJ.Japan" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://lin.ee/whDh44F" target="_blank"><i class="fab fa-line"></i></a>
            <a href="https://www.instagram.com/skj.japan" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@skj.japan" target="_blank"><i class="fab fa-tiktok"></i></a>
        </div>
    </div>
</div>
