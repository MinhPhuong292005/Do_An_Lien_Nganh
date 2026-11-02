<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!is_admin()) {
    redirect('index.php');
}

// 1. Nhận các tham số tìm kiếm & bộ lọc
$search = trim((string)($_GET['q'] ?? ''));
$ratingFilter = trim((string)($_GET['rating'] ?? 'all'));
$moderationFilter = trim((string)($_GET['moderation'] ?? 'all'));

$whereClauses = [];
$params = [];

// Tìm kiếm theo ai bình luận, nội dung, tên món hoặc mã ID
if ($search !== '') {
    $cleanId = ltrim(trim($search), '#');
    if (ctype_digit($cleanId)) {
        $whereClauses[] = '(c.id = :qid OR r.id = :qrid OR u.username LIKE :q1 OR c.content LIKE :q2 OR r.title LIKE :q3)';
        $params[':qid'] = (int)$cleanId;
        $params[':qrid'] = (int)$cleanId;
    } else {
        $whereClauses[] = '(u.username LIKE :q1 OR c.content LIKE :q2 OR r.title LIKE :q3)';
    }
    $params[':q1'] = '%' . $search . '%';
    $params[':q2'] = '%' . $search . '%';
    $params[':q3'] = '%' . $search . '%';
}

// Lọc theo đánh giá số sao
if ($ratingFilter !== '' && $ratingFilter !== 'all') {
    $whereClauses[] = 'c.rating = :rf';
    $params[':rf'] = (int) $ratingFilter;
}

// Lọc theo trạng thái kiểm duyệt tiêu chuẩn cộng đồng
if ($moderationFilter === 'flagged') {
    $whereClauses[] = 'c.is_flagged = 1';
} elseif ($moderationFilter === 'hidden') {
    $whereClauses[] = 'c.is_hidden = 1';
} elseif ($moderationFilter === 'approved') {
    $whereClauses[] = '(c.is_hidden = 0 OR c.is_hidden IS NULL) AND (c.is_flagged = 0 OR c.is_flagged IS NULL)';
}

$whereSql = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// 2. Đếm tổng số kết quả
$countSql = "
    SELECT COUNT(*) 
    FROM comments c 
    INNER JOIN users u ON u.id = c.user_id 
    INNER JOIN recipes r ON r.id = c.recipe_id 
    {$whereSql}
";
$stmtCount = db()->prepare($countSql);
$stmtCount->execute($params);
$totalComments = (int) $stmtCount->fetchColumn();

// 3. Phân trang
$allowedPerPage = [25, 30, 50, 100];
$perPage = (int) ($_GET['per_page'] ?? 25);
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 25;
}
$totalPages = max(1, (int) ceil($totalComments / $perPage));
$page = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));
$offset = ($page - 1) * $perPage;

// 4. Lấy danh sách bình luận (sắp xếp ID mới nhất đến cũ nhất)
$dataSql = "
    SELECT c.id, c.content, c.rating, c.image_url, c.created_at, c.is_hidden, c.is_flagged,
           u.username AS author_name, 
           r.id AS recipe_id, r.title AS recipe_title 
    FROM comments c 
    INNER JOIN users u ON u.id = c.user_id 
    INNER JOIN recipes r ON r.id = c.recipe_id 
    {$whereSql}
    ORDER BY c.id DESC 
    LIMIT {$perPage} OFFSET {$offset}
";
$stmtData = db()->prepare($dataSql);
$stmtData->execute($params);
$comments = $stmtData->fetchAll();

