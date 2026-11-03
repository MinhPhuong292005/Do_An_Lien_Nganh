<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = (string) ($_GET['action'] ?? 'search');

if ($action === 'search') {
    $q = trim((string) ($_GET['q'] ?? ''));
    if (mb_strlen($q) < 1) {
        echo json_encode(['success' => true, 'results' => []]);
        exit;
    }

    // Tách từ khóa & mở rộng tìm kiếm theo tiêu đề món ăn
    require_once __DIR__ . '/../includes/search_helper.php';
    [$searchClauses, $searchParams] = build_smart_search_conditions($q);

    $searchWhere = ["r.status = 'approved'"];
    foreach ($searchClauses as $sc) {
        $searchWhere[] = $sc;
    }

    $sql = "
        SELECT r.id, r.title, r.category, r.cooking_time, r.servings, r.image_url, r.calories, r.views_count,
               u.username AS author_name,
               (SELECT COUNT(*) FROM recipe_likes rl WHERE rl.recipe_id = r.id) AS likes_count,
               (SELECT ROUND(AVG(c.rating), 1) FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL)) AS avg_rating,
               (SELECT COUNT(*) FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL)) AS review_count
        FROM recipes r
        JOIN users u ON u.id = r.author_id
        WHERE " . implode(' AND ', $searchWhere) . "
        ORDER BY r.views_count DESC, r.created_at DESC
        LIMIT 6
    ";
    $stmt = db()->prepare($sql);
    $stmt->execute($searchParams);
    $rows = $stmt->fetchAll();

    $results = [];
    foreach ($rows as $row) {
        $img = (string) ($row['image_url'] ?? '');
        if ($img !== '') {
            if (!str_starts_with($img, 'assets/') && !str_starts_with($img, 'uploads/')) {
                $img = 'uploads/recipes/' . $img;
            }
            $imgUrl = BASE_URL . '/' . ltrim($img, '/');
        } else {
            $imgUrl = BASE_URL . '/assets/images/default-recipe.jpg';
        }

        $results[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'category' => (string) $row['category'],
            'cooking_time' => (string) $row['cooking_time'],
            'servings' => (string) $row['servings'],
            'calories' => $row['calories'] ? (int) $row['calories'] : null,
            'author_name' => (string) $row['author_name'],
            'likes_count' => (int) $row['likes_count'],
            'views_count' => (int) ($row['views_count'] ?? 0),
            'avg_rating' => $row['review_count'] > 0 ? (float) $row['avg_rating'] : null,
            'review_count' => (int) ($row['review_count'] ?? 0),
            'image_url' => $imgUrl,
            'url' => BASE_URL . '/views/recipe-detail.php?id=' . (int) $row['id'],
        ];
    }

    echo json_encode(['success' => true, 'results' => $results, 'count' => count($results)]);
    exit;
}

if ($action === 'popular') {
    // Top 5 trending recipes based on views_count
    $sql = "
        SELECT r.id, r.title, r.category, r.cooking_time, r.image_url, r.views_count,
               u.username AS author_name,
               (SELECT COUNT(*) FROM recipe_likes rl WHERE rl.recipe_id = r.id) AS likes_count,
               (SELECT ROUND(AVG(c.rating), 1) FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL)) AS avg_rating,
               (SELECT COUNT(*) FROM comments c WHERE c.recipe_id = r.id AND (c.is_hidden = 0 OR c.is_hidden IS NULL)) AS review_count
        FROM recipes r
        JOIN users u ON u.id = r.author_id
        WHERE r.status = 'approved'
        ORDER BY r.views_count DESC, r.created_at DESC
        LIMIT 5
    ";
    $rows = db()->query($sql)->fetchAll();

    $popular = [];
    foreach ($rows as $row) {
        $img = (string) ($row['image_url'] ?? '');
        if ($img !== '') {
            if (!str_starts_with($img, 'assets/') && !str_starts_with($img, 'uploads/')) {
                $img = 'uploads/recipes/' . $img;
            }
            $imgUrl = BASE_URL . '/' . ltrim($img, '/');
        } else {
            $imgUrl = BASE_URL . '/assets/images/default-recipe.jpg';
        }

        $popular[] = [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'category' => (string) $row['category'],
            'cooking_time' => (string) $row['cooking_time'],
            'author_name' => (string) $row['author_name'],
            'views_count' => (int) ($row['views_count'] ?? 0),
            'avg_rating' => $row['review_count'] > 0 ? (float) $row['avg_rating'] : null,
            'review_count' => (int) ($row['review_count'] ?? 0),
            'likes_count' => (int) $row['likes_count'],
            'image_url' => $imgUrl,
            'url' => BASE_URL . '/views/recipe-detail.php?id=' . (int) $row['id'],
        ];
    }

    echo json_encode(['success' => true, 'popular' => $popular]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
