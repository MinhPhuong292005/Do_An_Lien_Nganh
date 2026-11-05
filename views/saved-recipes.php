<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$recipes = [];
$categories = [];
$allIngredientsText = '';
$selectedCat = trim((string) ($_GET['cat'] ?? ''));
$currentTab = trim((string) ($_GET['tab'] ?? 'saved'));
if ($currentTab !== 'liked') {
    $currentTab = 'saved';
}

$savedCount = 0;
$likedCount = 0;

if (is_logged_in()) {
    $uid = (int) $_SESSION['user_id'];

    // 1. Get counts for tabs
    $stmtSavedCount = db()->prepare("SELECT COUNT(*) FROM saved_recipes s JOIN recipes r ON r.id = s.recipe_id WHERE s.user_id = ? AND r.status = 'approved'");
    $stmtSavedCount->execute([$uid]);
    $savedCount = (int) $stmtSavedCount->fetchColumn();

    $stmtLikedCount = db()->prepare("SELECT COUNT(*) FROM recipe_likes rl JOIN recipes r ON r.id = rl.recipe_id WHERE rl.user_id = ? AND r.status = 'approved'");
    $stmtLikedCount->execute([$uid]);
    $likedCount = (int) $stmtLikedCount->fetchColumn();

    if ($currentTab === 'saved') {
        // Fetch categories for saved recipes
        $stmtCats = db()->prepare("
            SELECT DISTINCT r.category 
            FROM recipes r 
            INNER JOIN saved_recipes s ON s.recipe_id = r.id 
            WHERE s.user_id = ? AND r.status = 'approved' AND r.category IS NOT NULL AND r.category != ''
            ORDER BY r.category ASC
        ");
        $stmtCats->execute([$uid]);
        $categories = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

        $sql = "
            SELECT r.*, u.username AS author_name, COALESCE(s.note, '') AS personal_note,
                   s.created_at AS action_at 
            FROM recipes r 
            INNER JOIN users u ON u.id = r.author_id 
            INNER JOIN saved_recipes s ON s.recipe_id = r.id
            WHERE s.user_id = ? AND r.status = 'approved'
        ";
        $params = [$uid];

        if ($selectedCat !== '') {
            $sql .= " AND r.category = ?";
            $params[] = $selectedCat;
        }

        $sql .= " ORDER BY s.created_at DESC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $recipes = $stmt->fetchAll();
    } else {
        // Fetch categories for liked recipes
        $stmtCats = db()->prepare("
            SELECT DISTINCT r.category 
            FROM recipes r 
            INNER JOIN recipe_likes rl ON rl.recipe_id = r.id 
            WHERE rl.user_id = ? AND r.status = 'approved' AND r.category IS NOT NULL AND r.category != ''
            ORDER BY r.category ASC
        ");
        $stmtCats->execute([$uid]);
        $categories = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

        $sql = "
            SELECT r.*, u.username AS author_name, rl.created_at AS action_at 
            FROM recipes r 
            INNER JOIN users u ON u.id = r.author_id 
            INNER JOIN recipe_likes rl ON rl.recipe_id = r.id
            WHERE rl.user_id = ? AND r.status = 'approved'
        ";
        $params = [$uid];

        if ($selectedCat !== '') {
            $sql .= " AND r.category = ?";
            $params[] = $selectedCat;
        }

        $sql .= " ORDER BY rl.created_at DESC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $recipes = $stmt->fetchAll();
    }

    // Compile ingredients for grocery modal
    $ingredientsList = [];
    foreach ($recipes as $r) {
        if (!empty($r['ingredients'])) {
            $ingredientsList[] = trim((string) $r['ingredients']);
        }
    }
    $allIngredientsText = implode("\n", $ingredientsList);
}

$pageTitle = ($currentTab === 'saved' ? 'Món đã lưu' : 'Món đã thả tim') . ' - Cookio';
require __DIR__ . '/../includes/header.php';
?>

<div class="recipe-detail-container" style="padding-top: 2rem; padding-bottom: 3.5rem;">

    <!-- Page Header -->
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.35rem;">
            Món Yêu Thích Của Bạn
        </h1>
        <p style="margin: 0; color: var(--text-muted); font-size: 0.95rem;">
            Không gian lưu giữ công thức tâm đắc, món bạn đã thả tim và ghi chú nấu nướng riêng.
        </p>
    </div>

    <?php if (isset($_GET['bookmark'])): ?>
        <div class="notice success" style="margin-bottom: 1.25rem;">
            <?= match ($_GET['bookmark']) {
                'unsaved' => '✅ Đã bỏ lưu món khỏi bộ sưu tập thành công.',
                'note_saved' => '✅ Đã lưu ghi chú bếp riêng thành công.',
                default => '✅ Thao tác cập nhật thành công.'
            } ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['liked'])): ?>
        <div class="notice success" style="margin-bottom: 1.25rem;">
            <?= $_GET['liked'] === '0' ? '✅ Đã bỏ thích món ăn.' : '✅ Đã thả tim món ăn thành công.' ?>
        </div>
    <?php endif; ?>

    <?php if (!is_logged_in()): ?>
        <!-- Not logged in state: Modern culinary showcase card -->
        <div class="saved-recipes-unauth-card" style="background: var(--bg-surface); border-radius: 20px; border: 1.5px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 3.5rem 2rem; text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--primary-light); color: var(--primary); padding: 0.4rem 1rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.25rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <span>Bộ sưu tập cá nhân</span>
            </div>

            <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.75rem; letter-spacing: -0.5px;">
                Lưu giữ những món ngon tâm đắc của bạn
            </h2>
            <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 600px; margin: 0 auto 2.5rem; line-height: 1.6;">
                Đăng nhập ngay để khám phá và quản lý thực đơn yêu thích, lưu công thức nấu ăn, viết ghi chú gia vị riêng và tự động tạo danh sách đi chợ thông minh.
            </p>

            <!-- 3 Benefit Pillars -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; max-width: 780px; margin: 0 auto 2.5rem; text-align: left;">
                <div style="background: var(--bg-page); border: 1.5px solid var(--border-color); border-radius: 14px; padding: 1.35rem 1.25rem;">
                    <div style="font-size: 1.3rem; margin-bottom: 0.5rem; color: var(--primary);">🔖</div>
                    <h4 style="margin: 0 0 0.35rem; font-size: 1rem; font-weight: 700; color: var(--text-main);">Lưu công thức 1 chạm</h4>
                    <p style="margin: 0; font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">Lưu nhanh món ăn muốn nấu vào cẩm nang bếp để mở lại bất kỳ lúc nào.</p>
                </div>
                <div style="background: var(--bg-page); border: 1.5px solid var(--border-color); border-radius: 14px; padding: 1.35rem 1.25rem;">
                    <div style="font-size: 1.3rem; margin-bottom: 0.5rem; color: #ef4444;">❤️</div>
                    <h4 style="margin: 0 0 0.35rem; font-size: 1rem; font-weight: 700; color: var(--text-main);">Theo dõi món đã thả tim</h4>
                    <p style="margin: 0; font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">Gom trọn những món bạn yêu thích và đánh giá cao trên cộng đồng ẩm thực.</p>
                </div>
                <div style="background: var(--bg-page); border: 1.5px solid var(--border-color); border-radius: 14px; padding: 1.35rem 1.25rem;">
                    <div style="font-size: 1.3rem; margin-bottom: 0.5rem; color: #16a34a;">🛒</div>
                    <h4 style="margin: 0 0 0.35rem; font-size: 1rem; font-weight: 700; color: var(--text-main);">Tạo giỏ đi chợ tiện lợi</h4>
                    <p style="margin: 0; font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">Tự động tổng hợp và tính toán khối lượng nguyên liệu cần mua chính xác.</p>
                </div>
            </div>

            <!-- Action buttons -->
            <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <button type="button" class="button button-create" data-open-login style="padding: 0.85rem 2.25rem; font-size: 1rem; border-radius: 9999px; font-weight: 700; box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3);">
                    Đăng nhập ngay
                </button>
                <a href="<?= BASE_URL ?>/index.php" class="button button-outline" style="padding: 0.85rem 1.85rem; font-size: 1rem; border-radius: 9999px; font-weight: 700; text-decoration: none;">
                    Khám phá công thức &rarr;
                </a>
            </div>
        </div>
    <?php else: ?>

        <!-- TWO DISTINCT TABS: Món đã lưu & Món đã thả tim -->
        <div class="favorite-tabs-nav" style="display: flex; gap: 0.85rem; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.65rem;">
            <!-- Tab 1: Món đã lưu -->
            <a href="<?= BASE_URL ?>/views/saved-recipes.php?tab=saved" 
               class="fav-tab-btn <?= $currentTab === 'saved' ? 'is-active' : '' ?>"
               style="text-decoration: none; padding: 0.55rem 1.35rem; border-radius: 9999px; font-weight: 700; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; <?= $currentTab === 'saved' ? 'background: #ea580c; color: #ffffff; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25);' : 'background: #f1f5f9; color: #475569;' ?>">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="<?= $currentTab === 'saved' ? '#ffffff' : 'none' ?>" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>Món đã lưu</span>
                <span style="font-size: 0.8rem; padding: 0.15rem 0.55rem; border-radius: 9999px; background: <?= $currentTab === 'saved' ? 'rgba(255,255,255,0.25)' : '#e2e8f0' ?>; color: inherit;">
                    <?= $savedCount ?>
                </span>
            </a>

            <!-- Tab 2: Món đã thả tim -->
            <a href="<?= BASE_URL ?>/views/saved-recipes.php?tab=liked" 
               class="fav-tab-btn <?= $currentTab === 'liked' ? 'is-active' : '' ?>"
               style="text-decoration: none; padding: 0.55rem 1.35rem; border-radius: 9999px; font-weight: 700; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; <?= $currentTab === 'liked' ? 'background: #dc2626; color: #ffffff; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);' : 'background: #f1f5f9; color: #475569;' ?>">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="<?= $currentTab === 'liked' ? '#ffffff' : '#dc2626' ?>" stroke="<?= $currentTab === 'liked' ? '#ffffff' : '#dc2626' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span>Món đã thả tim</span>
                <span style="font-size: 0.8rem; padding: 0.15rem 0.55rem; border-radius: 9999px; background: <?= $currentTab === 'liked' ? 'rgba(255,255,255,0.25)' : '#e2e8f0' ?>; color: inherit;">
                    <?= $likedCount ?>
                </span>
            </a>
        </div>

        <!-- Category Filter Pills Bar (nếu có category trong tab hiện tại) -->
        <?php if (!empty($categories)): ?>
            <div class="saved-filter-bar" style="margin-bottom: 1.25rem;">
                <a href="<?= BASE_URL ?>/views/saved-recipes.php?tab=<?= $currentTab ?>" class="saved-filter-pill <?= $selectedCat === '' ? 'is-active' : '' ?>">
                    Tất cả danh mục
                </a>
                <?php foreach ($categories as $catName): ?>
                    <a href="<?= BASE_URL ?>/views/saved-recipes.php?tab=<?= $currentTab ?>&cat=<?= urlencode($catName) ?>" 
                       class="saved-filter-pill <?= $selectedCat === $catName ? 'is-active' : '' ?>">
                        <?= e($catName) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Empty states -->
        <?php if (empty($recipes)): ?>
            <div style="text-align: center; padding: 3.5rem 1.5rem; background: #fff; border-radius: 1.25rem; border: 2px dashed #cbd5e1; margin-top: 1rem;">
                <div style="width: 68px; height: 68px; border-radius: 50%; background: <?= $currentTab === 'saved' ? '#fff7ed' : '#fef2f2' ?>; margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center; border: 1.5px solid <?= $currentTab === 'saved' ? '#fed7aa' : '#fecaca' ?>;">
                    <?php if ($currentTab === 'saved'): ?>
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                    <?php else: ?>
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <h3 style="margin: 0 0 0.5rem; font-size: 1.3rem; color: #1e293b; font-weight: 800;">
                    <?= $currentTab === 'saved' ? 'Chưa có món ăn nào được lưu' : 'Bạn chưa thả tim món ăn nào' ?>
                </h3>
                <p style="color: #64748b; margin: 0 0 1.5rem; font-size: 0.95rem; max-width: 480px; margin-left: auto; margin-right: auto;">
                    <?= $currentTab === 'saved' 
                        ? 'Khi xem các công thức, hãy nhấn nút "Lưu món" để lưu lại và thêm ghi chú nấu nướng riêng cho gia đình.'
                        : 'Thả tim cho những công thức thơm ngon hấp dẫn mà bạn yêu thích để dễ dàng tìm lại tại đây.' ?>
                </p>
                <a href="<?= BASE_URL ?>/index.php" class="button button-create" style="display: inline-block; padding: 0.75rem 2rem; font-weight: 700; text-decoration: none;">
                    Khám phá món ngon ngay
                </a>
            </div>
        <?php else: ?>

            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 1.25rem;">
                Đang hiển thị <strong style="color: var(--primary);"><?= count($recipes) ?> món</strong> trong danh sách <?= $currentTab === 'saved' ? 'đã lưu' : 'đã thả tim' ?> của bạn.
            </p>

            <!-- Sticky / Prominent Multi-Dish Grocery Batch Bar -->
            <div class="grocery-batch-bar" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 1.5px solid #fed7aa; border-radius: 14px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; box-shadow: 0 4px 14px rgba(234, 88, 12, 0.08);">
                <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; font-weight: 700; font-size: 0.88rem; color: #7c2d12; cursor: pointer;">
                        <input type="checkbox" id="selectAllGroceryCheckbox" checked onchange="toggleSelectAllGrocery(this.checked)" style="width: 17px; height: 17px; accent-color: #ea580c; cursor: pointer;">
                        <span>Chọn tất cả món</span>
                    </label>
                    <span style="color: #fdba74;">|</span>
                    <span style="font-size: 0.86rem; color: #9a3412;">
                        Đã tích chọn: <strong id="selectedDishCountText" style="color: #ea580c;"><?= count($recipes) ?></strong> món để nấu
                    </span>
                </div>
                <div>
                    <button type="button" onclick="openConsolidatedGroceryModal()" class="button button-create" style="padding: 0.55rem 1.35rem; font-size: 0.88rem; font-weight: 800; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25);">
                        🛒 Gom nguyên liệu đi chợ (<span id="groceryBtnCount"><?= count($recipes) ?></span> món)
                    </button>
                </div>
            </div>

            <!-- Recipe Grid -->
            <div class="recipe-grid">
                <?php foreach ($recipes as $recipe): ?>
                    <?php 
                    $imgSrc = !empty($recipe['image_url']) ? BASE_URL . '/' . e($recipe['image_url']) : BASE_URL . '/assets/images/default-recipe.jpg';
                    $hasNote = !empty($recipe['personal_note']);
                    ?>
                    <article class="recipe-card" style="display: flex; flex-direction: column; justify-content: space-between; border-radius: 1rem; overflow: hidden; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s;">
                        <div>
                            <!-- Card Media & Action Button -->
                            <div class="recipe-card-media" style="position: relative;">
                                <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>">
                                    <img src="<?= $imgSrc ?>" alt="<?= e($recipe['title']) ?>" loading="lazy" style="width: 100%; height: 200px; object-fit: cover; display: block;">
                                </a>
                                
                                <!-- Checkbox chọn món để gom nguyên liệu đi chợ -->
                                <label title="Tích chọn món này để gom nguyên liệu đi chợ" style="position: absolute; top: 0.75rem; left: 0.75rem; z-index: 3; cursor: pointer; display: flex; align-items: center; gap: 0.35rem; background: rgba(255,255,255,0.95); backdrop-filter: blur(4px); padding: 0.3rem 0.65rem; border-radius: 9999px; box-shadow: 0 2px 8px rgba(0,0,0,0.18); font-size: 0.76rem; font-weight: 700; color: #1e293b; border: 1px solid #fed7aa;">
                                    <input type="checkbox" class="dish-grocery-checkbox" 
                                           data-id="<?= (int)$recipe['id'] ?>" 
                                           data-title="<?= e($recipe['title']) ?>" 
                                           data-ingredients="<?= e($recipe['ingredients'] ?? '') ?>"
                                           checked 
                                           onchange="updateGroceryBatchBar()"
                                           style="width: 15px; height: 15px; accent-color: #ea580c; cursor: pointer;">
                                    <span>Chọn nấu</span>
                                </label>

                                <?php if (!empty($recipe['category'])): ?>
                                    <span style="position: absolute; top: auto; bottom: 0.75rem; left: 0.75rem; z-index: 2; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(4px); color: #ffffff; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.76rem; font-weight: 700; height: auto; line-height: 1.2; display: inline-flex; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                        <?= e($recipe['category']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($currentTab === 'saved'): ?>
                                    <!-- Nút Bỏ lưu món: Icon bookmark chuẩn (giống biểu tượng Món đã lưu) & Xác nhận bằng khung nhỏ -->
                                    <form id="form-unsave-<?= (int)$recipe['id'] ?>" method="post" action="<?= BASE_URL ?>/actions/saved_action.php" style="position: absolute; top: 0.65rem; right: 0.65rem; z-index: 2; margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
                                        <input type="hidden" name="return_url" value="views/saved-recipes.php?tab=saved<?= $selectedCat !== '' ? '&cat=' . urlencode($selectedCat) : '' ?>">
                                        <button type="button" 
                                                title="Bỏ lưu món này"
                                                onclick="promptConfirmAction('unsave', 'form-unsave-<?= (int)$recipe['id'] ?>', '<?= e(addslashes($recipe['title'])) ?>')"
                                                style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; padding: 0 !important; border-radius: 50%; background: #ffffff; border: 1.5px solid #fed7aa; color: #ea580c; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.12); transition: all 0.2s;"
                                                onmouseover="this.style.background='#fff7ed'; this.style.borderColor='#ea580c';"
                                                onmouseout="this.style.background='#ffffff'; this.style.borderColor='#fed7aa';">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#ea580c" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block; width: 18px; height: 18px; pointer-events: none;">
                                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Nút Bỏ thích: Trái tim đỏ & Bỏ thích ngay lập tức khi bấm, không hiện modal xác nhận -->
                                    <form id="form-unlike-<?= (int)$recipe['id'] ?>" method="post" action="<?= BASE_URL ?>/actions/like_action.php" style="position: absolute; top: 0.65rem; right: 0.65rem; z-index: 2; margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
                                        <input type="hidden" name="return_url" value="views/saved-recipes.php?tab=liked<?= $selectedCat !== '' ? '&cat=' . urlencode($selectedCat) : '' ?>">
                                        <button type="submit" 
                                                title="Bỏ thích món này"
                                                style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; padding: 0 !important; border-radius: 50%; background: #ffffff; border: 1.5px solid #fecaca; color: #dc2626; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.12); transition: all 0.2s;"
                                                onmouseover="this.style.background='#fef2f2'; this.style.borderColor='#dc2626';"
                                                onmouseout="this.style.background='#ffffff'; this.style.borderColor='#fecaca';">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#dc2626" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block; width: 18px; height: 18px; pointer-events: none;">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <!-- Card Body -->
                            <div class="recipe-card-body" style="padding: 1.1rem;">
                                <h3 class="recipe-card-title" style="margin: 0 0 0.5rem; font-size: 1.05rem; font-weight: 700; line-height: 1.4;">
                                    <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>" style="color: #1e293b; text-decoration: none;">
                                        <?= e($recipe['title']) ?>
                                    </a>
                                </h3>

                                <div class="card-author-chip" style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.82rem; color: #64748b; margin-bottom: 0.6rem;">
                                    <span class="author-mini-avatar" style="width: 20px; height: 20px; border-radius: 50%; background: #fed7aa; color: #c2410c; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.7rem;">
                                        <?= mb_strtoupper(mb_substr($recipe['author_name'], 0, 1)) ?>
                                    </span>
                                    <span>Bếp: <strong><?= e($recipe['author_name']) ?></strong></span>
                                </div>

                                <div style="display: flex; gap: 0.75rem; align-items: center; font-size: 0.8rem; color: #64748b; margin-bottom: 0.85rem;">
                                    <?php if (!empty($recipe['cooking_time'])): ?>
                                        <span>⏱️ <?= e($recipe['cooking_time']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($recipe['calories'])): ?>
                                        <span>🔥 <?= (int)$recipe['calories'] ?> kcal</span>
                                    <?php endif; ?>
                                    <span>❤️ <?= (int)($recipe['likes_count'] ?? 0) ?></span>
                                </div>

                                <?php if ($currentTab === 'saved'): ?>
                                    <!-- Personal Chef Note Box (chỉ hiện ở tab Món đã lưu) -->
                                    <div class="personal-note-box" id="personal-note-box-<?= (int) $recipe['id'] ?>" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 0.75rem; margin-top: 0.5rem;">
                                        <div class="personal-note-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; font-size: 0.8rem; font-weight: 700; color: #92400e;">
                                            <span>📝 Ghi chú bếp của bạn</span>
                                            <?php if ($hasNote): ?>
                                                <span style="font-size: 0.7rem; color: #16a34a; font-weight: 700;">✓ Có ghi chú</span>
                                            <?php endif; ?>
                                        </div>
                                        <textarea class="personal-note-textarea" placeholder="Thêm lưu ý riêng (VD: giảm cay, nấu 15 phút, chồng rất thích...)" style="width: 100%; box-sizing: border-box; border: 1px solid #fcd34d; border-radius: 0.5rem; padding: 0.5rem; font-size: 0.82rem; font-family: inherit; resize: vertical; min-height: 48px; background: #ffffff; color: #374151;"><?= e($recipe['personal_note'] ?? '') ?></textarea>
                                        <div style="display: flex; justify-content: flex-end; margin-top: 0.4rem;">
                                            <button type="button" class="personal-note-save-btn" onclick="saveRecipeNote(<?= (int) $recipe['id'] ?>, this)" style="background: #d97706; color: #ffffff; border: none; padding: 0.35rem 0.85rem; border-radius: 0.45rem; font-size: 0.78rem; font-weight: 700; cursor: pointer;">
                                                Lưu ghi chú
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Footer Actions: Vào bếp nấu & Đi chợ -->
                        <div style="padding: 0 1.1rem 1.1rem; display: flex; gap: 0.5rem;">
                            <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>" class="button button-primary" style="flex: 1.2; font-size: 0.84rem; padding: 0.55rem; text-decoration: none; text-align: center; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; border-radius: 9999px;">
                                👩‍🍳 Vào bếp nấu
                            </a>
                            <button type="button" class="button button-outline js-open-grocery-modal" data-title="Nguyên liệu: <?= e($recipe['title']) ?>" data-ingredients="<?= e($recipe['ingredients'] ?? '') ?>" style="flex: 0.8; font-size: 0.84rem; padding: 0.55rem; border-radius: 9999px;" title="Xem nguyên liệu món này">
                                📋 Đi chợ
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- =====================================================================
     CUSTOM IN-APP CONFIRMATION MODAL (Khung nhỏ xác nhận bỏ lưu / bỏ thích)
     ===================================================================== -->
