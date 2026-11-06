<?php
$sourceDir = 'C:/Users/Admin/Downloads/Do_An_Lien_Nganh-main/Do_An_Lien_Nganh-main';
$tempZip = sys_get_temp_dir() . '/Do_An_Lien_Nganh.zip';
$finalZip = 'C:/Users/Admin/OneDrive/Đồ án liên ngành/Do_An_Lien_Nganh.zip';

if (file_exists($tempZip)) {
    unlink($tempZip);
}

$zip = new ZipArchive();
if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Cannot create temp zip\n");
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $file) {
    if (!$file->isFile()) continue;
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($sourceDir) + 1);
    // Skip .git folder
    if (str_starts_with($relativePath, '.git\\') || str_starts_with($relativePath, '.git/')) {
        continue;
    }
    $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
}

$zip->close();
echo "Temp zip created, size: " . filesize($tempZip) . " bytes\n";
copy($tempZip, $finalZip);
echo "Copied to $finalZip, final size: " . filesize($finalZip) . " bytes\n";
