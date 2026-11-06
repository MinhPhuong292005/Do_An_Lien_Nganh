<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = db();

echo "Starting Master Dish Image Mapping...\n";

$prefixMappings = [
    // 1. Specific Bánh
    'Bánh khọt%' => 'assets/images/real_dishes/banh-khot.jpg',
    'Bánh xèo%' => 'assets/images/real_dishes/banh-xeo-mien-tay.jpg',
    'Bánh cuốn%' => 'assets/images/real_dishes/banh-cuon.jpg',
    'Bánh bao%' => 'assets/images/real_dishes/banh-bao.jpg',
    'Bánh bèo%' => 'assets/images/real_dishes/banh-beo.jpg',
    'Bánh bột lọc%' => 'assets/images/real_dishes/banh-bot-loc.jpg',
    'Bánh nậm%' => 'assets/images/real_dishes/banh-bot-loc.jpg',
    'Bánh chưng%' => 'assets/images/real_dishes/banh-chung.jpg',
    'Bánh tét%' => 'assets/images/real_dishes/banh-chung.jpg',
    'Bánh mì Việt Nam%' => 'assets/images/real_dishes/banh-mi-viet-nam.jpg',
    'Bánh mì chảo%' => 'assets/images/real_dishes/banh-mi-chao.jpg',
    'Bánh bông lan%' => 'assets/images/real_dishes/banh-bong-lan-pho-mai.jpg',
    'Bánh flan%' => 'assets/images/real_dishes/banh-flan.jpg',
    'Bánh tart trứng%' => 'assets/images/real_dishes/banh-flan.jpg',
    'Bánh muffin%' => 'assets/images/real_dishes/banh-muffin.jpg',
    'Bánh brownie%' => 'assets/images/real_dishes/banh-muffin.jpg',
    'Bánh chuối nướng%' => 'assets/images/real_dishes/chuoi-nep-nuong.jpg',
    'Bánh da lợn%' => 'assets/images/real_dishes/banh-da-lon.jpg',
    'Bánh giò%' => 'assets/images/real_dishes/banh-gio.jpg',
    'Bánh rán lúc lắc%' => 'assets/images/real_dishes/banh-ran-luc-lac.jpg',
    'Pizza%' => 'assets/images/real_dishes/pizza-hai-san.jpg',

    // 2. Noodles & Soups
    'Phở bò%' => 'assets/images/real_dishes/pho-bo.jpg',
    'Phở gà%' => 'assets/images/real_dishes/pho-ga.jpg',
    'Bún bò Huế%' => 'assets/images/real_dishes/bun-bo-hue.jpg',
    'Bún chả%' => 'assets/images/real_dishes/bun-cha-ha-noi.jpg',
    'Bún riêu%' => 'assets/images/real_dishes/bun-rieu.jpg',
    'Bún cá%' => 'assets/images/real_dishes/bun-ca.jpg',
    'Mì Quảng%' => 'assets/images/real_dishes/mi-quang.jpg',
    'Miến lươn%' => 'assets/images/real_dishes/mien-luon.jpg',
    'Mì vịt tiềm%' => 'assets/images/real_dishes/mi-vit-tiem.jpg',
    'Mì Udon%' => 'assets/images/real_dishes/mi-udon.jpg',
    'Mì Ramen%' => 'assets/images/real_dishes/mi-ramen.jpg',
    'Mì Ý%' => 'assets/images/real_dishes/spaghetti.jpg',
    'Mì ? Spaghetti%' => 'assets/images/real_dishes/spaghetti.jpg',
    'Miến xào%' => 'assets/images/real_dishes/mien-xao-long-ga.jpg',
    'Miến trộn%' => 'assets/images/real_dishes/mien-tron.jpg',

    // 3. Western Dishes
    'Burger bò%' => 'assets/images/real_dishes/hamburger-bo.jpg',
    'Sườn cừu%' => 'assets/images/real_dishes/suon-cuu-nuong.jpg',
    'Sườn heo BBQ%' => 'assets/images/real_dishes/suon-heo-bbq.jpg',
    'Bò Beefsteak%' => 'assets/images/real_dishes/bit-tet-bo.jpg',
    'Bò Wellington%' => 'assets/images/real_dishes/bo-wellington.jpg',
    'Lasagna%' => 'assets/images/real_dishes/lasagna.jpg',
    'Mac and Cheese%' => 'assets/images/real_dishes/mac-and-cheese.jpg',
    'Súp hành tây%' => 'assets/images/real_dishes/sup-hanh-tay.jpg',
    'Súp kem bí%' => 'assets/images/real_dishes/sup-bi-do-chay.jpg',
    'Súp kem nấm%' => 'assets/images/real_dishes/sup-kem-nam.jpg',
    'Xúc xích Đức%' => 'assets/images/real_dishes/xuc-xich-duc.jpg',
    'Salad Caesar%' => 'assets/images/real_dishes/salad-caesar.jpg',
    'Salad bơ%' => 'assets/images/real_dishes/salad-bo-dua-leo-chay.jpg',

    // 4. Seafood & Fish
    'Nghêu%' => 'assets/images/real_dishes/canh-ngheu.jpg',
    'Ngao%' => 'assets/images/real_dishes/canh-ngheu.jpg',
    'Canh chua cá lóc%' => 'assets/images/real_dishes/canh-chua-ca-loc.jpg',
    'Canh chua tôm%' => 'assets/images/real_dishes/canh-chua-tom-dua.jpg',
    'Hàu%' => 'assets/images/real_dishes/hau-nuong.jpg',
    'Mực%' => 'assets/images/real_dishes/muc-xao-sa-te.jpg',
    'Bạch tuộc%' => 'assets/images/real_dishes/muc-xao-sa-te.jpg',
    'Cá hồi%' => 'assets/images/real_dishes/ca-hoi.jpg',
    'Cá điêu hồng%' => 'assets/images/real_dishes/ca-dieu-hong-hap-xi-dau.jpg',
    'Cá tai tượng%' => 'assets/images/real_dishes/ca-tai-tuong-chien-xu.jpg',
    'Cá bống%' => 'assets/images/real_dishes/ca-bong-kho-tieu.jpg',
    'Cá cơm%' => 'assets/images/real_dishes/ca-com-kho-keo.jpg',
    'Cá chép%' => 'assets/images/real_dishes/ca-chep-hap-bia.jpg',
    'Cá lóc kho%' => 'assets/images/real_dishes/ca-loc-kho-to.jpg',
    'Cá thu kho%' => 'assets/images/real_dishes/ca-thu-kho-ca-chua.jpg',
    'Cua biển%' => 'assets/images/real_dishes/cua-bien.jpg',
    'Cua%' => 'assets/images/real_dishes/cua-bien.jpg',
    'Tôm nướng%' => 'assets/images/real_dishes/tom-nuong.jpg',
    'Tôm%' => 'assets/images/real_dishes/tom-rim-ba-chi.jpg',
    'Ốc bươu%' => 'assets/images/real_dishes/oc-buou-nhoi-thit.jpg',
    'Ốc hương%' => 'assets/images/real_dishes/oc-huong-xao-bo-toi.jpg',

    // 5. Beef, Pork, Poultry
    'Bò sốt vang%' => 'assets/images/real_dishes/bo-sot-vang.jpg',
    'Bò lúc lắc%' => 'assets/images/real_dishes/bo-luc-lac.jpg',
    'Bò né%' => 'assets/images/real_dishes/bo-ne-thien-ly.jpg',
    'Bắp bò%' => 'assets/images/real_dishes/bap-bo-hap-sa-gung.jpg',
    'Bò xào%' => 'assets/images/real_dishes/bo-xao-toi.jpg',
    'Thịt ba chỉ kho tàu%' => 'assets/images/real_dishes/thit-kho-tau.jpg',
    'Thịt ba chỉ nướng%' => 'assets/images/real_dishes/thit-heo-nuong.jpg',
    'Thịt ba chỉ quay%' => 'assets/images/real_dishes/thit-heo-quay.jpg',
    'Thịt nạc vai heo nướng%' => 'assets/images/real_dishes/thit-heo-nuong.jpg',
    'Thịt heo xào chua ngọt%' => 'assets/images/real_dishes/thit-heo-xao-chua-ngot.jpg',
    'Thịt kho tàu%' => 'assets/images/real_dishes/thit-kho-tau.jpg',
    'Sườn ram%' => 'assets/images/real_dishes/suon-ram-man-ngot.jpg',
    'Sườn xào chua ngọt%' => 'assets/images/real_dishes/suon-xao-chua-ngot.jpg',
    'Gà hấp lá chanh%' => 'assets/images/real_dishes/ga-hap-la-chanh.jpg',
    'Gà hấp muối%' => 'assets/images/real_dishes/ga-hap-muoi-hot.jpg',
    'Gà hấp hành%' => 'assets/images/real_dishes/ga-hap-hanh.jpg',
    'Gà nướng%' => 'assets/images/real_dishes/ga-nuong.jpg',
    'Gà xào sả ớt%' => 'assets/images/real_dishes/ga-xao-sa-ot.jpg',
    'Gà xào hạt điều%' => 'assets/images/real_dishes/ga-xao-hat-dieu.jpg',
    'Gà xào nấm%' => 'assets/images/real_dishes/ga-xao-nam-huong.jpg',
    'Cánh gà chiên mắm%' => 'assets/images/real_dishes/canh-ga-chien-mam.jpg',
    'Chân gà%' => 'assets/images/real_dishes/chan-ga-sot-thai.jpg',
    'Nem rán%' => 'assets/images/real_dishes/nem-ran.jpg',
    'Chả giò%' => 'assets/images/real_dishes/nem-ran.jpg',

    // 6. Vegetarian & Tofu
    'Đậu hũ non sốt nấm%' => 'assets/images/real_dishes/dau-hu-non-sot-nam.jpg',
    'Đậu phụ chiên giòn%' => 'assets/images/real_dishes/dau-phu-chien-gion.jpg',
    'Đậu phụ sốt cà chua%' => 'assets/images/real_dishes/dau-phu-sot-ca-chua.jpg',
    'Đậu phụ tẩm hành%' => 'assets/images/real_dishes/dau-phu-tam-hanh.jpg',
    'Đậu phụ%' => 'assets/images/ingredients/dau-hu.jpg',
    'Đậu hũ%' => 'assets/images/ingredients/dau-hu.jpg',
    'Rau muống xào tỏi%' => 'assets/images/real_dishes/rau-muong-xao-toi.jpg',
    'Rau bí xào tỏi%' => 'assets/images/real_dishes/rau-bi-xao-toi.jpg',
    'Canh khổ qua%' => 'assets/images/real_dishes/canh-kho-qua-nhoi-thit.jpg',
    'Canh bắp cải cuộn thịt%' => 'assets/images/real_dishes/bap-cai-cuon-thit-hap.jpg',
    'Canh bí đỏ hầm sườn%' => 'assets/images/real_dishes/canh-bi-do-ham-suon.jpg',
    'Canh cà chua trứng%' => 'assets/images/real_dishes/canh-ca-chua-trung.jpg',
    'Canh sườn củ sen%' => 'assets/images/real_dishes/canh-suon-cu-sen.jpg',
    'Lẩu hải sản%' => 'assets/images/real_dishes/lau-hai-san.jpg',
    'Lẩu bò%' => 'assets/images/real_dishes/lau-bo.jpg',
    'Xôi vò%' => 'assets/images/real_dishes/xoi-vo-hat-sen.jpg',
    'Nộm hoa chuối%' => 'assets/images/real_dishes/nom-hoa-chuoi.jpg',
    'Nộm bò Thái%' => 'assets/images/real_dishes/nom-bo-thai.jpg',
    'Cơm chiên dưa bò%' => 'assets/images/real_dishes/com-chien-dua-bo.jpg',
    'Cơm chiên gà%' => 'assets/images/real_dishes/com-chien-ga.jpg',
    'Cơm chiên%' => 'assets/images/real_dishes/com-chien-dua-bo.jpg',
];

$stmt = $pdo->prepare("UPDATE recipes SET image_url = ? WHERE title LIKE ?");
$totalUpdated = 0;

foreach ($prefixMappings as $prefix => $img) {
    $stmt->execute([$img, $prefix]);
    $count = $stmt->rowCount();
    $totalUpdated += $count;
    echo "Pattern '{$prefix}': Updated {$count} recipes -> {$img}\n";
}

echo "Master mapping completed! Total affected recipe rows: {$totalUpdated}\n";
