<?php
// SKJ Favicon for Google Search
function skj_custom_favicon() {
    echo '<link rel="icon" type="image/png" sizes="180x180" href="https://skjjapanshipping.com/favicon.png">' . "\n";
    echo '<link rel="shortcut icon" href="https://skjjapanshipping.com/favicon.png">' . "\n";
    echo '<link rel="apple-touch-icon" sizes="180x180" href="https://skjjapanshipping.com/favicon.png">' . "\n";
}
add_action('wp_head', 'skj_custom_favicon');
