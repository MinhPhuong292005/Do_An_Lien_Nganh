<?php
$currentAdminPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<aside class="admin-sidebar">
    <a class="admin-brand" href="<?= BASE_URL ?>/admin/index.php">
        Cookio <small>Admin Portal</small>
    </a>
    <nav aria-label="Điều hướng quản trị">
        <a href="<?= BASE_URL ?>/admin/index.php" class="<?= $currentAdminPage === 'index.php' ? 'is-active' : '' ?>">Tổng quan</a>
        <a href="<?= BASE_URL ?>/admin/manage-recipes.php" class="<?= $currentAdminPage === 'manage-recipes.php' ? 'is-active' : '' ?>">Quản lý công thức</a>
        <a href="<?= BASE_URL ?>/admin/manage-tips.php" class="<?= $currentAdminPage === 'manage-tips.php' ? 'is-active' : '' ?>">Quản lý kinh nghiệm</a>
        <a href="<?= BASE_URL ?>/admin/manage-categories.php" class="<?= $currentAdminPage === 'manage-categories.php' ? 'is-active' : '' ?>">Quản lý danh mục</a>
        <a href="<?= BASE_URL ?>/admin/manage-comments.php" class="<?= $currentAdminPage === 'manage-comments.php' ? 'is-active' : '' ?>">Quản lý bình luận</a>
        <a href="<?= BASE_URL ?>/admin/manage-users.php" class="<?= $currentAdminPage === 'manage-users.php' ? 'is-active' : '' ?>">Quản lý tài khoản</a>
        
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="<?= BASE_URL ?>/index.php" style="color: #4ade80; font-weight: 700; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(74, 222, 128, 0.3); border-radius: 8px; padding: 0.6rem 0.85rem; display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <span>&larr; Về Website Cookio</span>
            </a>
        </div>
    </nav>
    <a class="admin-logout" href="<?= BASE_URL ?>/logout.php">Đăng xuất</a>
</aside>