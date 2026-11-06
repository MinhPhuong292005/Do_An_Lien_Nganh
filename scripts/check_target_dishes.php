<?php
$files = [
    'assets/images/ingredients/thit-bo.jpg',
    'assets/images/ingredients/oc.jpg',
    'assets/images/ingredients/dau-hu.jpg',
    'assets/images/real_dishes/com-trang.jpg',
    'assets/images/real_dishes/mang-chua.jpg',
    'assets/images/real_dishes/ca-tram-kho-rieng.jpg',
    'assets/images/real_dishes/thit-ba-chi-luoc.jpg',
    'assets/images/real_dishes/sot-trung-muoi.jpg',
    'assets/images/real_dishes/muop-dang-hap-thit.jpg',
    'assets/images/real_dishes/mo-heo-top-mo.jpg',
    'assets/images/real_dishes/bun-rieu.jpg',
    'assets/images/real_dishes/mien-tron.jpg',
    'assets/images/real_dishes/canh-ngheu.jpg',
];

$wsDir = 'c:/Users/Admin/Downloads/Do_An_Lien_Nganh-main/Do_An_Lien_Nganh-main';
$odDir = 'C:/Users/Admin/OneDrive/Đồ án liên ngành/Do_An_Lien_Nganh-main';

foreach ($files as $f) {
    $wsPath = "$wsDir/$f";
    $odPath = "$odDir/$f";
    $wsOk = file_exists($wsPath) ? filesize($wsPath) . ' B' : 'MISSING';
    $odOk = file_exists($odPath) ? filesize($odPath) . ' B' : 'MISSING';
    echo sprintf("%-45s | WS: %-10s | OD: %-10s\n", $f, $wsOk, $odOk);
}
