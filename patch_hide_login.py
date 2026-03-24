#!/usr/bin/env python3
"""
Create a simple plugin to hide WordPress login URL.
New login URL: /skj-admin-login
Old /wp-login.php and /wp-admin (when not logged in) will return 404.
"""
import os

plugin_dir = '/var/www/vhosts/skjjapanshipping.com/httpdocs/wp-content/plugins/skj-hide-login'
os.makedirs(plugin_dir, exist_ok=True)

plugin_code = r'''<?php
/**
 * Plugin Name: SKJ Hide Login
 * Description: Hide wp-login.php behind a custom URL for security
 * Version: 1.0.0
 * Author: SKJ Japan
 */

if (!defined('ABSPATH')) exit;

define('SKJ_LOGIN_SLUG', 'skj-admin-login');

// Add rewrite rule for custom login slug
add_action('init', function() {
    add_rewrite_rule('^' . SKJ_LOGIN_SLUG . '/?$', 'wp-login.php', 'top');
});

// Flush rewrite rules on activation
register_activation_hook(__FILE__, function() {
    add_rewrite_rule('^' . SKJ_LOGIN_SLUG . '/?$', 'wp-login.php', 'top');
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Block direct access to wp-login.php unless accessed via custom slug
add_action('login_init', function() {
    $request_uri = $_SERVER['REQUEST_URI'];

    // Allow if accessed via custom slug
    if (strpos($request_uri, SKJ_LOGIN_SLUG) !== false) {
        return;
    }

    // Allow POST requests for login processing (form submission, AJAX)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return;
    }

    // Allow if action is logout, postpass, or other special actions
    $allowed_actions = ['logout', 'postpass', 'resetpass', 'rp', 'confirmaction'];
    if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions)) {
        return;
    }

    // Allow if user is already logged in (cookie check)
    if (isset($_COOKIE['wordpress_logged_in_' . COOKIEHASH])) {
        return;
    }

    // Block: return 404
    status_header(404);
    nocache_headers();
    include(get_query_template('404'));
    exit;
});

// Change wp-admin redirect for non-logged-in users
add_action('admin_init', function() {
    if (!is_user_logged_in()) {
        status_header(404);
        nocache_headers();
        include(get_query_template('404'));
        exit;
    }
});

// Fix login URL in WordPress (for password reset emails, etc.)
add_filter('login_url', function($login_url, $redirect, $force_reauth) {
    return site_url(SKJ_LOGIN_SLUG, 'login');
}, 10, 3);

// Fix logout URL redirect
add_filter('logout_redirect', function($redirect_to) {
    return home_url();
}, 10, 1);

// Fix register URL
add_filter('register_url', function($url) {
    return str_replace('wp-login.php?action=register', SKJ_LOGIN_SLUG . '?action=register', $url);
});

// Fix lost password URL
add_filter('lostpassword_url', function($url) {
    return str_replace('wp-login.php?action=lostpassword', SKJ_LOGIN_SLUG . '?action=lostpassword', $url);
});
'''

with open(os.path.join(plugin_dir, 'skj-hide-login.php'), 'w') as f:
    f.write(plugin_code)

print('Plugin created: skj-hide-login')

# Activate plugin via DB
import subprocess
result = subprocess.run([
    'mysql', 'skjjapan_wp426', '-e',
    "SELECT option_value FROM wpl9_options WHERE option_name = 'active_plugins';"
], capture_output=True, text=True)

current = result.stdout.strip().split('\n')[-1]
print('Current plugins:', current[:100])

# Use PHP to add plugin to active list
php_code = '''<?php
$db = new mysqli('localhost', 'skjjapan_wp426', 'p067Q6co?', 'skjjapan_wp426');
$result = $db->query("SELECT option_value FROM wpl9_options WHERE option_name = 'active_plugins'");
$row = $result->fetch_assoc();
$plugins = unserialize($row['option_value']);
$new_plugin = 'skj-hide-login/skj-hide-login.php';
if (!in_array($new_plugin, $plugins)) {
    $plugins[] = $new_plugin;
    $new_val = serialize($plugins);
    $stmt = $db->prepare("UPDATE wpl9_options SET option_value = ? WHERE option_name = 'active_plugins'");
    $stmt->bind_param('s', $new_val);
    $stmt->execute();
    echo "Plugin activated\\n";
} else {
    echo "Plugin already active\\n";
}
$db->close();
'''

with open('/tmp/activate_hide_login.php', 'w') as f:
    f.write(php_code)

result = subprocess.run(['php', '/tmp/activate_hide_login.php'], capture_output=True, text=True)
print(result.stdout.strip())

# Flush rewrite rules
result = subprocess.run(
    ['php', '/var/www/vhosts/skjjapanshipping.com/httpdocs/wp-cron.php'],
    capture_output=True, text=True, cwd='/var/www/vhosts/skjjapanshipping.com/httpdocs/'
)
print('Done!')
