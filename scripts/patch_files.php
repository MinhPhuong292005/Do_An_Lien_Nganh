<?php

declare(strict_types=1);

// 1. PATCH views/recipe-detail.php
$fileDetail = __DIR__ . '/../views/recipe-detail.php';
$contentDetail = file_get_contents($fileDetail);

$contentDetail = str_replace('$avgRating = 5.0;', '$avgRating = 0.0;', $contentDetail);

// Replace throttled view with direct increment
$patternView = '/if\s*\(!in_array\(\(int\)\s*\$recipe\[\'id\'\],\s*\$_SESSION\[\'viewed_recipes\'\],\s*true\)\)\s*\{[^\}]+\}/s';
$contentDetail = preg_replace(
    $patternView,
    'db()->prepare("UPDATE recipes SET views_count = views_count + 1 WHERE id = ?")->execute([(int) $recipe["id"]]); $recipe["views_count"] = (int) ($recipe["views_count"] ?? 0) + 1;',
    $contentDetail
);

// Specs rating display
$contentDetail = str_replace(
    '<strong><?= $avgRating ?>/5</strong> (<?= $ratingCount ?>)',
    '<strong><?= $ratingCount > 0 ? number_format($avgRating, 1) . \'/5\' : \'Chưa có\' ?></strong> (<?= $ratingCount ?>)',
    $contentDetail
);

// Related recipes rating
$patternRelRating = '/<\?=\s*round\(\(float\)\(\$rel\[\'avg_rating\'\]\s*\?\?\s*5\),\s*1\)\s*\?>\s*\((<\?=\s*\(int\)\$rel\[\'review_count\'\]\s*\?>)\)/';
$contentDetail = preg_replace(
    $patternRelRating,
    '<?= (int)$rel["review_count"] > 0 ? round((float)$rel["avg_rating"], 1) . " (" . (int)$rel["review_count"] . ")" : "Chưa có đánh giá" ?>',
    $contentDetail
);

file_put_contents($fileDetail, $contentDetail);
echo "Patched views/recipe-detail.php successfully!\n";

// 2. PATCH index.php
$fileIndex = __DIR__ . '/../index.php';
$contentIndex = file_get_contents($fileIndex);

// Add 'Món chay' to $categoryList if not already there
if (!str_contains($contentIndex, "'Món chay' => 'Món chay'")) {
    $contentIndex = str_replace(
        "'Món chiên' => 'Món chiên',",
        "'Món chiên' => 'Món chiên',\n    'Món chay' => 'Món chay',",
        $contentIndex
    );
}

