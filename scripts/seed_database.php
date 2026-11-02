<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

echo "Bắt đầu cập nhật 100 món ăn chuẩn chỉnh...\n";

$part1 = require __DIR__ . '/recipes_part1.php';
$part2 = require __DIR__ . '/recipes_part2.php';
$part3 = require __DIR__ . '/recipes_part3.php';

$allRecipes = array_merge($part1, $part2, $part3);
echo "Tổng số món ăn chuẩn bị nạp: " . count($allRecipes) . "\n";

$db = db();
$db->exec("SET FOREIGN_KEY_CHECKS = 0;");

// Clear existing recipes and comments
$db->exec("TRUNCATE TABLE recipe_likes;");
$db->exec("TRUNCATE TABLE saved_recipes;");
$db->exec("TRUNCATE TABLE comments;");
$db->exec("TRUNCATE TABLE cookbook_recipes;");
$db->exec("TRUNCATE TABLE recipes;");

$stmtInsert = $db->prepare("
    INSERT INTO recipes (
        id, title, description, category, cooking_time, servings,
        ingredients, instructions, tips, calories, protein, carbs, fat,
        dietary_tags, image_url, author_id, status, views_count, likes_count,
        created_at, updated_at
    ) VALUES (
        :id, :title, :description, :category, :cooking_time, :servings,
        :ingredients, :instructions, :tips, :calories, :protein, :carbs, :fat,
        :dietary_tags, :image_url, :author_id, 'approved', 0, 0,
        NOW() - INTERVAL :days_ago DAY, NOW()
    )
");

$days = 100;
foreach ($allRecipes as $r) {
    $stmtInsert->execute([
        ':id' => $r['id'],
        ':title' => $r['title'],
        ':description' => $r['description'],
        ':category' => $r['category'],
        ':cooking_time' => $r['cooking_time'],
        ':servings' => $r['servings'],
        ':ingredients' => $r['ingredients'],
        ':instructions' => $r['instructions'],
        ':tips' => $r['tips'],
        ':calories' => $r['calories'],
        ':protein' => $r['protein'],
        ':carbs' => $r['carbs'],
        ':fat' => $r['fat'],
        ':dietary_tags' => $r['dietary_tags'],
        ':image_url' => $r['image_url'],
        ':author_id' => $r['author_id'],
        ':days_ago' => $days--
    ]);
}
echo "Đã nạp thành công 100 món ăn vào database với lượt xem = 0!\n";

// Nạp các đánh giá thực tế (chỉ cho các món có phản hồi thực, các món còn lại để 0 đánh giá chuẩn)
$sampleComments = [
    // Bò xào hành tây
    [
        'recipe_id' => 1,
        'user_id' => 5, // me_bong
        'rating' => 5,
        'content' => 'Thịt bò xào lửa lớn đúng như mẹo của đầu bếp, mềm ngọt mọng nước và không hề bị dai chút nào!',
        'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
    ],
    [
        'recipe_id' => 1,
        'user_id' => 6, // bep_hoa
        'rating' => 5,
        'content' => 'Hành tây và cần tây xào chín tới giòn ngọt, thơm nức mũi. Cả nhà mình ai cũng thích!',
        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
    ],
    // Trứng chiên cà chua
    [
        'recipe_id' => 55,
        'user_id' => 8, // an_ngon_moingay
        'rating' => 5,
        'content' => 'Đánh trứng sủi bọt rán lửa vừa nở xốp mềm béo ngậy, sốt cà chua chua ngọt rất vừa vặn.',
        'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
    ],
    [
        'recipe_id' => 55,
        'user_id' => 4, // user1111
        'rating' => 5,
        'content' => 'Món này làm nhanh trong 10 phút là xong bữa tối, ăn với cơm nóng thì tuyệt vời.',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
    ],
    // Phở bò Hà Nội
    [
        'recipe_id' => 36,
        'user_id' => 5, // me_bong
        'rating' => 5,
        'content' => 'Nước dùng ninh xương bò trong veo và thơm ngát mùi hồi quế đúng điệu phố cổ cô Hoa ơi! Cả nhà cháu ăn ai cũng tấm tắc khen ngon.',
        'created_at' => date('Y-m-d H:i:s', strtotime('-7 days'))
    ],
    [
        'recipe_id' => 36,
        'user_id' => 7, // chu_nam_cook
        'rating' => 5,
        'content' => 'Công thức nấu nước dùng chuẩn chỉ lắm. Mình cho thêm 1 thìa nước mắm ngon lúc chuẩn bị múc ra bát, mùi thơm bốc lên nức nở khắp cả gian bếp.',
        'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
    ],
    // Canh chua cá lóc
    [
        'recipe_id' => 19,
        'user_id' => 2, // chef_lan
        'rating' => 5,
        'content' => 'Tô canh chua miền Tây đúng điệu giải nhiệt ngày oi bức. Thịt cá lóc đồng ngọt thơm, vị chua thanh từ me chín cực kỳ thanh mát.',
        'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
    ],
    // Thịt kho tàu
    [
        'recipe_id' => 37,
        'user_id' => 9, // lan_anh_kitchen
        'rating' => 5,
        'content' => 'Kho nước dừa liu riu miếng mỡ trong veo, thịt mềm tan béo ngậy mà không hề ngấy.',
        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
    ],
    // Nem rán truyền thống
    [
        'recipe_id' => 58,
        'user_id' => 1, // admin1111
        'rating' => 5,
        'content' => 'Bí quyết rán 2 lửa tuyệt đỉnh, vỏ nem giòn rụm suốt cả buổi cơm tối luôn!',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
    ],
    // Sườn xào chua ngọt
    [
        'recipe_id' => 39,
        'user_id' => 4, // user1111
        'rating' => 5,
        'content' => 'Tỷ lệ pha nước sốt chuẩn 10 điểm, sườn mềm ngấm sốt nâu bóng bám quanh từng miếng thịt.',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 days'))
    ]
];

$stmtComment = $db->prepare("
    INSERT INTO comments (recipe_id, user_id, rating, content, created_at)
    VALUES (?, ?, ?, ?, ?)
");
foreach ($sampleComments as $c) {
    $stmtComment->execute([
        $c['recipe_id'],
        $c['user_id'],
        $c['rating'],
        $c['content'],
        $c['created_at']
    ]);
}
echo "Đã thêm các đánh giá thực tế chuẩn xác cho các món tiêu biểu!\n";

$db->exec("SET FOREIGN_KEY_CHECKS = 1;");
echo "Hoàn tất toàn bộ quy trình cập nhật database!\n";