<div id="confirmActionModal" class="cookio-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 10000; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem;">
    <div style="background: #ffffff; border-radius: 1.25rem; max-width: 440px; width: 100%; padding: 1.75rem; text-align: center; box-shadow: 0 20px 35px -5px rgba(0,0,0,0.25);">
        <div id="confirmModalIconWrap" style="width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center;"></div>
        <h3 id="confirmModalTitle" style="margin: 0 0 0.5rem; font-size: 1.25rem; font-weight: 800; color: #1e293b;"></h3>
        <p id="confirmModalMessage" style="margin: 0 0 1.5rem; font-size: 0.95rem; color: #475569; line-height: 1.5;"></p>
        <div style="display: flex; gap: 0.75rem; justify-content: center;">
            <button type="button" onclick="closeConfirmActionModal()" class="button button-outline" style="padding: 0.65rem 1.35rem; font-weight: 700; border-radius: 9999px;">
                Hủy bỏ
            </button>
            <button type="button" id="confirmModalSubmitBtn" class="button button-create" style="padding: 0.65rem 1.5rem; font-weight: 800; border-radius: 9999px;">
                Xác nhận
            </button>
        </div>
    </div>
</div>

<!-- =====================================================================
     SMART GROCERY MODAL (Gom nguyên liệu nhiều món: Cộng dồn & Làm sạch gia vị)
     ===================================================================== -->
