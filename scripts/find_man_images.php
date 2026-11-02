<?php

declare(strict_types=1);

$targetMd5 = md5_file(__DIR__ . '/../assets/images/real_dishes/canh-suon-nau-sau.jpg');
echo "Target MD5: {$targetMd5}\n";

$matchingFiles = [];
foreach (glob(__DIR__ . '/../assets/images/real_dishes/*.jpg') as $f) {
    if (md5_file($f) === $targetMd5) {
        $matchingFiles[] = basename($f);
    }
}

echo "Matching images: " . implode(', ', $matchingFiles) . "\n";

