<?php
// Remove malware plugins from WordPress active_plugins
$db = new mysqli('localhost', 'skjjapan_wp426', 'p067Q6co?', 'skjjapan_wp426');
$result = $db->query("SELECT option_value FROM wpl9_options WHERE option_name = 'active_plugins'");
$row = $result->fetch_assoc();
$plugins = unserialize($row['option_value']);

$malware = ['wp-security-helper/wp-security-helper.php', 'wp-posts-cache-engine/wp-posts-cache-engine.php'];
$clean = [];
foreach ($plugins as $p) {
    if (in_array($p, $malware)) {
        echo "REMOVING: $p\n";
        continue;
    }
    $clean[] = $p;
}

$new_val = serialize($clean);
$stmt = $db->prepare("UPDATE wpl9_options SET option_value = ? WHERE option_name = 'active_plugins'");
$stmt->bind_param('s', $new_val);
$stmt->execute();
echo "Updated active_plugins: " . count($clean) . " plugins remaining\n";
$db->close();
