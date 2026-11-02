<?php

declare(strict_types=1);

$pageTitle = 'Kinh Nghiệm Hay & Bí Quyết Nấu Ngon - Cookio';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// Fetch all approved tips from database (only approved tips are public)
$allDbTips = [];
try {
    $stmtUserTips = db()->query("
        SELECT id, author_id, author_name, title, category, summary, content, image_url, time, difficulty, recipe_link, is_featured, views_count, created_at
        FROM kitchen_tips
        WHERE status = 'approved'
        ORDER BY is_featured DESC, id ASC
    ");
    $allDbTips = $stmtUserTips->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log("Error loading kitchen tips: " . $e->getMessage());
}

$totalApprovedTips = count($allDbTips);

// Featured tips (Image 2) - Up to 8 featured tips
$featuredTips = array_slice($allDbTips, 0, 8);

// List of all tips (Images 3 & 5) - Full list
$allTipsList = $allDbTips;
?>

<main class="container py-8" style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb mb-6" style="font-size: 0.9rem; color: #6b7280; display: flex; gap: 0.5rem; align-items: center;">
        <a href="<?= BASE_URL ?>/index.php" style="color: #ea580c; text-decoration: none; font-weight: 600;">Trang chủ</a>
        <span>&rsaquo;</span>
        <span style="font-weight: 700; color: #374151;">Kinh nghiệm hay</span>
    </nav>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'tip-published'): ?>
        <div class="notice success" style="margin-bottom: 2rem; background: #ecfdf5; border-color: #10b981; color: #065f46; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.4rem;">🎉</span>
            <span>Chúc mừng bạn! Bài viết chia sẻ kinh nghiệm nấu nướng đã được xuất bản thành công lên Cookio!</span>
        </div>
    <?php elseif (isset($_GET['success']) && $_GET['success'] === 'tip-pending'): ?>
        <div class="notice success" style="margin-bottom: 2rem; background: #fefce8; border: 1.5px solid #f59e0b; color: #854d0e; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.4rem;">⏳</span>
            <span>Bài viết chia sẻ kinh nghiệm của bạn đã được gửi thành công và đang chờ ban quản trị Cookio duyệt! Bạn sẽ nhận được thông báo khi bài viết được duyệt.</span>
        </div>
    <?php endif; ?>

    <!-- =====================================================================
         IMAGE 1: TOP BROWN BANNER & INGREDIENT EXPLORER SLIDER
         ===================================================================== -->
    <section class="tips-hero-banner" style="background: #3e1713; border-radius: 16px; padding: 2rem 2.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; margin-bottom: 3.5rem; box-shadow: 0 10px 25px rgba(62,23,19,0.15); overflow: hidden; position: relative;">
        <div style="z-index: 2; max-width: 750px;">
            <h1 style="font-size: 1.85rem; font-weight: 800; color: #ffffff; margin: 0 0 0.65rem; line-height: 1.35; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span>Tổng hợp mẹo hay cuộc sống bạn nhất định không nên bỏ lỡ</span>
            </h1>
            <p style="font-size: 1.05rem; font-weight: 600; color: #f59e0b; margin: 0;">
                Chuyên mục này có <?= number_format($totalApprovedTips) ?> bài.
            </p>
        </div>
        <div style="z-index: 2; flex-shrink: 0; text-align: right;">
            <!-- Elegant culinary badge replacing cartoon chef -->
            <div style="width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, rgba(245,158,11,0.25) 0%, rgba(245,158,11,0.05) 100%); border: 2px solid rgba(245,158,11,0.4); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(0,0,0,0.25);">
                <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                    <line x1="6" y1="17" x2="18" y2="17"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         CHIA SẺ KINH NGHIỆM NẤU NƯỚNG (Thay thế Khám phá nguyên liệu theo yêu cầu)
         ===================================================================== -->
    <section class="share-tips-cta-section mb-12" style="margin-bottom: 3.5rem; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 1.5px solid #fed7aa; border-radius: 20px; padding: 2.25rem 2rem; text-align: center; box-shadow: 0 4px 18px rgba(234, 88, 12, 0.08);">
        <div style="max-width: 720px; margin: 0 auto;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 50%; background: #ea580c; color: #fff; font-size: 1.6rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);">
                ✍️
            </div>
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #3e1713; margin: 0 0 0.65rem; line-height: 1.3;">
                Bạn có muốn chia sẻ kinh nghiệm nấu nướng của mình không?
            </h2>
            <p style="font-size: 1rem; color: #78350f; margin: 0 0 1.75rem; line-height: 1.55;">
                Mỗi mẹo nhỏ, tỷ lệ ướp gia vị hay bí quyết giữ rau xanh, thịt mềm của bạn đều là món quà quý giá giúp cộng đồng yêu bếp nấu ăn dễ dàng và ngon miệng hơn mỗi ngày!
            </p>
            <div>
                <a href="<?= BASE_URL ?>/views/create-tip.php" class="button button-create" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.85rem 2.25rem; font-size: 1.05rem; font-weight: 800; border-radius: 9999px; text-decoration: none; box-shadow: 0 6px 18px rgba(234, 88, 12, 0.3); transition: transform 0.2s, box-shadow 0.2s;">
                    <span>✍️</span>
                    <span>Viết bài chia sẻ kinh nghiệm</span>
                </a>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         IMAGE 2: CÁC KINH NGHIỆM HAY (8 CARDS, 2 ROWS OF 4)
         ===================================================================== -->
    <section class="featured-tips-section mb-12" style="margin-bottom: 4rem;">
        <div style="margin-bottom: 1.75rem;">
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #3e1713; margin: 0 0 0.35rem;">
                Các kinh nghiệm hay
            </h2>
            <p style="font-size: 0.98rem; color: #78350f; margin: 0;">
                Những mẹo hay và bí quyết nấu ăn hữu ích được cộng đồng yêu bếp Cookio yêu thích nhất
            </p>
        </div>

        <div class="featured-tips-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
            <?php foreach ($featuredTips as $t): ?>
                <article class="featured-tip-card" style="background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #f3f4f6; display: flex; flex-direction: column; transition: transform 0.25s, box-shadow 0.25s; cursor: pointer;" onclick="openTipModal(<?= (int)$t['id'] ?>)">
                    <!-- Card Thumbnail with Badge -->
                    <div style="position: relative; width: 100%; aspect-ratio: 1/1; overflow: hidden; background: #f3f4f6;">
                        <img src="<?= BASE_URL ?>/<?= e($t['image_url'] ?: 'assets/images/real_dishes/hero-dish-1.jpg') ?>" alt="<?= e($t['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" loading="lazy">
                        <span style="position: absolute; top: 0.75rem; left: 0.75rem; background: rgba(30, 27, 27, 0.78); backdrop-filter: blur(4px); color: #ffffff; font-size: 0.76rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 9999px;">
                            <?= e($t['category'] ?: 'Kinh nghiệm hay') ?>
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 1.15rem; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: #3e1713; margin: 0 0 0.75rem; line-height: 1.38; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= e($t['title']) ?>
                        </h3>
                        <?php if (!empty($t['time']) || !empty($t['difficulty'])): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: #9a3412; font-weight: 600;">
                            <?php if (!empty($t['time'])): ?>
                                <span>⏱ <?= e($t['time']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($t['time']) && !empty($t['difficulty'])): ?>
                                <span style="color: #cbd5e1;">|</span>
                            <?php endif; ?>
                            <?php if (!empty($t['difficulty'])): ?>
                                <span><?= e($t['difficulty']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- =====================================================================
         IMAGE 3: DANH SÁCH TẤT CẢ CÔNG THỨC (HORIZONTAL CARDS)
         ===================================================================== -->
    <section class="all-tips-list-section mb-12" style="margin-bottom: 4rem;">
        <div style="text-align: center; margin-bottom: 2.25rem;">
            <h2 id="allTipsListHeading" style="font-size: 1.95rem; font-weight: 800; color: #3e1713; margin: 0 0 0.35rem;">
                Danh sách tất cả công thức
            </h2>
            <p style="font-size: 1rem; color: #78350f; margin: 0;">
                Tổng hợp công thức kinh nghiệm hay đầy đủ, dễ làm tại nhà (<?= number_format($totalApprovedTips) ?> bí quyết)
            </p>
        </div>

        <div class="horizontal-tips-list" id="horizontalTipsList" style="display: flex; flex-direction: column; gap: 1.25rem; max-width: 900px; margin: 0 auto;">
            <?php foreach ($allTipsList as $idx => $item): ?>
                <?php $isExtra = ($idx >= 6); ?>
                <article class="horizontal-tip-card <?= $isExtra ? 'tip-item-extra' : '' ?>" style="display: <?= $isExtra ? 'none' : 'flex' ?>; gap: 1.5rem; align-items: center; background: #ffffff; border-radius: 16px; padding: 1rem; border: 1px solid #f3f4f6; box-shadow: 0 3px 12px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onclick="openTipModal(<?= (int)$item['id'] ?>)">
                    <!-- Left Photo -->
                    <div style="position: relative; width: 190px; height: 130px; border-radius: 12px; overflow: hidden; flex-shrink: 0; background: #f3f4f6;">
                        <img src="<?= BASE_URL ?>/<?= e($item['image_url'] ?: 'assets/images/real_dishes/hero-dish-1.jpg') ?>" alt="<?= e($item['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                        <span style="position: absolute; top: 0.5rem; left: 0.5rem; background: rgba(30, 27, 27, 0.78); backdrop-filter: blur(4px); color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 9999px;">
                            <?= e($item['category'] ?: 'Kinh nghiệm hay') ?>
                        </span>
                    </div>

                    <!-- Right Content -->
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #3e1713; margin: 0 0 0.5rem; line-height: 1.35;">
                            <?= e($item['title']) ?>
                        </h3>
                        <p style="font-size: 0.92rem; color: #6b7280; margin: 0 0 0.75rem; line-height: 1.45;">
                            <?= e($item['summary']) ?>
                        </p>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: #9a3412; font-weight: 600;">
                            <?php if (!empty($item['time'])): ?>
                                <span>⏱ <?= e($item['time']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['time']) && !empty($item['difficulty'])): ?>
                                <span style="color: #cbd5e1;">|</span>
                            <?php endif; ?>
                            <?php if (!empty($item['difficulty'])): ?>
                                <span><?= e($item['difficulty']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Điều hướng: Nút Xem thêm (Khớp yêu cầu Ảnh 5) -->
        <div style="text-align: center; margin-top: 2.5rem;">
            <button type="button" id="btnToggleAllTips" class="btn-load-more-tips" onclick="toggleMoreTips()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.85rem 2.2rem; background: #ea580c; color: #ffffff; border: none; border-radius: 9999px; font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 6px 18px rgba(234, 88, 12, 0.25); transition: all 0.25s ease;">
                <span id="btnTipsText">Xem thêm công thức & bí quyết</span>
                <svg id="btnTipsArrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s ease;">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
        </div>
    </section>
</main>

<!-- Interactive Tip Detail Modal -->
<div id="tipDetailModal" class="cookio-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); z-index: 9999; backdrop-filter: blur(5px); justify-content: center; align-items: center; padding: 1.5rem;">
    <div class="cookio-modal-content" style="background: #ffffff; border-radius: 1.25rem; max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 2rem; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.25);">
        <button type="button" onclick="closeTipModal()" style="position: absolute; top: 1.25rem; right: 1.25rem; background: #f3f4f6; border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #4b5563;">&times;</button>
        
        <span style="display: inline-block; background: #fff7ed; color: #c2410c; font-size: 0.8rem; font-weight: 800; padding: 0.3rem 0.75rem; border-radius: 9999px; margin-bottom: 0.75rem; border: 1px solid #fed7aa;">
            💡 KINH NGHIỆM ĐẦU BẾP COOKIO
        </span>

        <h2 id="modalTipTitle" style="font-size: 1.45rem; font-weight: 800; color: #3e1713; margin: 0 0 0.85rem; line-height: 1.35;"></h2>

        <div id="modalTipImageWrap" style="width: 100%; height: 260px; border-radius: 14px; overflow: hidden; margin-bottom: 1.25rem; background: #f3f4f6;">
            <img id="modalTipImg" src="" alt="" style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <p id="modalTipSummary" style="font-size: 0.95rem; color: #6b7280; font-style: italic; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px dashed #e5e7eb;"></p>

        <h4 style="font-size: 1.05rem; font-weight: 800; color: #1e293b; margin: 0 0 0.65rem;">
            📌 Các bước & Tỷ lệ gia vị chuẩn:
        </h4>
        <div id="modalTipContent" style="font-size: 0.92rem; line-height: 1.7; color: #374151; white-space: pre-line; background: #f8fafc; padding: 1.25rem; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;"></div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; flex-wrap: wrap;">
            <a id="modalTipRecipeLink" href="#" class="button button-create" style="padding: 0.65rem 1.25rem; font-weight: 700; text-decoration: none;">
                👩‍🍳 Xem món áp dụng mẹo này
            </a>
            <button type="button" class="button button-outline" onclick="closeTipModal()" style="padding: 0.65rem 1.25rem;">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
// Lookup table for all tips in modal
const allTipsData = <?= json_encode($allDbTips, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const tipsMap = new Map();

allTipsData.forEach(t => {
    tipsMap.set(parseInt(t.id), {
        id: parseInt(t.id),
        title: t.title,
        image: t.image_url || 'assets/images/real_dishes/hero-dish-1.jpg',
        time: t.time || '20p',
        difficulty: t.difficulty || 'Dễ',
        summary: t.summary || '',
        content: t.content || '',
        recipe_link: t.recipe_link || ('<?= BASE_URL ?>/index.php?q=' + encodeURIComponent(t.title.split(' ').slice(0, 3).join(' ')))
    });
});

function openTipModal(id) {
    const tip = tipsMap.get(parseInt(id));
    if (!tip) return;

    document.getElementById('modalTipTitle').textContent = tip.title;
    document.getElementById('modalTipImg').src = '<?= BASE_URL ?>/' + tip.image;
    document.getElementById('modalTipSummary').textContent = tip.summary;
    document.getElementById('modalTipContent').textContent = tip.content;
    document.getElementById('modalTipRecipeLink').href = tip.recipe_link || '#';

    const modal = document.getElementById('tipDetailModal');
    modal.style.display = 'flex';
}

function closeTipModal() {
    document.getElementById('tipDetailModal').style.display = 'none';
}

// Close on backdrop click
document.getElementById('tipDetailModal').addEventListener('click', function(e) {
    if (e.target === this) closeTipModal();
});

// Auto-open modal if ?tip=ID in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tipParam = urlParams.get('tip');
    if (tipParam && tipsMap.has(parseInt(tipParam))) {
        openTipModal(parseInt(tipParam));
    }
});

