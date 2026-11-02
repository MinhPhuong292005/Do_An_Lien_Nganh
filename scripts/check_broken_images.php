<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$recipes = db()->query("SELECT id, title, image_url, status FROM recipes ORDER BY id ASC")->fetchAll();

$missingOrBroken = [];
$tinyFiles = [];

foreach ($recipes as $r) {
    $img = trim((string)($r['image_url'] ?? ''));
    if ($img === '') {
        $missingOrBroken[] = ['id' => $r['id'], 'title' => $r['title'], 'reason' => 'Empty image_url', 'img' => $img];
        continue;
    }

    $path = __DIR__ . '/../' . ltrim($img, '/');
    if (!file_exists($path)) {
        $missingOrBroken[] = ['id' => $r['id'], 'title' => $r['title'], 'reason' => 'File does not exist: ' . $path, 'img' => $img];
        continue;
    }

    $size = filesize($path);
    if ($size < 1000) {
        $content = file_get_contents($path, false, null, 0, 50);
        $tinyFiles[] = ['id' => $r['id'], 'title' => $r['title'], 'reason' => "Tiny file ({$size} bytes): " . $content, 'img' => $img];
        $missingOrBroken[] = ['id' => $r['id'], 'title' => $r['title'], 'reason' => "Corrupt/404 file ({$size} bytes)", 'img' => $img];
    }
}

echo "Total recipes checked: " . count($recipes) . "\n";
echo "Total missing or broken: " . count($missingOrBroken) . "\n";

foreach ($missingOrBroken as $item) {
    echo "ID: {$item['id']} | Title: {$item['title']} | Reason: {$item['reason']} | Image: {$item['img']}\n";
}

