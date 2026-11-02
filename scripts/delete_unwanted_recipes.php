<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

// 1. Files containing the photo of the man
$manFiles = [
    'assets/images/real_dishes/canh-suon-nau-sau.jpg',
    'assets/images/real_dishes/nem-ran-truyen-thong.jpg',
];

// 2. Corrupt / 404 / broken image files
$brokenFiles = [
    'assets/images/real_dishes/canh-cua-rau-day.jpg',
    'assets/images/real_dishes/canh-ga-chien-nuoc-mam.jpg',
];

$allBadFiles = array_merge($manFiles, $brokenFiles);

// Find all recipes that reference these files or have broken images
$allRecipes = db()->query("SELECT id, title, image_url FROM recipes")->fetchAll();
$idsToDelete = [];

foreach ($allRecipes as $r) {
    $img = trim((string)($r['image_url'] ?? ''));
    if ($img === '') {
        $idsToDelete[] = (int)$r['id'];
        echo "Deleting recipe #{$r['id']} ({$r['title']}): Empty image\n";
        continue;
    }

    $normalizedImg = str_replace('\\', '/', $img);
    if (in_array($normalizedImg, $allBadFiles, true)) {
        $idsToDelete[] = (int)$r['id'];
        echo "Deleting recipe #{$r['id']} ({$r['title']}): Bad image file ({$normalizedImg})\n";
        continue;
    }

    $filePath = __DIR__ . '/../' . ltrim($normalizedImg, '/');
    if (!file_exists($filePath) || filesize($filePath) < 1000) {
        $idsToDelete[] = (int)$r['id'];
        echo "Deleting recipe #{$r['id']} ({$r['title']}): Missing or tiny file ({$normalizedImg})\n";
        continue;
    }
}

$idsToDelete = array_values(array_unique($idsToDelete));
echo "Total recipes to delete: " . count($idsToDelete) . "\n";

if (!empty($idsToDelete)) {
    $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
    $stmtDel = db()->prepare("DELETE FROM recipes WHERE id IN ({$placeholders})");
    $stmtDel->execute($idsToDelete);
    echo "Successfully deleted " . count($idsToDelete) . " recipes from database!\n";
}

// 3. Delete bad image files from disk
foreach ($allBadFiles as $relPath) {
    $absPath = __DIR__ . '/../' . ltrim($relPath, '/');
    if (file_exists($absPath)) {
        unlink($absPath);
        echo "Deleted file from disk: {$relPath}\n";
    }
}

// 4. Verify recipes remaining
$remaining = db()->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
echo "Remaining valid recipes: {$remaining}\n";

