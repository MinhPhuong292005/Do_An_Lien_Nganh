<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$cols = db()->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
echo "Columns in users table: " . implode(', ', $cols) . "\n";
if (!in_array('avatar', $cols)) {
    db()->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER bio");
    echo "Added avatar column successfully.\n";
} else {
    echo "avatar column already exists.\n";
}

