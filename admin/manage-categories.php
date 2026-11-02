<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/categories_helper.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!is_admin()) {
    redirect('index.php');
}

$categories = get_all_categories();
$totalCategories = count($categories);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục món ăn - Cookio Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <style>
        .cat-page-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #f0e6d8;
            padding: 1.75rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
        }
        .cat-table-container {
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
        }
        .cat-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .cat-table th {
            background: #fff7ed;
            padding: 1rem 1.15rem;
            font-size: 0.88rem;
            font-weight: 800;
            color: #9a3412;
            border-bottom: 2px solid #fed7aa;
        }
        .cat-table td {
            padding: 0.9rem 1.15rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.92rem;
            color: #334155;
            vertical-align: middle;
            transition: background 0.15s;
        }
        .cat-table tbody tr:hover {
            background: #fffbf7;
        }
        .cat-table tr.is-dragging {
            opacity: 0.4;
            background: #ffedd5 !important;
        }
        .cat-table tr.drag-over-top {
            border-top: 3px solid #ea580c;
        }
        .cat-table tr.drag-over-bottom {
            border-bottom: 3px solid #ea580c;
        }
        .drag-handle {
            cursor: grab;
            color: #94a3b8;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            user-select: none;
            transition: color 0.15s, background 0.15s;
        }
        .drag-handle:hover {
            color: #ea580c;
            background: #ffedd5;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .cat-badge-count {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
            transition: all 0.15s;
        }
        .cat-badge-count.has-recipes {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #dbeafe;
        }
        .cat-badge-count.has-recipes:hover {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-action-delete {
            background: #ffffff;
            color: #dc2626;
            border: 1.5px solid #fecaca;
            padding: 0.38rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s;
        }
        .btn-action-delete:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2);
        }
        .btn-edit-table {
            background: #ffffff;
            border: 1.5px solid #fed7aa;
            color: #ea580c;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.45rem 1rem;
            border-radius: 9999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 6px rgba(234, 88, 12, 0.08);
            transition: all 0.2s;
        }
        .btn-edit-table:hover {
            background: #ea580c;
            color: #ffffff;
            border-color: #ea580c;
        }
        .btn-save-table {
            background: #16a34a;
            border: 1.5px solid #16a34a;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.45rem 1.15rem;
            border-radius: 9999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.25);
            transition: all 0.2s;
        }
        .btn-save-table:hover {
            background: #15803d;
            border-color: #15803d;
        }
        .btn-cancel-table {
            background: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            color: #475569;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.45rem 0.9rem;
            border-radius: 9999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s;
        }
        .btn-cancel-table:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .btn-add-row {
            background: #fff7ed;
            border: 1.5px dashed #ea580c;
            color: #ea580c;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.45rem 1rem;
            border-radius: 9999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.15s;
        }
        .btn-add-row:hover {
            background: #ea580c;
            color: #ffffff;
        }
        .cat-edit-input {
            width: 100%;
            padding: 0.45rem 0.65rem;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.92rem;
            font-family: inherit;
            color: #1e293b;
            background: #ffffff;
            box-sizing: border-box;
            outline: none;
        }
        .cat-edit-input:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.15);
        }
        .cat-edit-icon {
            width: 48px;
            text-align: center;
            font-size: 1.3rem;
            padding: 0.35rem 0.2rem;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            background: #ffffff;
            cursor: pointer;
        }
        .cat-edit-icon:focus {
            border-color: #ea580c;
        }
        .edit-mode-bar {
            display: none;
            align-items: center;
            justify-content: space-between;
            background: #fff7ed;
            border: 1.5px solid #fed7aa;
            border-radius: 10px;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        /* Modal Backdrop & Dialog */
        .cookio-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            box-sizing: border-box;
        }
        .cookio-modal-dialog {
            background: #ffffff;
            border-radius: 16px;
            max-width: 480px;
            width: 100%;
            padding: 1.75rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            border: 1px solid #e2e8f0;
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="admin-page">
<?php require __DIR__ . '/../includes/admin-sidebar.php'; ?>

<main class="admin-content">
    <!-- Top Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.65rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                <span>📁</span> Quản lý danh mục món ăn
            </h1>
            <p style="margin: 0.35rem 0 0; color: #64748b; font-size: 0.92rem;">
                Quản lý, điều chỉnh tên, biểu tượng và kéo thả thứ tự hiển thị danh mục trên toàn hệ thống Cookio (Tổng cộng <?= $totalCategories ?> danh mục).
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/index.php" target="_blank" class="button button-outline" style="font-size: 0.85rem; padding: 0.45rem 0.95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                <span>🌐</span> Xem trên web
            </a>
            <!-- Nút nhỏ Chỉnh sửa bảng ở góc trên bên phải -->
            <button type="button" id="btnToggleEdit" class="btn-edit-table" onclick="toggleEditMode(true)" title="Chỉnh sửa icon, tên và kéo thả sắp xếp danh mục">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <span>Chỉnh sửa bảng</span>
            </button>
        </div>
    </div>

    <!-- Flash Notices -->
    <?php if (isset($_GET['success'])): ?>
        <div class="notice success" style="margin-bottom: 1.5rem;">
            <?= match ($_GET['success']) {
                'categories-saved' => '🎉 Đã lưu thay đổi thông tin và thứ tự bảng danh mục thành công!',
                'category-added' => '🎉 Đã thêm danh mục mới thành công!',
                'category-updated' => '🎉 Đã cập nhật danh mục thành công!',
                'category-deleted' => '🗑️ Đã xóa danh mục và toàn bộ dữ liệu công thức liên quan thành công!',
                default => 'Thao tác hoàn tất thành công.',
            } ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice error" style="margin-bottom: 1.5rem;">
            <?= match ($_GET['error']) {
                'missing-fields' => '⚠️ Vui lòng nhập đầy đủ tên danh mục.',
                'duplicate' => '⚠️ Tên danh mục đã tồn tại trong hệ thống. Vui lòng chọn tên khác.',
                'not-found' => '⚠️ Danh mục không tồn tại.',
                'save-failed' => '⚠️ Đã có lỗi xảy ra trong quá trình lưu dữ liệu. Vui lòng thử lại.',
                default => 'Đã có lỗi xảy ra. Vui lòng thử lại.',
            } ?>
        </div>
    <?php endif; ?>

    <!-- Main Table Card -->
    <div class="cat-page-card">
        <!-- Edit Mode Toolbar (Hiển thị khi bật Chỉnh sửa bảng) -->
        <div id="editModeToolbar" class="edit-mode-bar">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span style="font-size: 1.1rem;">✏️</span>
                <div>
                    <strong style="color: #9a3412; font-size: 0.92rem;">Đang trong chế độ Chỉnh sửa bảng:</strong>
                    <span style="color: #64748b; font-size: 0.85rem; margin-left: 0.35rem;">
                        Kéo thả biểu tượng <strong>☰</strong> để xếp lại thứ tự • Nhập tên hoặc icon để cập nhật • Thêm danh mục mới sẽ tự nhảy số STT tiếp theo.
                    </span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <button type="button" class="btn-add-row" onclick="addNewCategoryRow()">
                    <span>➕</span> Thêm danh mục mới
                </button>
                <button type="button" class="btn-save-table" onclick="saveCategoriesChanges()">
                    <span>💾</span> Lưu thay đổi
                </button>
                <button type="button" class="btn-cancel-table" onclick="toggleEditMode(false)">
                    <span>✕</span> Hủy
                </button>
            </div>
        </div>

        <div class="cat-table-container">
            <table class="cat-table" id="categoriesTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center; display: none;" class="col-drag"></th>
                        <th style="width: 60px; text-align: center;">STT</th>
                        <th style="width: 80px; text-align: center;">Icon</th>
                        <th>Tên danh mục</th>
                        <th style="width: 140px; text-align: center;">Số lượng món</th>
                        <th style="width: 110px; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <?php if (empty($categories)): ?>
                        <tr id="emptyRow">
                            <td colspan="6" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                                Chưa có danh mục nào. Hãy bấm "Chỉnh sửa bảng" để tạo danh mục đầu tiên!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $index => $c): ?>
                            <tr class="cat-row" data-id="<?= (int)$c['id'] ?>" data-name="<?= e($c['name']) ?>" data-count="<?= (int)$c['recipe_count'] ?>" draggable="false">
                                <!-- Drag Handle Cell (Chỉ hiển thị khi Chỉnh sửa) -->
                                <td style="text-align: center; width: 45px; display: none;" class="col-drag">
                                    <span class="drag-handle" title="Kéo thả để sắp xếp thứ tự">☰</span>
                                </td>
                                
                                <!-- STT -->
                                <td style="text-align: center; font-weight: 700; color: #64748b;" class="col-stt">
                                    <?= $index + 1 ?>
                                </td>

                                <!-- Icon -->
                                <td style="text-align: center;" class="col-icon">
                                    <span class="view-icon" style="font-size: 1.45rem;"><?= e($c['icon'] ?: '🍳') ?></span>
                                    <input type="text" class="cat-edit-icon edit-icon" value="<?= e($c['icon'] ?: '🍳') ?>" style="display: none;" title="Nhập icon emoji">
                                </td>

                                <!-- Tên danh mục -->
                                <td class="col-name">
                                    <div class="view-name" style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                                        <?= e($c['name']) ?>
                                    </div>
                                    <input type="text" class="cat-edit-input edit-name" value="<?= e($c['name']) ?>" required style="display: none;" placeholder="Nhập tên danh mục...">
                                </td>

                                <!-- Món ăn (Khớp chính xác số lượng công thức thực tế) -->
                                <td style="text-align: center;" class="col-count">
                                    <a href="<?= BASE_URL ?>/index.php?cat=<?= urlencode($c['name']) ?>" target="_blank" 
                                       class="cat-badge-count <?= (int)$c['recipe_count'] > 0 ? 'has-recipes' : '' ?>" 
                                       title="Bấm để xem danh sách món ăn thuộc danh mục này trên web">
                                        <?= number_format((int)$c['recipe_count']) ?> món
                                    </a>
                                </td>

                                <!-- Thao tác: CHỈ HIỂN THỊ NÚT XÓA -->
                                <td style="text-align: center;" class="col-action">
                                    <button type="button" class="btn-action-delete" onclick="openDeleteModal(<?= (int)$c['id'] ?>, '<?= e(addslashes($c['name'])) ?>', <?= (int)$c['recipe_count'] ?>)" title="Xóa danh mục">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        <span>Xóa</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Delete Confirmation Modal Dialog -->