<div id="smartGroceryModal" class="cookio-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 10000; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem;">
    <div style="background: #ffffff; border-radius: 1.25rem; max-width: 640px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 40px -10px rgba(0,0,0,0.3);">
        
        <!-- Modal Header -->
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1.5px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fff7ed;">
            <div>
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #9a3412; display: flex; align-items: center; gap: 0.5rem;">
                    🛒 Danh Sách Đi Chợ Tổng Hợp
                </h3>
                <div id="smartGroceryDishesSubtitle" style="font-size: 0.82rem; color: #c2410c; margin-top: 0.25rem; font-weight: 600;"></div>
            </div>
            <button type="button" onclick="closeSmartGroceryModal()" style="background: #ffffff; border: 1px solid #fed7aa; width: 34px; height: 34px; border-radius: 50%; font-size: 1.25rem; cursor: pointer; color: #9a3412; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1;">
            
            <!-- Section 1: Main Countable / Measurable Ingredients (Summed) -->
            <div style="margin-bottom: 1.5rem;">
                <div style="font-weight: 800; font-size: 0.95rem; color: #166534; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.65rem; background: #f0fdf4; padding: 0.4rem 0.75rem; border-radius: 8px; border-left: 4px solid #16a34a;">
                    <span>🥦 Thực phẩm & Nguyên liệu cần mua (Đã cộng dồn):</span>
                </div>
                <div id="smartMainIngredientsList" style="display: flex; flex-direction: column; gap: 0.45rem;"></div>
            </div>

            <!-- Section 2: Spices & Seasonings (Clean Names Without Spoons) -->
            <div>
                <div style="font-weight: 800; font-size: 0.95rem; color: #92400e; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.65rem; background: #fffbeb; padding: 0.4rem 0.75rem; border-radius: 8px; border-left: 4px solid #d97706;">
                    <span>🧂 Gia vị & Nước sốt (Kiểm tra lại tủ bếp):</span>
                </div>
                <div id="smartSpicesList" style="display: flex; flex-wrap: wrap; gap: 0.45rem;"></div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 1rem 1.5rem; border-top: 1.5px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-wrap: wrap; gap: 0.65rem;">
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" onclick="copySmartGroceryList()" class="button button-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 700; border-radius: 9999px;">
                    📋 Sao chép danh sách
                </button>
                <button type="button" onclick="window.print()" class="button button-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 700; border-radius: 9999px;">
                    🖨️ In
                </button>
            </div>
            <button type="button" onclick="closeSmartGroceryModal()" class="button button-create" style="padding: 0.55rem 1.5rem; font-size: 0.9rem; font-weight: 800; border-radius: 9999px;">
                Xong
            </button>
        </div>
    </div>
