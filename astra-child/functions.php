<?php
/**
 * Astra Child Theme - SKJ Japan
 * Modern redesign for skjjapanshipping.com
 */

// Redirect /calculate → /#calculator
add_action('template_redirect', 'skj_redirect_calculate');
function skj_redirect_calculate() {
    if (trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') === 'calculate') {
        wp_redirect(home_url('/#calculator'), 301);
        exit;
    }
}

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', 'skj_child_enqueue_styles', 15);
function skj_child_enqueue_styles() {
    // Parent theme style
    wp_enqueue_style(
        'astra-parent-style',
        get_template_directory_uri() . '/style.css'
    );

    // Child theme style
    wp_enqueue_style(
        'skj-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('astra-parent-style'),
        wp_get_theme()->get('Version')
    );

    // SKJ Theme CSS (shared across all custom templates)
    wp_enqueue_style(
        'skj-theme-css',
        get_stylesheet_directory_uri() . '/assets/css/skj-theme.css',
        array('skj-child-style'),
        wp_get_theme()->get('Version')
    );

    // Font Awesome
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );

    // SKJ Pages CSS (page-specific styles)
    wp_enqueue_style(
        'skj-pages-css',
        get_stylesheet_directory_uri() . '/assets/css/skj-pages.css',
        array('skj-theme-css'),
        wp_get_theme()->get('Version')
    );

    // Custom JS (no jQuery dependency for faster load)
    wp_enqueue_script(
        'skj-custom-js',
        get_stylesheet_directory_uri() . '/assets/js/skj-custom.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
}

// Performance: remove unnecessary WP head clutter
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'rest_output_link_wp_head');
add_filter('emoji_svg_url', '__return_false');

// Add Google Fonts
add_action('wp_enqueue_scripts', 'skj_google_fonts', 5);
function skj_google_fonts() {
    wp_enqueue_style(
        'google-fonts-noto',
        'https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;700&display=swap',
        array(),
        null
    );
}

// Force override Astra dynamic CSS (runs after Astra outputs its inline styles)
add_action('wp_head', 'skj_override_astra_css', 9999);
function skj_override_astra_css() {
    echo '<style id="skj-astra-override">
        body, body.flavor-flavor, #page, .site, #content, .site-content,
        .ast-separate-container, .ast-plain-container, .ast-page-builder-template,
        .ast-separate-container #primary, .ast-plain-container #primary,
        body.ast-separate-container, .ast-separate-container .ast-article-single,
        .ast-separate-container .ast-article-post, .ast-separate-container .ast-comment-list li,
        .ast-separate-container .comment-respond {
            background: #ffffff !important;
            background-color: #ffffff !important;
        }
        #masthead, .ast-above-header-wrap, .ast-below-header-wrap,
        .ast-mobile-header-wrap, .main-header-bar-wrap, #ast-mobile-header,
        .site-header, .ast-primary-header-bar, #astra-header-menu,
        .ast-footer-overlay, #colophon, .site-footer {
            display: none !important;
        }
        .entry-content, #primary, .ast-container {
            margin: 0 !important; padding: 0 !important;
        }
        body.page .site-content > .ast-container,
        body.single .site-content > .ast-container {
            max-width: 100% !important; padding: 0 !important;
        }
        .hero-slider, .hero-slider .slider-track, .hero-slider .slider-slide {
            max-height: none !important; height: auto !important;
        }
        .hero-slider { position: relative !important; overflow: hidden !important; }
        .slider-viewport { position: relative !important; overflow: hidden !important; }
        .hero-overlay {
            position: absolute !important; top: 0 !important; left: 0 !important;
            right: 0 !important; bottom: 0 !important; z-index: 2 !important;
            display: flex !important; align-items: flex-end !important;
            background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.1) 40%, transparent 65%) !important;
            padding: 0 5% 24px !important;
        }
        .hero-overlay-content { max-width: 650px; color: #fff; }
        @media (max-width: 768px) {
            .hero-overlay {
                position: relative !important; top: auto !important; left: auto !important;
                right: auto !important; bottom: auto !important;
                background: #1a1a2e !important; padding: 20px 16px !important;
            }
            .hero-overlay-content {
                max-width: 100%; background: none !important;
                backdrop-filter: none !important; border: none !important; padding: 0 !important;
            }
        }
        .hero-slider img, .hero-slider .slider-slide img,
        .entry-content .hero-slider img {
            width: 100% !important; height: auto !important;
            max-height: none !important; object-fit: unset !important;
        }
        .article-body img, .article-body p img,
        .article-body .wp-block-image img,
        .entry-content img, .entry-content .wp-block-image img,
        .entry-content p img, .article-body figure img {
            display: block !important; visibility: visible !important;
            opacity: 1 !important; max-width: 100% !important;
            height: auto !important;
        }
        img.wp-image, img.aligncenter, img.alignnone,
        img.alignleft, img.alignright, img.size-full,
        img.size-large, img.size-medium {
            display: block !important; visibility: visible !important;
            opacity: 1 !important; max-width: 100% !important;
            height: auto !important;
        }
        .featured-post {
            display: grid !important;
            grid-template-columns: 1.2fr 1fr !important;
        }
        .featured-post .featured-img {
            width: 100% !important; object-fit: cover !important;
            display: block !important;
        }
        .nav-actions .btn-red,
        .nav-actions .btn-outline {
            border: 2px solid currentColor !important;
            transform: none !important;
            box-shadow: none !important;
            transition: background 0.25s, color 0.25s !important;
        }
        .nav-actions .btn-red {
            background: #C9301D !important;
            color: #fff !important;
            border-color: #C9301D !important;
        }
        .nav-actions .btn-red:hover {
            background: #fff !important;
            color: #C9301D !important;
            border-color: #C9301D !important;
            transform: none !important;
            box-shadow: none !important;
        }
    </style>';
}

