<?php
$src = 'C:/Users/Admin/Downloads/Do_An_Lien_Nganh-main/Do_An_Lien_Nganh-main';
$dest1 = 'C:/Users/Admin/OneDrive/Đồ án liên ngành/Do_An_Lien_Nganh-main';
$dest2 = 'C:/Users/Admin/OneDrive/Đồ án liên ngành/Do_An_Lien_Nganh';

function copy_dir($src, $dst) {
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }
    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..' || $file === '.git') continue;
        $srcPath = "$src/$file";
        $dstPath = "$dst/$file";
        if (is_dir($srcPath)) {
            copy_dir($srcPath, $dstPath);
        } else {
            // Only copy if modified or not exists
            if (!file_exists($dstPath) || filemtime($srcPath) > filemtime($dstPath) || filesize($srcPath) !== filesize($dstPath)) {
                copy($srcPath, $dstPath);
            }
        }
    }
    closedir($dir);
}

echo "Copying to $dest1...\n";
copy_dir($src, $dest1);
echo "Copying to $dest2...\n";
copy_dir($src, $dest2);
echo "Done!\n";
