<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!is_admin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$csrfToken = (string) ($_POST['csrf_token'] ?? '');
if (!verify_csrf_token($csrfToken)) {
    redirect('admin/index.php?error=csrf');
}

$action = (string) ($_POST['action'] ?? 'update_status');
$returnUrl = (string) ($_POST['return_url'] ?? 'admin/index.php');

if ($action === 'delete_recipe') {
    $recipeId = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
    if ($recipeId) {
        $stmtFind = db()->prepare('SELECT image_url FROM recipes WHERE id = ?');
        $stmtFind->execute([$recipeId]);
        $row = $stmtFind->fetch();
        if ($row && !empty($row['image_url'])) {
            $filePath = __DIR__ . '/../' . $row['image_url'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $stmtDelete = db()->prepare('DELETE FROM recipes WHERE id = ?');
        $stmtDelete->execute([$recipeId]);
    }
    $targetUrl = $returnUrl ?: 'admin/manage-recipes.php';
    redirect($targetUrl . (str_contains($targetUrl, '?') ? '&' : '?') . 'success=deleted');
}

if ($action === 'delete_tip') {
    $tipId = filter_input(INPUT_POST, 'tip_id', FILTER_VALIDATE_INT);
    if ($tipId) {
        $stmtFind = db()->prepare('SELECT image_url FROM kitchen_tips WHERE id = ?');
        $stmtFind->execute([$tipId]);
        $row = $stmtFind->fetch();
        if ($row && !empty($row['image_url'])) {
            $filePath = __DIR__ . '/../' . $row['image_url'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $stmtDelete = db()->prepare('DELETE FROM kitchen_tips WHERE id = ?');
        $stmtDelete->execute([$tipId]);
    }
    $targetUrl = $returnUrl ?: 'admin/manage-tips.php';
    redirect($targetUrl . (str_contains($targetUrl, '?') ? '&' : '?') . 'success=deleted');
}

if ($action === 'update_tip_status') {
    $tipId = filter_input(INPUT_POST, 'tip_id', FILTER_VALIDATE_INT);
    $status = (string) ($_POST['status'] ?? '');
    if ($tipId && in_array($status, ['approved', 'rejected'], true)) {
        // Fetch tip details
        $stmtTip = db()->prepare('SELECT title, author_id FROM kitchen_tips WHERE id = ?');
        $stmtTip->execute([$tipId]);
        $tipInfo = $stmtTip->fetch();

        $statement = db()->prepare('UPDATE kitchen_tips SET status = ? WHERE id = ?');
        $statement->execute([$status, $tipId]);

        if ($tipInfo && !empty($tipInfo['author_id'])) {
            $authorId = (int) $tipInfo['author_id'];
            $adminActorId = (int) ($_SESSION['user_id'] ?? 1);
            $tipTitle = (string) $tipInfo['title'];

            if ($status === 'approved') {
                $content = 'đã phê duyệt bài viết kinh nghiệm «' . $tipTitle . '» của bạn.';
            } else {
                $content = 'đã từ chối duyệt bài viết kinh nghiệm «' . $tipTitle . '» của bạn.';
            }

            $notifStmt = db()->prepare('
                INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                VALUES (?, ?, "system", ?, ?, 0, NOW())
            ');
            $notifStmt->execute([$authorId, $adminActorId, $tipId, $content]);
        }
    }
    $targetUrl = $returnUrl ?: 'admin/manage-tips.php';
    redirect($targetUrl . (str_contains($targetUrl, '?') ? '&' : '?') . 'success=updated');
}

if ($action === 'delete_user') {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if ($userId && $userId !== (int) ($_SESSION['user_id'] ?? 0)) {
        // Xóa các file ảnh công thức của user
        $stmtRecs = db()->prepare('SELECT image_url FROM recipes WHERE author_id = ?');
        $stmtRecs->execute([$userId]);
        $userRecipes = $stmtRecs->fetchAll();
        foreach ($userRecipes as $ur) {
            if (!empty($ur['image_url'])) {
                $imgFile = __DIR__ . '/../' . $ur['image_url'];
                if (is_file($imgFile)) @unlink($imgFile);
            }
        }
        // Xóa công thức (sẽ cascade likes, comments của công thức)
        db()->prepare('DELETE FROM recipes WHERE author_id = ?')->execute([$userId]);
        db()->prepare('DELETE FROM comments WHERE user_id = ?')->execute([$userId]);
        db()->prepare('DELETE FROM recipe_likes WHERE user_id = ?')->execute([$userId]);
        db()->prepare('DELETE FROM saved_recipes WHERE user_id = ?')->execute([$userId]);

        $stmtDelete = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmtDelete->execute([$userId]);
    }
    $targetUrl = $returnUrl !== 'admin/index.php' ? $returnUrl : 'admin/manage-users.php';
    redirect($targetUrl . (str_contains($targetUrl, '?') ? '&' : '?') . 'success=user-deleted');
}

if ($action === 'toggle_role') {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $newRole = (string) ($_POST['new_role'] ?? 'user');
    if ($userId && $userId !== (int) ($_SESSION['user_id'] ?? 0) && in_array($newRole, ['user', 'admin'], true)) {
        $stmtUpdate = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmtUpdate->execute([$newRole, $userId]);
    }
    $targetUrl = $returnUrl !== 'admin/index.php' ? $returnUrl : 'admin/manage-users.php';
    redirect($targetUrl . (str_contains($targetUrl, '?') ? '&' : '?') . 'success=role-updated');
}

// 4. CATEGORY MANAGEMENT
if ($action === 'save_categories_bulk') {
    require_once __DIR__ . '/../includes/categories_helper.php';
    $categoriesJson = (string)($_POST['categories_data'] ?? '');
    $categoriesList = json_decode($categoriesJson, true);

    if (!is_array($categoriesList)) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
            exit;
        }
        redirect('admin/manage-categories.php?error=failed');
    }

    $db = db();
    $db->beginTransaction();

    try {
        foreach ($categoriesList as $index => $item) {
            $name = trim((string)($item['name'] ?? ''));
            $icon = trim((string)($item['icon'] ?? '🍳')) ?: '🍳';
            $order = $index + 1;
            $catId = isset($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : null;

            if ($name === '') {
                continue;
            }

            $slug = slugify_category($name);

            if ($catId && $catId > 0) {
                // Fetch old category name
                $stmtOld = $db->prepare('SELECT name FROM categories WHERE id = ?');
                $stmtOld->execute([$catId]);
                $oldName = $stmtOld->fetchColumn();

                // Update category
                $stmtUp = $db->prepare('UPDATE categories SET name = ?, slug = ?, icon = ?, display_order = ? WHERE id = ?');
                $stmtUp->execute([$name, $slug, $icon, $order, $catId]);

                // If name changed, update corresponding recipes
                if ($oldName && $oldName !== $name) {
                    $stmtRec = $db->prepare('UPDATE recipes SET category = ? WHERE category = ?');
                    $stmtRec->execute([$name, $oldName]);
                }
            } else {
                // Insert new category
                $stmtIns = $db->prepare('INSERT INTO categories (name, slug, icon, display_order) VALUES (?, ?, ?, ?)');
                $stmtIns->execute([$name, $slug, $icon, $order]);
            }
        }

        $db->commit();

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        redirect('admin/manage-categories.php?success=categories-saved');
    } catch (Throwable $e) {
        $db->rollBack();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
        redirect('admin/manage-categories.php?error=save-failed');
    }
}

if ($action === 'add_category') {
    require_once __DIR__ . '/../includes/categories_helper.php';
    $name = trim((string)($_POST['name'] ?? ''));
    $icon = trim((string)($_POST['icon'] ?? '🍳')) ?: '🍳';

    // Auto sequence: max display_order + 1
    $stmtMax = db()->query('SELECT COALESCE(MAX(display_order), 0) + 1 FROM categories');
    $order = (int)$stmtMax->fetchColumn();

    if ($name === '') {
        redirect('admin/manage-categories.php?error=missing-fields');
    }

    $slug = slugify_category($name);

    try {
        $stmt = db()->prepare('INSERT INTO categories (name, slug, icon, display_order) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $slug, $icon, $order]);
        redirect('admin/manage-categories.php?success=category-added');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
            redirect('admin/manage-categories.php?error=duplicate');
        }
        redirect('admin/manage-categories.php?error=failed');
    }
}

if ($action === 'edit_category') {
    require_once __DIR__ . '/../includes/categories_helper.php';
    $catId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $name = trim((string)($_POST['name'] ?? ''));
    $icon = trim((string)($_POST['icon'] ?? '🍳')) ?: '🍳';

    if (!$catId || $name === '') {
        redirect('admin/manage-categories.php?error=missing-fields');
    }

    $slug = slugify_category($name);

    $stmtOld = db()->prepare('SELECT name FROM categories WHERE id = ?');
    $stmtOld->execute([$catId]);
    $oldName = $stmtOld->fetchColumn();

    if (!$oldName) {
        redirect('admin/manage-categories.php?error=not-found');
    }

    try {
        $stmt = db()->prepare('UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $icon, $catId]);

        // If category name changed, update all existing recipes with that category
        if ($oldName !== $name) {
            $stmtUpdateRecipes = db()->prepare('UPDATE recipes SET category = ? WHERE category = ?');
            $stmtUpdateRecipes->execute([$name, $oldName]);
        }

        redirect('admin/manage-categories.php?success=category-updated');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
            redirect('admin/manage-categories.php?error=duplicate');
        }
        redirect('admin/manage-categories.php?error=failed');
    }
}

if ($action === 'delete_category') {
    $catId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    if (!$catId) {
        redirect('admin/manage-categories.php?error=not-found');
    }

    $stmtFind = db()->prepare('SELECT name FROM categories WHERE id = ?');
    $stmtFind->execute([$catId]);
    $catName = $stmtFind->fetchColumn();

    if ($catName) {
        // Delete all recipe images and recipes belonging to this category
        $stmtRecipes = db()->prepare('SELECT id, image_url FROM recipes WHERE category = ?');
        $stmtRecipes->execute([$catName]);
        $recipesToDelete = $stmtRecipes->fetchAll();
        foreach ($recipesToDelete as $r) {
            if (!empty($r['image_url'])) {
                $imgFile = __DIR__ . '/../' . $r['image_url'];
                if (is_file($imgFile)) {
                    @unlink($imgFile);
                }
            }
        }

        // Delete recipes (cascades likes, comments, bookmarks)
        $stmtDelRecipes = db()->prepare('DELETE FROM recipes WHERE category = ?');
        $stmtDelRecipes->execute([$catName]);

        // Delete the category itself
        $stmtDel = db()->prepare('DELETE FROM categories WHERE id = ?');
        $stmtDel->execute([$catId]);
    }

    redirect('admin/manage-categories.php?success=category-deleted');
}

$recipeId = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
$status = (string) ($_POST['status'] ?? '');
$targetUrl = $returnUrl ?: 'admin/manage-recipes.php';
if (!$recipeId || !in_array($status, ['approved', 'rejected'], true)) {
    redirect($targetUrl . (str_contains($targetUrl, '?') ? '&' : '?') . 'error=invalid-action');
}

// Fetch recipe details before update
$stmtRecipe = db()->prepare('SELECT title, author_id FROM recipes WHERE id = ?');
$stmtRecipe->execute([$recipeId]);
$recipeInfo = $stmtRecipe->fetch();

$statement = db()->prepare('UPDATE recipes SET status = ? WHERE id = ?');
$statement->execute([$status, $recipeId]);

if ($recipeInfo && !empty($recipeInfo['author_id'])) {
    $authorId = (int) $recipeInfo['author_id'];
    $adminActorId = (int) ($_SESSION['user_id'] ?? 1);
    $recipeTitle = (string) $recipeInfo['title'];

    if ($status === 'approved') {
        $content = 'đã phê duyệt và xuất bản công thức «' . $recipeTitle . '» của bạn.';
    } else {
        $content = 'đã từ chối duyệt công thức «' . $recipeTitle . '» của bạn.';
    }

    $notifStmt = db()->prepare('
        INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
        VALUES (?, ?, "system", ?, ?, 0, NOW())
    ');
    $notifStmt->execute([$authorId, $adminActorId, $recipeId, $content]);
}

redirect($targetUrl . (str_contains($targetUrl, '?') ? '&' : '?') . 'success=updated');
