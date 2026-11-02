<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pageTitle = 'Hồ sơ tài khoản - Cookio';
require __DIR__ . '/../includes/header.php';

if (!is_logged_in()) {
    echo '<section class="saved-recipes" style="padding: 4rem 1rem; text-align: center;"><div class="empty-state-box"><p style="font-size: 1.1rem; color: #64748b; margin-bottom: 1.5rem;">Vui lòng đăng nhập để xem thông tin hồ sơ tài khoản.</p><button type="button" class="button button-create" data-open-login style="padding: 0.75rem 2rem;">Đăng nhập ngay</button></div></section>';
    require __DIR__ . '/../includes/modal-login.php';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$userId = (int) $_SESSION['user_id'];
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// 1. Stats calculation
$totalRecipes = (int) db()->query("SELECT COUNT(*) FROM recipes WHERE author_id = $userId")->fetchColumn();
$approvedRecipes = (int) db()->query("SELECT COUNT(*) FROM recipes WHERE author_id = $userId AND status = 'approved'")->fetchColumn();
$pendingRecipes = (int) db()->query("SELECT COUNT(*) FROM recipes WHERE author_id = $userId AND status = 'pending'")->fetchColumn();

// Followers & Following counts
$followersCount = (int) db()->query("SELECT COUNT(*) FROM follows WHERE author_id = $userId")->fetchColumn();
$followingCount = (int) db()->query("SELECT COUNT(*) FROM follows WHERE follower_id = $userId")->fetchColumn();

// Total Likes received across all dishes posted by this user
$stmtTotalLikes = db()->prepare("
    SELECT COUNT(rl.id) 
    FROM recipe_likes rl
    JOIN recipes r ON r.id = rl.recipe_id
    WHERE r.author_id = ?
");
$stmtTotalLikes->execute([$userId]);
$totalLikesReceived = (int) $stmtTotalLikes->fetchColumn();

// Authentic rating received on author's dishes (Defaults to 0/5 if unrated)
$stmtRating = db()->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS count_rating
    FROM comments 
    WHERE recipe_id IN (SELECT id FROM recipes WHERE author_id = ?) AND rating > 0 AND (is_hidden = 0 OR is_hidden IS NULL)
");
$stmtRating->execute([$userId]);
$ratingRow = $stmtRating->fetch();
$totalRatingsCount = (int) ($ratingRow['count_rating'] ?? 0);
if ($totalRatingsCount > 0 && $ratingRow['avg_rating'] !== null) {
    $avgRating = round((float) $ratingRow['avg_rating'], 1);
    $avgRatingText = $avgRating . '/5';
} else {
    $avgRating = 0.0;
    $avgRatingText = '0/5';
}

require_once __DIR__ . '/../includes/badges.php';
$badgeData = check_and_get_user_badges($userId);
$displayedBadges = $badgeData['displayed'];
$pendingBadges = $badgeData['pending'];

// 2. Fetch User Recipes (Approved and Pending)
$stmtRecipes = db()->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM comments WHERE recipe_id = r.id) AS comments_count
    FROM recipes r 
    WHERE r.author_id = ? 
    ORDER BY r.id DESC
");
$stmtRecipes->execute([$userId]);
$myRecipes = $stmtRecipes->fetchAll();

// 3. Fetch Followers list
$stmtFollowers = db()->prepare("
    SELECT u.id, u.username, u.role, u.created_at,
           (SELECT COUNT(*) FROM recipes WHERE author_id = u.id AND status = 'approved') AS recipe_count
    FROM follows f 
    INNER JOIN users u ON u.id = f.follower_id 
    WHERE f.author_id = ? 
    ORDER BY f.created_at DESC
");
$stmtFollowers->execute([$userId]);
$followersList = $stmtFollowers->fetchAll();

// 4. Fetch Following chefs list
$stmtFollowing = db()->prepare("
    SELECT u.id, u.username, u.role, u.created_at,
           (SELECT COUNT(*) FROM recipes WHERE author_id = u.id AND status = 'approved') AS recipe_count
    FROM follows f 
    INNER JOIN users u ON u.id = f.author_id 
    WHERE f.follower_id = ? 
    ORDER BY f.created_at DESC
");
$stmtFollowing->execute([$userId]);
$followingList = $stmtFollowing->fetchAll();

// 5. Fetch Reviews & Ratings received on author's dishes
$stmtReviews = db()->prepare("
    SELECT c.*, u.username AS reviewer_name, r.id AS recipe_id, r.title AS recipe_title, r.image_url
    FROM comments c
    INNER JOIN users u ON u.id = c.user_id
    INNER JOIN recipes r ON r.id = c.recipe_id
    WHERE r.author_id = ? AND (c.is_hidden = 0 OR c.is_hidden IS NULL)
    ORDER BY c.created_at DESC
");
$stmtReviews->execute([$userId]);
$reviewsList = $stmtReviews->fetchAll();

$showEditByDefault = isset($_GET['edit']) && $_GET['edit'] === '1';
?>

<div class="recipe-detail-container" style="padding-top: 1.75rem; padding-bottom: 4rem;">

    <!-- Breadcrumb -->
    <nav class="cookio-breadcrumb" style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.88rem; margin-bottom: 1.5rem;">
        <a href="<?= BASE_URL ?>/index.php" style="color: #ea580c; text-decoration: none; font-weight: 600;">Trang chủ</a>
        <span style="color: #94a3b8;">&rsaquo;</span>
        <span style="color: #64748b;">Hồ sơ đầu bếp</span>
        <span style="color: #94a3b8;">&rsaquo;</span>
        <span style="color: #334155; font-weight: 700;"><?= e($user['username']) ?></span>
    </nav>

    <?php if (isset($_GET['success'])): ?>
        <div class="notice success" style="margin-bottom: 1.5rem;">
            <?= match ($_GET['success']) {
                'info-updated'     => '✅ Cập nhật thông tin tài khoản thành công!',
                'password-updated' => '✅ Đổi mật khẩu thành công! Mật khẩu mới đã có hiệu lực.',
                'recipe-deleted'   => '✅ Đã xóa công thức nấu ăn thành công.',
                'badge-updated'    => '✅ Đã cập nhật cài đặt hiển thị danh hiệu thành công!',
                default            => '✅ Thao tác thành công!'
            } ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($pendingBadges)): ?>
        <!-- BADGE RECOGNITION CONFIRMATION PROMPTS (Theo yêu cầu: Thông báo khi đạt danh hiệu và xác nhận hiển thị) -->
        <?php foreach ($pendingBadges as $pBadge): ?>
            <div class="badge-award-banner" style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 1rem; padding: 1.15rem 1.35rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.1);">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <span style="font-size: 2rem;"><?= $pBadge['icon'] ?></span>
                    <div>
                        <div style="font-weight: 800; color: #92400e; font-size: 1rem; margin-bottom: 0.2rem;">
                            🎉 Chúc mừng! Bạn đã nhận được danh hiệu «<?= e($pBadge['name']) ?>»
                        </div>
                        <div style="font-size: 0.86rem; color: #b45309;">
                            Thành tích: <?= e($pBadge['desc']) ?>. Bạn có muốn hiển thị danh hiệu danh dự này trên hồ sơ bếp của mình không?
                        </div>
                    </div>
                </div>
                <form method="post" action="<?= BASE_URL ?>/actions/badge_action.php" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="badge_key" value="<?= e($pBadge['key']) ?>">
                    <button type="submit" name="action" value="show" class="button button-create" style="padding: 0.45rem 1.15rem; font-size: 0.86rem; border-radius: 9999px; font-weight: 800;">
                        ✓ Có, hiển thị danh hiệu
                    </button>
                    <button type="submit" name="action" value="hide" class="button button-outline" style="padding: 0.45rem 1rem; font-size: 0.86rem; border-radius: 9999px;">
                        ✕ Để sau
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice error" style="margin-bottom: 1.5rem;">
            <?= match ($_GET['error']) {
                'csrf'                   => '⚠️ Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn.',
                'username-taken'         => '⚠️ Tên tài khoản này đã có người sử dụng. Vui lòng chọn tên khác.',
                'username-invalid'       => '⚠️ Tên tài khoản không hợp lệ (tối thiểu 2 ký tự).',
                'avatar-invalid'         => '⚠️ Tệp ảnh đại diện không hợp lệ (chỉ chấp nhận ảnh JPG, PNG, WEBP).',
                'avatar-too-large'       => '⚠️ Dung lượng ảnh đại diện quá lớn (tối đa 5MB).',
                'current-password-wrong' => '⚠️ Mật khẩu hiện tại không chính xác.',
                'password-mismatch'      => '⚠️ Mật khẩu mới và mật khẩu xác nhận không trùng khớp.',
                'password-short'         => '⚠️ Mật khẩu mới phải có độ dài từ 3 ký tự trở lên.',
                default                  => '⚠️ Đã có lỗi xảy ra. Vui lòng thử lại.'
            } ?>
        </div>
    <?php endif; ?>

    <!-- =====================================================================
         PROFILE VIEW CARD (DESIGNER MODERN LAYOUT)
         ===================================================================== -->
    <div id="profileViewSection" class="cookio-profile-card" style="<?= $showEditByDefault ? 'display: none;' : '' ?> background: #ffffff; border-radius: 1.25rem; border: 1px solid #e2e8f0; padding: 2.25rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 2rem;">
        
        <!-- Top Profile Row: Cột Avatar với nút Chỉnh sửa nhỏ ngay phía dưới + Cột thông tin tài khoản -->
        <div style="display: flex; align-items: flex-start; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem;">
            
            <!-- Cột Avatar & Nút chỉnh sửa nhỏ ngay dưới Avatar -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.65rem; flex-shrink: 0;">
                <div style="width: 96px; height: 96px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #ffffff; font-size: 2.5rem; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(234, 88, 12, 0.25); border: 3.5px solid #ffffff;">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= BASE_URL ?>/<?= e($user['avatar']) ?>" alt="<?= e($user['username']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <?php else: ?>
                        <?= mb_strtoupper(mb_substr($user['username'], 0, 1)) ?>
                    <?php endif; ?>
                </div>

                <!-- Khung nhỏ chỉnh sửa tài khoản ngay dưới avatar -->
                <button type="button" onclick="toggleEditProfile(true)" 
                        title="Chỉnh sửa thông tin tài khoản"
                        style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.38rem 0.85rem; background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 9999px; font-weight: 700; font-size: 0.8rem; color: #ea580c; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 6px rgba(234, 88, 12, 0.08); white-space: nowrap;"
                        onmouseover="this.style.background='#ea580c'; this.style.color='#ffffff'; this.style.borderColor='#ea580c';"
                        onmouseout="this.style.background='#ffffff'; this.style.color='#ea580c'; this.style.borderColor='#fed7aa';">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                    <span>Chỉnh sửa tài khoản</span>
                </button>
            </div>

            <!-- Cột thông tin cá nhân: Tên, Danh hiệu, Bio, Ngày gia nhập, SĐT (Không chứa nút Thêm món mới hay Admin portal) -->
            <div style="flex: 1; min-width: 250px;">
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.65rem; margin-bottom: 0.5rem;">
                    <h1 style="font-size: 1.85rem; font-weight: 800; color: #1e293b; margin: 0; line-height: 1.2;">
                        <?= e($user['username']) ?>
                    </h1>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span style="background: #fee2e2; color: #b91c1c; font-size: 0.78rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid #fecaca;">
                            🛡️ Quản trị viên
                        </span>
                    <?php endif; ?>

                    <!-- Authentic Badges -->
                    <?php foreach ($displayedBadges as $b): ?>
                        <span style="<?= $b['badge_style'] ?> font-size: 0.78rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem;" title="<?= e($b['desc']) ?>">
                            <?= $b['icon'] ?> <?= e($b['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <p style="margin: 0 0 0.6rem; color: #475569; font-size: 0.95rem; line-height: 1.5; max-width: 650px;">
                    <?= !empty($user['bio']) ? e($user['bio']) : 'Thành viên đam mê nấu nướng và chia sẻ những công thức chuẩn vị cho cộng đồng Cookio.' ?>
                </p>

                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 1.25rem; font-size: 0.85rem; color: #64748b;">
                    <span>📅 Gia nhập: <strong><?= date('d/m/Y', strtotime((string)$user['created_at'])) ?></strong></span>
                    <?php if (!empty($user['phone'])): ?>
                        <span>📞 <strong><?= e($user['phone']) ?></strong></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 5 Stats (Thiết kế thanh lịch, gọn gàng, bấm vào để xem nội dung tương ứng) -->
        <div class="profile-interactive-stats" style="display: flex; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem; justify-content: flex-start;">
            
            <!-- 1. Công thức món -->
            <button type="button" class="profile-stat-box is-active" id="statBtn-recipes" onclick="switchProfileTab('recipes')"
                    style="flex: 1; min-width: 125px; background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 1rem; padding: 1rem 0.65rem; text-align: center; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                <div style="font-size: 1.65rem; font-weight: 800; color: #ea580c; line-height: 1; white-space: nowrap; flex-shrink: 0;"><?= $approvedRecipes ?></div>
                <div style="font-size: 0.85rem; font-weight: 400; color: #9a3412; text-transform: lowercase; line-height: 1.2;">công thức món</div>
            </button>

            <!-- 2. Người theo dõi -> theo dõi -->
            <button type="button" class="profile-stat-box" id="statBtn-followers" onclick="switchProfileTab('followers')"
                    style="flex: 1; min-width: 125px; background: #faf5ff; border: 1.5px solid #e9d5ff; border-radius: 1rem; padding: 1rem 0.65rem; text-align: center; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                <div style="font-size: 1.65rem; font-weight: 800; color: #7c3aed; line-height: 1; white-space: nowrap; flex-shrink: 0;"><?= $followersCount ?></div>
                <div style="font-size: 0.85rem; font-weight: 400; color: #6b21a8; text-transform: lowercase; white-space: nowrap; flex-shrink: 0;">theo dõi</div>
            </button>

            <!-- 3. Đang theo dõi -->
            <button type="button" class="profile-stat-box" id="statBtn-following" onclick="switchProfileTab('following')"
                    style="flex: 1; min-width: 125px; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 1rem; padding: 1rem 0.65rem; text-align: center; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                <div style="font-size: 1.65rem; font-weight: 800; color: #2563eb; line-height: 1; white-space: nowrap; flex-shrink: 0;"><?= $followingCount ?></div>
                <div style="font-size: 0.85rem; font-weight: 400; color: #1e40af; text-transform: lowercase; white-space: nowrap; flex-shrink: 0;">đang theo dõi</div>
            </button>

            <!-- 4. Lượt thích -->
            <button type="button" class="profile-stat-box" id="statBtn-likes" onclick="switchProfileTab('recipes')"
                    style="flex: 1; min-width: 125px; background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 1rem; padding: 1rem 0.65rem; text-align: center; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                <div style="font-size: 1.65rem; font-weight: 800; color: #dc2626; line-height: 1; white-space: nowrap; flex-shrink: 0;"><?= $totalLikesReceived ?></div>
                <div style="font-size: 0.85rem; font-weight: 400; color: #991b1b; text-transform: lowercase; white-space: nowrap; flex-shrink: 0;">lượt thích</div>
            </button>

            <!-- 5. Điểm đánh giá -> đánh giá (hiển thị 4/5 ⭐ gọn gàng trên 1 dòng) -->
            <button type="button" class="profile-stat-box" id="statBtn-reviews" onclick="switchProfileTab('reviews')"
                    style="flex: 1; min-width: 130px; background: #fefce8; border: 1.5px solid #fef08a; border-radius: 1rem; padding: 1rem 0.65rem; text-align: center; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;">
                <div style="font-size: 1.45rem; font-weight: 800; color: #ca8a04; line-height: 1; display: inline-flex; align-items: center; gap: 0.25rem; white-space: nowrap; flex-shrink: 0;">
                    <span><?= $avgRatingText ?></span>
                    <span style="font-size: 1.15rem; line-height: 1;">⭐</span>
                </div>
                <div style="font-size: 0.85rem; font-weight: 400; color: #854d0e; text-transform: lowercase; white-space: nowrap; flex-shrink: 0;">đánh giá</div>
            </button>
        </div>
    </div>

    <!-- =====================================================================
         PROFILE EDIT FORM (Clean & Accessible)
         ===================================================================== -->
    <div id="profileEditSection" class="cookio-profile-card" style="<?= $showEditByDefault ? '' : 'display: none;' ?> background: #ffffff; border-radius: 1.25rem; border: 1.5px solid #ea580c; padding: 2.25rem; box-shadow: 0 6px 25px rgba(234, 88, 12, 0.08); margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; padding-bottom: 0.85rem; border-bottom: 1.5px solid #f1f5f9;">
            <h2 style="font-size: 1.35rem; font-weight: 800; color: #ea580c; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                ✏️ Chỉnh sửa thông tin hồ sơ
            </h2>
            <button type="button" onclick="toggleEditProfile(false)" style="background: #f1f5f9; border: none; padding: 0.45rem 1rem; border-radius: 9999px; color: #475569; font-size: 0.88rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.35rem;">
                &larr; Quay lại hồ sơ
            </button>
        </div>

        <form method="post" action="<?= BASE_URL ?>/actions/profile_action.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="update_info">

            <!-- 1. Thay đổi Ảnh đại diện (Avatar) -->
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem; padding: 1.25rem; background: #fff7ed; border: 1.5px dashed #fed7aa; border-radius: 1rem; flex-wrap: wrap;">
                <div style="width: 76px; height: 76px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #ffffff; font-size: 2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2); flex-shrink: 0;" id="avatarPreviewContainer">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= BASE_URL ?>/<?= e($user['avatar']) ?>" alt="Avatar" id="avatarPreviewImg" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <?php else: ?>
                        <span id="avatarPreviewInitial"><?= mb_strtoupper(mb_substr($user['username'], 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 220px;">
                    <label style="font-weight: 700; font-size: 0.92rem; color: #9a3412; display: block; margin-bottom: 0.35rem;">
                        🖼️ Thay đổi ảnh đại diện (Avatar)
                    </label>
                    <input type="file" name="avatar" id="avatarInput" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewAvatarImage(this)"
                           style="font-size: 0.88rem; color: #475569; display: block; margin-bottom: 0.25rem;">
                    <div style="font-size: 0.78rem; color: #c2410c;">
                        Chấp nhận ảnh JPG, PNG, WEBP (Dung lượng tối đa 5MB)
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                <!-- 2. Thay đổi Tên tài khoản (Username) -->
                <label style="display: block;">
                    <span style="font-weight: 700; font-size: 0.88rem; color: #334155; display: block; margin-bottom: 0.4rem;">
                        👤 Tên tài khoản <span style="color: #ea580c;">*</span>
                    </span>
                    <input type="text" name="username" value="<?= e($user['username']) ?>" required minlength="2" maxlength="50" placeholder="Nhập tên tài khoản"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 0.65rem; font-size: 0.95rem; background: #ffffff; color: #1e293b; box-sizing: border-box; font-family: inherit;">
                </label>

                <!-- 3. Số điện thoại -->
                <label style="display: block;">
                    <span style="font-weight: 700; font-size: 0.88rem; color: #334155; display: block; margin-bottom: 0.4rem;">Số điện thoại liên hệ</span>
                    <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="Ví dụ: 0912345678"
                           style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 0.65rem; font-size: 0.95rem; box-sizing: border-box; font-family: inherit;">
                </label>
            </div>

            <!-- Giới thiệu (Bio) -->
            <label style="display: block; margin-bottom: 1.5rem;">
                <span style="font-weight: 700; font-size: 0.88rem; color: #334155; display: block; margin-bottom: 0.4rem;">Giới thiệu bản thân (Bio)</span>
                <textarea name="bio" rows="3" placeholder="Chia sẻ câu chuyện nấu nướng, món tủ hoặc kinh nghiệm làm bếp..."
                          style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 0.65rem; font-size: 0.95rem; box-sizing: border-box; font-family: inherit; resize: vertical;"><?= e($user['bio'] ?? '') ?></textarea>
            </label>

            <button type="submit" class="button button-create" style="padding: 0.75rem 2rem; font-size: 0.95rem; font-weight: 800; border-radius: 0.65rem; cursor: pointer;">
                Lưu thông tin hồ sơ
            </button>
        </form>

        <!-- Đổi mật khẩu -->
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed #cbd5e1;">
            <details>
                <summary style="font-weight: 700; color: #ea580c; font-size: 0.95rem; cursor: pointer; outline: none;">
                    🔐 Đổi mật khẩu tài khoản
                </summary>
                <form method="post" action="<?= BASE_URL ?>/actions/profile_action.php" style="margin-top: 1.25rem; max-width: 420px;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="change_password">

                    <label style="display: block; margin-bottom: 0.75rem;">
                        <span style="font-weight: 600; font-size: 0.85rem; color: #475569; display: block; margin-bottom: 0.25rem;">Mật khẩu hiện tại</span>
                        <input type="password" name="current_password" required placeholder="Nhập mật khẩu đang dùng"
                               style="width: 100%; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.9rem; box-sizing: border-box;">
                    </label>

                    <label style="display: block; margin-bottom: 0.75rem;">
                        <span style="font-weight: 600; font-size: 0.85rem; color: #475569; display: block; margin-bottom: 0.25rem;">Mật khẩu mới</span>
                        <input type="password" name="new_password" required placeholder="Tối thiểu 3 ký tự"
                               style="width: 100%; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.9rem; box-sizing: border-box;">
                    </label>

                    <label style="display: block; margin-bottom: 1.25rem;">
                        <span style="font-weight: 600; font-size: 0.85rem; color: #475569; display: block; margin-bottom: 0.25rem;">Xác nhận mật khẩu mới</span>
                        <input type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu mới"
                               style="width: 100%; padding: 0.65rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 0.5rem; font-size: 0.9rem; box-sizing: border-box;">
                    </label>

                    <button type="submit" style="background: #334155; color: #ffffff; border: none; padding: 0.65rem 1.5rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.88rem; cursor: pointer;">
                        Cập nhật mật khẩu mới
                    </button>
                </form>
            </details>
        </div>
    </div>

    <!-- =====================================================================
         INTERACTIVE TABS CONTENT CONTAINER
         1. Công thức món ăn tài khoản đăng
         2. Người theo dõi
         3. Đang theo dõi
         4. Điểm đánh giá & Nhận xét
         ===================================================================== -->
    <div class="profile-tabs-wrapper">
        
        <!-- Tab Navigation Bar: Nằm trên cùng 1 dòng, không xuống hàng -->
        <div class="profile-tabs-nav" style="display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.75rem; padding-bottom: 0.4rem; scrollbar-width: none; -ms-overflow-style: none;">
            <button type="button" class="profile-nav-tab is-active" id="tabNav-recipes" onclick="switchProfileTab('recipes')"
                    style="padding: 0.65rem 0.95rem; font-weight: 700; font-size: 0.92rem; border: none; background: none; border-bottom: 3px solid #ea580c; color: #ea580c; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; white-space: nowrap; flex-shrink: 0;">
                <span>🍳 Công thức đã đăng</span>
                <span style="background: #ffedd5; color: #c2410c; padding: 0.15rem 0.55rem; border-radius: 9999px; font-size: 0.8rem;"><?= count($myRecipes) ?></span>
            </button>

            <button type="button" class="profile-nav-tab" id="tabNav-followers" onclick="switchProfileTab('followers')"
                    style="padding: 0.65rem 0.95rem; font-weight: 700; font-size: 0.92rem; border: none; background: none; border-bottom: 3px solid transparent; color: #64748b; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; white-space: nowrap; flex-shrink: 0;">
                <span>👥 Theo dõi</span>
                <span style="background: #f1f5f9; color: #475569; padding: 0.15rem 0.55rem; border-radius: 9999px; font-size: 0.8rem;"><?= count($followersList) ?></span>
            </button>

            <button type="button" class="profile-nav-tab" id="tabNav-following" onclick="switchProfileTab('following')"
                    style="padding: 0.65rem 0.95rem; font-weight: 700; font-size: 0.92rem; border: none; background: none; border-bottom: 3px solid transparent; color: #64748b; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; white-space: nowrap; flex-shrink: 0;">
                <span>✨ Đang theo dõi</span>
                <span style="background: #f1f5f9; color: #475569; padding: 0.15rem 0.55rem; border-radius: 9999px; font-size: 0.8rem;"><?= count($followingList) ?></span>
            </button>

            <button type="button" class="profile-nav-tab" id="tabNav-reviews" onclick="switchProfileTab('reviews')"
                    style="padding: 0.65rem 0.95rem; font-weight: 700; font-size: 0.92rem; border: none; background: none; border-bottom: 3px solid transparent; color: #64748b; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; white-space: nowrap; flex-shrink: 0;">
                <span>⭐ Đánh giá & Nhận xét</span>
                <span style="background: #fef08a; color: #854d0e; padding: 0.15rem 0.55rem; border-radius: 9999px; font-size: 0.8rem;"><?= count($reviewsList) ?></span>
            </button>
        </div>

        <!-- -------------------------------------------------------------
             TAB PANEL 1: CÔNG THỨC MÓN ĂN TÀI KHOẢN ĐĂNG
             ------------------------------------------------------------- -->
        <div id="panel-recipes" class="profile-tab-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #1e293b;">
                    Danh sách công thức của bạn (<?= count($myRecipes) ?> món)
                </h3>
                <a href="<?= BASE_URL ?>/views/create-recipe.php" class="button button-create" style="padding: 0.45rem 1.15rem; font-size: 0.86rem; border-radius: 9999px; text-decoration: none;">
                    + Đăng món mới
                </a>
            </div>

            <?php if (empty($myRecipes)): ?>
                <div style="text-align: center; padding: 3.5rem 1.5rem; background: #ffffff; border-radius: 1.25rem; border: 2px dashed #cbd5e1;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🍳</div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.2rem; color: #1e293b; font-weight: 800;">Bạn chưa đăng công thức nấu ăn nào</h4>
                    <p style="color: #64748b; margin: 0 0 1.5rem; font-size: 0.95rem;">Hãy chia sẻ công thức món tủ của bạn để lưu giữ và cùng cộng đồng yêu bếp trổ tài!</p>
                    <a href="<?= BASE_URL ?>/views/create-recipe.php" class="button button-create" style="padding: 0.75rem 2rem; text-decoration: none; font-weight: 700;">
                        Bắt đầu viết món đầu tiên
                    </a>
                </div>
            <?php else: ?>
                <div class="recipe-grid">
                    <?php foreach ($myRecipes as $r): ?>
                        <?php 
                        $imgSrc = !empty($r['image_url']) ? BASE_URL . '/' . e($r['image_url']) : BASE_URL . '/assets/images/default-recipe.jpg';
                        $isApproved = ($r['status'] === 'approved');
                        ?>
                        <article class="recipe-card" style="display: flex; flex-direction: column; justify-content: space-between; border-radius: 1rem; overflow: hidden; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                            <div>
                                <!-- Image & Badges -->
                                <div class="recipe-card-media" style="position: relative;">
                                    <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $r['id'] ?>">
                                        <img src="<?= $imgSrc ?>" alt="<?= e($r['title']) ?>" loading="lazy" style="width: 100%; height: 190px; object-fit: cover; display: block;">
                                    </a>
                                    
                                    <?php if (!empty($r['category'])): ?>
                                        <span class="card-category-badge" style="position: absolute; top: 0.75rem; left: 0.75rem; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(4px); color: #fff; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.76rem; font-weight: 700;">
                                            <?= e($r['category']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <span style="position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.76rem; font-weight: 800; <?= $isApproved ? 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;' : 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' ?>">
                                        <?= $isApproved ? '✓ Đã duyệt' : '⏳ Chờ duyệt' ?>
                                    </span>
                                </div>

                                <!-- Body -->
                                <div class="recipe-card-body" style="padding: 1.1rem;">
                                    <h3 class="recipe-card-title" style="margin: 0 0 0.5rem; font-size: 1.05rem; font-weight: 700; line-height: 1.4;">
                                        <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $r['id'] ?>" style="color: #1e293b; text-decoration: none;">
                                            <?= e($r['title']) ?>
                                        </a>
                                    </h3>

                                    <div style="display: flex; gap: 0.75rem; align-items: center; font-size: 0.8rem; color: #64748b; margin-top: 0.5rem;">
                                        <?php if (!empty($r['cooking_time'])): ?>
                                            <span>⏱️ <?= e($r['cooking_time']) ?></span>
                                        <?php endif; ?>
                                        <span>❤️ <?= (int)($r['likes_count'] ?? 0) ?></span>
                                        <span>💬 <?= (int)($r['comments_count'] ?? 0) ?></span>
                                        <?php if (!empty($r['calories'])): ?>
                                            <span>🔥 <?= (int)$r['calories'] ?> kcal</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div style="padding: 0 1.1rem 1.1rem; display: flex; gap: 0.5rem; border-top: 1px solid #f8fafc; padding-top: 0.85rem;">
                                <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $r['id'] ?>" class="button button-outline" style="flex: 1; font-size: 0.82rem; padding: 0.5rem; text-decoration: none; text-align: center; font-weight: 700; border-radius: 9999px;">
                                    Xem chi tiết
                                </a>
                                <a href="<?= BASE_URL ?>/views/edit-recipe.php?id=<?= (int) $r['id'] ?>" class="button button-primary" style="flex: 1; font-size: 0.82rem; padding: 0.5rem; text-decoration: none; text-align: center; font-weight: 700; border-radius: 9999px;">
                                    ✏️ Chỉnh sửa
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- -------------------------------------------------------------
             TAB PANEL 2: NGƯỜI THEO DÕI
             ------------------------------------------------------------- -->
        <div id="panel-followers" class="profile-tab-panel" style="display: none;">
            <div style="margin-bottom: 1.25rem;">
                <h3 style="margin: 0 0 0.35rem; font-size: 1.25rem; font-weight: 800; color: #1e293b;">
                    Người đang theo dõi gian bếp của bạn (<?= count($followersList) ?> người)
                </h3>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
                    Những thành viên quan tâm và thường xuyên theo dõi các bài viết, công thức mới từ bạn.
                </p>
            </div>

            <?php if (empty($followersList)): ?>
                <div style="text-align: center; padding: 3.5rem 1.5rem; background: #ffffff; border-radius: 1.25rem; border: 2px dashed #cbd5e1;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.2rem; color: #1e293b; font-weight: 800;">Chưa có người theo dõi</h4>
                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Hãy đăng thêm nhiều công thức hấp dẫn và tương tác với cộng đồng để có thêm người theo dõi nhé!</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <?php foreach ($followersList as $follower): ?>
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                            <div style="display: flex; align-items: center; gap: 0.85rem;">
                                <div style="width: 46px; height: 46px; border-radius: 50%; background: #7c3aed; color: #ffffff; font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <?= mb_strtoupper(mb_substr($follower['username'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 style="margin: 0 0 0.2rem; font-size: 0.98rem; font-weight: 700; color: #1e293b;">
                                        <?= e($follower['username']) ?>
                                    </h4>
                                    <div style="font-size: 0.78rem; color: #64748b;">
                                        🍳 <?= (int)$follower['recipe_count'] ?> công thức
                                    </div>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>/views/author.php?id=<?= (int)$follower['id'] ?>" class="button button-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem; border-radius: 9999px; text-decoration: none; white-space: nowrap;">
                                Xem bếp
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- -------------------------------------------------------------
             TAB PANEL 3: ĐANG THEO DÕI
             ------------------------------------------------------------- -->
        <div id="panel-following" class="profile-tab-panel" style="display: none;">
            <div style="margin-bottom: 1.25rem;">
                <h3 style="margin: 0 0 0.35rem; font-size: 1.25rem; font-weight: 800; color: #1e293b;">
                    Đầu bếp bạn đang theo dõi (<?= count($followingList) ?> người)
                </h3>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
                    Danh sách các đầu bếp bạn quan tâm để luôn cập nhật những món ngon mới nhất.
                </p>
            </div>

            <?php if (empty($followingList)): ?>
                <div style="text-align: center; padding: 3.5rem 1.5rem; background: #ffffff; border-radius: 1.25rem; border: 2px dashed #cbd5e1;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">✨</div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.2rem; color: #1e293b; font-weight: 800;">Bạn chưa theo dõi đầu bếp nào</h4>
                    <p style="color: #64748b; margin: 0 0 1.25rem; font-size: 0.95rem;">Khám phá các món ăn ngon và nhấn "Theo dõi" để cập nhật bài đăng từ các bếp trưởng!</p>
                    <a href="<?= BASE_URL ?>/index.php" class="button button-create" style="padding: 0.65rem 1.75rem; text-decoration: none; font-weight: 700;">
                        Khám phá đầu bếp Cookio
                    </a>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <?php foreach ($followingList as $chef): ?>
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                            <div style="display: flex; align-items: center; gap: 0.85rem;">
                                <div style="width: 46px; height: 46px; border-radius: 50%; background: #2563eb; color: #ffffff; font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <?= mb_strtoupper(mb_substr($chef['username'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 style="margin: 0 0 0.2rem; font-size: 0.98rem; font-weight: 700; color: #1e293b;">
                                        <?= e($chef['username']) ?>
                                    </h4>
                                    <div style="font-size: 0.78rem; color: #64748b;">
                                        🍳 <?= (int)$chef['recipe_count'] ?> công thức
                                    </div>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>/views/author.php?id=<?= (int)$chef['id'] ?>" class="button button-outline" style="padding: 0.4rem 0.9rem; font-size: 0.8rem; border-radius: 9999px; text-decoration: none; white-space: nowrap;">
                                Xem bếp
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- -------------------------------------------------------------
             TAB PANEL 4: ĐÁNH GIÁ & NHẬN XÉT
             ------------------------------------------------------------- -->
        <div id="panel-reviews" class="profile-tab-panel" style="display: none;">
            <div style="margin-bottom: 1.25rem;">
                <h3 style="margin: 0 0 0.35rem; font-size: 1.25rem; font-weight: 800; color: #1e293b;">
                    Đánh giá & Nhận xét từ cộng đồng (<?= count($reviewsList) ?> nhận xét)
                </h3>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
                    Điểm đánh giá trung bình: <strong style="color: #ca8a04;"><?= $avgRatingText ?> ⭐</strong> <?= $totalRatingsCount > 0 ? "dựa trên $totalRatingsCount đánh giá thực tế từ cộng đồng." : "(Hiện chưa có đánh giá nào)." ?>
                </p>
            </div>

            <?php if (empty($reviewsList)): ?>
                <div style="text-align: center; padding: 3.5rem 1.5rem; background: #ffffff; border-radius: 1.25rem; border: 2px dashed #cbd5e1;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                    <h4 style="margin: 0 0 0.5rem; font-size: 1.2rem; color: #1e293b; font-weight: 800;">Chưa có nhận xét nào</h4>
                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Khi các thành viên nấu thử và bình luận đánh giá công thức của bạn, nhận xét sẽ hiện đầy đủ ở đây.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($reviewsList as $rev): ?>
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.65rem;">
                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #fed7aa; color: #c2410c; font-weight: 800; font-size: 0.95rem; display: flex; align-items: center; justify-content: center;">
                                        <?= mb_strtoupper(mb_substr($rev['reviewer_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong style="font-size: 0.92rem; color: #1e293b;"><?= e($rev['reviewer_name']) ?></strong>
                                        <div style="font-size: 0.75rem; color: #94a3b8;"><?= date('d/m/Y H:i', strtotime((string)$rev['created_at'])) ?></div>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.35rem; background: #fefce8; border: 1px solid #fef08a; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.82rem; font-weight: 800; color: #ca8a04;">
                                    <span><?= str_repeat('⭐', (int)$rev['rating']) ?></span>
                                    <span>(<?= (int)$rev['rating'] ?>/5)</span>
                                </div>
                            </div>

                            <p style="margin: 0 0 0.65rem; font-size: 0.95rem; color: #334155; line-height: 1.5;">
                                "<?= e($rev['content']) ?>"
                            </p>

                            <div style="font-size: 0.82rem; color: #64748b; background: #f8fafc; padding: 0.4rem 0.75rem; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                                <span>Món ăn:</span>
                                <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int)$rev['recipe_id'] ?>#comments" style="color: #ea580c; font-weight: 700; text-decoration: none;">
                                    <?= e($rev['recipe_title']) ?> &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<script>
function toggleEditProfile(showEdit) {
    const viewSection = document.getElementById('profileViewSection');
    const editSection = document.getElementById('profileEditSection');
    if (showEdit) {
        if (viewSection) viewSection.style.display = 'none';
        if (editSection) {
            editSection.style.display = 'block';
            editSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        if (editSection) editSection.style.display = 'none';
        if (viewSection) {
            viewSection.style.display = 'block';
            viewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

function previewAvatarImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (!file.type.match('image.*')) {
            alert('Vui lòng chọn đúng tệp hình ảnh (JPG, PNG, WEBP)!');
            input.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Dung lượng ảnh tối đa 5MB!');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.getElementById('avatarPreviewContainer');
            if (container) {
                container.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover; display: block;">`;
            }
        };
        reader.readAsDataURL(file);
    }
}

function switchProfileTab(tabName) {
    // 1. Hide all panels
    const panels = document.querySelectorAll('.profile-tab-panel');
    panels.forEach(p => p.style.display = 'none');

    // 2. Show target panel
    const targetPanel = document.getElementById('panel-' + tabName);
    if (targetPanel) {
        targetPanel.style.display = 'block';
    }

    // 3. Update tab buttons
    const navTabs = document.querySelectorAll('.profile-nav-tab');
    navTabs.forEach(t => {
        t.style.borderBottomColor = 'transparent';
        t.style.color = '#64748b';
        t.classList.remove('is-active');
    });

    const activeNavTab = document.getElementById('tabNav-' + tabName);
    if (activeNavTab) {
        activeNavTab.style.borderBottomColor = '#ea580c';
        activeNavTab.style.color = '#ea580c';
        activeNavTab.classList.add('is-active');
    }

    // 4. Update stat cards highlight
    const statBoxes = document.querySelectorAll('.profile-stat-box');
    statBoxes.forEach(b => {
        b.style.boxShadow = 'none';
        b.style.transform = 'translateY(0)';
    });

    const activeStatBtn = document.getElementById('statBtn-' + tabName);
    if (activeStatBtn) {
        activeStatBtn.style.boxShadow = '0 6px 16px rgba(0,0,0,0.08)';
        activeStatBtn.style.transform = 'translateY(-2px)';
    }

    // Smooth scroll to tabs section if called from stat cards
    const wrapper = document.querySelector('.profile-tabs-wrapper');
    if (wrapper) {
        wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php
require __DIR__ . '/../includes/footer.php';