// Disable WordPress lazy loading on single posts to ensure all content images load
add_filter('wp_lazy_loading_enabled', function($default, $tag_name) {
    if (is_single() && $tag_name === 'img') return false;
    return $default;
}, 10, 2);

// AJAX Contact Form Handler
add_action('wp_ajax_skj_contact', 'skj_handle_contact_form');
add_action('wp_ajax_nopriv_skj_contact', 'skj_handle_contact_form');
function skj_handle_contact_form() {
    check_ajax_referer('skj_contact_form', 'nonce');
    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $line_id = sanitize_text_field($_POST['line_id'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        wp_send_json_error('กรุณากรอกข้อมูลที่จำเป็นให้ครบ');
    }
    $to = 'skj.japanshipping@gmail.com';
    $mail_subject = '[SKJ Contact] ' . $subject;
    $body  = "ชื่อ: $name\nอีเมล: $email\nเบอร์โทร: $phone\nLINE ID: $line_id\nหัวข้อ: $subject\n\nข้อความ:\n$message\n";
    $headers = array('Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>');

    if (wp_mail($to, $mail_subject, $body, $headers)) {
        wp_send_json_success('ส่งข้อความสำเร็จ!');
    } else {
        wp_send_json_error('ไม่สามารถส่งข้อความได้ กรุณาติดต่อผ่าน LINE');
    }
}

// Helper: get template part from child theme
function skj_get_part($name) {
    get_template_part('template-parts/skj-' . $name);
}

// Helper: get page URL by slug
function skj_page_url($slug) {
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page) : home_url('/' . $slug . '/');
}

