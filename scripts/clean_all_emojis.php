<?php
$targetDirs = [
    '/var/www/html/views',
    '/var/www/html/includes',
    '/var/www/html/admin',
    '/var/www/html/actions',
];
$targetFiles = [
    '/var/www/html/index.php',
];

$emojiRegex = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}]/u';

$filesToProcess = $targetFiles;
foreach ($targetDirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $filesToProcess[] = $f->getPathname();
        }
    }
}

$count = 0;
foreach ($filesToProcess as $file) {
    $orig = file_get_contents($file);
    if (preg_match($emojiRegex, $orig)) {
        // Replace emojis
        $cleaned = preg_replace($emojiRegex, '', $orig);
        // Clean empty emoji spans like <span></span> or <span> </span>
        $cleaned = preg_replace('/<span>\s*<\/span>\s*/u', '', $cleaned);
        // Clean leading space after tag if emoji was at the start
        $cleaned = preg_replace('/>\s*([A-Za-z0-9À-ỹ])/u', '>$1', $cleaned);
        
        file_put_contents($file, $cleaned);
        $rel = str_replace('/var/www/html/', '', $file);
        echo "Cleaned emojis from: $rel\n";
        $count++;
    }
}
echo "Total files cleaned: $count\n";