<?php

declare(strict_types=1);

$verticals = [
    'bap-bo-hap-sa-gung.jpg',
    'bo-kho-gung-sa.jpg',
    'bun-xao-chay.jpg',
    'canh-bau-nau-tom.jpg',
    'canh-bi-do-ham-suon.jpg',
    'canh-ca-chua-trung.jpg',
    'canh-cai-ca-ro-dong.jpg',
    'long-heo-xao-chua-ngot.jpg',
    'mang-tuoi-kho-thit.jpg',
    'nem-ran-truyen-thong.jpg',
    'nom-hoa-chuoi.jpg',
    'rau-bi-xao-toi.jpg',
    'rau-cu-kho-quet-chay.jpg',
    'salad-bo-dua-leo-chay.jpg',
    'sup-bi-do-chay.jpg',
    'thit-kho-quet-tom-kho.jpg',
];

foreach ($verticals as $v) {
    $path = __DIR__ . '/../assets/images/real_dishes/' . $v;
    $len = file_exists($path) ? filesize($path) : 0;
    echo "{$v}: {$len} bytes\n";
}

