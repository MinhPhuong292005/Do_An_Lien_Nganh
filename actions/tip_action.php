<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/kitchen-tips.php');
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!verify_csrf_token($csrfToken)) {
    redirect('views/create-tip.php?error=csrf');
}

$title = trim((string) ($_POST['title'] ?? ''));
$category = trim((string) ($_POST['category'] ?? 'Kinh nghiệm hay')) ?: 'Kinh nghiệm hay';
$summary = trim((string) ($_POST['summary'] ?? ''));
$content = trim((string) ($_POST['content'] ?? ''));
$time = null;
$difficulty = null;
$recipeLink = trim((string) ($_POST['recipe_link'] ?? ''));

if ($title === '' || $summary === '' || $content === '') {
    redirect('views/create-tip.php?error=missing-fields');
}

$authorId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$authorName = isset($_SESSION['username']) ? (string) $_SESSION['username'] : 'Đầu bếp Cookio';

$imageUrl = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $image = $_FILES['image'];
    if ($image['error'] !== UPLOAD_ERR_OK || $image['size'] > MAX_UPLOAD_SIZE) {
        redirect('views/create-tip.php?error=invalid-image');
    }

    $origExt = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($origExt, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        redirect('views/create-tip.php?error=invalid-image');
    }

    if (!@getimagesize($image['tmp_name'])) {
        redirect('views/create-tip.php?error=invalid-image');
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($image['tmp_name']);
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        redirect('views/create-tip.php?error=invalid-image');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $extension = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        default => 'webp',
    };

    $fileName = 'tip_' . bin2hex(random_bytes(14)) . '.' . $extension;
    if (!move_uploaded_file($image['tmp_name'], UPLOAD_DIR . $fileName)) {
        redirect('views/create-tip.php?error=upload-failed');
    }
    $imageUrl = 'uploads/recipes/' . $fileName;
}

$status = is_admin() ? 'approved' : 'pending';

try {
    $stmt = db()->prepare("
        INSERT INTO kitchen_tips (author_id, author_name, title, category, summary, content, image_url, time, difficulty, recipe_link, is_featured, status, views_count)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 0)
    ");
    $stmt->execute([
        $authorId,
        $authorName,
        $title,
        $category,
        $summary,
        $content,
        $imageUrl,
        $time,
        $difficulty,
        $recipeLink ?: null,
        $status
    ]);

    if ($status === 'approved') {
        redirect('views/kitchen-tips.php?success=tip-published');
    } else {
        redirect('views/kitchen-tips.php?success=tip-pending');
    }
} catch (Throwable $e) {
    error_log("Error saving kitchen tip: " . $e->getMessage());
    redirect('views/create-tip.php?error=failed');
}

