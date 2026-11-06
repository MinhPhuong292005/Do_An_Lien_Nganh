<?php

declare(strict_types=1);

$images = [
    // Ingredients Carousel (Home Page)
    'assets/images/ingredients/thit-bo.jpg' => 'https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=600&q=80',
    'assets/images/ingredients/dau-hu.jpg' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80',
    'assets/images/ingredients/oc.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/d/d7/Caracoles-del-restaurante-granero.jpg',

    // Specific Dishes & Tips
    'assets/images/real_dishes/mien-tron.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/c8/Polish_Korean_Cuisine_and_Culture_Exchanges_Gradmother%E2%80%99s_Recipes_05.jpg/960px-Polish_Korean_Cuisine_and_Culture_Exchanges_Gradmother%E2%80%99s_Recipes_05.jpg',
    'assets/images/real_dishes/canh-ngheu.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/2/25/Clam_soup.JPG/960px-Clam_soup.JPG',
    'assets/images/real_dishes/bun-cha-ha-noi.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/4/4d/B%C3%BAn_ch%E1%BA%A3_Vietnamese_food.jpg/960px-B%C3%BAn_ch%E1%BA%A3_Vietnamese_food.jpg',
    'assets/images/real_dishes/nem-ran.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/6/6b/Cha_gio.jpg',
    'assets/images/real_dishes/canh-chua-ca-loc.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Canhchua2.jpg',
    'assets/images/real_dishes/ga-luoc-cung.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/a/ae/BeiQieJi-WhiteCutChicken.jpg/960px-BeiQieJi-WhiteCutChicken.jpg',
    'assets/images/real_dishes/trung-long-dao.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/cc/Soft-boiled-egg.jpg/960px-Soft-boiled-egg.jpg',
    'assets/images/real_dishes/thit-heo-quay.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/1/12/RoastPig.jpg',
    'assets/images/real_dishes/bao-quan-rau-thom.jpg' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80',
    'assets/images/real_dishes/gan-bo.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/5/5d/Stewed-beef-tendon%2Cgyusuji-nikomi%2Ckatori-city%2Cjapan.JPG',
    'assets/images/real_dishes/chan-ga-sot-thai.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/4/43/Chicken_feet.jpg/960px-Chicken_feet.jpg',
    'assets/images/real_dishes/nuoc-mau.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/8/89/Caramel-2.jpg/960px-Caramel-2.jpg',

    // Mirror to assets/images/recipes/ for fallback
    'assets/images/recipes/thit-bo.jpg' => 'https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=600&q=80',
    'assets/images/recipes/dau-hu.jpg' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=600&q=80',
    'assets/images/recipes/oc.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/d/d7/Caracoles-del-restaurante-granero.jpg',
    'assets/images/recipes/mien-tron.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/c8/Polish_Korean_Cuisine_and_Culture_Exchanges_Gradmother%E2%80%99s_Recipes_05.jpg/960px-Polish_Korean_Cuisine_and_Culture_Exchanges_Gradmother%E2%80%99s_Recipes_05.jpg',
    'assets/images/recipes/canh-ngheu.jpg' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/2/25/Clam_soup.JPG/960px-Clam_soup.JPG',
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

echo "All downloads completed!\n";