// =============================================
// SEO: Meta tags, Open Graph, Canonical
// =============================================
add_action('wp_head', 'skj_seo_meta_tags', 1);
function skj_seo_meta_tags() {
    $title = 'SKJ Japan Shipping - บริการขนส่งสินค้าจากญี่ปุ่นมาไทย';
    $desc = 'บริการขนส่งสินค้าจากญี่ปุ่นมาไทย ทางเรือเริ่มต้น 150 บาท/กก. ทางเครื่องบิน 339 บาท/กก. ปิดตู้ทุกสัปดาห์ พร้อม Track & Trace เรียลไทม์';
    $keywords = 'ขนส่งสินค้าจากญี่ปุ่น, ส่งของจากญี่ปุ่น, shipping japan thailand, นำเข้าสินค้าญี่ปุ่น, ส่งพัสดุจากญี่ปุ่น, SKJ Japan';
    $default_img = 'https://skjjapanshipping.com/wp-content/uploads/2025/03/1.webp';
    $url = home_url('/');

    if (is_page('about')) {
        $desc = 'เกี่ยวกับ SKJ Japan Shipping บริษัทขนส่งสินค้าจากญี่ปุ่นมาไทย ประสบการณ์กว่า 5 ปี ลูกค้ากว่า 1,000 ราย';
        $url = home_url('/about/');
    } elseif (is_page('services')) {
        $desc = 'บริการขนส่งทางเรือ ทางเครื่องบิน รับสั่งซื้อสินค้าจากญี่ปุ่น คลังสินค้าที่ญี่ปุ่น พร้อม Track & Trace เรียลไทม์';
        $url = home_url('/services/');
    } elseif (is_page('contact-us')) {
        $desc = 'ติดต่อ SKJ Japan Shipping LINE @skj.japan โทร 082-460-9940 อีเมล skj.japanshipping@gmail.com';
        $url = home_url('/contact-us/');
    } elseif (is_page('blog') || is_home()) {
        $desc = 'บทความน่ารู้เกี่ยวกับการสั่งซื้อสินค้าจากญี่ปุ่น เทคนิคการประมูล Yahoo Auctions และแหล่งช็อปปิ้ง';
        $url = home_url('/blog/');
    } elseif (is_single()) {
        $desc = wp_trim_words(get_the_excerpt(), 30, '...');
        $url = get_permalink();
        if (has_post_thumbnail()) $default_img = get_the_post_thumbnail_url(null, 'large');
    }

    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    if ($keywords) echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($default_img) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="500">' . "\n";
    echo '<meta property="og:site_name" content="SKJ Japan Shipping">' . "\n";
    echo '<meta property="og:locale" content="th_TH">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($default_img) . '">' . "\n";
    echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
}