</div>

<script>
/* ==========================================================================
   1. CUSTOM IN-APP CONFIRMATION MODAL (Unsave & Unlike)
   ========================================================================== */
let pendingTargetFormId = null;

function promptConfirmAction(actionType, formId, recipeTitle) {
    pendingTargetFormId = formId;
    const modal = document.getElementById('confirmActionModal');
    const iconWrap = document.getElementById('confirmModalIconWrap');
    const titleEl = document.getElementById('confirmModalTitle');
    const msgEl = document.getElementById('confirmModalMessage');
    const submitBtn = document.getElementById('confirmModalSubmitBtn');

    if (actionType === 'unsave') {
        iconWrap.style.background = '#fff7ed';
        iconWrap.style.border = '2px solid #fed7aa';
        iconWrap.innerHTML = `
            <svg width="28" height="28" viewBox="0 0 24 24" fill="#ea580c" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
            </svg>
        `;
        titleEl.textContent = 'Xác nhận bỏ lưu món ăn?';
        msgEl.innerHTML = `Bạn có chắc chắn muốn bỏ lưu món «<strong>${recipeTitle}</strong>» khỏi danh sách đã lưu không?`;
        submitBtn.textContent = 'Xác nhận bỏ lưu';
        submitBtn.style.background = '#ea580c';
    } else {
        iconWrap.style.background = '#fef2f2';
        iconWrap.style.border = '2px solid #fecaca';
        iconWrap.innerHTML = `
            <svg width="30" height="30" viewBox="0 0 24 24" fill="#dc2626" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        `;
        titleEl.textContent = 'Xác nhận bỏ thích món ăn?';
        msgEl.innerHTML = `Bạn có chắc chắn muốn bỏ thả tim món «<strong>${recipeTitle}</strong>» không?`;
        submitBtn.textContent = 'Xác nhận bỏ thích';
        submitBtn.style.background = '#dc2626';
    }

    submitBtn.onclick = function() {
        if (pendingTargetFormId) {
            const form = document.getElementById(pendingTargetFormId);
            if (form) form.submit();
        }
    };

    modal.style.display = 'flex';
}

