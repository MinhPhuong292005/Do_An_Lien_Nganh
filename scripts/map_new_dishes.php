<?php
$pdo = new PDO('mysql:host=db;dbname=cookio_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rules = [
    'bun-rieu.jpg' => "title LIKE '%Bún riêu%' OR title LIKE '%bún riêu%'",
    'mi-quang.jpg' => "title LIKE '%Mì Quảng%' OR title LIKE '%mì quảng%'",
    'banh-cuon.jpg' => "title LIKE '%Bánh cuốn%' OR title LIKE '%bánh cuốn%'",
    'banh-bao.jpg' => "title LIKE '%Bánh bao%' OR title LIKE '%bánh bao%'",
    'banh-chung.jpg' => "title LIKE '%Bánh chưng%' OR title LIKE '%bánh chưng%' OR title LIKE '%Bánh tét%' OR title LIKE '%bánh tét%'",
    'banh-flan.jpg' => "title LIKE '%Bánh flan%' OR title LIKE '%bánh flan%' OR title LIKE '%caramel%'",
    'banh-beo.jpg' => "title LIKE '%Bánh bèo%' OR title LIKE '%bánh bèo%'",
    'banh-bot-loc.jpg' => "title LIKE '%Bánh bột lọc%' OR title LIKE '%bánh bột lọc%' OR title LIKE '%Bánh nậm%' OR title LIKE '%bánh nậm%'",
    'hau-nuong.jpg' => "title LIKE '%Hàu%' OR title LIKE '%hàu%' OR title LIKE '%Sò huyết%' OR title LIKE '%sò huyết%'",
    'cua-bien.jpg' => "title LIKE '%Cua biển%' OR title LIKE '%cua biển%' OR title LIKE '%Ghẹ xanh%' OR title LIKE '%ghẹ xanh%'",
    'mien-luon.jpg' => "title LIKE '%Miến lươn%' OR title LIKE '%miến lươn%'",
    'mi-vit-tiem.jpg' => "title LIKE '%Mì vịt tiềm%' OR title LIKE '%mì vịt tiềm%' OR title LIKE '%Miến măng vịt%' OR title LIKE '%măng vịt%'",
    'mi-udon.jpg' => "title LIKE '%Mì Udon%' OR title LIKE '%mì udon%'",
    'banh-muffin.jpg' => "title LIKE '%Bánh muffin%' OR title LIKE '%bánh muffin%'",
    'banh-crepe.jpg' => "title LIKE '%Bánh crepe%' OR title LIKE '%bánh crepe%'",
];

$totalUpdated = 0;
foreach ($rules as $img => $where) {
    $sql = "UPDATE recipes SET image_url = 'assets/images/real_dishes/$img' WHERE $where";
    $affected = $pdo->exec($sql);
    echo "Mapped $img: $affected recipes updated\n";
    $totalUpdated += $affected;
}

echo "Total recipes updated with authentic images: $totalUpdated\n";