<?php

declare(strict_types=1);

$images = [
    'assets/images/real_dishes/lasagna.jpg' => 'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?auto=format&fit=crop&w=600&q=80',
    'assets/images/real_dishes/mac-and-cheese.jpg' => 'https://images.unsplash.com/photo-1543339308-43e59d6b73a6?auto=format&fit=crop&w=600&q=80',
    'assets/images/real_dishes/suon-heo-bbq.jpg' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80',
    'assets/images/real_dishes/banh-da-lon.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/b/b3/B%C3%A1nh_da_l%C6%A1n_%C4%91%E1%BA%ADu_xanh_v%C3%A0_l%C3%A1_d%E1%BB%A9a..jpg',
    'assets/images/real_dishes/banh-gio.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/9/91/B%C3%A1nh_gi%C3%B2.jpg/960px-B%C3%A1nh_gi%C3%B2.jpg',
    'assets/images/real_dishes/banh-ran-luc-lac.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/a/a4/Banhran.jpg',
    'assets/images/real_dishes/bo-wellington.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/a/a9/Beef_Wellington_2019.jpg/960px-Beef_Wellington_2019.jpg',
    'assets/images/real_dishes/xuc-xich-duc.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/b/b0/German_Bratw%C3%BCrste.jpg',
    'assets/images/real_dishes/sup-hanh-tay.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/5/5a/Soupe_%C3%A0_l%27oignon.jpg/960px-Soupe_%C3%A0_l%27oignon.jpg',
    'assets/images/real_dishes/salad-caesar.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/2/23/Caesar_salad_%282%29.jpg/960px-Caesar_salad_%282%29.jpg',
    'assets/images/real_dishes/sup-kem-nam.jpg' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=600&q=80',
    'assets/images/real_dishes/spaghetti.jpg' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=600&q=80',
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
