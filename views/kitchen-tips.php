<?php

declare(strict_types=1);

$pageTitle = 'Kinh Nghiệm Hay & Bí Quyết Nấu Ngon - Cookio';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// Fetch all approved tips from database
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

// Featured tips - Up to 8 featured tips
$featuredTips = array_slice($allDbTips, 0, 8);

// Full list
$allTipsList = $allDbTips;
?>

<main class="container py-8" style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb mb-6" style="font-size: 0.9rem; color: var(--text-muted); display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1.5rem;">
        <a href="<?= BASE_URL ?>/index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Trang chủ</a>
        <span>&rsaquo;</span>
        <span style="font-weight: 700; color: var(--text-main);">Kinh nghiệm hay</span>
    </nav>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'tip-published'): ?>
        <div class="notice success" style="margin-bottom: 2rem; background: #ecfdf5; border-color: #10b981; color: #065f46; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
            <span>Chúc mừng bạn! Bài viết chia sẻ kinh nghiệm nấu nướng đã được xuất bản thành công lên Cookio!</span>
        </div>
    <?php elseif (isset($_GET['success']) && $_GET['success'] === 'tip-pending'): ?>
        <div class="notice success" style="margin-bottom: 2rem; background: #fefce8; border: 1.5px solid #f59e0b; color: #854d0e; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
            <span>Bài viết chia sẻ kinh nghiệm của bạn đã được gửi thành công và đang chờ ban quản trị Cookio duyệt! Bạn sẽ nhận được thông báo khi bài viết được duyệt.</span>
        </div>
    <?php endif; ?>

    <!-- =====================================================================
         TOP HERO BANNER (Xóa icon hoạt họa, tương phản cao, giao diện phẳng sang trọng)
         ===================================================================== -->
    <section class="tips-hero-banner" style="border-radius: 18px; padding: 2.25rem 2.5rem; margin-bottom: 2.5rem; box-shadow: var(--shadow-sm); overflow: hidden; position: relative;">
        <div style="max-width: 800px;">
            <h1 style="font-size: 2.1rem; font-weight: 800; color: #ffffff; margin: 0 0 0.65rem; line-height: 1.3; letter-spacing: -0.5px;">
                Tổng hợp mẹo hay cuộc sống bạn nhất định không nên bỏ lỡ
            </h1>
            <p style="font-size: 1.05rem; font-weight: 600; color: #fbbf24; margin: 0;">
                Chuyên mục này có <?= number_format($totalApprovedTips) ?> bài viết &amp; bí quyết.
            </p>
        </div>
    </section>

    <!-- =====================================================================
         CHIA SẺ KINH NGHIỆM NẤU NƯỚNG (Xóa icon, tương phản cao trên cả 2 theme)
         ===================================================================== -->
    <section class="share-tips-cta-section" style="margin-bottom: 3.5rem; border-radius: 20px; padding: 2.5rem 2rem; text-align: center; box-shadow: var(--shadow-sm);">
        <div style="max-width: 720px; margin: 0 auto;">
            <h2 class="share-tips-title" style="font-size: 1.85rem; font-weight: 800; margin: 0 0 0.75rem; line-height: 1.35; letter-spacing: -0.3px;">
                Bạn có muốn chia sẻ kinh nghiệm nấu nướng của mình không?
            </h2>
            <p class="share-tips-desc" style="font-size: 1.02rem; margin: 0 0 1.75rem; line-height: 1.6;">
                Mỗi mẹo nhỏ, tỷ lệ ướp gia vị hay bí quyết giữ rau xanh, thịt mềm của bạn đều là món quà quý giá giúp cộng đồng yêu bếp nấu ăn dễ dàng và ngon miệng hơn mỗi ngày!
            </p>
            <div>
                <a href="<?= BASE_URL ?>/views/create-tip.php" class="button button-create" style="display: inline-flex; align-items: center; justify-content: center; padding: 0.85rem 2.5rem; font-size: 1rem; font-weight: 800; border-radius: 9999px; text-decoration: none; box-shadow: 0 6px 18px rgba(234, 88, 12, 0.3); transition: transform 0.2s, box-shadow 0.2s;">
                    Viết bài chia sẻ kinh nghiệm
                </a>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         CÁC KINH NGHIỆM HAY (8 CARDS)
         ===================================================================== -->
    <section class="featured-tips-section" style="margin-bottom: 4rem;">
        <div style="margin-bottom: 1.75rem;">
            <h2 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.35rem; letter-spacing: -0.3px;">
                Các kinh nghiệm hay
            </h2>
            <p style="font-size: 0.98rem; color: var(--text-muted); margin: 0;">
                Những mẹo hay và bí quyết nấu ăn hữu ích được cộng đồng yêu bếp Cookio yêu thích nhất
            </p>
        </div>

        <div class="featured-tips-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
            <?php foreach ($featuredTips as $t): ?>
                <article class="featured-tip-card" style="border-radius: 16px; overflow: hidden; border: 1.5px solid var(--border-color); background: var(--bg-surface); display: flex; flex-direction: column; transition: transform 0.25s, box-shadow 0.25s; cursor: pointer;" onclick="openTipModal(<?= (int)$t['id'] ?>)">
                    <!-- Card Thumbnail with Badge -->
                    <div style="position: relative; width: 100%; aspect-ratio: 1/1; overflow: hidden; background: var(--bg-page);">
                        <img src="<?= BASE_URL ?>/<?= e($t['image_url'] ?: 'assets/images/real_dishes/hero-dish-1.jpg') ?>" alt="<?= e($t['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" loading="lazy">
                        <span style="position: absolute; top: 0.75rem; left: 0.75rem; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(4px); color: #ffffff; font-size: 0.76rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 9999px;">
                            <?= e($t['category'] ?: 'Kinh nghiệm hay') ?>
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 1.25rem; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                        <h3 class="tip-card-title" style="font-size: 1.05rem; font-weight: 800; margin: 0 0 0.75rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: var(--text-main);">
                            <?= e($t['title']) ?>
                        </h3>
                        <?php if (!empty($t['time']) || !empty($t['difficulty'])): ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--primary); font-weight: 700;">
                            <?php if (!empty($t['time'])): ?>
                                <span><?= e($t['time']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($t['time']) && !empty($t['difficulty'])): ?>
                                <span style="color: var(--border-color);">•</span>
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
         DANH SÁCH TẤT CẢ CÔNG THỨC (HORIZONTAL CARDS)
         ===================================================================== -->
    <section class="all-tips-list-section" style="margin-bottom: 4rem;">
        <div style="text-align: center; margin-bottom: 2.25rem;">
            <h2 id="allTipsListHeading" style="font-size: 1.95rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.35rem; letter-spacing: -0.3px;">
                Danh sách tất cả công thức
            </h2>
            <p style="font-size: 1rem; color: var(--text-muted); margin: 0;">
                Tổng hợp công thức kinh nghiệm hay đầy đủ, dễ làm tại nhà (<?= number_format($totalApprovedTips) ?> bí quyết)
            </p>
        </div>

        <div class="horizontal-tips-list" id="horizontalTipsList" style="display: flex; flex-direction: column; gap: 1.25rem; max-width: 900px; margin: 0 auto;">
            <?php foreach ($allTipsList as $idx => $item): ?>
                <?php $isExtra = ($idx >= 6); ?>
                <article class="horizontal-tip-card <?= $isExtra ? 'tip-item-extra' : '' ?>" style="display: <?= $isExtra ? 'none' : 'flex' ?>; gap: 1.5rem; align-items: center; background: var(--bg-surface); border-radius: 16px; padding: 1.15rem; border: 1.5px solid var(--border-color); box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onclick="openTipModal(<?= (int)$item['id'] ?>)">
                    <!-- Left Photo -->
                    <div style="position: relative; width: 190px; height: 130px; border-radius: 12px; overflow: hidden; flex-shrink: 0; background: var(--bg-page);">
                        <img src="<?= BASE_URL ?>/<?= e($item['image_url'] ?: 'assets/images/real_dishes/hero-dish-1.jpg') ?>" alt="<?= e($item['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                        <span style="position: absolute; top: 0.5rem; left: 0.5rem; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(4px); color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 9999px;">
                            <?= e($item['category'] ?: 'Kinh nghiệm hay') ?>
                        </span>
                    </div>

                    <!-- Right Content -->
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                        <h3 class="tip-card-title" style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.5rem; line-height: 1.35;">
                            <?= e($item['title']) ?>
                        </h3>
                        <p style="font-size: 0.92rem; color: var(--text-muted); margin: 0 0 0.75rem; line-height: 1.5;">
                            <?= e($item['summary']) ?>
                        </p>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--primary); font-weight: 700;">
                            <?php if (!empty($item['time'])): ?>
                                <span><?= e($item['time']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['time']) && !empty($item['difficulty'])): ?>
                                <span style="color: var(--border-color);">•</span>
                            <?php endif; ?>
                            <?php if (!empty($item['difficulty'])): ?>
                                <span><?= e($item['difficulty']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Điều hướng: Nút Xem thêm -->
        <div style="text-align: center; margin-top: 2.5rem;">
            <button type="button" id="btnToggleAllTips" class="btn-load-more-tips" onclick="toggleMoreTips()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.85rem 2.2rem; background: var(--primary); color: #ffffff; border: none; border-radius: 9999px; font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 6px 18px rgba(234, 88, 12, 0.25); transition: all 0.25s ease;">
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
    <div class="cookio-modal-content" style="background: var(--bg-surface); border: 1.5px solid var(--border-color); border-radius: 1.25rem; max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 2rem; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.25);">
        <button type="button" onclick="closeTipModal()" style="position: absolute; top: 1.25rem; right: 1.25rem; background: var(--bg-page); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 50%; font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">&times;</button>
        <div id="tipModalBody"></div>
    </div>
</div>

<script>
const allTipsData = <?= json_encode($allDbTips, JSON_UNESCAPED_UNICODE) ?>;
let isTipsExpanded = false;

function toggleMoreTips() {
    isTipsExpanded = !isTipsExpanded;
    const extraItems = document.querySelectorAll('.tip-item-extra');
    const btnText = document.getElementById('btnTipsText');
    const btnArrow = document.getElementById('btnTipsArrow');

    extraItems.forEach(el => {
        el.style.display = isTipsExpanded ? 'flex' : 'none';
    });

    if (isTipsExpanded) {
        btnText.textContent = 'Thu gọn danh sách bí quyết';
        btnArrow.style.transform = 'rotate(180deg)';
    } else {
        btnText.textContent = 'Xem thêm công thức & bí quyết';
        btnArrow.style.transform = 'rotate(0deg)';
        const heading = document.getElementById('allTipsListHeading');
        if (heading) {
            heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

function openTipModal(id) {
    const tip = allTipsData.find(t => parseInt(t.id) === parseInt(id));
    if (!tip) return;

    const modal = document.getElementById('tipDetailModal');
    const body = document.getElementById('tipModalBody');

    const authorLine = tip.author_name ? `<p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:1rem;">Đăng bởi: <strong style="color:var(--text-main);">${escapeHtml(tip.author_name)}</strong></p>` : '';
    const imgHtml = tip.image_url ? `<div style="width:100%; max-height:300px; overflow:hidden; border-radius:12px; margin-bottom:1.25rem;"><img src="${escapeHtml('<?= BASE_URL ?>/' + tip.image_url)}" style="width:100%; height:100%; object-fit:cover;" alt=""></div>` : '';
    const recipeBtn = tip.recipe_link ? `<div style="margin-top:1.5rem;"><a href="${escapeHtml(tip.recipe_link)}" class="button button-create" style="display:inline-block; border-radius:9999px; text-decoration:none;">Xem công thức áp dụng &rarr;</a></div>` : '';

    body.innerHTML = `
        <span style="display:inline-block; font-size:0.8rem; font-weight:700; color:var(--primary); background:var(--primary-light); padding:0.3rem 0.75rem; border-radius:9999px; margin-bottom:0.75rem;">
            ${escapeHtml(tip.category || 'Kinh nghiệm hay')}
        </span>
        <h2 style="font-size:1.6rem; font-weight:800; color:var(--text-main); margin-bottom:0.5rem; line-height:1.35;">
            ${escapeHtml(tip.title)}
        </h2>
        ${authorLine}
        ${imgHtml}
        <div style="font-size:1.02rem; line-height:1.7; color:var(--text-main); white-space:pre-line;">
            ${escapeHtml(tip.content || tip.summary || '')}
        </div>
        ${recipeBtn}
    `;

    modal.style.display = 'flex';
}

function closeTipModal() {
    const modal = document.getElementById('tipDetailModal');
    if (modal) modal.style.display = 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('tipDetailModal');
    if (e.target === modal) {
        closeTipModal();
    }
});
</script>

<?php
require __DIR__ . '/../includes/modal-login.php';
require __DIR__ . '/../includes/footer.php';