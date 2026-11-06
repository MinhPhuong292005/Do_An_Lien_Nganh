<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' =>false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$action = (string) ($_GET['action'] ?? $_POST['action'] ?? 'list');
$filterType = trim((string) ($_GET['type'] ?? $_POST['type'] ?? 'all'));

if ($action === 'list') {
    $whereClause = "WHERE n.user_id = ?";
    $params = [$userId];

    if ($filterType === 'like') {
        $whereClause .= " AND n.type = 'like'";
    } elseif ($filterType === 'comment') {
        $whereClause .= " AND (n.type = 'comment' OR n.type = 'cooksnap')";
    } elseif ($filterType === 'follow') {
        $whereClause .= " AND n.type = 'follow'";
    } elseif ($filterType === 'system') {
        $whereClause .= " AND n.type = 'system'";
    }

    $stmt = db()->prepare("
        SELECT n.*, u.username AS actor_name
        FROM notifications n
        JOIN users u ON u.id = n.actor_id
        $whereClause
        ORDER BY n.created_at DESC
        LIMIT 30
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $unreadCount = (int) db()->query("SELECT COUNT(*) FROM notifications WHERE user_id = $userId AND is_read = 0")->fetchColumn();

    $items = [];
    $now = time();

    foreach ($rows as $row) {
        $type = $row['type'];
        $icon = match ($type) {
            'like' => '',
            'comment' => '',
            'cooksnap' => '',
            'follow' => '',
            'system' => '',
            default => ''
        };

        $actorName = (string) $row['actor_name'];
        if ($type === 'system') {
            $actorName = 'Ban quản trị Cookio';
        }

        $targetUrl = '#';
        $isRejected = false;
        $rejectTitle = '';
        $rejectMessage = '';

        if ($type === 'system') {
            $isRejectedContent = str_contains((string)$row['content'], 'từ chối') 
                              || str_contains((string)$row['content'], 'không được duyệt') 
                              || str_contains((string)$row['content'], 'Rất tiếc');

            if ($isRejectedContent) {
                $isRejected = true;
                if (preg_match('/[«"“]([^»"”]+)[»"”]/u', (string)$row['content'], $matches)) {
                    $rejectTitle = trim($matches[1]);
                } else {
                    $rejectTitle = 'bài đăng của bạn';
                }

                $rejectMessage = 'Rất tiếc, bài đăng «<strong>' . htmlspecialchars($rejectTitle, ENT_QUOTES, 'UTF-8') . '</strong>» không được duyệt.';
                $targetUrl = 'javascript:void(0);';
            } else {
                // Đã duyệt ->chuyển tới bài đăng đó
                if (str_contains((string)$row['content'], 'kinh nghiệm') || str_contains((string)$row['content'], 'bí quyết')) {
                    $targetUrl = BASE_URL . '/views/kitchen-tips.php' . ($row['target_id'] ? '#tip-' . $row['target_id'] : '');
                } elseif ($row['target_id']) {
                    $targetUrl = BASE_URL . '/views/recipe-detail.php?id=' . $row['target_id'];
                } else {
                    $targetUrl = BASE_URL . '/views/my-recipes.php';
                }
            }
        } elseif ($row['target_id']) {
            $targetUrl = BASE_URL . '/views/recipe-detail.php?id=' . $row['target_id'] . ($type === 'comment' || $type === 'cooksnap' ? '#comments' : '');
        } elseif ($type === 'follow') {
            $targetUrl = BASE_URL . '/views/author.php?id=' . $row['actor_id'];
        }

        // Relative time calculation
        $createdTime = strtotime((string)$row['created_at']);
        $diff = $now - $createdTime;
        if ($diff < 60) {
            $timeAgo = 'Vừa xong';
        } elseif ($diff < 3600) {
            $timeAgo = floor($diff / 60) . ' phút trước';
        } elseif ($diff < 86400) {
            $timeAgo = floor($diff / 3600) . ' giờ trước';
        } elseif ($diff < 172800) {
            $timeAgo = 'Hôm qua, ' . date('H:i', $createdTime);
        } else {
            $timeAgo = date('d/m/Y H:i', $createdTime);
        }

        $items[] = [
            'id' => (int) $row['id'],
            'actor_name' => $actorName,
            'icon' => $icon,
            'type' => $type,
            'content' => (string) $row['content'],
            'is_read' => (int) $row['is_read'] === 1,
            'target_url' => $targetUrl,
            'created_at' => $timeAgo,
            'is_rejected' => $isRejected,
            'reject_title' => $rejectTitle,
            'reject_message' => $rejectMessage
        ];
    }

    echo json_encode([
        'success' =>true,
        'unread_count' => $unreadCount,
        'notifications' => $items
    ]);
    exit;
}

if ($action === 'mark_read') {
    $notifId = filter_input(INPUT_POST, 'notif_id', FILTER_VALIDATE_INT);
    if ($notifId) {
        $up = db()->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $up->execute([$notifId, $userId]);
    }
    echo json_encode(['success' =>true]);
    exit;
}

if ($action === 'mark_all_read') {
    $up = db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $up->execute([$userId]);
    echo json_encode(['success' =>true]);
    exit;
}

echo json_encode(['success' =>false, 'message' => 'Action không hợp lệ']);
