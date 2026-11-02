<?php

declare(strict_types=1);

$files = glob(__DIR__ . '/../assets/images/real_dishes/*.jpg');
foreach ($files as $f) {
    $sz = @getimagesize($f);
    if (!$sz) {
        echo 'INVALID: ' . basename($f) . ' (' . filesize($f) . " bytes)\n";
    } else {
        $w = $sz[0];
        $h = $sz[1];
        if ($h > $w * 1.2) {
            echo 'VERTICAL (possible portrait): ' . basename($f) . ' (' . $w . 'x' . $h . ")\n";
        }
    }
}

