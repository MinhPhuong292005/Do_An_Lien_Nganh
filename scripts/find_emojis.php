<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/var/www/html'));
$emojiRegex = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}]/u';
$found = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        if (strpos($path, '/scripts/') !== false) continue; // skip build scripts
        $content = file_get_contents($path);
        if (preg_match_all($emojiRegex, $content, $matches)) {
            $unique = array_unique($matches[0]);
            $found[$path] = $unique;
        }
    }
}

foreach ($found as $path => $emojis) {
    echo str_replace('/var/www/html/', '', $path) . ': ' . implode(' ', $emojis) . "\n";
}