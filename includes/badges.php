<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Checks and fetches authentic badges for a user based on strict criteria:
 * 1. 'chef' (Đầu bếp Cookio): earned when posting the 1st approved recipe (COUNT >= 1)
 * 2. 'gold_chef' (Bếp Trưởng Vàng): earned when posting 10 approved recipes (COUNT >= 10)
 * 3. 'fav' (Được Yêu Thích): earned when recipes reach 100 real likes (likes >= 100)
 * 
 * Badges require user confirmation before being displayed publicly.
 */
function check_and_get_user_badges(int $userId): array
{
    $pdo = db();

    // 1. Calculate actual achievements
    $stmtR = $pdo->prepare("SELECT COUNT(*) FROM recipes WHERE author_id = ? AND status = 'approved'");
    $stmtR->execute([$userId]);
    $recipeCount = (int) $stmtR->fetchColumn();

    $stmtL = $pdo->prepare("
        SELECT COUNT(rl.id) 
        FROM recipe_likes rl 
        JOIN recipes r ON r.id = rl.recipe_id 
        WHERE r.author_id = ?
    ");
    $stmtL->execute([$userId]);
    $likesCount = (int) $stmtL->fetchColumn();

    $badgeDefinitions = [
        'chef' => [
            'key' => 'chef',
            'name' => 'Đầu bếp Cookio',
            'icon' => '',
            'desc' => 'Đăng thành công công thức nấu ăn đầu tiên',
            'eligible' => ($recipeCount >= 1),
            'badge_style' => 'background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;'
        ],
        'gold_chef' => [
            'key' => 'gold_chef',
            'name' => 'Bếp Trưởng Vàng',
            'icon' => '',
            'desc' => 'Đăng từ 10 công thức nấu ăn được duyệt trở lên',
            'eligible' => ($recipeCount >= 10),
            'badge_style' => 'background: #fef9c3; color: #854d0e; border: 1px solid #fef08a;'
        ],
        'fav' => [
            'key' => 'fav',
            'name' => 'Được Yêu Thích',
            'icon' => '',
            'desc' => 'Đạt từ 100 lượt thả tim thực tế từ cộng đồng',
            'eligible' => ($likesCount >= 100),
            'badge_style' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;'
        ]
    ];

    // Fetch existing records from user_badges
    $stmtB = $pdo->prepare("SELECT badge_key, is_displayed FROM user_badges WHERE user_id = ?");
    $stmtB->execute([$userId]);
    $existing = $stmtB->fetchAll(PDO::FETCH_KEY_PAIR); // [badge_key =>is_displayed]

    $result = [];
    $pendingConfirm = [];

    foreach ($badgeDefinitions as $key => $def) {
        if ($def['eligible']) {
            if (!isset($existing[$key])) {
                // Not recorded yet: insert with is_displayed = 0 (unconfirmed)
                $ins = $pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_key, is_displayed) VALUES (?, ?, 0)");
                $ins->execute([$userId, $key]);
                $def['is_displayed'] = 0;
                $def['needs_confirmation'] = true;
                $pendingConfirm[] = $def;
            } else {
                $def['is_displayed'] = (int) $existing[$key];
                $def['needs_confirmation'] = ((int) $existing[$key] === 0);
                if ($def['needs_confirmation']) {
                    $pendingConfirm[] = $def;
                }
            }
            $result[$key] = $def;
        }
    }

    return [
        'all' => $result,
        'displayed' =>array_filter($result, fn($b) => (int)$b['is_displayed'] === 1),
        'pending' => $pendingConfirm,
        'recipe_count' => $recipeCount,
        'likes_count' => $likesCount
    ];
}

