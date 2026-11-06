<?php

declare(strict_types=1);

$images = [
    'assets/images/real_dishes/com-trang.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/d/d6/Meshi_001.jpg/960px-Meshi_001.jpg',
    'assets/images/real_dishes/mang-chua.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/6/6b/M%C4%83ng_chua_ng%C3%A2m_qu%E1%BA%A3_m%C3%B3c_m%E1%BA%ADt.jpg/960px-M%C4%83ng_chua_ng%C3%A2m_qu%E1%BA%A3_m%C3%B3c_m%E1%BA%ADt.jpg',
    'assets/images/real_dishes/thit-ba-chi-luoc.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/4/47/KOCIS_BOSSAM%2C_napa_wraps_with_pork_%284618280268%29.jpg/960px-KOCIS_BOSSAM%2C_napa_wraps_with_pork_%284618280268%29.jpg',
    'assets/images/real_dishes/sot-trung-muoi.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Salty_egg.JPG',
    'assets/images/real_dishes/mo-heo-top-mo.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/a/af/Homelard.jpg/960px-Homelard.jpg',
    'assets/images/real_dishes/bun-rieu.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/3/3b/B%C3%BAn_ri%C3%AAu_%C4%91%E1%BA%B7c_bi%E1%BB%87t.jpg/960px-B%C3%BAn_ri%C3%AAu_%C4%91%E1%BA%B7c_bi%E1%BB%87t.jpg',
];

$baseDir = dirname(__DIR__);

foreach ($images as $relPath => $url) {
    $fullPath = $baseDir . '/' . $relPath;
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    echo "Downloading {$relPath}... ";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $data !== false && strlen($data) > 1000) {
        file_put_contents($fullPath, $data);
        echo "OK (" . strlen($data) . " bytes)\n";
    } else {
        echo "FAILED (HTTP {$httpCode})\n";
    }
}

echo "Downloads done!\n";
