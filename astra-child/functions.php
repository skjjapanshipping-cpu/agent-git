<?php
/**
 * Astra Child Theme - SKJ Japan
 * Modern redesign for skjjapanshipping.com
 */

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
