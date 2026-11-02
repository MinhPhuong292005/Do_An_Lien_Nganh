<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin1111';
$_SESSION['role'] = 'admin';

// Test 1: Recipe management with search
$_GET = ['q' => 'gà', 'rating' => 'all', 'status' => 'all'];
ob_start();
require __DIR__ . '/../admin/manage-recipes.php';
$html = ob_get_clean();
assert(str_contains($html, 'gà') || str_contains($html, 'Gà'), "Search gà failed");
echo "✓ manage-recipes search OK\n";

// Test 2: Recipe management with 3-star filter
$_GET = ['q' => '', 'rating' => '3', 'status' => 'all'];
ob_start();
require __DIR__ . '/../admin/manage-recipes.php';
$html = ob_get_clean();
assert(str_contains($html, '#60'), "3-star recipe 60 not found");
echo "✓ manage-recipes 3-star filter OK\n";

// Test 3: Comment management with 3-star filter
$_GET = ['q' => '', 'rating' => '3', 'moderation' => 'all'];
ob_start();
require __DIR__ . '/../admin/manage-comments.php';
$html = ob_get_clean();
assert(str_contains($html, 'không ngon'), "3-star comment not found");
echo "✓ manage-comments 3-star filter OK\n";

// Test 4: Comment management with flagged filter
$_GET = ['q' => '', 'rating' => 'all', 'moderation' => 'flagged'];
ob_start();
require __DIR__ . '/../admin/manage-comments.php';
$html = ob_get_clean();
assert(str_contains($html, 'Vi phạm tiêu chuẩn cộng đồng'), "Flagged comment not found");
echo "✓ manage-comments flagged moderation filter OK\n";

// Test 5: Users management ordering
$_GET = [];
ob_start();
require __DIR__ . '/../admin/manage-users.php';
$html = ob_get_clean();
$posAdmin1 = strpos($html, 'admin1111');
$posAdmin2 = strpos($html, 'admin2222');
$posChefLan = strpos($html, 'chef_lan');

assert($posAdmin1 !== false && $posAdmin2 !== false && $posChefLan !== false, "Users not found in HTML");
assert($posAdmin1 < $posAdmin2, "admin1111 must appear before admin2222");
assert($posAdmin2 < $posChefLan, "admin2222 must appear before chef_lan");
echo "✓ manage-users ordering: admin1111 (#1) -> admin2222 (#10) -> chef_lan (#2) VERIFIED!\n";

echo "\n>>> ALL 5 SUITE TESTS PASSED WITH 100% SUCCESS! <<<\n";
