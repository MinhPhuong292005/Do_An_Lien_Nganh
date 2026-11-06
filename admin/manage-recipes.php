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

// 1. Xử lý bộ lọc & tìm kiếm
$search = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? 'all'));
$ratingFilter = trim((string)($_GET['rating'] ?? 'all'));

$whereClauses = [];
$params = [];

if ($search !== '') {
    $cleanId = ltrim(trim($search), '#');
    if (ctype_digit($cleanId)) {
        $whereClauses[] = '(r.id = :qid OR r.title LIKE :q1 OR u.username LIKE :q2)';
        $params[':qid'] = (int)$cleanId;
    } else {
        $whereClauses[] = '(r.title LIKE :q1 OR u.username LIKE :q2)';
    }
    $params[':q1'] = '%' . $search . '%';
    $params[':q2'] = '%' . $search . '%';
}

if ($statusFilter !== '' && $statusFilter !== 'all') {
    $whereClauses[] = 'r.status = :status';
    $params[':status'] = $statusFilter;
}

if ($ratingFilter !== '' && $ratingFilter !== 'all') {
    if ($ratingFilter === 'unrated') {
        $whereClauses[] = 'NOT EXISTS (SELECT 1 FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL))';
    } elseif (in_array($ratingFilter, ['1', '2', '3', '4', '5'], true)) {
        $rInt = (int)$ratingFilter;
        // Khớp món nhận được đánh giá số sao này HOẶC điểm trung bình làm tròn bằng số sao này
        $whereClauses[] = '(EXISTS (SELECT 1 FROM comments c WHERE c.recipe_id = r.id AND c.rating = :rf1 AND (c.is_hidden = 0 OR c.is_hidden IS NULL)) OR ROUND((SELECT AVG(c.rating) FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL))) = :rf2)';
        $params[':rf1'] = $rInt;
        $params[':rf2'] = $rInt;
    }
}

$whereSql = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// 2. Đếm tổng số kết quả
$countSql = "SELECT COUNT(*) FROM recipes r INNER JOIN users u ON u.id = r.author_id {$whereSql}";
$stmtCount = db()->prepare($countSql);
$stmtCount->execute($params);
$totalRecipes = (int) $stmtCount->fetchColumn();

// 3. Phân trang
$allowedPerPage = [25, 30, 50, 100];
$perPage = (int) ($_GET['per_page'] ?? 25);
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 25;
}
$totalPages = max(1, (int) ceil($totalRecipes / $perPage));
$page = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));
$offset = ($page - 1) * $perPage;

// 4. Lấy danh sách công thức, SẮP XẾP CHÍNH XÁC THEO ID MỚI NHẤT ĐẾN CŨ NHẤT
$dataSql = "
    SELECT r.id, r.title, r.status, r.created_at, r.views_count,
           u.username AS author_name,
           (SELECT ROUND(AVG(c.rating), 1) FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL)) AS avg_rating,
           (SELECT COUNT(*) FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL)) AS review_count
    FROM recipes r 
    INNER JOIN users u ON u.id = r.author_id 
    {$whereSql}
    ORDER BY r.id DESC 
    LIMIT {$perPage} OFFSET {$offset}
";
$stmtData = db()->prepare($dataSql);
$stmtData->execute($params);
$recipes = $stmtData->fetchAll();

$pageTitle = 'Quản lý công thức - Cookio Admin';
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
    </style>
