<?php
$urls = [
    'assets/images/ingredients/thit-bo.jpg' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?auto=format&fit=crop&w=600&q=80', // Beautiful fresh red raw beef steak
    'assets/images/ingredients/oc.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Oc_luoc.jpg/800px-Oc_luoc.jpg', // Real boiled snails with lemongrass
    'assets/images/ingredients/dau-hu.jpg' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Tofu_in_bowl.jpg/800px-Tofu_in_bowl.jpg', // Real white tofu blocks
];

// Fallback mirror in assets/images/recipes/ as well
$mirror = [
    'assets/images/recipes/thit-bo.jpg' => 'assets/images/ingredients/thit-bo.jpg',
    'assets/images/recipes/oc.jpg' => 'assets/images/ingredients/oc.jpg',
    'assets/images/recipes/dau-hu.jpg' => 'assets/images/ingredients/dau-hu.jpg',
];

$ctx = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
        'timeout' => 15
    ]
]);

foreach ($urls as $dest => $url) {
    echo "Downloading $dest from $url...\n";
    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false && strlen($data) > 2000) {
        file_put_contents($dest, $data);
        echo "SUCCESS: " . strlen($data) . " bytes -> $dest\n";
    } else {
        echo "FAILED: $url\n";
    }
}

foreach ($mirror as $target => $src) {
    if (file_exists($src)) {
        copy($src, $target);
        echo "Mirrored $src -> $target\n";
    }
}
