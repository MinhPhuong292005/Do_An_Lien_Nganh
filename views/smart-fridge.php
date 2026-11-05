<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$recipes = db()->query(
    "SELECT id, title, category, cooking_time, servings, ingredients, image_url 
     FROM recipes 
     WHERE status = 'approved' 
     ORDER BY created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$initialRecipes = array_slice($recipes, 0, 12);

$ingredientGroups = [
    'Thịt & Thủy hải sản' => ['Thịt bò', 'Thịt gà', 'Thịt heo', 'Tôm', 'Cá'],
    'Rau củ quả' => ['Cà chua', 'Hành tây', 'Rau xanh', 'Cà rốt', 'Nấm'],
    'Trứng & Đậu phụ' => ['Trứng', 'Đậu phụ'],
    'Gia vị & Thảo mộc' => ['Tỏi', 'Gừng', 'Sả', 'Tiêu', 'Ớt', 'Lá chanh']
];

$loadSmartFridgeScript = true;
$pageTitle = 'Nguyên liệu - Tủ lạnh thông minh - Cookio';
require __DIR__ . '/../includes/header.php';
?>
<section class="recipe-detail-container" style="padding-top: 1.5rem; padding-bottom: 3.5rem;">
    <!-- Page Header (Clean, professional, matching Cookio/YummyDay aesthetic without cartoon icons) -->
    <div style="text-align: center; margin-bottom: 2.25rem;">
        <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.5px;">
            Tủ lạnh thông minh
        </h1>
        <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 580px; margin: 0 auto; line-height: 1.6;">
            Tích chọn những nguyên liệu đang có trong gian bếp của bạn, Cookio sẽ gợi ý ngay các món ăn nấu được tức thì!
        </p>
    </div>

    <!-- Fridge Drawer Card -->
    <div class="fridge-container">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1.5px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-main);">
                Chọn nguyên liệu có sẵn
            </h2>

            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <!-- Matching Mode Toggle -->
                <div class="fridge-mode-toggle" style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-page); padding: 0.4rem 0.75rem; border-radius: 99px; border: 1.5px solid var(--border-color); font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                    <span>Chế độ:</span>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; margin: 0; font-weight: 600;">
                        <input type="radio" name="match_mode" value="any" checked style="width: auto; margin: 0; accent-color: var(--primary);"> Linh hoạt
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; margin: 0; font-weight: 600;">
                        <input type="radio" name="match_mode" value="all" style="width: auto; margin: 0; accent-color: var(--primary);"> Chính xác
                    </label>
                </div>

                <button type="button" id="btnClearAll" class="button button-outline" style="padding: 0.4rem 0.95rem; font-size: 0.82rem; border-radius: 99px; font-weight: 700;">
                    Bỏ chọn tất cả
                </button>
            </div>
        </div>

        <div id="ingredientPicker">
            <?php foreach ($ingredientGroups as $groupTitle => $groupItems): ?>
                <div class="fridge-section-title">
                    <span style="display: inline-block; width: 4px; height: 16px; background: var(--primary); border-radius: 2px; margin-right: 0.4rem;"></span>
                    <?= e($groupTitle) ?>
                </div>
                <div class="fridge-ingredients-group" style="margin-bottom: 1.25rem;">
                    <?php foreach ($groupItems as $item): ?>
                        <label class="fridge-chip">
                            <input type="checkbox" value="<?= e($item) ?>">
                            <span><?= e($item) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <!-- Custom Ingredients Group -->
            <div id="customGroup" class="hidden">
                <div class="fridge-section-title">
                    <span style="display: inline-block; width: 4px; height: 16px; background: var(--accent); border-radius: 2px; margin-right: 0.4rem;"></span>
                    Nguyên liệu tùy chọn của bạn
                </div>
                <div id="customChips" class="fridge-ingredients-group" style="margin-bottom: 1.25rem;"></div>
            </div>
        </div>

        <!-- Add Custom Ingredient Input -->
        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px dashed var(--border-color); display: flex; gap: 10px; max-width: 34rem;">
            <input type="text" id="customIngredientInput" placeholder="Thêm nguyên liệu khác (vd: Me chua, Nước dừa, Sả cay...)" style="border-radius: 9999px; padding: 0.65rem 1.15rem;">
            <button type="button" id="btnAddIngredient" class="button button-create" style="white-space: nowrap; padding: 0.65rem 1.4rem; border-radius: 9999px; font-weight: 700;">
                + Thêm
            </button>
        </div>
    </div>

    <!-- Status Message -->
    <div id="fridgeMessage" class="notice" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; margin-bottom: 1.5rem; border-radius: 12px;">
        Hãy chọn ít nhất một nguyên liệu ở trên để bắt đầu tìm món phù hợp.
    </div>

    <!-- Recipe Results Grid -->
    <div class="recipe-grid" id="fridgeResults">
        <?php foreach ($initialRecipes as $recipe): ?>
            <?php 
            $imgSrc = !empty($recipe['image_url']) ? BASE_URL . '/' . e($recipe['image_url']) : BASE_URL . '/assets/images/default-recipe.jpg';
            ?>
            <article class="recipe-card fridge-recipe" data-ingredients="<?= e($recipe['ingredients']) ?>">
                <div class="recipe-card-media">
                    <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>">
                        <img src="<?= $imgSrc ?>" alt="<?= e($recipe['title']) ?>" loading="lazy">
                    </a>
                    <span class="card-category-badge"><?= e($recipe['category'] ?? 'Món chính') ?></span>
                </div>

                <div class="recipe-card-body">
                    <!-- Match Badge Container -->
                    <div class="match-badge-container" style="margin-bottom: 0.5rem; min-height: 22px;"></div>

                    <div class="card-meta-row" style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; display: flex; gap: 1rem; margin-bottom: 0.5rem;">
                        <span><?= e($recipe['cooking_time'] ?? '30 phút') ?></span>
                        <span>•</span>
                        <span><?= e($recipe['servings'] ?? '2 - 4 người') ?></span>
                    </div>

                    <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 700;">
                        <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>" style="color: var(--text-main);">
                            <?= e($recipe['title']) ?>
                        </a>
                    </h3>

                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1rem; background: var(--bg-page); padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--border-color);">
                        <strong style="color: var(--text-main);">Nguyên liệu:</strong> <?= e(mb_strimwidth((string) str_replace(["\r\n", "\n", "\r"], ', ', $recipe['ingredients']), 0, 75, '...')) ?>
                    </p>

                    <a class="button button-create full-width" style="text-align: center; border-radius: 9999px; font-weight: 700;" href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>">
                        Xem cách nấu ngay &rarr;
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <script id="smartFridgeData" type="application/json"><?= json_encode($recipes, JSON_UNESCAPED_UNICODE) ?></script>

    <!-- No Match State -->
    <div id="noMatchMessage" class="empty-state-box hidden" style="margin-top: 2rem; padding: 3rem 2rem; border-radius: 16px; border: 1.5px solid var(--border-color); background: var(--bg-surface); text-align: center;">
        <p style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">Chưa có món nào khớp với toàn bộ nguyên liệu bạn đã chọn</p>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem;">Mẹo: Bạn có thể chuyển sang chế độ Linh hoạt hoặc bỏ bớt nguyên liệu kén người nấu!</p>
        <a href="<?= BASE_URL ?>/views/create-recipe.php" class="button button-create" style="border-radius: 9999px; font-weight: 700; padding: 0.75rem 1.75rem;">+ Đăng công thức với nguyên liệu này</a>
    </div>
</section>

<?php
require __DIR__ . '/../includes/modal-login.php';
require __DIR__ . '/../includes/footer.php';