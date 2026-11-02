<?php

declare(strict_types=1);

$f1 = __DIR__ . '/../assets/images/real_dishes/canh-suon-nau-sau.jpg';
$f2 = __DIR__ . '/../assets/images/real_dishes/nem-ran-truyen-thong.jpg';

echo "f1: " . md5_file($f1) . " (" . filesize($f1) . ")\n";
echo "f2: " . md5_file($f2) . " (" . filesize($f2) . ")\n";

