<?php
foreach ([1, 2] as $page) {
    $url = 'http://localhost:8000/index.php?q=' . urlencode('ốc') . '&page=' . $page;
    $html = file_get_contents($url);
    preg_match_all('/<img src="([^"]+)"[^>]*alt="([^"]+)"/i', $html, $matches, PREG_SET_ORDER);
    echo "=== Page $page: " . count($matches) . " images ===\n";
    foreach ($matches as $m) {
        if (strpos($m[1], 'logo') !== false) continue;
        echo "Src: {$m[1]} | Alt: {$m[2]}\n";
    }
}