<div id="deleteModalBackdrop" class="cookio-modal-backdrop">
    <div class="cookio-modal-dialog">
        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                ⚠️
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e293b;">Xác nhận xóa danh mục</h3>
                <span style="font-size: 0.85rem; color: #64748b;">Hành động này sẽ xóa dữ liệu vĩnh viễn</span>
            </div>
        </div>

        <p style="color: #334155; font-size: 0.95rem; line-height: 1.5; margin: 0 0 1rem;">
            Bạn có chắc chắn muốn xóa danh mục <strong id="modalCatName" style="color: #ea580c;"></strong> không?
        </p>

        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem; color: #991b1b; font-size: 0.88rem; line-height: 1.5;">
            <div style="font-weight: 800; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem; color: #b91c1c;">
                <span>🚨 CẢNH BÁO QUAN TRỌNG:</span>
            </div>
            Nếu xóa danh mục này, <strong id="modalCatCount">0</strong> công thức nấu ăn có trong danh mục cùng toàn bộ dữ liệu bình luận, yêu thích và đánh giá sẽ bị <strong>XÓA HOÀN TOÀN</strong> khỏi hệ thống và không thể khôi phục!
        </div>

        <form id="deleteCatForm" method="post" action="<?= BASE_URL ?>/actions/admin_action.php" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="category_id" id="modalCatId" value="">
            <input type="hidden" name="return_url" value="admin/manage-categories.php">

            <button type="button" onclick="closeDeleteModal()" style="padding: 0.55rem 1.15rem; background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #475569; cursor: pointer;">
                Hủy bỏ
            </button>
            <button type="submit" style="padding: 0.55rem 1.25rem; background: #dc2626; border: 1.5px solid #dc2626; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #ffffff; cursor: pointer; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);">
                Xác nhận xóa vĩnh viễn
            </button>
        </form>
    </div>
