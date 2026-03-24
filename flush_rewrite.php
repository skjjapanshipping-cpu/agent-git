<?php
define('ABSPATH', '/var/www/vhosts/skjjapanshipping.com/httpdocs/');
require_once(ABSPATH . 'wp-load.php');
flush_rewrite_rules();
echo "Rewrite rules flushed\n";
