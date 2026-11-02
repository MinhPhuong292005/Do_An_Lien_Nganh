<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Viết bài chia sẻ kinh nghiệm nấu nướng - Cookio';
require __DIR__ . '/../includes/header.php';
?>

<div class="recipe-detail-container" style="max-width: 860px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" style="margin-bottom: 1.5rem; font-size: 0.9rem; color: #6b7280; display: flex; gap: 0.5rem; align-items: center;">
        <a href="<?= BASE_URL ?>/index.php" style="color: #ea580c; text-decoration: none; font-weight: 600;">Trang chủ</a>
        <span>&rsaquo;</span>
        <a href="<?= BASE_URL ?>/views/kitchen-tips.php" style="color: #ea580c; text-decoration: none; font-weight: 600;">Kinh nghiệm hay</a>
        <span>&rsaquo;</span>
        <span style="font-weight: 700; color: #374151;">Viết bài chia sẻ kinh nghiệm</span>
    </nav>

    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.1rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem;">
            ✍️ Chia sẻ kinh nghiệm nấu nướng của bạn
        </h1>
        <p style="color: #6b7280; font-size: 1.05rem; line-height: 1.55;">
            Chia sẻ những mẹo nhỏ, kỹ thuật ướp gia vị, cách chọn nguyên liệu tươi ngon hoặc bí quyết giữ món ăn luôn tròn vị tới cộng đồng yêu bếp Cookio!
        </p>
    </div>

    <?php if (!is_logged_in()): ?>
        <div class="notice" style="background: #fff7ed; border-color: #ea580c; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 2rem;">
            <span>💡 Bạn đang viết bài với tư cách khách. Đăng nhập để ghi nhận danh hiệu và gắn tên tác giả của bạn vào bài viết!</span>
            <button type="button" class="button button-create" data-open-login style="padding: 0.4rem 1rem;">Đăng nhập ngay</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notice error" style="margin-bottom: 1.75rem;">
            <?= match ($_GET['error']) {
                'csrf' => '⚠️ Phiên làm việc đã hết hạn. Vui lòng thử lại.',
                'missing-fields' => '⚠️ Vui lòng điền đầy đủ tiêu đề, tóm tắt và nội dung kinh nghiệm.',
                'invalid-image' => '⚠️ Tệp tải lên không phải ảnh hợp lệ (chỉ chấp nhận JPG, PNG, WEBP dưới 5MB).',
                'upload-failed' => '⚠️ Không thể lưu ảnh lên máy chủ. Vui lòng thử lại.',
                default => '⚠️ Đã có lỗi xảy ra. Vui lòng kiểm tra lại thông tin.',
            } ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/actions/tip_action.php" enctype="multipart/form-data" class="recipe-detail-hero-card" style="background: #ffffff; border-radius: 16px; border: 1.5px solid #fed7aa; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <h2 style="font-size: 1.2rem; font-weight: 800; border-bottom: 1.5px solid #f1ebe1; padding-bottom: 0.75rem; margin-bottom: 1.25rem; color: #ea580c; display: flex; align-items: center; gap: 0.5rem;">
            <span>📋</span>
            <span>1. Thông tin bí quyết</span>
        </h2>

        <label style="display: block; margin-bottom: 1.25rem;">
            <span style="font-weight: 700; color: #374151; display: block; margin-bottom: 0.4rem;">
                Tiêu đề kinh nghiệm / bí quyết <span style="color:red">*</span>
            </span>
            <input type="text" name="title" required placeholder="Ví dụ: 5 Cách ướp gà nướng cực thơm ngon và đậm đà hương vị" 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 1.05rem; font-weight: 600; outline: none; box-sizing: border-box;">
        </label>

        <label style="display: block; margin-bottom: 1.25rem;">
            <span style="font-weight: 700; color: #374151; display: block; margin-bottom: 0.4rem;">
                Tóm tắt ngắn (1 - 2 câu) <span style="color:red">*</span>
            </span>
            <textarea name="summary" rows="2" required placeholder="Ví dụ: Bí quyết ướp sườn mềm tan không bị khô cứng bằng nước ép táo tươi và sữa đặc..." 
                      style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; box-sizing: border-box;"></textarea>
        </label>

        <label style="display: block; margin-bottom: 1.5rem;">
            <span style="font-weight: 700; color: #374151; display: block; margin-bottom: 0.4rem;">
                📸 Hình ảnh minh họa bí quyết
            </span>
            <input type="file" name="image" id="tipImageInput" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" onchange="validateTipImage(this)"
                   style="width: 100%; padding: 0.5rem 0; font-size: 0.95rem;">
            <span style="font-size: 0.85rem; color: #6b7280; display: block; margin-top: 0.35rem;">
                Chỉ chấp nhận tệp hình ảnh (.jpg, .jpeg, .png, .webp), dung lượng tối đa 5MB. Không chọn tệp nén hoặc file khác (.zip, .bat, .exe...).
            </span>
        </label>

        <h2 style="font-size: 1.2rem; font-weight: 800; border-bottom: 1.5px solid #f1ebe1; padding-bottom: 0.75rem; margin-bottom: 1.25rem; margin-top: 1.5rem; color: #ea580c; display: flex; align-items: center; gap: 0.5rem;">
            <span>📖</span>
            <span>2. Chi tiết các bước & Tỷ lệ gia vị chuẩn</span>
        </h2>

        <label style="display: block; margin-bottom: 1.5rem;">
            <span style="font-weight: 700; color: #374151; display: block; margin-bottom: 0.4rem;">
                Nội dung chi tiết từng bước <span style="color:red">*</span>
            </span>
            <textarea name="content" rows="8" required placeholder="Ví dụ:&#10;1. Sơ chế khử hôi: Gân bò bóp kỹ với muối hạt, gừng nướng đập dập và rượu trắng...&#10;2. Mẹo sốc nhiệt: Khi gân bò đang ninh sôi sùng sục, thả vào nồi 4-5 viên đá lạnh...&#10;3. Dùng dứa tươi: Thả 2 lát dứa tươi vào nồi ninh để enzyme làm mềm thịt tự nhiên..." 
                      style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; line-height: 1.6; outline: none; box-sizing: border-box;"></textarea>
        </label>

        <label style="display: block; margin-bottom: 1.75rem;">
            <span style="font-weight: 700; color: #374151; display: block; margin-bottom: 0.4rem;">
                🔗 Tên món hoặc đường dẫn áp dụng mẹo này (Không bắt buộc)
            </span>
            <input type="text" name="recipe_link" placeholder="Ví dụ: Bò kho gừng sả hoặc để trống" 
                   style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; box-sizing: border-box;">
        </label>

        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <button type="submit" class="button button-create" style="padding: 0.85rem 2.25rem; font-size: 1.05rem; font-weight: 800; border-radius: 9999px;">
                🚀 Xuất bản kinh nghiệm ngay
            </button>
            <a href="<?= BASE_URL ?>/views/kitchen-tips.php" class="link-button" style="color: #64748b; font-weight: 600; text-decoration: none; padding: 0.85rem 1.25rem;">
                Hủy bỏ
            </a>
        </div>
    </form>
</div>

<script>
function validateTipImage(input) {
    if (!input || !input.files || !input.files[0]) return true;
    const file = input.files[0];
    const allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    const parts = file.name.split('.');
    const ext = parts.length > 1 ? parts.pop().toLowerCase() : '';
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedExts.includes(ext) || (file.type && !allowedTypes.includes(file.type))) {
        alert('⚠️ Định dạng tệp không được hỗ trợ!\n\nVui lòng chỉ tải lên ảnh có đuôi .jpg, .jpeg, .png hoặc .webp (Tệp "' + file.name + '" không hợp lệ).');
        input.value = '';
        return false;
    }
    if (file.size > 5 * 1024 * 1024) {
        alert('⚠️ Dung lượng tệp vượt quá 5MB. Vui lòng tải lên ảnh có dung lượng nhỏ hơn.');
        input.value = '';
        return false;
    }
    return true;
}
</script>

<?php
require __DIR__ . '/../includes/modal-login.php';
require __DIR__ . '/../includes/footer.php';