</div>

<script>
let isEditMode = false;
let draggedRow = null;

// Bật / Tắt chế độ Chỉnh sửa bảng
function toggleEditMode(enable) {
    isEditMode = enable;
    const toolbar = document.getElementById('editModeToolbar');
    const btnToggle = document.getElementById('btnToggleEdit');
    const dragCols = document.querySelectorAll('.col-drag');
    const viewIcons = document.querySelectorAll('.view-icon');
    const editIcons = document.querySelectorAll('.edit-icon');
    const viewNames = document.querySelectorAll('.view-name');
    const editNames = document.querySelectorAll('.edit-name');
    const rows = document.querySelectorAll('#categoriesTableBody tr.cat-row');

    if (enable) {
        toolbar.style.display = 'flex';
        btnToggle.style.display = 'none';
        dragCols.forEach(col => col.style.display = 'table-cell');
        viewIcons.forEach(i => i.style.display = 'none');
        editIcons.forEach(i => i.style.display = 'inline-block');
        viewNames.forEach(n => n.style.display = 'none');
        editNames.forEach(n => n.style.display = 'block');

        // Bật tính năng kéo thả trên các hàng
        rows.forEach(row => {
            row.setAttribute('draggable', 'true');
            initRowDragEvents(row);
        });
    } else {
        // Hủy bỏ / Tắt chỉnh sửa: tải lại trang để phục hồi dữ liệu ban đầu
        window.location.href = '<?= BASE_URL ?>/admin/manage-categories.php';
    }
}

