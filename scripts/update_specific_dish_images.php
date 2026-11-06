<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = db();

echo "1. Updating kitchen_tips table with real images...\n";

$tipUpdates = [
    3 => 'assets/images/real_dishes/gan-bo.jpg',
    5 => 'assets/images/real_dishes/thit-heo-quay.jpg',
    6 => 'assets/images/real_dishes/chan-ga-sot-thai.jpg',
    7 => 'assets/images/real_dishes/ga-luoc-cung.jpg',
    8 => 'assets/images/real_dishes/nuoc-mau.jpg',
    11 => 'assets/images/real_dishes/nem-ran.jpg',
    14 => 'assets/images/real_dishes/trung-long-dao.jpg',
    17 => 'assets/images/real_dishes/bao-quan-rau-thom.jpg',
];

$stmtTip = $pdo->prepare("UPDATE kitchen_tips SET image_url = ? WHERE id = ?");
foreach ($tipUpdates as $id => $img) {
    $stmtTip->execute([$img, $id]);
    echo " - Tip #{$id} -> {$img}\n";
}

echo "2. Updating recipes table with accurate dish images...\n";

$dishMappings = [
    'Miến trộn Hàn Quốc%' => 'assets/images/real_dishes/mien-tron.jpg',
    '%Nghêu%canh chua%' => 'assets/images/real_dishes/canh-ngheu.jpg',
    '%Nghêu bến tre%' => 'assets/images/real_dishes/canh-ngheu.jpg',
    '%ngao nấu canh chua%' => 'assets/images/real_dishes/canh-ngheu.jpg',
    '%Bún chả%' => 'assets/images/real_dishes/bun-cha-ha-noi.jpg',
    '%Nem rán%' => 'assets/images/real_dishes/nem-ran.jpg',
    '%Chả giò%' => 'assets/images/real_dishes/nem-ran.jpg',
    '%Canh chua cá lóc%' => 'assets/images/real_dishes/canh-chua-ca-loc.jpg',
    '%Gà luộc%' => 'assets/images/real_dishes/ga-luoc-cung.jpg',
    '%gà cúng%' => 'assets/images/real_dishes/ga-luoc-cung.jpg',
    '%Chân gà sốt Thái%' => 'assets/images/real_dishes/chan-ga-sot-thai.jpg',
    '%chân gà rút xương%' => 'assets/images/real_dishes/chan-ga-sot-thai.jpg',
    '%Gân bò%' => 'assets/images/real_dishes/gan-bo.jpg',
    '%gân bò hầm%' => 'assets/images/real_dishes/gan-bo.jpg',
    '%Thịt heo quay%' => 'assets/images/real_dishes/thit-heo-quay.jpg',
    '%heo quay giòn bì%' => 'assets/images/real_dishes/thit-heo-quay.jpg',
    '%Trứng lòng đào%' => 'assets/images/real_dishes/trung-long-dao.jpg',
];

$stmtRecipe = $pdo->prepare("UPDATE recipes SET image_url = ? WHERE title LIKE ?");
foreach ($dishMappings as $pattern => $img) {
    $stmtRecipe->execute([$img, $pattern]);
    $affected = $stmtRecipe->rowCount();
    echo " - Updated {$affected} recipes matching '{$pattern}' -> {$img}\n";
}

echo "Database updates complete!\n";