function closeConfirmActionModal() {
    const modal = document.getElementById('confirmActionModal');
    if (modal) modal.style.display = 'none';
    pendingTargetFormId = null;
}

/* ==========================================================================
   2. MULTI-DISH GROCERY CHECKBOXES & SMART AGGREGATION
   ========================================================================== */
function updateGroceryBatchBar() {
    const checks = document.querySelectorAll('.dish-grocery-checkbox');
    const checkedBoxes = Array.from(checks).filter(c => c.checked);
    const count = checkedBoxes.length;

    const countText = document.getElementById('selectedDishCountText');
    const btnCount = document.getElementById('groceryBtnCount');
    const selectAllCheck = document.getElementById('selectAllGroceryCheckbox');

    if (countText) countText.textContent = count;
    if (btnCount) btnCount.textContent = count;
    if (selectAllCheck) selectAllCheck.checked = (count === checks.length && count > 0);
}

function toggleSelectAllGrocery(checked) {
    const checks = document.querySelectorAll('.dish-grocery-checkbox');
    checks.forEach(c => c.checked = checked);
    updateGroceryBatchBar();
}

function parseVietnameseIngredient(rawLine) {
    let line = rawLine.trim()
        .replace(/^[-*•]+\s*/, '')
        .replace(/^\d+[\.\)]\s+(?=\d)/, '')
        .trim();
    if (!line) return null;

    // Seasonings & pantry items (User rule: show clean name without spoons, e.g. "1 thìa hạt nêm" -> "Hạt nêm")
    const spiceList = [
        { key: 'hạt nêm', name: 'Hạt nêm' },
        { key: 'tiêu xay', name: 'Tiêu xay' },
        { key: 'tiêu đen', name: 'Tiêu' },
        { key: 'hạt tiêu', name: 'Hạt tiêu' },
        { key: 'tiêu', name: 'Tiêu' },
        { key: 'nước mắm', name: 'Nước mắm' },
        { key: 'dầu hào', name: 'Dầu hào' },
        { key: 'dầu ăn', name: 'Dầu ăn' },
        { key: 'nước tương', name: 'Nước tương' },
        { key: 'xì dầu', name: 'Xì dầu' },
        { key: 'muối', name: 'Muối' },
        { key: 'đường', name: 'Đường' },
        { key: 'bột ngọt', name: 'Bột ngọt / Mì chính' },
        { key: 'mì chính', name: 'Bột ngọt / Mì chính' },
        { key: 'sa tế', name: 'Sa tế' },
        { key: 'dầu mè', name: 'Dầu mè' },
        { key: 'giấm', name: 'Giấm gạo' },
        { key: 'ngũ vị hương', name: 'Ngũ vị hương' },
        { key: 'bột bắp', name: 'Bột bắp' },
        { key: 'bột năng', name: 'Bột năng' },
        { key: 'rượu trắng', name: 'Rượu nấu ăn' },
        { key: 'ớt bột', name: 'Ớt bột' },
        { key: 'tương ớt', name: 'Tương ớt' },
        { key: 'tương cà', name: 'Tương cà' },
        { key: 'mayonnaise', name: 'Sốt Mayonnaise' }
    ];

    const lower = line.toLowerCase();
    for (const sp of spiceList) {
        if (lower.includes(sp.key)) {
            return { type: 'spice', name: sp.name };
        }
    }

    // Main ingredients with countable/measurable units:
    // e.g. "2 quả trứng", "300g thịt bò", "1 củ hành tây", "2 quả cà chua", "500g sườn non"
    const m = line.match(/^(\d+(?:[\.,]\d+)?|\d+\/\d+)?\s*(quả|trái|g|gram|kg|lạng|ml|lít|lit|củ|tép|nhánh|cây|bó|mớ|gói|hộp|con|lát|miếng|khúc)?\s*(.+)$/i);
    if (m) {
        let qty = m[1] ? parseFloat(m[1].replace(',', '.')) : 1;
        let unit = m[2] ? m[2].toLowerCase() : '';
        let name = m[3] ? m[3].trim() : line;

        // Clean action words
        name = name.replace(/\s*\((?:thái|cắt|băm|rửa|chín|tươi|đập dập|nấu|luộc).*?\)/ig, '')
                   .replace(/,\s*(?:thái|cắt|băm|rửa|chín|đập dập).*$/ig, '')
                   .trim();

        // Standardize common base names
        let norm = name.toLowerCase();
        if (norm === 'trứng' || norm === 'trứng gà') {
            name = 'Trứng gà';
            unit = unit || 'quả';
        } else if (norm === 'trứng vịt') {
            name = 'Trứng vịt';
            unit = unit || 'quả';
        } else if (norm.includes('thịt bò')) {
            name = 'Thịt bò';
            unit = unit || 'g';
        } else if (norm.includes('thịt heo') || norm.includes('thịt lợn') || norm.includes('thịt ba chỉ')) {
            name = 'Thịt ba chỉ / thịt heo';
            unit = unit || 'g';
        } else if (norm.includes('hành tây')) {
            name = 'Hành tây';
            unit = unit || 'củ';
        } else if (norm.includes('cà chua')) {
            name = 'Cà chua';
            unit = unit || 'quả';
        } else {
            name = name.charAt(0).toUpperCase() + name.slice(1);
        }

        return { type: 'main', qty, unit, name };
    }

    return { type: 'main', qty: 1, unit: '', name: line };
}