// Thêm hàng danh mục mới (Tự động tăng STT tiếp theo)
function addNewCategoryRow() {
    const tbody = document.getElementById('categoriesTableBody');
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.remove();

    const currentRows = tbody.querySelectorAll('tr.cat-row');
    const nextStt = currentRows.length + 1;

    const tr = document.createElement('tr');
    tr.className = 'cat-row is-new-row';
    tr.setAttribute('data-id', 'new');
    tr.setAttribute('draggable', 'true');

    tr.innerHTML = `
        <td style="text-align: center; width: 45px; display: table-cell;" class="col-drag">
            <span class="drag-handle" title="Kéo thả để sắp xếp thứ tự">☰</span>
        </td>
        <td style="text-align: center; font-weight: 700; color: #ea580c;" class="col-stt">
            ${nextStt}
        </td>
        <td style="text-align: center;" class="col-icon">
            <input type="text" class="cat-edit-icon edit-icon" value="🍽️" style="display: inline-block;" title="Nhập icon emoji">
        </td>
        <td class="col-name">
            <input type="text" class="cat-edit-input edit-name" value="" required style="display: block;" placeholder="Nhập tên danh mục mới (Ví dụ: Món ăn dịp đặc biệt)...">
        </td>
        <td style="text-align: center;" class="col-count">
            <span class="cat-badge-count">0 món</span>
        </td>
        <td style="text-align: center;" class="col-action">
            <button type="button" class="btn-action-delete" onclick="removeNewRow(this)" title="Hủy hàng này">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <span>Hủy</span>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    initRowDragEvents(tr);

    // Tự động focus vào ô nhập tên của danh mục mới
    const newNameInput = tr.querySelector('.edit-name');
    if (newNameInput) {
        newNameInput.focus();
    }
}

// Hủy hàng mới thêm
function removeNewRow(btn) {
    const tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        updateRowNumbers();
    }
}

// Cập nhật lại số thứ tự STT sau khi kéo thả hoặc xóa
function updateRowNumbers() {
    const rows = document.querySelectorAll('#categoriesTableBody tr.cat-row');
    rows.forEach((row, idx) => {
        const sttCell = row.querySelector('.col-stt');
        if (sttCell) {
            sttCell.textContent = (idx + 1);
        }
    });
}

// Xử lý kéo thả hàng (Drag and Drop HTML5)
function initRowDragEvents(row) {
    row.addEventListener('dragstart', (e) => {
        draggedRow = row;
        e.dataTransfer.effectAllowed = 'move';
        row.classList.add('is-dragging');
    });

    row.addEventListener('dragend', () => {
        if (draggedRow) {
            draggedRow.classList.remove('is-dragging');
            draggedRow = null;
        }
        document.querySelectorAll('#categoriesTableBody tr.cat-row').forEach(r => {
            r.classList.remove('drag-over-top', 'drag-over-bottom');
        });
        updateRowNumbers();
    });

    row.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (!draggedRow || draggedRow === row) return;

        const bounding = row.getBoundingClientRect();
        const offset = e.clientY - bounding.top;
        if (offset < bounding.height / 2) {
            row.classList.add('drag-over-top');
            row.classList.remove('drag-over-bottom');
        } else {
            row.classList.add('drag-over-bottom');
            row.classList.remove('drag-over-top');
        }
    });

    row.addEventListener('dragleave', () => {
        row.classList.remove('drag-over-top', 'drag-over-bottom');
    });

    row.addEventListener('drop', (e) => {
        e.preventDefault();
        if (!draggedRow || draggedRow === row) return;

        const bounding = row.getBoundingClientRect();
        const offset = e.clientY - bounding.top;
        const tbody = document.getElementById('categoriesTableBody');

        if (offset < bounding.height / 2) {
            tbody.insertBefore(draggedRow, row);
        } else {
            tbody.insertBefore(draggedRow, row.nextSibling);
        }

        row.classList.remove('drag-over-top', 'drag-over-bottom');
        updateRowNumbers();
    });
}

// Lưu tất cả thay đổi danh mục (thứ tự kéo thả, icon, tên)
function saveCategoriesChanges() {
    const rows = document.querySelectorAll('#categoriesTableBody tr.cat-row');
    const categoriesData = [];

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const idVal = row.getAttribute('data-id');
        const iconInput = row.querySelector('.edit-icon');
        const nameInput = row.querySelector('.edit-name');

        const name = nameInput ? nameInput.value.trim() : '';
        const icon = iconInput ? iconInput.value.trim() : '🍽️';

        if (!name) {
            alert(`Hàng thứ ${i + 1} chưa có tên danh mục. Vui lòng nhập đầy đủ tên!`);
            if (nameInput) nameInput.focus();
            return;
        }

        categoriesData.push({
            id: idVal,
            name: name,
            icon: icon || '🍽️'
        });
    }

    if (categoriesData.length === 0) {
        alert('Danh sách danh mục không được để trống!');
        return;
    }

    // Tạo form ẩn để gửi dữ liệu lên actions/admin_action.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/actions/admin_action.php';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= csrf_token() ?>';
    form.appendChild(csrfInput);

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'save_categories_bulk';
    form.appendChild(actionInput);

    const dataInput = document.createElement('input');
    dataInput.type = 'hidden';
    dataInput.name = 'categories_data';
    dataInput.value = JSON.stringify(categoriesData);
    form.appendChild(dataInput);

    document.body.appendChild(form);
    form.submit();
}

// Mở Modal xác nhận xóa danh mục
function openDeleteModal(catId, catName, recipeCount) {
    document.getElementById('modalCatId').value = catId;
    document.getElementById('modalCatName').textContent = `"${catName}"`;
    document.getElementById('modalCatCount').textContent = recipeCount;
    document.getElementById('deleteModalBackdrop').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModalBackdrop').style.display = 'none';
}

// Đóng modal khi bấm ra ngoài hộp thoại
window.addEventListener('click', (e) => {
    const backdrop = document.getElementById('deleteModalBackdrop');
    if (e.target === backdrop) {
        closeDeleteModal();
    }
});
</script>
</body>
</html>