// Replace search query logic in index.php
$oldSearchBlock = <<<'SEARCH_BLOCK'
if ($searchQuery !== '') {
    $where[] = "(r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ?)";
    $like = '%' . $searchQuery . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
SEARCH_BLOCK;

$newSearchBlock = <<<'SEARCH_BLOCK'
$titlePrioritySql = "";
if ($searchQuery !== '') {
    if (str_contains($searchQuery, ',') || str_contains($searchQuery, ';') || str_contains($searchQuery, '+')) {
        $rawTokens = preg_split('/[,;+]+/', $searchQuery);
    } else {
        $rawTokens = [$searchQuery];
    }
    $tokens = array_values(array_filter(array_map('trim', $rawTokens), fn($t) => $t !== ''));

    if (!empty($tokens)) {
        if (count($tokens) === 1) {
            $singleTerm = $tokens[0];
            $words = array_values(array_filter(explode(' ', $singleTerm), fn($w) => mb_strlen(trim($w)) > 1));
            if (count($words) >= 2) {
                $subConditions = [];
                $subParams = [];
                foreach ($words as $w) {
                    $subConditions[] = "(r.title LIKE ? OR r.ingredients LIKE ?)";
                    $subParams[] = '%' . $w . '%';
                    $subParams[] = '%' . $w . '%';
                }
                $where[] = "((r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ?) OR (" . implode(' AND ', $subConditions) . "))";
                $like = '%' . $singleTerm . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                foreach ($subParams as $sp) {
                    $params[] = $sp;
                }
            } else {
                $where[] = "(r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ?)";
                $like = '%' . $singleTerm . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }
            $titlePrioritySql = "CASE WHEN r.title LIKE " . db()->quote('%' . $singleTerm . '%') . " THEN 0 ELSE 1 END ASC, ";
        } else {
            // Khi người dùng nhập nhiều nguyên liệu cách nhau bởi dấu phẩy (vd: trứng, cà chua)
            // BẮT BUỘC công thức phải có ĐẦY ĐỦ các nguyên liệu đó (AND logic)
            $ingredientConditions = [];
            foreach ($tokens as $token) {
                $ingredientConditions[] = "(r.title LIKE ? OR r.ingredients LIKE ?)";
                $params[] = '%' . $token . '%';
                $params[] = '%' . $token . '%';
            }
            $where[] = "(" . implode(' AND ', $ingredientConditions) . ")";
            $titlePrioritySql = "CASE WHEN r.title LIKE " . db()->quote('%' . $tokens[0] . '%') . " THEN 0 ELSE 1 END ASC, ";
        }
    }
}
SEARCH_BLOCK;

$contentIndex = str_replace($oldSearchBlock, $newSearchBlock, $contentIndex);

// Add $titlePrioritySql to $orderBy
$contentIndex = str_replace(
    '$orderBy = match ($sortBy) {',
    '$orderBy = ($titlePrioritySql ?? "") . match ($sortBy) {',
    $contentIndex
);

// Remove COALESCE fake 5.0
$contentIndex = str_replace(
    'COALESCE((SELECT ROUND(AVG(c.rating), 1) FROM comments c WHERE c.recipe_id = r.id), 5.0) AS avg_rating,',
    '(SELECT ROUND(AVG(c.rating), 1) FROM comments c WHERE c.recipe_id = r.id) AS avg_rating,',
    $contentIndex
);

// Update Curated menuSuggestDishes IDs (Image 2)
$oldMenuSuggestPattern = '/\$stmtMenuSuggest\s*=\s*db\(\)->query\(".*?"\);/s';
$newMenuSuggest = <<<'SQL'
$stmtMenuSuggest = db()->query("
    SELECT r.*, u.username AS author_name,
           (SELECT ROUND(AVG(c.rating), 1) FROM comments c WHERE c.recipe_id = r.id) AS avg_rating,
           (SELECT COUNT(*) FROM comments c WHERE c.recipe_id = r.id) AS review_count
    FROM recipes r
    JOIN users u ON u.id = r.author_id
    WHERE r.status = 'approved' AND r.id IN (60, 2, 19, 32)
    ORDER BY FIELD(r.id, 60, 2, 19, 32)
    LIMIT 4
");
SQL;
$contentIndex = preg_replace($oldMenuSuggestPattern, $newMenuSuggest, $contentIndex);

// Update Curated tipsDishes IDs (Image 4)
$oldTipsPattern = '/\$stmtTipsDishes\s*=\s*db\(\)->query\(".*?"\);/s';
$newTips = <<<'SQL'
$stmtTipsDishes = db()->query("
    SELECT r.*, u.username AS author_name,
           (SELECT ROUND(AVG(c.rating), 1) FROM comments c WHERE c.recipe_id = r.id) AS avg_rating,
           (SELECT COUNT(*) FROM comments c WHERE c.recipe_id = r.id) AS review_count
    FROM recipes r
    JOIN users u ON u.id = r.author_id
    WHERE r.status = 'approved' AND r.id IN (38, 72, 8, 9)
    ORDER BY FIELD(r.id, 38, 72, 8, 9)
    LIMIT 4
");
SQL;
$contentIndex = preg_replace($oldTipsPattern, $newTips, $contentIndex);

// Update badge matching to include Chay & Xào
$oldBadge1 = <<<'BADGE'
$badgeTag = match($dish['category'] ?? '') {
                        'Món chiên' => 'Chiên',
                        'Món kho' => 'Kho',
                        'Món hấp' => 'Hấp',
                        'Món canh' => 'Canh',
                        default => 'Xào'
                    };
BADGE;

$newBadge1 = <<<'BADGE'
$badgeTag = match($dish['category'] ?? '') {
                        'Món chiên' => 'Chiên',
                        'Món kho' => 'Kho',
                        'Món hấp' => 'Hấp',
                        'Món canh' => 'Canh',
                        'Món chay' => 'Chay',
                        default => 'Xào'
                    };
BADGE;
$contentIndex = str_replace($oldBadge1, $newBadge1, $contentIndex);

$oldBadge2 = <<<'BADGE'
$badgeTag = match($recipe['category'] ?? '') {
                    'Món chiên' => 'Chiên',
                    'Món kho' => 'Kho',
                    'Món hấp' => 'Hấp',
                    'Món canh' => 'Canh',
                    default => 'Xào'
                };
BADGE;

$newBadge2 = <<<'BADGE'
$badgeTag = match($recipe['category'] ?? '') {
                    'Món chiên' => 'Chiên',
                    'Món kho' => 'Kho',
                    'Món hấp' => 'Hấp',
                    'Món canh' => 'Canh',
                    'Món chay' => 'Chay',
                    default => 'Xào'
                };
BADGE;
$contentIndex = str_replace($oldBadge2, $newBadge2, $contentIndex);

// Author & Rating in Latest Featured Card (lines ~595)
$oldFeaturedBody = <<<'BODY'
                        <div class="yummy-card-body">
                            <h3 class="yummy-card-title">
                                <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $dish['id'] ?>">
                                    <?= e($dish['title']) ?>
                                </a>
                            </h3>
                            <div class="yummy-card-meta">
                                <span>⏱ <?= e($dish['cooking_time']) ?></span>
                                <span class="meta-sep">|</span>
                                <span><?= e($dish['difficulty'] ?? 'Trung bình') ?></span>
                                <span class="meta-sep">|</span>
                                <span>⭐ <?= $dish['avg_rating'] ?></span>
                            </div>
                        </div>
BODY;

$newFeaturedBody = <<<'BODY'
                        <div class="yummy-card-body">
                            <h3 class="yummy-card-title">
                                <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $dish['id'] ?>">
                                    <?= e($dish['title']) ?>
                                </a>
                            </h3>
                            <div class="card-author-chip" style="font-size: 0.82rem; color: #64748b; margin-top: 0.2rem; margin-bottom: 0.35rem;">
                                Bởi: <strong style="color: #ea580c;"><?= e($dish['author_name']) ?></strong>
                            </div>
                            <div class="yummy-card-meta">
                                <span>⏱ <?= e($dish['cooking_time']) ?></span>
                                <span class="meta-sep">|</span>
                                <span><?= e($dish['difficulty'] ?? 'Dễ làm') ?></span>
                                <span class="meta-sep">|</span>
                                <?php if (!empty($dish['review_count']) && (int)$dish['review_count'] > 0): ?>
                                    <span>⭐ <?= number_format((float)$dish['avg_rating'], 1) ?> (<?= (int)$dish['review_count'] ?>)</span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted, #94a3b8); font-size: 0.82rem;">Chưa có đánh giá</span>
                                <?php endif; ?>
                            </div>
                        </div>
BODY;
$contentIndex = str_replace($oldFeaturedBody, $newFeaturedBody, $contentIndex);

// Author & Rating in Search Results Card (lines ~722)
$oldSearchCardBody = <<<'BODY'
                    <div class="yummy-card-body">
                        <h3 class="yummy-card-title">
                            <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>">
                                <?= e($recipe['title']) ?>
                            </a>
                        </h3>
                        <div class="yummy-card-meta">
                            <span>⏱ <?= e($recipe['cooking_time']) ?></span>
                            <span class="meta-sep">|</span>
                            <span><?= e($recipe['difficulty'] ?? 'Trung bình') ?></span>
                            <span class="meta-sep">|</span>
                            <span>⭐ <?= $recipe['avg_rating'] ?></span>
                        </div>
                    </div>
BODY;

$newSearchCardBody = <<<'BODY'
                    <div class="yummy-card-body">
                        <h3 class="yummy-card-title">
                            <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $recipe['id'] ?>">
                                <?= e($recipe['title']) ?>
                            </a>
                        </h3>
                        <div class="card-author-chip" style="font-size: 0.82rem; color: #64748b; margin-top: 0.2rem; margin-bottom: 0.35rem;">
                            Bởi: <strong style="color: #ea580c;"><?= e($recipe['author_name']) ?></strong>
                        </div>
                        <div class="yummy-card-meta">
                            <span>⏱ <?= e($recipe['cooking_time']) ?></span>
                            <span class="meta-sep">|</span>
                            <span><?= e($recipe['difficulty'] ?? 'Dễ làm') ?></span>
                            <span class="meta-sep">|</span>
                            <?php if (!empty($recipe['review_count']) && (int)$recipe['review_count'] > 0): ?>
                                <span>⭐ <?= number_format((float)$recipe['avg_rating'], 1) ?> (<?= (int)$recipe['review_count'] ?>)</span>
                            <?php else: ?>
                                <span style="color: var(--text-muted, #94a3b8); font-size: 0.82rem;">Chưa có đánh giá</span>
                            <?php endif; ?>
                        </div>
                    </div>
BODY;
$contentIndex = str_replace($oldSearchCardBody, $newSearchCardBody, $contentIndex);

// Also add author in menu suggest cards (~line 458)
$oldMenuBody = <<<'BODY'
                        <div class="yummy-card-body">
                            <h3 class="yummy-card-title">
                                <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $dish['id'] ?>">
                                    <?= e($dish['title']) ?>
                                </a>
                            </h3>
                            <div class="yummy-card-meta">
                                <span>⏱ <?= e($dish['cooking_time']) ?></span>
                                <span class="meta-sep">|</span>
                                <span><?= e($dish['difficulty'] ?? 'Trung bình') ?></span>
                            </div>
                        </div>
BODY;

$newMenuBody = <<<'BODY'
                        <div class="yummy-card-body">
                            <h3 class="yummy-card-title">
                                <a href="<?= BASE_URL ?>/views/recipe-detail.php?id=<?= (int) $dish['id'] ?>">
                                    <?= e($dish['title']) ?>
                                </a>
                            </h3>
                            <div class="card-author-chip" style="font-size: 0.82rem; color: #64748b; margin-top: 0.2rem; margin-bottom: 0.35rem;">
                                Bởi: <strong style="color: #ea580c;"><?= e($dish['author_name']) ?></strong>
                            </div>
                            <div class="yummy-card-meta">
                                <span>⏱ <?= e($dish['cooking_time']) ?></span>
                                <span class="meta-sep">|</span>
                                <span><?= e($dish['difficulty'] ?? 'Dễ làm') ?></span>
                                <span class="meta-sep">|</span>
                                <?php if (!empty($dish['review_count']) && (int)$dish['review_count'] > 0): ?>
                                    <span>⭐ <?= number_format((float)$dish['avg_rating'], 1) ?> (<?= (int)$dish['review_count'] ?>)</span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted, #94a3b8); font-size: 0.82rem;">Chưa có đánh giá</span>
                                <?php endif; ?>
                            </div>
                        </div>
BODY;
$contentIndex = str_replace($oldMenuBody, $newMenuBody, $contentIndex);

file_put_contents($fileIndex, $contentIndex);
echo "Patched index.php successfully!\n";