function openConsolidatedGroceryModal() {
    const checkedBoxes = Array.from(document.querySelectorAll('.dish-grocery-checkbox:checked'));
    if (checkedBoxes.length === 0) {
        alert('Vui lòng tích chọn ít nhất 1 món để gom nguyên liệu đi chợ!');
        return;
    }

    const dishTitles = checkedBoxes.map(c => c.dataset.title);
    const subtitleEl = document.getElementById('smartGroceryDishesSubtitle');
    if (subtitleEl) {
        subtitleEl.textContent = `Áp dụng cho ${checkedBoxes.length} món: ` + dishTitles.join(' • ');
    }

    // Collect all raw lines
    const mainMap = new Map(); // key -> { name, unit, qty }
    const spicesSet = new Set();

    checkedBoxes.forEach(c => {
        const ingText = c.dataset.ingredients || '';
        const lines = ingText.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
        lines.forEach(line => {
            const parsed = parseVietnameseIngredient(line);
            if (!parsed) return;

            if (parsed.type === 'spice') {
                spicesSet.add(parsed.name);
            } else {
                const key = `${parsed.name.toLowerCase()}__${parsed.unit.toLowerCase()}`;
                if (mainMap.has(key)) {
                    const item = mainMap.get(key);
                    item.qty += parsed.qty;
                } else {
                    mainMap.set(key, {
                        name: parsed.name,
                        unit: parsed.unit,
                        qty: parsed.qty
                    });
                }
            }
        });
    });

    // Render Section 1: Main Ingredients (Summed)
    const mainListEl = document.getElementById('smartMainIngredientsList');
    if (mainListEl) {
        if (mainMap.size === 0) {
            mainListEl.innerHTML = '<p style="color: #64748b; font-size: 0.88rem; margin: 0;">Chưa có nguyên liệu chính.</p>';
        } else {
            let html = '';
            let idx = 0;
            mainMap.forEach((val) => {
                const displayQty = val.qty % 1 === 0 ? val.qty : val.qty.toFixed(1);
                const fullText = `${displayQty} ${val.unit} ${val.name}`.replace(/\s+/g, ' ').trim();
                html += `
                    <label style="display: flex; align-items: center; gap: 0.65rem; padding: 0.55rem 0.75rem; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: background 0.15s;">
                        <input type="checkbox" style="width: 17px; height: 17px; accent-color: #16a34a; cursor: pointer;" onchange="this.parentElement.style.opacity = this.checked ? '0.5' : '1'; this.parentElement.style.textDecoration = this.checked ? 'line-through' : 'none';">
                        <span style="font-size: 0.92rem; font-weight: 700; color: #1e293b;">${fullText}</span>
                    </label>
                `;
                idx++;
            });
            mainListEl.innerHTML = html;
        }
    }

    // Render Section 2: Spices & Seasonings (Clean list without spoons)
    const spicesListEl = document.getElementById('smartSpicesList');
    if (spicesListEl) {
        if (spicesSet.size === 0) {
            spicesListEl.innerHTML = '<p style="color: #64748b; font-size: 0.88rem; margin: 0;">Không cần thêm gia vị đặc biệt.</p>';
        } else {
            let html = '';
            Array.from(spicesSet).sort().forEach(sp => {
                html += `
                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.45rem 0.85rem; background: #ffffff; border: 1.5px solid #fde68a; border-radius: 9999px; cursor: pointer; font-size: 0.86rem; font-weight: 700; color: #92400e;">
                        <input type="checkbox" style="width: 15px; height: 15px; accent-color: #d97706; cursor: pointer;" onchange="this.parentElement.style.opacity = this.checked ? '0.5' : '1'; this.parentElement.style.textDecoration = this.checked ? 'line-through' : 'none';">
                        <span>${sp}</span>
                    </label>
                `;
            });
            spicesListEl.innerHTML = html;
        }
    }

    const modal = document.getElementById('smartGroceryModal');
    if (modal) modal.style.display = 'flex';
}

function closeSmartGroceryModal() {
    const modal = document.getElementById('smartGroceryModal');
    if (modal) modal.style.display = 'none';
}

function copySmartGroceryList() {
    const mainItems = document.querySelectorAll('#smartMainIngredientsList label span');
    const spiceItems = document.querySelectorAll('#smartSpicesList label span');

    let text = '🛒 DANH SÁCH ĐI CHỢ COOKIO:\n\n🥦 THỰC PHẨM CẦN MUA:\n';
    mainItems.forEach(el => text += '- ' + el.textContent.trim() + '\n');
    text += '\n🧂 GIA VỊ KIỂM TRA LẠI:\n';
    spiceItems.forEach(el => text += '- ' + el.textContent.trim() + '\n');

    navigator.clipboard.writeText(text).then(() => {
        alert('Đã sao chép toàn bộ danh sách đi chợ vào bộ nhớ tạm!');
    }).catch(() => {
        alert('Không thể tự động sao chép. Vui lòng thử lại!');
    });
}
</script>

<?php
require __DIR__ . '/../includes/modal-login.php';
require __DIR__ . '/../includes/footer.php';