// Điều hướng Xem thêm / Thu gọn (Image 5 Navigation)
function toggleMoreTips() {
    const extraItems = document.querySelectorAll('.tip-item-extra');
    const btnText = document.getElementById('btnTipsText');
    const btnArrow = document.getElementById('btnTipsArrow');
    if (!extraItems.length) return;

    const isHidden = (extraItems[0].style.display === 'none' || getComputedStyle(extraItems[0]).display === 'none');

    extraItems.forEach(item => {
        item.style.display = isHidden ? 'flex' : 'none';
        if (isHidden) {
            item.style.animation = 'fadeInTip 0.35s ease forwards';
        }
    });

    if (isHidden) {
        btnText.textContent = 'Thu gọn danh sách công thức';
        btnArrow.style.transform = 'rotate(180deg)';
    } else {
        btnText.textContent = 'Xem thêm công thức & bí quyết';
        btnArrow.style.transform = 'rotate(0deg)';
        document.getElementById('allTipsListHeading')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Ingredient slider controls
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('ingScrollTrack');
    const prev = document.getElementById('btnIngPrev');
    const next = document.getElementById('btnIngNext');

    if (track && prev && next) {
        prev.addEventListener('click', () => track.scrollBy({ left: -220, behavior: 'smooth' }));
        next.addEventListener('click', () => track.scrollBy({ left: 220, behavior: 'smooth' }));
    }
});
</script>

