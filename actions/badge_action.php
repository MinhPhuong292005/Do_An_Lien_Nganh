<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!verify_csrf_token($csrfToken)) {
    redirect('views/profile.php?error=csrf');
}

$userId = (int) $_SESSION['user_id'];
$badgeKey = trim((string) ($_POST['badge_key'] ?? ''));
$action = trim((string) ($_POST['action'] ?? 'show')); // 'show' or 'hide'

if (!in_array($badgeKey, ['chef', 'gold_chef', 'fav'], true)) {
    redirect('views/profile.php?error=invalid-badge');
}

$isDisplayed = ($action === 'show') ? 1 : 0;

$stmt = db()->prepare("
    INSERT INTO user_badges (user_id, badge_key, is_displayed) 
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE is_displayed = ?
");
$stmt->execute([$userId, $badgeKey, $isDisplayed, $isDisplayed]);

redirect('views/profile.php?success=badge-updated');

