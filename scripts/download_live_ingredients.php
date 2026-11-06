<?php
$titles = [
    'beef' => 'File:Raw beef slices.jpg',
    'tofu' => 'File:Tofu 4.jpg',
    'oc_buou' => 'File:Ốc bươu nướng tiêu Cần Thơ.jpg',
    'escargots' => 'File:Escargots.jpg',
    'crab' => 'File:Live crab, Rue Cler, Paris 24 May 2014.jpg',
];

$ctx = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Cookio/1.0\r\n",
        'timeout' => 20
    ]
]);

$urls = [];
foreach ($titles as $k => $title) {
    $apiUrl = "https://commons.wikimedia.org/w/api.php?action=query&titles=" . urlencode($title) . "&prop=imageinfo&iiprop=url&format=json";
    $json = @file_get_contents($apiUrl, false, $ctx);
    if ($json) {
        $data = json_decode($json, true);
        $pages = $data['query']['pages'] ?? [];
        foreach ($pages as $p) {
            if (isset($p['imageinfo'][0]['url'])) {
                $urls[$k] = $p['imageinfo'][0]['url'];
            }
        }
    }
}

print_r($urls);

// Now download each
$targets = [
    'assets/images/ingredients/thit-bo.jpg' => $urls['beef'] ?? null,
    'assets/images/ingredients/dau-hu.jpg' => $urls['tofu'] ?? null,
    'assets/images/ingredients/oc.jpg' => $urls['oc_buou'] ?? $urls['escargots'] ?? null,
    'assets/images/ingredients/cua.jpg' => $urls['crab'] ?? null,
];

foreach ($targets as $path => $url) {
    if (!$url) {
        echo "No URL for $path\n";
        continue;
    }
    echo "Downloading $path from $url...\n";
    $content = @file_get_contents($url, false, $ctx);
    if ($content && strlen($content) > 5000) {
        file_put_contents($path, $content);
        echo "SAVED $path: " . strlen($content) . " bytes\n";
    } else {
        echo "FAILED to download $path\n";
    }
}
