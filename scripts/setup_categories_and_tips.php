<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = db();

echo "1. Creating categories table...\n";
$pdo->exec("
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$initialCategories = [
    ['name' => 'Món Âu - Mỹ', 'slug' => 'mon-au-my', 'icon' => '🥩', 'display_order' => 1],
    ['name' => 'Món bún & phở', 'slug' => 'mon-bun-pho', 'icon' => '🍜', 'display_order' => 2],
    ['name' => 'Món hấp', 'slug' => 'mon-hap', 'icon' => '♨️', 'display_order' => 3],
    ['name' => 'Món kho', 'slug' => 'mon-kho', 'icon' => '🍲', 'display_order' => 4],
    ['name' => 'Món chiên', 'slug' => 'mon-chien', 'icon' => '🍤', 'display_order' => 5],
    ['name' => 'Món xào', 'slug' => 'mon-xao', 'icon' => '🥘', 'display_order' => 6],
    ['name' => 'Món bánh', 'slug' => 'mon-banh', 'icon' => '🍰', 'display_order' => 7],
    ['name' => 'Món canh', 'slug' => 'mon-canh', 'icon' => '🥣', 'display_order' => 8],
    ['name' => 'Món nướng', 'slug' => 'mon-nuong', 'icon' => '🍖', 'display_order' => 9],
    ['name' => 'Món chay', 'slug' => 'mon-chay', 'icon' => '🥗', 'display_order' => 10],
    ['name' => 'Món luộc', 'slug' => 'mon-luoc', 'icon' => '🥬', 'display_order' => 11],
    ['name' => 'Món lẩu', 'slug' => 'mon-lau', 'icon' => '🫕', 'display_order' => 12],
];

$stmtCat = $pdo->prepare("
    INSERT INTO categories (name, slug, icon, display_order)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        slug = VALUES(slug), 
        icon = VALUES(icon), 
        display_order = VALUES(display_order)
");

foreach ($initialCategories as $c) {
    $stmtCat->execute([$c['name'], $c['slug'], $c['icon'], $c['display_order']]);
}
echo "Seeded " . count($initialCategories) . " categories successfully.\n";

echo "2. Creating kitchen_tips table...\n";
$pdo->exec("
CREATE TABLE IF NOT EXISTS kitchen_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NULL,
    author_name VARCHAR(100) NOT NULL DEFAULT 'Đầu bếp Cookio',
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'Kinh nghiệm hay',
    summary TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    time VARCHAR(50) DEFAULT '20p',
    difficulty VARCHAR(50) DEFAULT 'Dễ',
    recipe_link VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_author (author_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "kitchen_tips table created successfully.\n";

echo "Setup completed successfully!\n";