<style>
.featured-tip-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px rgba(62,23,19,0.12);
}
.featured-tip-card:hover img {
    transform: scale(1.04);
}
.horizontal-tip-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(62,23,19,0.08);
}
.ing-circle-item:hover div {
    transform: scale(1.08);
    border-color: #ea580c !important;
}
.ing-nav-btn:hover {
    background: #ea580c !important;
    color: #ffffff !important;
    border-color: #ea580c !important;
}
@media (max-width: 900px) {
    .featured-tips-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
@media (max-width: 600px) {
    .featured-tips-grid {
        grid-template-columns: 1fr !important;
    }
    .horizontal-tip-card {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
    .horizontal-tip-card div:first-child {
        width: 100% !important;
        height: 180px !important;
    }
@keyframes fadeInTip {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.btn-load-more-tips:hover {
    background: #c2410c !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(234, 88, 12, 0.35) !important;
}

/* DARK MODE ADAPTATIONS FOR KITCHEN TIPS */
[data-theme="dark"] .featured-tip-card,
[data-theme="dark"] .horizontal-tip-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
[data-theme="dark"] .featured-tip-card h3,
[data-theme="dark"] .horizontal-tip-card h3 {
    color: #f8fafc !important;
}
[data-theme="dark"] .horizontal-tip-card p {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .tips-hero-banner {
    background: linear-gradient(135deg, #2b1810 0%, #1a1514 100%) !important;
    border: 1px solid #78350f !important;
}
[data-theme="dark"] .ingredient-explorer h2,
[data-theme="dark"] .featured-tips-section h2,
[data-theme="dark"] .all-tips-list-section h2 {
    color: #f8fafc !important;
}
[data-theme="dark"] .ingredient-explorer p,
[data-theme="dark"] .featured-tips-section p,
[data-theme="dark"] .all-tips-list-section p {
    color: #fdba74 !important;
}
[data-theme="dark"] .ing-nav-btn {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
}
[data-theme="dark"] .ing-circle-item span {
    color: #f1f5f9 !important;
}
[data-theme="dark"] .cookio-modal-content {
    background: #1e293b !important;
    color: #f8fafc !important;
    border: 1px solid #475569 !important;
}
[data-theme="dark"] #modalTipTitle {
    color: #f8fafc !important;
}
[data-theme="dark"] #modalTipContent {
    background: #0f172a !important;
    color: #e2e8f0 !important;
    border-color: #334155 !important;
}
[data-theme="dark"] #modalTipSummary {
    color: #94a3b8 !important;
    border-color: #334155 !important;
}
[data-theme="dark"] .breadcrumb span {
    color: #94a3b8 !important;
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
