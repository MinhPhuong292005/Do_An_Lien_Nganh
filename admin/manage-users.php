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
$roleFilter = trim((string)($_GET['role'] ?? 'all'));

$whereClauses = [];
$params = [];

if ($search !== '') {
    $rawSearch = trim($search);
    $cleanId = preg_replace('/^(#|id\s*:?\s*)/i', '', $rawSearch);
    if (ctype_digit($cleanId)) {
        $whereClauses[] = '(u.id = :qid OR u.username LIKE :q1 OR u.phone LIKE :q2)';
        $params[':qid'] = (int)$cleanId;
    } else {
        $whereClauses[] = '(u.username LIKE :q1 OR u.phone LIKE :q2)';
    }
    $params[':q1'] = '%' . $rawSearch . '%';
    $params[':q2'] = '%' . $rawSearch . '%';
}

if ($roleFilter !== '' && $roleFilter !== 'all') {
    $whereClauses[] = 'u.role = :role';
    $params[':role'] = $roleFilter;
}

$whereSql = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// 2. Đếm tổng số kết quả
$countSql = "SELECT COUNT(*) FROM users u {$whereSql}";
$stmtCount = db()->prepare($countSql);
$stmtCount->execute($params);
$totalUsers = (int) $stmtCount->fetchColumn();

// 3. Phân trang
$allowedPerPage = [25, 30, 50, 100];
$perPage = (int) ($_GET['per_page'] ?? 25);
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 25;
}
$totalPages = max(1, (int) ceil($totalUsers / $perPage));
$page = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));
$offset = ($page - 1) * $perPage;

// 4. Lấy danh sách tài khoản
// QUY TẮC SẮP XẾP: Tài khoản Admin luôn được đưa lên đầu (sắp xếp theo id admin), sau đó đến các Thành viên (sắp xếp theo id user)
$dataSql = "
    SELECT u.id, u.username, u.phone, u.role, u.created_at, 
           (SELECT COUNT(*) FROM recipes WHERE author_id = u.id) AS recipe_count 
    FROM users u 
    {$whereSql}
    ORDER BY CASE WHEN u.role = 'admin' THEN 0 ELSE 1 END ASC, u.id ASC 
    LIMIT {$perPage} OFFSET {$offset}
";
$stmtData = db()->prepare($dataSql);
$stmtData->execute($params);
$users = $stmtData->fetchAll();

$pageTitle = 'Quản lý tài khoản - Cookio Admin';
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
        .admin-crown-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.78rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            box-shadow: 0 2px 4px rgba(234,88,12,0.25);
        }
        .user-regular-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="admin-page">
