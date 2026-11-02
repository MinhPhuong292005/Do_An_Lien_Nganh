<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_logged_in()) {
    redirect('index.php?error=login-required');
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!verify_csrf_token($csrfToken)) {
    redirect('views/profile.php?error=csrf');
}

$action = (string) ($_POST['action'] ?? 'update_info');
$userId = (int) $_SESSION['user_id'];

if ($action === 'update_info') {
    $newUsername = trim((string) ($_POST['username'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $bio = trim((string) ($_POST['bio'] ?? ''));

    // Validate username
    if ($newUsername === '' || mb_strlen($newUsername) < 2) {
        redirect('views/profile.php?error=username-invalid');
    }

    // Check if username is already taken by another account
    $stmtCheck = db()->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
    $stmtCheck->execute([$newUsername, $userId]);
    if ($stmtCheck->fetch()) {
        redirect('views/profile.php?error=username-taken');
    }

    // Handle avatar upload if provided
    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        if ($file['size'] > 5 * 1024 * 1024) {
            redirect('views/profile.php?error=avatar-too-large');
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            redirect('views/profile.php?error=avatar-invalid');
        }

        $imgInfo = @getimagesize($file['tmp_name']);
        if (!$imgInfo) {
            redirect('views/profile.php?error=avatar-invalid');
        }

        $uploadDir = __DIR__ . '/../uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $avatarPath = 'uploads/avatars/' . $fileName;
        }
    }

    if ($avatarPath !== null) {
        $stmt = db()->prepare('UPDATE users SET username = ?, phone = ?, bio = ?, avatar = ? WHERE id = ?');
        $stmt->execute([$newUsername, $phone ?: null, $bio ?: null, $avatarPath, $userId]);
        $_SESSION['avatar'] = $avatarPath;
    } else {
        $stmt = db()->prepare('UPDATE users SET username = ?, phone = ?, bio = ? WHERE id = ?');
        $stmt->execute([$newUsername, $phone ?: null, $bio ?: null, $userId]);
    }

    $_SESSION['username'] = $newUsername;
    $_SESSION['phone'] = $phone;

    redirect('views/profile.php?success=info-updated');
}

if ($action === 'change_password') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($newPassword === '' || $newPassword !== $confirmPassword) {
        redirect('views/profile.php?error=password-mismatch');
    }

    if (strlen($newPassword) < 3) {
        redirect('views/profile.php?error=password-short');
    }

    $stmtUser = db()->prepare('SELECT password FROM users WHERE id = ?');
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch();

    if (!$user || !password_verify($currentPassword, (string) $user['password'])) {
        redirect('views/profile.php?error=current-password-wrong');
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmtUpdate = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmtUpdate->execute([$hashed, $userId]);

    redirect('views/profile.php?success=password-updated');
}

redirect('views/profile.php');
