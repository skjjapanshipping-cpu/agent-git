#!/usr/bin/env python3
"""
v2: Use .htaccess rewrite + cookie approach for hiding wp-login.php
- /skj-admin-login sets a cookie, then redirects to wp-login.php
- wp-login.php checks for the cookie; no cookie = 403
"""

# 1. Update the plugin to use cookie-based approach
plugin_path = '/var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/plugins/skj-hide-login/skj-hide-login.php'

plugin_code = '''<?php
/**
 * Plugin Name: SKJ Hide Login
 * Description: Hide wp-login.php behind a custom URL for security
 * Version: 1.0.1
 * Author: SKJ Japan
 */

if (!defined('ABSPATH')) exit;

define('SKJ_LOGIN_SLUG', 'skj-admin-login');
define('SKJ_LOGIN_COOKIE', 'skj_login_access');
define('SKJ_LOGIN_SECRET', 'skj2026secure');

// Handle custom login slug — set cookie and redirect to wp-login.php
add_action('init', function() {
    $request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($request === SKJ_LOGIN_SLUG) {
        setcookie(SKJ_LOGIN_COOKIE, SKJ_LOGIN_SECRET, time() + 3600, '/');
        $_COOKIE[SKJ_LOGIN_COOKIE] = SKJ_LOGIN_SECRET;
        wp_redirect(site_url('wp-login.php?' . $_SERVER['QUERY_STRING']));
        exit;
    }
});

// Block direct access to wp-login.php without cookie
add_action('login_init', function() {
    // Allow if cookie is set
    if (isset($_COOKIE[SKJ_LOGIN_COOKIE]) && $_COOKIE[SKJ_LOGIN_COOKIE] === SKJ_LOGIN_SECRET) {
        return;
    }

    // Allow POST requests (form submission after cookie was set)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return;
    }

    // Allow special actions
    $allowed_actions = ['logout', 'postpass', 'resetpass', 'rp', 'confirmaction'];
    if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions)) {
        return;
    }

    // Block: return 404
    status_header(404);
    nocache_headers();
    $template = get_query_template('404');
    if ($template) {
        include($template);
    } else {
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>';
    }
    exit;
});

// Block wp-admin for non-logged-in users
add_action('admin_init', function() {
    if (!is_user_logged_in() && !wp_doing_ajax()) {
        status_header(404);
        nocache_headers();
        $template = get_query_template('404');
        if ($template) {
            include($template);
        } else {
            echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>';
        }
        exit;
    }
});

// Fix login URL references
add_filter('login_url', function($login_url, $redirect, $force_reauth) {
    $new_url = site_url(SKJ_LOGIN_SLUG);
    if (!empty($redirect)) {
        $new_url = add_query_arg('redirect_to', urlencode($redirect), $new_url);
    }
    return $new_url;
}, 10, 3);

add_filter('logout_redirect', function($redirect_to) {
    return home_url();
}, 10, 1);
'''

with open(plugin_path, 'w') as f:
    f.write(plugin_code)
print('Plugin v2 written')

# 2. Clear WP cache
import subprocess
subprocess.run(['rm', '-rf', '/var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/cache/'], check=False)
print('Cache cleared')
print('Done!')