$pageTitle = 'Quản lý bình luận & đánh giá - Cookio Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <style>
        .filter-form-bar {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
            background: #fff;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #f0e6d8;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .filter-group label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            white-space: nowrap;
        }
        .filter-input, .filter-select {
            padding: 0.45rem 0.85rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.88rem;
            outline: none;
            background: #fff;
            color: #1e293b;
            font-family: inherit;
        }
        .filter-input:focus, .filter-select:focus {
            border-color: #ea580c;
        }
        .btn-filter {
            background: #ea580c;
            color: #fff;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-filter:hover { background: #c2410c; }
        .btn-reset {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-reset:hover { background: #e2e8f0; }
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.25rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .pagination-links {
            display: flex;
            gap: 0.35rem;
            align-items: center;
        }
        .page-link {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            font-size: 0.85rem;
            text-decoration: none;
            font-weight: 600;
        }
        .page-link.is-active {
            background: #ea580c;
            color: #fff;
            border-color: #ea580c;
        }
        .page-link:hover:not(.is-active) {
            background: #f8fafc;
        }
        .toxic-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        .badge-live {
            background: #dcfce7;
            color: #15803d;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-hidden {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
        }
    </style>
</head>
<body class="admin-page">
<?php require __DIR__ . '/../includes/admin-sidebar.php'; ?>
<main class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.6rem; color: #1e293b;">Quản lý bình luận & đánh giá</h1>
            <p style="margin: 0.25rem 0 0; color: #64748b; font-size: 0.88rem;">
                Kiểm duyệt bình luận • Tự động phát hiện nội dung tiêu cực vi phạm tiêu chuẩn cộng đồng • Tổng cộng <strong><?= number_format($totalComments) ?></strong> đánh giá
            </p>
        </div>
    </div>

    <?php if (isset($_GET['comment'])): ?>
        <p class="notice success" style="margin-bottom: 1rem;">
            <?= match ($_GET['comment']) {
                'deleted' => 'Đã xóa bình luận thành công.',
                'updated' => 'Đã cập nhật trạng thái hiển thị bình luận thành công.',
                default => 'Thao tác hoàn tất.'
            } ?>
        </p>
    <?php endif; ?>

    <!-- THANH TÌM KIẾM & BỘ LỌC BÌNH LUẬN -->
    <form method="get" action="<?= BASE_URL ?>/admin/manage-comments.php" class="filter-form-bar">
        <!-- TÌM KIẾM THEO NGƯỜI BÌNH LUẬN / NỘI DUNG / MÓN ĂN -->
        <div class="filter-group" style="flex: 1; min-width: 220px;">
            <label for="q">👤 Ai bình luận / Nội dung / ID:</label>
            <input type="text" name="q" id="q" class="filter-input" placeholder="Tìm ID món/bình luận, tên người gửi, nội dung..." value="<?= e($search) ?>" style="width: 100%;">
        </div>

        <!-- Ô CHỌN LỰA BẢNG: LỌC THEO SỐ SAO -->
        <div class="filter-group">
            <label for="rating">⭐ Số sao:</label>
            <select name="rating" id="rating" class="filter-select">
                <option value="all" <?= $ratingFilter === 'all' ? 'selected' : '' ?>>-- Tất cả số sao --</option>
                <option value="5" <?= $ratingFilter === '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ 5 sao (Xuất sắc / Tuyệt vời)</option>
                <option value="4" <?= $ratingFilter === '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ 4 sao (Đánh giá tốt / Rất ngon)</option>
                <option value="3" <?= $ratingFilter === '3' ? 'selected' : '' ?>>⭐⭐⭐ 3 sao (Bị đánh giá 3 sao)</option>
                <option value="2" <?= $ratingFilter === '2' ? 'selected' : '' ?>>⭐⭐ 2 sao (Đánh giá thấp)</option>
                <option value="1" <?= $ratingFilter === '1' ? 'selected' : '' ?>>⭐ 1 sao (Đánh giá kém)</option>
            </select>
        </div>

        <!-- Ô CHỌN LỰA: LỌC THEO KIỂM DUYỆT & TIÊU CHUẨN CỘNG ĐỒNG -->
        <div class="filter-group">
            <label for="moderation">🛡️ Tiêu chuẩn:</label>
            <select name="moderation" id="moderation" class="filter-select">
                <option value="all" <?= $moderationFilter === 'all' ? 'selected' : '' ?>>-- Tất cả bình luận --</option>
                <option value="approved" <?= $moderationFilter === 'approved' ? 'selected' : '' ?>>✅ Hợp lệ (Đang hiện)</option>
                <option value="flagged" <?= $moderationFilter === 'flagged' ? 'selected' : '' ?>>⚠️ Vi phạm tiêu chuẩn (Chứa từ cấm)</option>
                <option value="hidden" <?= $moderationFilter === 'hidden' ? 'selected' : '' ?>>🚫 Đã bị ẩn (Không hiện trên web)</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="per_page">📄 Hiển thị:</label>
            <select name="per_page" id="per_page" class="filter-select" onchange="this.form.submit()">
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 bình luận / trang</option>
                <option value="30" <?= $perPage === 30 ? 'selected' : '' ?>>30 bình luận / trang</option>
                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 bình luận / trang</option>
                <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100 bình luận / trang</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">Lọc</button>
        <?php if ($search !== '' || $ratingFilter !== 'all' || $moderationFilter !== 'all' || $perPage !== 25): ?>
            <a href="<?= BASE_URL ?>/admin/manage-comments.php" class="btn-reset">✕ Bỏ lọc</a>
        <?php endif; ?>
    </form>

    <div class="admin-table-box">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 65px;">ID</th>
                        <th style="width: 140px;">Người gửi</th>
                        <th style="width: 200px;">Công thức</th>
                        <th style="width: 110px;">Đánh giá</th>
                        <th>Nội dung & Cooksnap</th>
                        <th style="width: 115px;">Trạng thái</th>
                        <th style="width: 110px;">Thời gian</th>
                        <th style="width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($comments as $comm): ?>
                    <?php
                    // Kiểm tra từ khóa tiêu cực theo tiêu chuẩn cộng đồng
                    $toxCheck = check_toxic_comment((string)$comm['content']);
                    $isToxic = $toxCheck['is_toxic'] || (int)($comm['is_flagged'] ?? 0) === 1;
                    $isHidden = (int)($comm['is_hidden'] ?? 0) === 1;
                    ?>
                    <tr style="<?= $isToxic ? 'background: rgba(254, 226, 226, 0.25);' : '' ?>">
                        <td><strong>#<?= (int) $comm['id'] ?></strong></td>
                        <td>
                            <strong style="color: #1e293b;"><?= e($comm['author_name']) ?></strong>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $comm['recipe_id'] ?>" target="_blank" style="color: #ea580c; font-weight: 600; text-decoration: none;">
                                <?= e($comm['recipe_title']) ?> &rarr;
                            </a>
                        </td>
                        <td>
                            <span style="color:#f59e0b; white-space:nowrap; font-size: 0.95rem;">
                                <?= str_repeat('⭐', max(1, min(5, (int) ($comm['rating'] ?? 5)))) ?>
                            </span>
                            <br>
                            <small style="color: #64748b; font-weight: 700;"><?= (int) ($comm['rating'] ?? 5) ?> / 5 sao</small>
                        </td>
                        <td>
                            <?php if ($isToxic): ?>
                                <div class="toxic-tag">
                                    ⚠️ Vi phạm tiêu chuẩn cộng đồng <?= $toxCheck['matched'] !== '' ? '(từ khóa: "' . e($toxCheck['matched']) . '")' : '' ?>
                                </div>
                            <?php endif; ?>
                            <div style="font-size: 0.9rem; line-height: 1.45; color: #334155;">
                                <?= nl2br(e($comm['content'])) ?>
                            </div>
                            <?php if (!empty($comm['image_url'])): ?>
                                <div style="margin-top:0.4rem;">
                                    <a href="<?= BASE_URL . '/' . e($comm['image_url']) ?>" target="_blank">
                                        <img src="<?= BASE_URL . '/' . e($comm['image_url']) ?>" alt="Cooksnap" style="height:50px; border-radius:6px; object-fit:cover; border:1px solid #cbd5e1;">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isHidden): ?>
                                <span class="badge-hidden">🚫 Đã ẩn</span>
                                <small style="display:block; color: #94a3b8; margin-top:2px; font-size: 0.72rem;">Không hiện trên web</small>
                            <?php else: ?>
                                <span class="badge-live">✅ Đang hiện</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 0.82rem; color: #64748b;">
                            <?= date('d/m/Y H:i', strtotime((string) $comm['created_at'])) ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <!-- NÚT ẨN / HIỆN BÌNH LUẬN -->
                                <form method="post" action="<?= BASE_URL ?>/actions/comment_action.php" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="toggle_hidden">
                                    <input type="hidden" name="comment_id" value="<?= (int) $comm['id'] ?>">
                                    <input type="hidden" name="return_url" value="admin/manage-comments.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">
                                    <button type="submit" class="button-sm" style="<?= $isHidden ? 'background: #dcfce7; color: #15803d;' : 'background: #f1f5f9; color: #475569;' ?>" title="<?= $isHidden ? 'Bật hiển thị bình luận này lên website' : 'Ẩn bình luận này khỏi website' ?>">
                                        <?= $isHidden ? 'Hiện' : 'Ẩn' ?>
                                    </button>
                                </form>

                                <!-- NÚT XÓA -->
                                <button type="button" class="button-sm danger" onclick="openDeleteCommentModal(<?= (int)$comm['id'] ?>, '<?= e(addslashes($comm['author_name'])) ?>')">
                                    Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            Không tìm thấy bình luận nào phù hợp với bộ lọc hiện tại.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($totalComments > 0): ?>
            <?php
            $paginationParams = [
                'q' => $search,
                'rating' => $ratingFilter,
                'moderation' => $moderationFilter,
                'per_page' => $perPage !== 25 ? $perPage : '',
            ];
            $buildPageUrl = function(int $p, ?int $customPerPage = null) use ($paginationParams) {
                $params = $paginationParams;
                $params['page'] = $p;
                if ($customPerPage !== null) {
                    $params['per_page'] = $customPerPage !== 25 ? $customPerPage : '';
                }
                $filtered = array_filter($params, fn($v) => $v !== '' && $v !== 'all');
                return BASE_URL . '/admin/manage-comments.php?' . http_build_query($filtered);
            };
            $pagesList = build_pagination_pages($page, $totalPages);
            ?>
            <div class="pagination-bar">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="font-size: 0.85rem; color: #64748b;">
                        Đang hiển thị trang <strong><?= $page ?></strong> trên tổng số <strong><?= $totalPages ?></strong> trang (<?= number_format($totalComments) ?> bình luận)
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; color: #475569;">
                        <label for="bottom_per_page" style="font-weight: 600;">Hiển thị:</label>
                        <select id="bottom_per_page" class="filter-select" style="padding: 0.2rem 0.5rem; font-size: 0.82rem; height: auto;" onchange="window.location.href = this.value;">
                            <?php foreach ($allowedPerPage as $opt): ?>
                                <option value="<?= $buildPageUrl(1, $opt) ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                                    <?= $opt ?> bình luận / trang
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="<?= $buildPageUrl(1) ?>" class="page-link" title="Trang đầu">&laquo; Đầu</a>
                        <a href="<?= $buildPageUrl($page - 1) ?>" class="page-link" title="Trang trước">&lsaquo; Trước</a>
                    <?php endif; ?>

                    <?php foreach ($pagesList as $p): ?>
                        <?php if ($p === '...'): ?>
                            <span class="page-ellipsis" style="padding: 0.35rem 0.5rem; color: #94a3b8; font-weight: 700;">...</span>
                        <?php else: ?>
                            <a href="<?= $buildPageUrl((int)$p) ?>" class="page-link <?= (int)$p === $page ? 'is-active' : '' ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $buildPageUrl($page + 1) ?>" class="page-link" title="Trang sau">Sau &rsaquo;</a>
                        <a href="<?= $buildPageUrl($totalPages) ?>" class="page-link" title="Trang cuối">Cuối &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Xác nhận xóa bình luận -->
<div id="deleteCommentModalBackdrop" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 16px; max-width: 480px; width: 100%; padding: 1.75rem; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                ⚠️
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e293b;">Xác nhận xóa bình luận</h3>
                <span style="font-size: 0.85rem; color: #64748b;">Hành động này không thể hoàn tác</span>
            </div>
        </div>

        <p style="color: #334155; font-size: 0.95rem; line-height: 1.5; margin: 0 0 1rem;">
            Bạn có chắc chắn muốn xóa vĩnh viễn bình luận của tác giả <strong id="modalCommentAuthor" style="color: #ea580c;"></strong> không?
        </p>

        <form id="deleteCommentForm" method="post" action="<?= BASE_URL ?>/actions/comment_action.php" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="comment_id" id="modalCommentId" value="">
            <input type="hidden" name="return_url" value="admin/manage-comments.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">

            <button type="button" onclick="closeDeleteCommentModal()" style="padding: 0.55rem 1.15rem; background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #475569; cursor: pointer;">
                Hủy bỏ
            </button>
            <button type="submit" style="padding: 0.55rem 1.25rem; background: #dc2626; border: 1.5px solid #dc2626; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #ffffff; cursor: pointer; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);">
                Xác nhận xóa
            </button>
        </form>
    </div>
</div>

<script>
function openDeleteCommentModal(commentId, authorName) {
    document.getElementById('modalCommentId').value = commentId;
    document.getElementById('modalCommentAuthor').textContent = `"${authorName}"`;
    document.getElementById('deleteCommentModalBackdrop').style.display = 'flex';
}

function closeDeleteCommentModal() {
    document.getElementById('deleteCommentModalBackdrop').style.display = 'none';
}

window.addEventListener('click', (e) => {
    const backdrop = document.getElementById('deleteCommentModalBackdrop');
    if (e.target === backdrop) {
        closeDeleteCommentModal();
    }
});
</script>
<script src="<?= BASE_URL ?>/assets/js/admin.js?v=<?= time() ?>"></script>
</body>
</html>
