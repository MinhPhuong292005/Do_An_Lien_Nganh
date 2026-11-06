<?php
$html = file_get_contents('http://localhost:8000/index.php');
preg_match_all('/class="hero-flat-slide"[^>]*>.*?<img src="([^"]+)"/s', $html, $matches);
echo "Total banner slides: " . count($matches[1]) . "\n";
$unique = array_unique($matches[1]);
echo "Unique images: " . count($unique) . "\n";
foreach ($matches[1] as $idx => $img) {
    echo ($idx + 1) . ". " . $img . "\n";
}
