<?php
$pdo = new PDO('mysql:host=db;dbname=cookio_db;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Direct IDs
$directMaps = [
    15 => 'assets/images/real_dishes/gia-do-xao-long-me.jpg',
    17 => 'assets/images/real_dishes/nam-dui-ga-xao-dau-hao.jpg',
    18 => 'assets/images/real_dishes/mang-tay-xao-tom.jpg',
    20 => 'assets/images/real_dishes/canh-chua-tom-dua.jpg',
    33 => 'assets/images/real_dishes/canh-rong-bien-dau-hu.jpg',
    42 => 'assets/images/real_dishes/ca-thu-kho-ca-chua.jpg',
    50 => 'assets/images/real_dishes/thit-kho-cui-dua.jpg',
    54 => 'assets/images/real_dishes/thit-kho-tieu-xanh.jpg',
    76 => 'assets/images/real_dishes/muc-ong-hap-gung-sa.jpg',
    77 => 'assets/images/real_dishes/tom-su-hap-nuoc-dua.jpg',
    78 => 'assets/images/real_dishes/trung-hap-van-hoa.jpg',
    83 => 'assets/images/real_dishes/muop-dang-hap-thit.jpg',
    84 => 'assets/images/real_dishes/nam-dong-co-hap-gio-song.jpg',
    88 => 'assets/images/real_dishes/nam-dui-ga-kho-tieu.jpg',
    89 => 'assets/images/real_dishes/nem-chay-ran-gion.jpg',
    90 => 'assets/images/real_dishes/canh-nam-hat-sen-chay.jpg',
    91 => 'assets/images/real_dishes/canh-chua-nam-chay.jpg',
    92 => 'assets/images/real_dishes/rau-cu-kho-quet-chay.jpg',
    94 => 'assets/images/real_dishes/salad-bo-dua-leo-chay.jpg',
    95 => 'assets/images/real_dishes/ca-tim-nuong-mo-hanh.jpg',
    97 => 'assets/images/real_dishes/cha-lua-chay-kho-tieu.jpg',
    98 => 'assets/images/real_dishes/cha-nam-chien-com.jpg',
    99 => 'assets/images/real_dishes/sup-bi-do-chay.jpg',
    100 => 'assets/images/real_dishes/com-chien-hat-sen-chay.jpg',
    1986 => 'assets/images/real_dishes/bun-bo-hue.jpg',
];

foreach ($directMaps as $id => $img) {
    $stmt = $pdo->prepare('UPDATE recipes SET image_url = ? WHERE id = ?');
    $stmt->execute([$img, $id]);
}

// 2. Bun bo
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/bun-bo-hue.jpg' WHERE title LIKE '%Bún bò%' OR title LIKE '%bún bò%'");

// 3. Bo sot vang
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/bo-sot-vang.jpg' WHERE title LIKE '%Bò sốt vang%' OR title LIKE '%bò sốt vang%'");

// 4. Bo Wellington / bit tet
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/bit-tet-bo.jpg' WHERE (title LIKE '%Bò Wellington%' OR title LIKE '%bít tết bò%' OR title LIKE '%Bò sốt tiêu đen%')");

// 5. Bo xao / thit bo
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/bo-luc-lac.jpg' WHERE (title LIKE '%thịt bò%' OR title LIKE '%Bò xào%') AND image_url NOT LIKE '%bo-sot-vang%' AND image_url NOT LIKE '%pho%' AND image_url NOT LIKE '%bun-bo%'");

// 6. Pork dishes previously mapped to bo-xao-toi
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/thit-heo-xao-chua-ngot.jpg' WHERE (title LIKE '%heo%' OR title LIKE '%lợn%') AND image_url = 'assets/images/real_dishes/bo-xao-toi.jpg'");

// 7. Grilled shrimp
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/tom-nuong.jpg' WHERE (title LIKE '%Tôm%nướng%' OR title LIKE '%tôm%nướng%')");

// 8. Grilled squid
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/muc-nuong.jpg' WHERE (title LIKE '%Mực%nướng%' OR title LIKE '%mực%nướng%' OR title LIKE '%Bạch tuộc%nướng%')");

// 9. Lau bo
$pdo->exec("UPDATE recipes SET image_url = 'assets/images/real_dishes/lau-bo.jpg' WHERE title LIKE '%Lẩu%bò%' OR title LIKE '%lẩu%bò%'");

echo "Database dish images updated successfully!\n";