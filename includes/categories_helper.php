<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (!function_exists('slugify_category')) {
    function slugify_category(string $str): string
    {
        $str = mb_strtolower(trim($str), 'UTF-8');
        $transliterations = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y'
        ];
        $str = strtr($str, $transliterations);
        $str = preg_replace('/[^a-z0-9]+/i', '-', $str);
        return trim($str, '-');
    }
}

if (!function_exists('get_all_categories')) {
    function get_all_categories(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        try {
            $stmt = db()->query("
                SELECT c.*, 
                    (SELECT COUNT(*) FROM recipes r WHERE r.category = c.name) AS recipe_count 
                FROM categories c 
                ORDER BY c.display_order ASC, c.id ASC
            ");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                $cached = $rows;
                return $cached;
            }
        } catch (Throwable $e) {
            error_log("Error in get_all_categories: " . $e->getMessage());
        }

        // Fallback default list
        return [
            ['id' => 1, 'name' => 'Món Âu - Mỹ', 'slug' => 'mon-au-my', 'icon' => '', 'display_order' => 1, 'recipe_count' => 0],
            ['id' => 2, 'name' => 'Món bún & phở', 'slug' => 'mon-bun-pho', 'icon' => '', 'display_order' => 2, 'recipe_count' => 0],
            ['id' => 3, 'name' => 'Món hấp', 'slug' => 'mon-hap', 'icon' => '', 'display_order' => 3, 'recipe_count' => 0],
            ['id' => 4, 'name' => 'Món kho', 'slug' => 'mon-kho', 'icon' => '', 'display_order' => 4, 'recipe_count' => 0],
            ['id' => 5, 'name' => 'Món chiên', 'slug' => 'mon-chien', 'icon' => '', 'display_order' => 5, 'recipe_count' => 0],
            ['id' => 6, 'name' => 'Món xào', 'slug' => 'mon-xao', 'icon' => '', 'display_order' => 6, 'recipe_count' => 0],
            ['id' => 7, 'name' => 'Món bánh', 'slug' => 'mon-banh', 'icon' => '', 'display_order' => 7, 'recipe_count' => 0],
            ['id' => 8, 'name' => 'Món canh', 'slug' => 'mon-canh', 'icon' => '', 'display_order' => 8, 'recipe_count' => 0],
            ['id' => 9, 'name' => 'Món nướng', 'slug' => 'mon-nuong', 'icon' => '', 'display_order' => 9, 'recipe_count' => 0],
            ['id' => 10, 'name' => 'Món chay', 'slug' => 'mon-chay', 'icon' => '', 'display_order' => 10, 'recipe_count' => 0],
            ['id' => 11, 'name' => 'Món luộc', 'slug' => 'mon-luoc', 'icon' => '', 'display_order' => 11, 'recipe_count' => 0],
            ['id' => 12, 'name' => 'Món lẩu', 'slug' => 'mon-lau', 'icon' => '', 'display_order' => 12, 'recipe_count' => 0],
        ];
    }
}