</head>
<body class="admin-page">
<?php require __DIR__ . '/../includes/admin-sidebar.php'; ?>
<main class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.6rem; color: #1e293b;">Quản lý tất cả công thức</h1>
            <p style="margin: 0.25rem 0 0; color: #64748b; font-size: 0.88rem;">Sắp xếp theo ID mới nhất đến cũ nhất • Tổng cộng <strong><?= number_format($totalRecipes) ?></strong>món ăn
            </p>
        </div>
        <a href="<?= BASE_URL ?>/views/create-recipe.php" target="_blank" class="btn-filter" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
            <span>+</span>Đăng công thức mới
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <p class="notice success" style="margin-bottom: 1rem;">
            <?= match ($_GET['success']) {
                'deleted' => 'Đã xóa công thức thành công.',
                'updated' => 'Cập nhật trạng thái công thức thành công.',
                default => 'Thao tác hoàn tất.'
            } ?>
        </p>
    <?php endif; ?>

    <!-- THANH TÌM KIẾM & BỘ LỌC CHỌN LỰA BẢNG MUỐN XEM -->
    <form method="get" action="<?= BASE_URL ?>/admin/manage-recipes.php" class="filter-form-bar">
        <div class="filter-group" style="flex: 1; min-width: 220px;">
            <label for="q">Tìm kiếm:</label>
            <input type="text" name="q" id="q" class="filter-input" placeholder="Tìm theo ID (VD: 23), tên món ăn hoặc tác giả..." value="<?= e($search) ?>" style="width: 100%;">
        </div>

        <!-- Ô CHỌN LỰA BẢNG / MỤC ĐÁNH GIÁ SỐ SAO -->
        <div class="filter-group">
            <label for="rating">⭐ Đánh giá sao:</label>
            <select name="rating" id="rating" class="filter-select">
                <option value="all" <?= $ratingFilter === 'all' ? 'selected' : '' ?>>-- Tất cả đánh giá --</option>
                <option value="5" <?= $ratingFilter === '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ 5 sao (Xuất sắc / Tuyệt vời)</option>
                <option value="4" <?= $ratingFilter === '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ 4 sao (Đánh giá tốt / Rất ngon)</option>
                <option value="3" <?= $ratingFilter === '3' ? 'selected' : '' ?>>⭐⭐⭐ 3 sao (Bị đánh giá 3 sao)</option>
                <option value="2" <?= $ratingFilter === '2' ? 'selected' : '' ?>>⭐⭐ 2 sao (Đánh giá thấp)</option>
                <option value="1" <?= $ratingFilter === '1' ? 'selected' : '' ?>>⭐ 1 sao (Đánh giá kém)</option>
            </select>
        </div>

        <!-- Ô CHỌN LỰA TRẠNG THÁI -->
        <div class="filter-group">
            <label for="status">Trạng thái:</label>
            <select name="status" id="status" class="filter-select">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>-- Tất cả trạng thái --</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
            </select>
        </div>

        <!-- Ô CHỌN SỐ MÓN HIỂN THỊ TRÊN 1 TRANG -->
        <div class="filter-group">
            <label for="per_page">Hiển thị:</label>
            <select name="per_page" id="per_page" class="filter-select" onchange="this.form.submit()">
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 món / trang</option>
                <option value="30" <?= $perPage === 30 ? 'selected' : '' ?>>30 món / trang</option>
                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 món / trang</option>
                <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100 món / trang</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">Lọc công thức</button>
        <?php if ($search !== '' || $statusFilter !== 'all' || $ratingFilter !== 'all' || $perPage !== 25): ?>
            <a href="<?= BASE_URL ?>/admin/manage-recipes.php" class="btn-reset">Bỏ lọc</a>
        <?php endif; ?>
    </form>

    <div class="admin-table-box">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Tên món ăn</th>
                        <th>Tác giả</th>
                        <th>Đánh giá sao</th>
                        <th>Trạng thái</th>
                        <th>Lượt xem</th>
                        <th>Ngày tạo</th>
                        <th style="width: 140px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recipes as $recipe): ?>
                    <tr>
                        <td><strong>#<?= (int) $recipe['id'] ?></strong></td>
                        <td>
                            <strong style="font-size: 0.95rem; color: #1e293b;"><?= e($recipe['title']) ?></strong>
                            <br>
                            <small><a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>" target="_blank" style="color: #ea580c; font-weight: 600;">Xem trang chi tiết &rarr;</a></small>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #334155;"><?= e($recipe['author_name']) ?></span>
                        </td>
                        <td>
                            <?php if ($recipe['avg_rating'] !== null && (int)$recipe['review_count'] >0): ?>
                                <span style="color: #f59e0b; font-weight: 700; white-space: nowrap;">
                                    ⭐ <?= round((float)$recipe['avg_rating'], 1) ?>
                                    <small style="color: #64748b; font-weight: normal;">(<?= (int)$recipe['review_count'] ?>)</small>
                                </span>
                            <?php else: ?>
                                <small style="color: #94a3b8;">Chưa có</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?= e($recipe['status']) ?>">
                                <?= match ($recipe['status']) {
                                    'approved' => 'Đã duyệt',
                                    'rejected' => 'Từ chối',
                                    default => 'Chờ duyệt',
                                } ?>
                            </span>
                        </td>
                        <td><?= number_format((int) ($recipe['views_count'] ?? 0)) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime((string) $recipe['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <?php if ($recipe['status'] !== 'approved'): ?>
                                    <form method="post" action="<?= BASE_URL ?>/actions/admin_action.php" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
                                        <input type="hidden" name="return_url" value="admin/manage-recipes.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">
                                        <button type="submit" name="status" value="approved" class="button-sm" style="background: #dcfce7; color: #15803d;">Duyệt</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($recipe['status'] === 'pending'): ?>
                                    <form method="post" action="<?= BASE_URL ?>/actions/admin_action.php" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
                                        <input type="hidden" name="return_url" value="admin/manage-recipes.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">
                                        <button type="submit" name="status" value="rejected" class="button-sm danger">Từ chối</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" action="<?= BASE_URL ?>/actions/admin_action.php" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="delete_recipe">
                                    <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
                                    <input type="hidden" name="return_url" value="admin/manage-recipes.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">
                                    <button type="submit" class="button-sm danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recipes): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: #64748b;">Không tìm thấy công thức nào phù hợp với bộ lọc hiện tại.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($totalRecipes >0): ?>
            <?php
            $paginationParams = [
                'q' => $search,
                'status' => $statusFilter,
                'rating' => $ratingFilter,
                'per_page' => $perPage !== 25 ? $perPage : '',
            ];
            $buildPageUrl = function(int $p, ?int $customPerPage = null) use ($paginationParams) {
                $params = $paginationParams;
                $params['page'] = $p;
                if ($customPerPage !== null) {
                    $params['per_page'] = $customPerPage !== 25 ? $customPerPage : '';
                }
                $filtered = array_filter($params, fn($v) => $v !== '' && $v !== 'all');
                return BASE_URL . '/admin/manage-recipes.php?' . http_build_query($filtered);
            };
            $pagesList = build_pagination_pages($page, $totalPages);
            ?>
            <div class="pagination-bar">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="font-size: 0.85rem; color: #64748b;">Đang hiển thị trang <strong><?= $page ?></strong>trên tổng số <strong><?= $totalPages ?></strong>trang (<?= number_format($totalRecipes) ?>món)
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; color: #475569;">
                        <label for="bottom_per_page" style="font-weight: 600;">Hiển thị:</label>
                        <select id="bottom_per_page" class="filter-select" style="padding: 0.2rem 0.5rem; font-size: 0.82rem; height: auto;" onchange="window.location.href = this.value;">
                            <?php foreach ($allowedPerPage as $opt): ?>
                                <option value="<?= $buildPageUrl(1, $opt) ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                                    <?= $opt ?>món / trang
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($totalPages >1): ?>
                <div class="pagination-links">
                    <?php if ($page >1): ?>
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
<script src="<?= BASE_URL ?>/assets/js/admin.js?v=<?= time() ?>"></script>
</body>
</html>
