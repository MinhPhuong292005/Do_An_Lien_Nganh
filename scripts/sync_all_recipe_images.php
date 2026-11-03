<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = db();

function findBestMatch(string $title, string $cat): string {
    $t = mb_strtolower($title, 'UTF-8');
    $c = mb_strtolower($cat, 'UTF-8');

    // 1. Specific dish titles
    if (str_contains($t, 'cá hồi')) return 'assets/images/real_dishes/ca-hoi.jpg';
    if (str_contains($t, 'gà nướng')) return 'assets/images/real_dishes/ga-nuong.jpg';
    if (str_contains($t, 'heo quay') || str_contains($t, 'thịt quay') || str_contains($t, 'giòn bì')) return 'assets/images/real_dishes/thit-quay-gion-bi.jpg';
    if (str_contains($t, 'bò sốt vang') || str_contains($t, 'sốt vang')) return 'assets/images/real_dishes/bo-sot-vang.jpg';
    if (str_contains($t, 'bún cá')) return 'assets/images/real_dishes/bun-ca.jpg';
    if (str_contains($t, 'bánh khọt')) return 'assets/images/real_dishes/banh-khot.jpg';
    if (str_contains($t, 'mì xào giòn')) return 'assets/images/real_dishes/mi-xao-gion.jpg';
    if (str_contains($t, 'ramen')) return 'assets/images/real_dishes/mi-ramen.jpg';
    if (str_contains($t, 'miến trộn')) return 'assets/images/real_dishes/mien-tron.jpg';
    if (str_contains($t, 'sườn cừu')) return 'assets/images/real_dishes/suon-cuu-nuong.jpg';
    if (str_contains($t, 'sườn non kho trứng cút')) return 'assets/images/real_dishes/suon-kho-trung-cut.jpg';
    if (str_contains($t, 'cải chua') || str_contains($t, 'lòng xào')) return 'assets/images/real_dishes/long-xao-cai-chua.jpg';
    if (str_contains($t, 'lòng heo xào')) return 'assets/images/real_dishes/long-heo-xao-chua-ngot.jpg';
    if (str_contains($t, 'nộm hoa chuối')) return 'assets/images/real_dishes/nom-hoa-chuoi.jpg';
    if (str_contains($t, 'nạc vai heo nướng') || (str_contains($t, 'thịt') && str_contains($t, 'nướng'))) return 'assets/images/real_dishes/thit-heo-nuong.jpg';
    if (str_contains($t, 'nghêu') || str_contains($t, 'ngao')) return 'assets/images/real_dishes/canh-ngheu.jpg';
    if (str_contains($t, 'pizza')) return 'assets/images/real_dishes/pizza-hai-san.jpg';
    if (str_contains($t, 'spaghetti') || str_contains($t, 'mì ý') || str_contains($t, 'pasta')) return 'assets/images/real_dishes/my-y-spaghetti.jpg';
    if (str_contains($t, 'bít tết') || str_contains($t, 'steak')) return 'assets/images/real_dishes/bit-tet-bo.jpg';
    if (str_contains($t, 'burger') || str_contains($t, 'hamburger')) return 'assets/images/real_dishes/hamburger-bo.jpg';
    if (str_contains($t, 'lẩu')) return 'assets/images/real_dishes/lau-hai-san.jpg';
    if (str_contains($t, 'bánh mì chảo')) return 'assets/images/real_dishes/banh-mi-chao.jpg';
    if (str_contains($t, 'bánh mì')) return 'assets/images/real_dishes/banh-mi-viet-nam.jpg';
    if (str_contains($t, 'bánh bông lan') || str_contains($t, 'bánh kem') || str_contains($t, 'bánh phô mai') || str_contains($t, 'bánh ngọt')) return 'assets/images/real_dishes/banh-bong-lan-pho-mai.jpg';
    if (str_contains($t, 'bánh xèo')) return 'assets/images/real_dishes/banh-xeo-mien-tay.jpg';
    if (str_contains($t, 'phở bò') || str_contains($t, 'phở')) return 'assets/images/real_dishes/pho-bo.jpg';
    if (str_contains($t, 'bún chả')) return 'assets/images/real_dishes/bun-cha-ha-noi.jpg';
    if (str_contains($t, 'bún xào')) return 'assets/images/real_dishes/bun-xao-chay.jpg';
    if (str_contains($t, 'bún')) return 'assets/images/real_dishes/bun-ca.jpg';
    if (str_contains($t, 'miến xào')) return 'assets/images/real_dishes/mien-xao-long-ga.jpg';
    if (str_contains($t, 'mì xào')) return 'assets/images/real_dishes/mi-xao-gion.jpg';

    // 2. Beef dishes
    if (str_contains($t, 'bò né') || str_contains($t, 'thiên lý')) return 'assets/images/real_dishes/bo-ne-thien-ly.jpg';
    if (str_contains($t, 'bò xào hành tây')) return 'assets/images/real_dishes/bo-xao-hanh-tay.jpg';
    if (str_contains($t, 'bò xào tỏi')) return 'assets/images/real_dishes/bo-xao-toi.jpg';
    if (str_contains($t, 'bò kho')) return 'assets/images/real_dishes/bo-kho-gung-sa.jpg';
    if (str_contains($t, 'bắp bò') || (str_contains($t, 'bò') && str_contains($t, 'hấp'))) return 'assets/images/real_dishes/bap-bo-hap-sa-gung.jpg';
    if (str_contains($t, 'nộm bò') || str_contains($t, 'gỏi bò')) return 'assets/images/real_dishes/nom-bo-thai.jpg';
    if (str_contains($t, 'canh thịt bò') || str_contains($t, 'canh bò')) return 'assets/images/real_dishes/canh-bo-rau-xanh.jpg';
    if (str_contains($t, 'su su xào thịt bò')) return 'assets/images/real_dishes/su-su-xao-thit-bo.jpg';
    if (str_contains($t, 'bò') && (str_contains($t, 'xào') || str_contains($c, 'xào'))) return 'assets/images/real_dishes/bo-xao-toi.jpg';

    // 3. Chicken dishes
    if (str_contains($t, 'gà hấp lá chanh')) return 'assets/images/real_dishes/ga-hap-la-chanh.jpg';
    if (str_contains($t, 'gà hấp hành')) return 'assets/images/real_dishes/ga-hap-hanh.jpg';
    if (str_contains($t, 'gà hấp')) return 'assets/images/real_dishes/ga-hap-muoi-hot.jpg';
    if (str_contains($t, 'gà kho gừng')) return 'assets/images/real_dishes/ga-kho-gung.jpg';
    if (str_contains($t, 'gà kho nấm') || str_contains($t, 'gà kho')) return 'assets/images/real_dishes/ga-kho-nam-dong-co.jpg';
    if (str_contains($t, 'gà xào sả ớt')) return 'assets/images/real_dishes/ga-xao-sa-ot.jpg';
    if (str_contains($t, 'gà xào nấm')) return 'assets/images/real_dishes/ga-xao-nam-huong.jpg';
    if (str_contains($t, 'gà xào')) return 'assets/images/real_dishes/ga-xao-hat-dieu.jpg';
    if (str_contains($t, 'cơm chiên gà')) return 'assets/images/real_dishes/com-chien-ga.jpg';
    if (str_contains($t, 'súp gà')) return 'assets/images/real_dishes/sup-ga-be.jpg';
    if (str_contains($t, 'ức gà')) return 'assets/images/real_dishes/uc-ga-teriyaki.jpg';
    if (str_contains($t, 'cánh gà') || (str_contains($t, 'gà') && str_contains($t, 'chiên'))) return 'assets/images/real_dishes/canh-ga-chien-mam.jpg';
    if (str_contains($t, 'salad') && str_contains($t, 'gà')) return 'assets/images/real_dishes/salad-uc-ga.jpg';
    if (str_contains($t, 'gà')) {
        if (str_contains($c, 'hấp')) return 'assets/images/real_dishes/ga-hap-la-chanh.jpg';
        if (str_contains($c, 'kho')) return 'assets/images/real_dishes/ga-kho-gung.jpg';
        if (str_contains($c, 'chiên')) return 'assets/images/real_dishes/canh-ga-chien-mam.jpg';
        return 'assets/images/real_dishes/ga-xao-sa-ot.jpg';
    }

    // 4. Pork & Ribs
    if (str_contains($t, 'sườn xào chua ngọt')) return 'assets/images/real_dishes/suon-xao-chua-ngot.jpg';
    if (str_contains($t, 'sườn ram')) return 'assets/images/real_dishes/suon-ram-man-ngot.jpg';
    if (str_contains($t, 'canh bí đỏ hầm sườn')) return 'assets/images/real_dishes/canh-bi-do-ham-suon.jpg';
    if (str_contains($t, 'canh sườn')) return 'assets/images/real_dishes/canh-suon-cu-sen.jpg';
    if (str_contains($t, 'sườn')) return 'assets/images/real_dishes/suon-ram-man-ngot.jpg';

    if (str_contains($t, 'thịt kho tàu')) return 'assets/images/real_dishes/thit-kho-tau.jpg';
    if (str_contains($t, 'thịt kho cùi dừa')) return 'assets/images/real_dishes/thit-kho-cui-dua.jpg';
    if (str_contains($t, 'kho quẹt')) return 'assets/images/real_dishes/thit-kho-quet-tom-kho.jpg';
    if (str_contains($t, 'thịt kho tiêu')) return 'assets/images/real_dishes/thit-kho-tieu-xanh.jpg';
    if (str_contains($t, 'thịt chiên xù') || str_contains($t, 'thịt chiên')) return 'assets/images/real_dishes/thit-chien-xu.jpg';
    if (str_contains($t, 'măng tươi kho thịt')) return 'assets/images/real_dishes/mang-tuoi-kho-thit.jpg';
    if (str_contains($t, 'bắp cải cuộn thịt')) return 'assets/images/real_dishes/bap-cai-cuon-thit-hap.jpg';
    if (str_contains($t, 'lòng heo xào chua ngọt')) return 'assets/images/real_dishes/long-heo-xao-chua-ngot.jpg';
    if (str_contains($t, 'lòng mề')) return 'assets/images/real_dishes/gia-do-xao-long-me.jpg';

    // 5. Fish & Seafood
    if (str_contains($t, 'cá bống kho')) return 'assets/images/real_dishes/ca-bong-kho-tieu.jpg';
    if (str_contains($t, 'cá lóc kho')) return 'assets/images/real_dishes/ca-loc-kho-to.jpg';
    if (str_contains($t, 'cá cơm kho')) return 'assets/images/real_dishes/ca-com-kho-keo.jpg';
    if (str_contains($t, 'cá thu kho')) return 'assets/images/real_dishes/ca-thu-kho-ca-chua.jpg';
    if (str_contains($t, 'cá trắm kho')) return 'assets/images/real_dishes/ca-tram-kho-rieng.jpg';
    if (str_contains($t, 'cá chép hấp')) return 'assets/images/real_dishes/ca-chep-hap-bia.jpg';
    if (str_contains($t, 'cá diêu hồng') || str_contains($t, 'cá điêu hồng')) return 'assets/images/real_dishes/ca-dieu-hong-hap-xi-dau.jpg';
    if (str_contains($t, 'cá tai tượng') || (str_contains($t, 'cá') && str_contains($t, 'chiên'))) return 'assets/images/real_dishes/ca-tai-tuong-chien-xu.jpg';
    if (str_contains($t, 'canh chua cá')) return 'assets/images/real_dishes/canh-chua-ca-loc.jpg';
    if (str_contains($t, 'cá rô đồng')) return 'assets/images/real_dishes/canh-cai-ca-ro-dong.jpg';
    if (str_contains($t, 'cá')) {
        if (str_contains($c, 'kho')) return 'assets/images/real_dishes/ca-loc-kho-to.jpg';
        if (str_contains($c, 'hấp')) return 'assets/images/real_dishes/ca-dieu-hong-hap-xi-dau.jpg';
        return 'assets/images/real_dishes/ca-thu-kho-ca-chua.jpg';
    }

    // 6. Shrimp
    if (str_contains($t, 'tôm rim') || str_contains($t, 'tôm kho')) return 'assets/images/real_dishes/tom-rim-ba-chi.jpg';
    if (str_contains($t, 'tôm hấp') || str_contains($t, 'tôm luộc')) return 'assets/images/real_dishes/tom-su-hap-nuoc-dua.jpg';
    if (str_contains($t, 'tôm chiên')) return 'assets/images/real_dishes/tom-chien-hoang-bao.jpg';
    if (str_contains($t, 'tôm xào')) return 'assets/images/real_dishes/tom-xao-bong-cai.jpg';
    if (str_contains($t, 'canh') && str_contains($t, 'tôm')) return 'assets/images/real_dishes/canh-bau-nau-tom.jpg';
    if (str_contains($t, 'tôm')) return 'assets/images/real_dishes/tom-rim-ba-chi.jpg';

    // 7. Squid / Snails / Shellfish
    if (str_contains($t, 'mực xào')) return 'assets/images/real_dishes/muc-xao-sa-te.jpg';
    if (str_contains($t, 'mực chiên')) return 'assets/images/real_dishes/muc-chien-nuoc-mam.jpg';
    if (str_contains($t, 'mực hấp')) return 'assets/images/real_dishes/muc-ong-hap-gung-sa.jpg';
    if (str_contains($t, 'mực')) return 'assets/images/real_dishes/muc-xao-sa-te.jpg';
    if (str_contains($t, 'ốc hương')) return 'assets/images/real_dishes/oc-huong-xao-bo-toi.jpg';
    if (str_contains($t, 'ốc')) return 'assets/images/real_dishes/oc-buou-nhoi-thit.jpg';

    // 8. Eggs & Tofu
    if (str_contains($t, 'trứng cuộn')) return 'assets/images/real_dishes/trung-cuon-xuc-xich.jpg';
    if (str_contains($t, 'trứng cút rim')) return 'assets/images/real_dishes/trung-cut-rim-mam-toi.jpg';
    if (str_contains($t, 'trứng hấp')) return 'assets/images/real_dishes/trung-hap-chawanmushi.jpg';
    if (str_contains($t, 'canh cà chua trứng')) return 'assets/images/real_dishes/canh-ca-chua-trung.jpg';
    if (str_contains($t, 'trứng')) return 'assets/images/real_dishes/trung-chien-ca-chua.jpg';

    if (str_contains($t, 'đậu hũ non') || str_contains($t, 'đậu phụ non')) return 'assets/images/real_dishes/dau-hu-non-sot-nam.jpg';
    if (str_contains($t, 'đậu phụ sốt cà chua') || str_contains($t, 'đậu hũ sốt cà')) return 'assets/images/real_dishes/dau-phu-sot-ca-chua.jpg';
    if (str_contains($t, 'đậu phụ tẩm hành')) return 'assets/images/real_dishes/dau-phu-tam-hanh.jpg';
    if (str_contains($t, 'đậu phụ chiên') || str_contains($t, 'đậu hũ chiên')) return 'assets/images/real_dishes/dau-phu-chien-gion.jpg';
    if (str_contains($t, 'đậu')) return 'assets/images/real_dishes/dau-phu-sot-ca-chua.jpg';

    // 9. Vegetables & Soup
    if (str_contains($t, 'rau muống')) return 'assets/images/real_dishes/rau-muong-xao-toi.jpg';
    if (str_contains($t, 'rau bí')) return 'assets/images/real_dishes/rau-bi-xao-toi.jpg';
    if (str_contains($t, 'khổ qua') || str_contains($t, 'mướp đắng')) return 'assets/images/real_dishes/canh-kho-qua-nhoi-thit.jpg';
    if (str_contains($t, 'canh nấm')) return 'assets/images/real_dishes/canh-nam-hat-sen.jpg';
    if (str_contains($t, 'canh bóng')) return 'assets/images/real_dishes/canh-bong-thap-cam.jpg';
    if (str_contains($t, 'canh rong biển')) return 'assets/images/real_dishes/canh-rong-bien-dau-hu.jpg';
    if (str_contains($t, 'nấm')) return 'assets/images/real_dishes/nam-dui-ga-xao-dau-hao.jpg';
    if (str_contains($t, 'chả giò') || str_contains($t, 'nem rán')) return 'assets/images/real_dishes/cha-gio-re-hai-san.jpg';
    if (str_contains($t, 'nem chay')) return 'assets/images/real_dishes/nem-chay-ran-gion.jpg';
    if (str_contains($t, 'cơm chiên')) return 'assets/images/real_dishes/com-chien-dua-bo.jpg';
    if (str_contains($t, 'xôi') || str_contains($t, 'hạt sen')) return 'assets/images/real_dishes/xoi-vo-hat-sen.jpg';
    if (str_contains($t, 'salad') || str_contains($t, 'nộm') || str_contains($t, 'gỏi')) return 'assets/images/real_dishes/salad-rau-cu.jpg';

    // Fallbacks based on category
    if (str_contains($c, 'âu') || str_contains($c, 'mỹ')) return 'assets/images/real_dishes/bit-tet-bo.jpg';
    if (str_contains($c, 'bún') || str_contains($c, 'phở')) return 'assets/images/real_dishes/pho-bo.jpg';
    if (str_contains($c, 'bánh')) return 'assets/images/real_dishes/banh-bong-lan-pho-mai.jpg';
    if (str_contains($c, 'nướng')) return 'assets/images/real_dishes/thit-heo-nuong.jpg';
    if (str_contains($c, 'chiên')) return 'assets/images/real_dishes/thit-chien-xu.jpg';
    if (str_contains($c, 'kho')) return 'assets/images/real_dishes/thit-kho-tau.jpg';
    if (str_contains($c, 'hấp')) return 'assets/images/real_dishes/ga-hap-la-chanh.jpg';
    if (str_contains($c, 'canh')) return 'assets/images/real_dishes/canh-suon-cu-sen.jpg';
    if (str_contains($c, 'xào')) return 'assets/images/real_dishes/bo-xao-toi.jpg';
    if (str_contains($c, 'chay')) return 'assets/images/real_dishes/dau-hu-non-sot-nam.jpg';
    if (str_contains($c, 'lẩu')) return 'assets/images/real_dishes/lau-hai-san.jpg';

    return 'assets/images/real_dishes/hero-dish-1.jpg';
}

// 1. Update recipes
$recipes = $pdo->query("SELECT id, title, category, image_url FROM recipes")->fetchAll();
$updateStmt = $pdo->prepare("UPDATE recipes SET image_url = ? WHERE id = ?");
$updatedR = 0;
foreach ($recipes as $r) {
    $correctImg = findBestMatch($r['title'], $r['category'] ?? '');
    if ($correctImg !== $r['image_url']) {
        $updateStmt->execute([$correctImg, $r['id']]);
        $updatedR++;
    }
}
echo "Updated $updatedR recipes with synchronized realistic images.\n";

// 2. Update kitchen_tips
$tips = $pdo->query("SELECT id, title, category, image_url FROM kitchen_tips")->fetchAll();
$updateTipStmt = $pdo->prepare("UPDATE kitchen_tips SET image_url = ? WHERE id = ?");
$updatedT = 0;
foreach ($tips as $t) {
    $correctImg = findBestMatch($t['title'], $t['category'] ?? '');
    if ($correctImg !== $t['image_url']) {
        $updateTipStmt->execute([$correctImg, $t['id']]);
        $updatedT++;
    }
}
echo "Updated $updatedT kitchen tips with synchronized realistic images.\n";
