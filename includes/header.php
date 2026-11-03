<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/categories_helper.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Counters for Cookpad sidebar
$sidebarTotal = (int) db()->query("SELECT COUNT(*) FROM recipes WHERE status = 'approved'")->fetchColumn();
$sidebarSaved = 0;
$sidebarMy = 0;
$sidebarLive = 0;
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $stmtS = db()->prepare("SELECT COUNT(*) FROM saved_recipes WHERE user_id = ?");
    $stmtS->execute([$uid]);
    $sidebarSaved = (int) $stmtS->fetchColumn();

    $stmtM = db()->prepare("SELECT COUNT(*), COALESCE(SUM(status = 'approved'), 0) FROM recipes WHERE author_id = ?");
    $stmtM->execute([$uid]);
    $rowM = $stmtM->fetch(PDO::FETCH_NUM);
    $sidebarMy = (int) ($rowM[0] ?? 0);
    $sidebarLive = (int) ($rowM[1] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Cookio - Nấu ngon mỗi ngày', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/user.css?v=<?= filemtime(__DIR__ . '/../assets/css/user.css') ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dark-theme.css?v=<?= filemtime(__DIR__ . '/../assets/css/dark-theme.css') ?>">
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.CSRF_TOKEN = '<?= csrf_token() ?>';
    </script>
    <script src="<?= BASE_URL ?>/assets/js/enterprise-features.js" defer></script>
</head>
<body>
<?php require_once __DIR__ . '/modal-login.php'; ?>

<!-- COOKPAD STYLE MINIMALIST SIDEBAR DRAWER (MATCHING IMAGE 1) -->
<div class="cookpad-sidebar-overlay" id="cookpadOverlay" onclick="toggleCookpadSidebar(false)"></div>
<aside class="cookpad-sidebar" id="cookpadSidebar" aria-label="Menu điều hướng Cookio">
    <!-- Header: Logo + Collapse Arrow -->
    <div class="cookpad-sidebar-header">
        <a href="<?= BASE_URL ?>/index.php" class="cookpad-brand" aria-label="Cookio">
            <div class="cookpad-hat-circle">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                    <line x1="6" y1="17" x2="18" y2="17"/>
                </svg>
            </div>
            <span class="cookpad-brand-text">Cookio</span>
        </a>
        <button type="button" class="cookpad-collapse-btn" id="btnCollapseSidebar" onclick="toggleCookpadSidebar(false)" title="Thu gọn" aria-label="Thu gọn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="11 17 6 12 11 7"/>
                <polyline points="18 17 13 12 18 7"/>
            </svg>
        </button>
    </div>

    <!-- Navigation Items with Minimalist Outline Stroke Icons (Image 1) -->
    <nav class="cookpad-nav-list">
        <!-- 1. Tìm kiếm (Active Orange Style) -->
        <a href="<?= BASE_URL ?>/index.php" class="cookpad-nav-item is-search-active" onclick="focusHeaderSearch(event)">
            <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span style="color: #ea580c; font-weight: 700;">Tìm kiếm</span>
        </a>

        <!-- 2. Premium (Ribbon bookmark with P) -->
        <a href="<?= BASE_URL ?>/views/cookbooks.php" class="cookpad-nav-item">
            <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                <text x="12" y="13" font-size="9" font-family="sans-serif" font-weight="900" text-anchor="middle" fill="#64748b" stroke="none">P</text>
            </svg>
            <span>Premium</span>
        </a>

        <!-- 3. Thống Kê Bếp (Bar chart) -->
        <a href="<?= BASE_URL ?>/views/my-recipes.php" class="cookpad-nav-item">
            <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            <span>Thống Kê Bếp</span>
        </a>

        <!-- 4. Thử Thách (Award medal with ribbon) -->
        <a href="<?= BASE_URL ?>/views/smart-fridge.php" class="cookpad-nav-item">
            <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="6"/>
                <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
            </svg>
            <span>Thử Thách</span>
        </a>

        <!-- 5. Tương Tác (Notification Bell) -->
        <a href="javascript:void(0)" class="cookpad-nav-item" onclick="toggleCookpadSidebar(false); toggleNotificationsDropdown();">
            <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span>Tương Tác</span>
        </a>

        <!-- 6. Mẹo Vặt Nhà Bếp (YummyDay Kitchen Tips) -->
        <a href="<?= BASE_URL ?>/views/kitchen-tips.php" class="cookpad-nav-item">
            <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18h6"/>
                <path d="M10 22h4"/>
                <path d="M12 2a7 7 0 0 0-7 7c0 2.38 1.19 4.47 3 5.74V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.26c1.81-1.27 3-3.36 3-5.74a7 7 0 0 0-7-7z"/>
            </svg>
            <span>Mẹo Vặt Bếp</span>
        </a>

        <?php if (is_admin()): ?>
            <a href="<?= BASE_URL ?>/admin/index.php" class="cookpad-nav-item" style="color: #ea580c; font-weight: 800; background: rgba(234, 88, 12, 0.08); border-radius: 8px;">
                <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="3" y1="9" x2="21" y2="9"/>
                    <line x1="9" y1="21" x2="9" y2="9"/>
                </svg>
                <span>⚡ Admin Portal (Quản trị)</span>
            </a>
        <?php endif; ?>

        <!-- 6. KHO MÓN NGON CỦA BẠN (Book section) -->
        <div class="cookpad-section-divider"></div>
        <div class="cookpad-section-header">
            <svg class="cookpad-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                <circle cx="12" cy="8" r="2"/>
            </svg>
            <strong>Kho Món Ngon Của Bạn</strong>
        </div>

        <!-- Mini Search in Repository -->
        <div class="cookpad-mini-search">
            <form method="get" action="<?= BASE_URL ?>/index.php">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="q" placeholder="Tìm trong kho món ngon" autocomplete="off">
            </form>
        </div>

        <!-- 4 Sub-Categories with Rounded Square Icon Containers (Image 1) -->
        <div class="cookpad-sub-list">
            <!-- Tất Cả -->
            <a href="<?= BASE_URL ?>/index.php" class="cookpad-sub-item">
                <div class="cookpad-sub-icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <div class="cookpad-sub-text">
                    <span class="sub-name">Tất Cả</span>
                    <span class="sub-count"><?= $sidebarTotal ?> món</span>
                </div>
            </a>

            <!-- Đã Lưu -->
            <a href="<?= BASE_URL ?>/views/saved-recipes.php" class="cookpad-sub-item">
                <div class="cookpad-sub-icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="cookpad-sub-text">
                    <span class="sub-name">Đã Lưu</span>
                    <span class="sub-count"><?= $sidebarSaved ?> món</span>
                </div>
            </a>

            <!-- Món Của Tôi -->
            <a href="<?= BASE_URL ?>/views/my-recipes.php" class="cookpad-sub-item">
                <div class="cookpad-sub-icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="cookpad-sub-text">
                    <span class="sub-name">Món Của Tôi</span>
                    <span class="sub-count"><?= $sidebarMy ?> món</span>
                </div>
            </a>

            <!-- Đã lên sóng -->
            <a href="<?= BASE_URL ?>/views/my-recipes.php" class="cookpad-sub-item">
                <div class="cookpad-sub-icon-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                </div>
                <div class="cookpad-sub-text">
                    <span class="sub-name">Đã lên sóng</span>
                    <span class="sub-count"><?= $sidebarLive ?> món</span>
                </div>
            </a>
        </div>
    </nav>
</aside>

<!-- MAIN SITE HEADER (COOKIO BRAND & STREAMLINED NAVIGATION) -->
<header class="yummy-site-header">
    <!-- Top Row: Logo Cookio & Pill Search Input -->
    <div class="yummy-top-bar">
        <div class="yummy-top-container">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button type="button" class="btn-sidebar-toggle" id="btnToggleCookpadSidebar" onclick="toggleCookpadSidebar(true)" title="Menu mở rộng" aria-label="Menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <a href="<?= BASE_URL ?>/index.php" class="cookio-brand" aria-label="Cookio Trang chủ">
                    <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="Cookio" class="cookio-logo-img" style="height: 44px; width: auto; display: block;">
                </a>
            </div>

            <!-- Top Search Pill (Khớp yêu cầu: Xóa logo/nút màu nâu trong thanh tìm kiếm) -->
            <div class="yummy-top-search-wrap">
                <form method="get" action="<?= BASE_URL ?>/index.php" class="yummy-search-form" id="yummySearchForm">
                    <svg class="y-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" placeholder="Tìm theo tên món hoặc nguyên liệu..." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" onclick="openHorizontalSearch()">
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Navigation Menu Bar -->
    <div class="yummy-nav-bar">
        <div class="yummy-nav-container">
            <?php
            $currScript = basename($_SERVER['PHP_SELF'] ?? '');
            $currUri = $_SERVER['REQUEST_URI'] ?? '';
            $currCat = trim((string)($_GET['cat'] ?? ''));
            $currQ = trim((string)($_GET['q'] ?? ''));

            $isHomeActive = ($currScript === 'index.php' && $currCat === '' && $currQ === '');
            $isDishesActive = ($currCat !== '');
            $isTipsActive = str_contains($currUri, 'kitchen-tips.php');
            $isFridgeActive = str_contains($currUri, 'smart-fridge.php');
            $isSavedActive = str_contains($currUri, 'saved-recipes.php');
            ?>
            <nav class="yummy-main-nav">
                <!-- 1. Trang chủ -->
                <a href="<?= BASE_URL ?>/index.php" class="y-nav-item <?= $isHomeActive ? 'is-active' : '' ?>">
                    <?= $isHomeActive ? '<span class="y-dot">•</span> ' : '' ?>Trang chủ
                </a>

                <!-- 2. Món ngon (Dropdown & Click directly opens all recipes) -->
                <div class="y-nav-dropdown-wrap">
                    <a href="<?= BASE_URL ?>/index.php?cat=all&view=vertical" class="y-nav-item <?= $isDishesActive ? 'is-active' : '' ?>" title="Xem tất cả công thức món ngon theo hàng dọc">
                        <?= $isDishesActive ? '<span class="y-dot">•</span> ' : '' ?>Món ngon
                        <svg class="y-nav-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>
                    <div class="y-dropdown-menu">
                        <?php
                        $headerCategories = get_all_categories();
                        foreach ($headerCategories as $hCat):
                        ?>
                            <a href="<?= BASE_URL ?>/index.php?cat=<?= urlencode($hCat['name']) ?>">
                                <?= e($hCat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Kinh nghiệm hay -->
                <a href="<?= BASE_URL ?>/views/kitchen-tips.php" class="y-nav-item <?= $isTipsActive ? 'is-active' : '' ?>">
                    <?= $isTipsActive ? '<span class="y-dot">•</span> ' : '' ?>Kinh nghiệm hay
                </a>

                <!-- 4. Nguyên liệu (Tách riêng chức năng tủ lạnh thông minh ở giữa Kinh nghiệm hay và Món yêu thích) -->
                <a href="<?= BASE_URL ?>/views/smart-fridge.php" class="y-nav-item <?= $isFridgeActive ? 'is-active' : '' ?>">
                    <?= $isFridgeActive ? '<span class="y-dot">•</span> ' : '' ?>Nguyên liệu
                </a>

                <!-- 5. Món yêu thích (Đổi tên từ Món đã lưu) -->
                <a href="<?= BASE_URL ?>/views/saved-recipes.php" class="y-nav-item <?= $isSavedActive ? 'is-active' : '' ?>">
                    <?= $isSavedActive ? '<span class="y-dot">•</span> ' : '' ?>Món yêu thích
                </a>
            </nav>

            <!-- Right Action Buttons: Viết món mới (Click ra Login nếu chưa đăng nhập) & User Actions -->
            <div class="yummy-nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/views/create-recipe.php" class="button button-create" style="padding: 0.45rem 1.1rem; border-radius: 9999px; font-weight: 700; font-size: 0.88rem;">
                        + Viết món mới
                    </a>

                    <!-- Notification Bell (Khớp Ảnh 1: Hiện rõ logo hình cái chuông thông báo) -->
                    <div class="notification-wrapper">
                        <button type="button" class="notification-btn" id="btnNotifications" title="Thông báo" aria-label="Thông báo" onclick="toggleNotificationsDropdown()" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; cursor: pointer;">
                            <svg class="cookio-bell-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" style="display: block; width: 20px; height: 20px; flex-shrink: 0;">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notif-badge" id="notifBadge" style="display: none;">0</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
                            <div class="notification-header">
                                <span>Thông báo của bạn</span>
                                <button type="button" class="btn-mark-all-read" onclick="markAllNotificationsRead()">Đã đọc tất cả</button>
                            </div>
                            <div class="notification-tabs">
                                <button type="button" class="notif-tab is-active" data-filter="all" onclick="filterNotifications('all', this)">Tất cả</button>
                                <button type="button" class="notif-tab" data-filter="system" onclick="filterNotifications('system', this)">Hệ thống</button>
                                <button type="button" class="notif-tab" data-filter="like" onclick="filterNotifications('like', this)">Yêu thích</button>
                                <button type="button" class="notif-tab" data-filter="comment" onclick="filterNotifications('comment', this)">Bình luận</button>
                                <button type="button" class="notif-tab" data-filter="follow" onclick="filterNotifications('follow', this)">Theo dõi</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div style="padding: 1.5rem; text-align: center; color: #64748b; font-size: 0.85rem;">Đang tải...</div>
                            </div>
                        </div>
                    </div>

                    <?php if (is_admin()): ?>
                        <a href="<?= BASE_URL ?>/admin/index.php" class="admin-portal-btn" style="padding: 0.38rem 0.95rem; border-radius: 9999px; font-weight: 800; font-size: 0.82rem; background: #0f172a; color: #fed7aa; border: 1.5px solid #ea580c; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25); text-decoration: none;" title="Truy cập Bảng điều khiển Quản trị">
                            <span style="font-size: 0.95rem;">⚡</span>
                            <span>Admin Portal</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>/views/profile.php" class="user-menu-chip" title="Xem hồ sơ">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?= BASE_URL ?>/<?= e($_SESSION['avatar']) ?>" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; display: inline-block;">
                        <?php else: ?>
                            <span class="user-avatar-circle"><?= mb_substr((string) $_SESSION['username'], 0, 1) ?></span>
                        <?php endif; ?>
                        <span><?= e((string) $_SESSION['username']) ?></span>
                    </a>

                    <a href="<?= BASE_URL ?>/logout.php" style="color: #64748b; font-size: 0.88rem; display: flex; align-items: center;" title="Đăng xuất">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </a>
                <?php else: ?>
                    <!-- Nút Viết món mới khi click vào sẽ bật form/modal đăng nhập và tự động chuyển trang khi đăng nhập thành công -->
                    <button type="button" class="button button-create" onclick="openLoginForCreateRecipe()" style="padding: 0.45rem 1.1rem; border-radius: 9999px; font-weight: 700; font-size: 0.88rem;">
                        + Viết món mới
                    </button>

                    <!-- Nút Đăng nhập -->
                    <button type="button" class="y-pill-btn-brown" data-open-login style="padding: 0.45rem 1.15rem; font-size: 0.88rem;">
                        Đăng nhập
                    </button>
                <?php endif; ?>

                <!-- Dark Mode Toggle Button -->
                <button type="button" class="dark-mode-toggle" id="btnToggleDarkMode" title="Chuyển chế độ sáng/tối" aria-label="Chuyển chế độ sáng/tối">
                    <span class="theme-icon">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- REJECTION NOTICE MODAL (Khung hiển thị ở giữa màn hình khi bài đăng bị từ chối) -->
    <div id="rejectionNoticeModal" class="cookio-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 10002; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem; white-space: normal !important;">
        <div style="background: #ffffff; border-radius: 1.25rem; max-width: 440px; width: 100%; padding: 2rem 1.75rem; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); position: relative; animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1); white-space: normal !important; box-sizing: border-box !important;">
            
            <button type="button" onclick="closeRejectionNoticeModal()" style="position: absolute; top: 1rem; right: 1rem; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #64748b; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b';" title="Đóng">&times;</button>

            <div style="width: 58px; height: 58px; border-radius: 50%; background: #fef2f2; border: 2px solid #fecaca; margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>

            <h3 style="margin: 0 0 0.85rem; font-size: 1.2rem; font-weight: 800; color: #1e293b; line-height: 1.35; white-space: normal !important;">
                Thông báo kiểm duyệt nội dung
            </h3>

            <div id="rejectionModalMessage" style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 0.85rem 1rem; font-size: 0.95rem; font-weight: 700; color: #9a3412; margin-bottom: 1.25rem; line-height: 1.5; text-align: center; white-space: normal !important; word-break: break-word !important; overflow-wrap: break-word !important;">
                Rất tiếc, bài đăng không được duyệt.
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <button type="button" onclick="closeRejectionNoticeModal()" class="button button-create" style="padding: 0.65rem 2rem; border-radius: 9999px; font-weight: 800; font-size: 0.9rem; background: #ea580c; color: #ffffff; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25);">
                    Đã hiểu
                </button>
                <a href="<?= BASE_URL ?>/views/profile.php" class="button button-outline" style="padding: 0.65rem 1.35rem; border-radius: 9999px; font-weight: 700; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    Quản lý bài của tôi
                </a>
            </div>
        </div>
    </div>

    <!-- HORIZONTAL FULL-WIDTH LIVE SEARCH OVERLAY -->
    <div class="horizontal-search-overlay" id="horizontalSearchOverlay" style="display: none;">
        <div class="horizontal-search-backdrop" onclick="closeHorizontalSearch()"></div>
        <div class="horizontal-search-container">
            <div class="horizontal-search-bar">
                <svg class="h-search-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="horizontalSearchInput" class="horizontal-search-input" placeholder="Tìm kiếm món ăn, nguyên liệu, cách nấu..." autocomplete="off" oninput="handleHorizontalSearchInput(this.value)">
                <button type="button" id="hSearchClear" class="btn-h-search-clear" onclick="clearHorizontalSearch()" title="Xóa tìm kiếm" style="display: none;">&times;</button>
                <button type="button" class="btn-h-search-close" onclick="closeHorizontalSearch()" title="Đóng tìm kiếm (Esc)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="horizontal-search-body">
                <!-- Recent Searches -->
                <div id="hRecentWrap" class="h-suggestions-section" style="display: none;">
                    <div class="h-section-title">Lịch sử tìm kiếm gần đây</div>
                    <div id="hRecentTags" class="h-tags-flex"></div>
                </div>

                <!-- Trending Topics / Shortcuts -->
                <div class="h-suggestions-section">
                    <div class="h-section-title">Gợi ý tìm kiếm phổ biến</div>
                    <div class="h-tags-flex">
                        <a href="<?= BASE_URL ?>/index.php?q=gà" class="h-tag-pill">Thịt gà</a>
                        <a href="<?= BASE_URL ?>/index.php?q=bò" class="h-tag-pill">Thịt bò</a>
                        <a href="<?= BASE_URL ?>/index.php?q=sườn" class="h-tag-pill">Sườn non</a>
                        <a href="<?= BASE_URL ?>/index.php?q=canh" class="h-tag-pill">Món canh</a>
                        <a href="<?= BASE_URL ?>/index.php?q=nộm" class="h-tag-pill">Món nộm</a>
                        <a href="<?= BASE_URL ?>/index.php?q=cơm+chiên" class="h-tag-pill">Cơm chiên</a>
                        <a href="<?= BASE_URL ?>/index.php?cat=Món+kho" class="h-tag-pill">Món kho đưa cơm</a>
                    </div>
                </div>

                <!-- Live Results -->
                <div id="hLiveResultsWrap" class="h-live-results-wrap" style="display: none;">
                    <div class="h-results-header">
                        <span class="h-section-title">Kết quả gợi ý</span>
                        <span id="hResultsCount" class="h-results-badge">(0 món)</span>
                    </div>
                    <div id="hResultsGrid" class="h-results-grid"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
    function openCookioLogin(redirectPath = '') {
        const redirectInput = document.getElementById('loginRedirectTo');
        if (redirectInput) {
            redirectInput.value = redirectPath;
        }
        const modal = document.getElementById('loginModal');
        if (modal) {
            if (typeof modal.showModal === 'function') {
                modal.showModal();
            } else {
                modal.setAttribute('open', '');
            }
        }
    }

    function openLoginForCreateRecipe() {
        openCookioLogin('views/create-recipe.php');
    }

    window.showRejectionNoticeModal = function(title, message) {
        const modal = document.getElementById('rejectionNoticeModal');
        const msgEl = document.getElementById('rejectionModalMessage');
        if (msgEl) {
            msgEl.innerHTML = message || `Rất tiếc, bài đăng «<strong>${title || ''}</strong>» không được duyệt.`;
        }
        const notifDropdown = document.getElementById('notificationDropdown');
        if (notifDropdown) notifDropdown.style.display = 'none';
        if (modal) modal.style.display = 'flex';
    };

    window.closeRejectionNoticeModal = function() {
        const modal = document.getElementById('rejectionNoticeModal');
        if (modal) modal.style.display = 'none';
    };

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-open-login]');
        if (btn) {
            e.preventDefault();
            const redirectTo = btn.getAttribute('data-redirect') || '';
            openCookioLogin(redirectTo);
        }
        const closeBtn = e.target.closest('[data-close-modal]');
        if (closeBtn) {
            e.preventDefault();
            const modal = document.getElementById('loginModal');
            if (modal) {
                if (typeof modal.close === 'function') {
                    modal.close();
                } else {
                    modal.removeAttribute('open');
                }
            }
        }
    });
    </script>
</header>
<main class="page-content">
