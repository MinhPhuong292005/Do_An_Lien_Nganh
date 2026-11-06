<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    session_start();
}

if (!defined('BASE_URL')) {
    $envBase = getenv('BASE_URL');
    if ($envBase !== false) {
        define('BASE_URL', $envBase);
    } elseif (PHP_SAPI === 'cli-server') {
        define('BASE_URL', '');
    } else {
        define('BASE_URL', '/cooking-website');
    }
}

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1;port=3307');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'cookio_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
}
const UPLOAD_DIR = __DIR__ . '/../uploads/recipes/';
const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function get_img_url(?string $path, string $default = 'assets/images/default-recipe.jpg'): string
{
    $target = !empty($path) ? ltrim($path, '/') : $default;
    if (!str_starts_with($target, 'assets/') && !str_starts_with($target, 'uploads/')) {
        $target = 'uploads/recipes/' . $target;
    }
    $localFile = dirname(__DIR__) . '/' . $target;
    $v = file_exists($localFile) ? filemtime($localFile) : 1793960000;
    return BASE_URL . '/' . $target . '?v=' . $v;
}

function redirect(string $path): never
{
    $base = rtrim(BASE_URL, '/');
    $target = ltrim($path, '/');
    $url = $base !== '' ? $base . '/' . $target : '/' . $target;
    header('Location: ' . $url);
    exit;
}

function is_admin(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_logged_in(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
        session_start();
    }
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize_text(string $input): string
{
    return trim(strip_tags($input));
}

function get_chef_badges(int $userId): array
{
    require_once __DIR__ . '/../includes/badges.php';
    $data = check_and_get_user_badges($userId);
    $result = [];
    foreach ($data['displayed'] as $b) {
        $result[] = [
            'label' => $b['name'],
            'class' => $b['key'],
            'icon'  => $b['icon'],
            'style' => $b['badge_style']
        ];
    }
    return $result;
}

function build_pagination_pages(int $page, int $totalPages): array
{
    if ($totalPages <= 8) {
        return range(1, $totalPages);
    }
    $pageMap = [1 => 1, 2 => 2, $totalPages - 1 => $totalPages - 1, $totalPages => $totalPages];
    for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++) {
        $pageMap[$i] = $i;
    }
    if ($page <= 3) {
        $pageMap[3] = 3;
    }
    if ($page >= $totalPages - 2) {
        $pageMap[$totalPages - 2] = $totalPages - 2;
    }
    ksort($pageMap);
    $items = [];
    $prev = 0;
    foreach ($pageMap as $p) {
        if ($prev > 0 && $p - $prev > 1) {
            $items[] = '...';
        }
        $items[] = $p;
        $prev = $p;
    }
    return $items;
}

require_once __DIR__ . '/../includes/search_helper.php';
require_once __DIR__ . '/../includes/community_standards.php';