<?php require __DIR__ . '/../includes/admin-sidebar.php'; ?>
<main class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.6rem; color: #1e293b;">Quản lý tài khoản người dùng</h1>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <p class="notice success" style="margin-bottom: 1rem;">
            <?= match ($_GET['success']) {
                'user-deleted' => 'Đã xóa tài khoản thành công.',
                'role-updated' => 'Đã cập nhật vai trò người dùng thành công.',
                default => 'Thao tác hoàn tất.'
            } ?>
        </p>
    <?php endif; ?>

    <!-- THANH TÌM KIẾM & BỘ LỌC CHỌN LỰA TÀI KHOẢN -->
    <form method="get" action="<?= BASE_URL ?>/admin/manage-users.php" class="filter-form-bar">
        <!-- TÌM KIẾM THEO USERNAME / SĐT -->
        <div class="filter-group" style="flex: 1; min-width: 220px;">
            <label for="q">🔍 Tìm tài khoản (ID, Tên, SĐT):</label>
            <input type="text" name="q" id="q" class="filter-input" placeholder="Tìm theo ID (VD: 1, #10), tên đăng nhập hoặc số điện thoại..." value="<?= e($search) ?>" style="width: 100%;">
        </div>

        <!-- Ô CHỌN LỰA BẢNG: LỌC THEO VAI TRÒ -->
        <div class="filter-group">
            <label for="role">👑 Vai trò:</label>
            <select name="role" id="role" class="filter-select">
                <option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>-- Tất cả tài khoản --</option>
                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>⭐ Quản trị viên (Admin)</option>
                <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>👤 Thành viên thông thường</option>
            </select>
        </div>

        <!-- Ô CHỌN SỐ TÀI KHOẢN HIỂN THỊ TRÊN 1 TRANG -->
        <div class="filter-group">
            <label for="per_page">📄 Hiển thị:</label>
            <select name="per_page" id="per_page" class="filter-select" onchange="this.form.submit()">
                <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 tài khoản / trang</option>
                <option value="30" <?= $perPage === 30 ? 'selected' : '' ?>>30 tài khoản / trang</option>
                <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 tài khoản / trang</option>
                <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100 tài khoản / trang</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">Lọc tài khoản</button>
        <?php if ($search !== '' || $roleFilter !== 'all' || $perPage !== 25): ?>
            <a href="<?= BASE_URL ?>/admin/manage-users.php" class="btn-reset">✕ Bỏ lọc</a>
        <?php endif; ?>
    </form>

    <div class="admin-table-box">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 75px;">ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Số điện thoại</th>
                        <th>Vai trò hệ thống</th>
                        <th>Số công thức</th>
                        <th>Ngày tạo</th>
                        <th style="width: 220px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $isAdminRow = ($user['role'] === 'admin');
                    $isSelf = ((int)$user['id'] === (int)($_SESSION['user_id'] ?? 0));
                    ?>
                    <tr style="<?= $isAdminRow ? 'background: rgba(254, 243, 199, 0.2);' : '' ?>">
                        <td><strong>#<?= (int) $user['id'] ?></strong></td>
                        <td>
                            <strong style="color: #1e293b; font-size: 0.95rem;"><?= e($user['username']) ?></strong>
                            <?php if ($isSelf): ?>
                                <small style="color: #ea580c; font-weight: 700; margin-left: 4px;">(Bạn)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($user['phone'] ?? '-') ?></td>
                        <td>
                            <?php if ($isAdminRow): ?>
                                <span class="admin-crown-badge">
                                    👑 Quản trị viên
                                </span>
                            <?php else: ?>
                                <span class="user-regular-badge">
                                    👤 Thành viên
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= (int) $user['recipe_count'] ?></strong> món</td>
                        <td><?= date('d/m/Y', strtotime((string) $user['created_at'])) ?></td>
                        <td>
                            <?php if ($isAdminRow): ?>
                                <?php if ($isSelf): ?>
                                    <span style="font-size: 0.82rem; color: #94a3b8; font-style: italic;">Đang đăng nhập</span>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: #94a3b8; font-style: italic;">-</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <button type="button" class="button-sm danger" onclick="openDeleteUserModal(<?= (int)$user['id'] ?>, '<?= e(addslashes($user['username'])) ?>', <?= (int)$user['recipe_count'] ?>)">
                                    Xóa
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            Không tìm thấy tài khoản nào phù hợp với bộ lọc hiện tại.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($totalUsers > 0): ?>
            <?php
            $paginationParams = [
                'q' => $search,
                'role' => $roleFilter,
                'per_page' => $perPage !== 25 ? $perPage : '',
            ];
            $buildPageUrl = function(int $p, ?int $customPerPage = null) use ($paginationParams) {
                $params = $paginationParams;
                $params['page'] = $p;
                if ($customPerPage !== null) {
                    $params['per_page'] = $customPerPage !== 25 ? $customPerPage : '';
                }
                $filtered = array_filter($params, fn($v) => $v !== '' && $v !== 'all');
                return BASE_URL . '/admin/manage-users.php?' . http_build_query($filtered);
            };
            $pagesList = build_pagination_pages($page, $totalPages);
            ?>
            <div class="pagination-bar">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="font-size: 0.85rem; color: #64748b;">
                        Đang hiển thị trang <strong><?= $page ?></strong> trên tổng số <strong><?= $totalPages ?></strong> trang (<?= number_format($totalUsers) ?> tài khoản)
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; color: #475569;">
                        <label for="bottom_per_page" style="font-weight: 600;">Hiển thị:</label>
                        <select id="bottom_per_page" class="filter-select" style="padding: 0.2rem 0.5rem; font-size: 0.82rem; height: auto;" onchange="window.location.href = this.value;">
                            <?php foreach ($allowedPerPage as $opt): ?>
                                <option value="<?= $buildPageUrl(1, $opt) ?>" <?= $perPage === $opt ? 'selected' : '' ?>>
                                    <?= $opt ?> tài khoản / trang
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

<!-- Modal Xác nhận xóa tài khoản -->
<div id="deleteUserModalBackdrop" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 16px; max-width: 480px; width: 100%; padding: 1.75rem; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                ⚠️
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e293b;">Xác nhận xóa tài khoản</h3>
                <span style="font-size: 0.85rem; color: #64748b;">Hành động này không thể hoàn tác</span>
            </div>
        </div>

        <p style="color: #334155; font-size: 0.95rem; line-height: 1.5; margin: 0 0 1rem;">
            Bạn có chắc chắn muốn xóa tài khoản <strong id="modalUserName" style="color: #ea580c;"></strong> không?
        </p>

        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem; color: #991b1b; font-size: 0.88rem; line-height: 1.5;">
            <div style="font-weight: 800; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem; color: #b91c1c;">
                <span>🚨 CẢNH BÁO:</span>
            </div>
            Toàn bộ công thức nấu ăn (<strong id="modalUserRecipeCount">0</strong> món) cùng các bình luận và dữ liệu liên quan của người dùng này sẽ bị <strong>xóa hoàn toàn</strong> khỏi hệ thống!
        </div>

        <form id="deleteUserForm" method="post" action="<?= BASE_URL ?>/actions/admin_action.php" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" id="modalUserId" value="">
            <input type="hidden" name="return_url" value="admin/manage-users.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">

            <button type="button" onclick="closeDeleteUserModal()" style="padding: 0.55rem 1.15rem; background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #475569; cursor: pointer;">
                Hủy bỏ
            </button>
            <button type="submit" style="padding: 0.55rem 1.25rem; background: #dc2626; border: 1.5px solid #dc2626; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #ffffff; cursor: pointer; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);">
                Xác nhận xóa
            </button>
        </form>
    </div>
</div>

<script>
function openDeleteUserModal(userId, username, recipeCount) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUserName').textContent = `"${username}"`;
    document.getElementById('modalUserRecipeCount').textContent = recipeCount;
    document.getElementById('deleteUserModalBackdrop').style.display = 'flex';
}

function closeDeleteUserModal() {
    document.getElementById('deleteUserModalBackdrop').style.display = 'none';
}

window.addEventListener('click', (e) => {
    const backdrop = document.getElementById('deleteUserModalBackdrop');
    if (e.target === backdrop) {
        closeDeleteUserModal();
    }
});
</script>
<script src="<?= BASE_URL ?>/assets/js/admin.js?v=<?= time() ?>"></script>
</body>
</html>