// =============================================
// SEO: JSON-LD Structured Data
// =============================================
add_action('wp_head', 'skj_jsonld_structured_data', 2);
function skj_jsonld_structured_data() {
    $schemas = array();
    $schemas[] = array(
        '@type' => 'LocalBusiness',
        '@id' => home_url('/#organization'),
        'name' => 'SKJ Japan Shipping',
        'description' => 'บริการขนส่งสินค้าจากญี่ปุ่นมาไทย ทางเรือและทางเครื่องบิน รับสั่งซื้อสินค้าจากเว็บญี่ปุ่น',
        'url' => home_url('/'),
        'telephone' => '+66824609940',
        'email' => 'skj.japanshipping@gmail.com',
        'priceRange' => '฿฿',
        'address' => array('@type' => 'PostalAddress', 'addressCountry' => 'TH'),
        'openingHoursSpecification' => array(
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
            'opens' => '09:00', 'closes' => '18:00'
        ),
    );
    $schemas[] = array('@type' => 'WebSite', 'url' => home_url('/'), 'name' => 'SKJ Japan Shipping');

    if (is_front_page()) {
        $schemas[] = array(
            '@type' => 'FAQPage',
            'mainEntity' => array(
                array('@type' => 'Question', 'name' => 'ค่าขนส่งทางเรือจากญี่ปุ่นมาไทยเท่าไหร่?', 'acceptedAnswer' => array('@type' => 'Answer', 'text' => 'เริ่มต้นที่ 150 บาท/กก. ใช้เวลา 14-25 วันหลังปิดตู้')),
                array('@type' => 'Question', 'name' => 'การจัดส่งสินค้าใช้เวลานานเท่าไหร่?', 'acceptedAnswer' => array('@type' => 'Answer', 'text' => 'ทางเรือ 14-25 วัน ทางเครื่องบิน 3-7 วัน')),
                array('@type' => 'Question', 'name' => 'มีบริการรับสั่งซื้อสินค้าให้ไหม?', 'acceptedAnswer' => array('@type' => 'Answer', 'text' => 'มีครับ สั่งซื้อจากทุกเว็บญี่ปุ่น Yahoo Auctions, Mercari, Rakuten, Amazon JP')),
            ),
        );
    }
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode(array('@context' => 'https://schema.org', '@graph' => $schemas), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

// =============================================
// SEO: XML Sitemap
// =============================================
add_action('init', 'skj_render_sitemap', 0);
function skj_render_sitemap() {
    if (strpos($_SERVER['REQUEST_URI'], 'skj-sitemap.xml') === false) return;
    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex');
    header('Cache-Control: public, max-age=3600');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $pages = array(
        array('url' => home_url('/'), 'priority' => '1.0', 'changefreq' => 'daily'),
        array('url' => home_url('/about/'), 'priority' => '0.8', 'changefreq' => 'monthly'),
        array('url' => home_url('/services/'), 'priority' => '0.8', 'changefreq' => 'monthly'),
        array('url' => home_url('/blog/'), 'priority' => '0.7', 'changefreq' => 'weekly'),
        array('url' => home_url('/contact-us/'), 'priority' => '0.7', 'changefreq' => 'monthly'),
    );
    foreach ($pages as $page) {
        echo "  <url>\n    <loc>" . esc_url($page['url']) . "</loc>\n    <changefreq>{$page['changefreq']}</changefreq>\n    <priority>{$page['priority']}</priority>\n  </url>\n";
    }
    $posts = get_posts(array('numberposts' => 100, 'post_status' => 'publish'));
    foreach ($posts as $post) {
        echo "  <url>\n    <loc>" . get_permalink($post) . "</loc>\n    <lastmod>" . get_the_modified_date('c', $post) . "</lastmod>\n    <changefreq>monthly</changefreq>\n    <priority>0.6</priority>\n  </url>\n";
    }
    echo '</urlset>';
    exit;
}

// =============================================
// SECURITY: HTTP Headers
// =============================================
add_action('send_headers', 'skj_security_headers');
function skj_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: frame-ancestors 'self'");
    }
}

// =============================================
// SECURITY: Disable XML-RPC (already blocked in .htaccess, extra layer)
// =============================================
add_filter('xmlrpc_enabled', '__return_false');

// =============================================
// SECURITY: Remove WordPress version from head
// =============================================
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// =============================================
// SECURITY: Disable file editor in admin
// =============================================
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

// =============================================
// SECURITY: Hide login error details
// =============================================
add_filter('login_errors', function() {
    return 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง';
});

// =============================================
// FAVICON: Custom favicon tags (trimmed, no white space)
// =============================================
add_action('wp_head', 'skj_custom_favicon', 0);
function skj_custom_favicon() {
    $base = home_url('/');
    $v = '20260325';
    echo '<link rel="icon" type="image/png" sizes="48x48" href="' . $base . 'favicon-48x48.png?v=' . $v . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $base . 'favicon-32x32.png?v=' . $v . '">' . "\n";
    echo '<link rel="icon" type="image/png" sizes="16x16" href="' . $base . 'favicon-16x16.png?v=' . $v . '">' . "\n";
    echo '<link rel="icon" type="image/x-icon" href="' . $base . 'favicon.ico?v=' . $v . '">' . "\n";
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $base . 'apple-touch-icon.png?v=' . $v . '">' . "\n";
}
remove_action('wp_head', 'wp_site_icon', 99);

// Performance: Preload critical resources
add_action('wp_head', 'skj_preload_resources', 1);
function skj_preload_resources() {
    echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;600;700&display=swap" as="style">' . "\n";
    if (is_front_page()) {
        echo '<link rel="preload" href="https://skjjapanshipping.com/wp-content/uploads/2025/03/1.webp" as="image" type="image/webp">' . "\n";
    }
}
