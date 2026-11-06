<?php
$titles = [
    'beef' => 'File:Raw beef slices.jpg',
    'tofu' => 'File:Tofu 4.jpg',
    'snail' => 'File:Escargots.jpg',
    'crab' => 'File:Live crab, Rue Cler, Paris 24 May 2014.jpg',
];

$ctx = stream_context_create([
    'http' => [
        'header' => "User-Agent: CookioProject/1.0 (contact@cookio.vn)\r\n"
    ]
]);

foreach ($titles as $k => $title) {
    $apiUrl = "https://commons.wikimedia.org/w/api.php?action=query&titles=" . urlencode($title) . "&prop=imageinfo&iiprop=url&format=json";
    $json = file_get_contents($apiUrl, false, $ctx);
    $data = json_decode($json, true);
    $pages = $data['query']['pages'] ?? [];
    foreach ($pages as $p) {
        $imgUrl = $p['imageinfo'][0]['url'] ?? null;
        echo "$k ($title) => $imgUrl\n";
    }
}
