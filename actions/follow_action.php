<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit;
    }
    redirect('index.php');
}

$returnUrl = (string) ($_POST['return_url'] ?? 'index.php');

if (!is_logged_in()) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để theo dõi đầu bếp!']);
        exit;
    }
    redirect($returnUrl . (str_contains($returnUrl, '?') ? '&' : '?') . 'error=login-required');
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!verify_csrf_token($csrfToken)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Mã bảo mật (CSRF) không hợp lệ']);
        exit;
    }
    redirect($returnUrl . (str_contains($returnUrl, '?') ? '&' : '?') . 'error=csrf');
}

$authorId = filter_input(INPUT_POST, 'author_id', FILTER_VALIDATE_INT) ?: (int)($_POST['author_id'] ?? 0);
$followerId = (int) $_SESSION['user_id'];

if (!$authorId || $authorId === $followerId) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
        exit;
    }
    redirect($returnUrl);
}

// Check author exists
$stmtCheck = db()->prepare('SELECT id, username FROM users WHERE id = ?');
$stmtCheck->execute([$authorId]);
$authorUser = $stmtCheck->fetch();
if (!$authorUser) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đầu bếp']);
        exit;
    }
    redirect($returnUrl);
}

// Check follow state (using following_id)
$stmtFollow = db()->prepare('SELECT id FROM follows WHERE follower_id = ? AND following_id = ?');
$stmtFollow->execute([$followerId, $authorId]);
$existing = $stmtFollow->fetch();

if ($existing) {
    $stmtDel = db()->prepare('DELETE FROM follows WHERE follower_id = ? AND following_id = ?');
    $stmtDel->execute([$followerId, $authorId]);
    $msg = 'unfollowed';
} else {
    $stmtIns = db()->prepare('INSERT INTO follows (follower_id, following_id) VALUES (?, ?)');
    $stmtIns->execute([$followerId, $authorId]);
    $msg = 'followed';

    // Trigger notification
    $followerName = (string) ($_SESSION['username'] ?? 'Một thành viên');
    $notif = db()->prepare("INSERT INTO notifications (user_id, actor_id, type, target_id, content) VALUES (?, ?, 'follow', NULL, ?)");
    $notif->execute([$authorId, $followerId, 'Bếp ' . $followerName . ' đã bắt đầu theo dõi gian bếp của bạn.']);
}

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    $cStmt = db()->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
    $cStmt->execute([$authorId]);
    $followersCount = (int) $cStmt->fetchColumn();
    echo json_encode([
        'success' => true,
        'followed' => ($msg === 'followed'),
        'followers_count' => $followersCount,
        'message' => ($msg === 'followed') ? 'Đã theo dõi đầu bếp thành công!' : 'Đã hủy theo dõi đầu bếp.'
    ]);
    exit;
}

redirect($returnUrl . (str_contains($returnUrl, '?') ? '&' : '?') . 'follow=' . $msg);
