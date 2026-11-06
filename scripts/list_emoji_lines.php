<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/var/www/html'));
$emojiRegex = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u';

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        if (strpos($path, '/scripts/') !== false) continue;
        $lines = file($path);
        foreach ($lines as $num => $line) {
            if (preg_match($emojiRegex, $line)) {
                $rel = str_replace('/var/www/html/', '', $path);
                echo "$rel:" . ($num + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}