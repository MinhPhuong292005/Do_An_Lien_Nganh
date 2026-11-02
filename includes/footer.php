</main>
<footer class="yummy-site-footer">
    <div class="yummy-footer-container">
        <!-- Cột 1: Thương hiệu Cookio & Slogan Know - Love - Share -->
        <div class="y-footer-col y-footer-brand">
            <a href="<?= BASE_URL ?>/index.php" class="y-footer-logo-link" aria-label="Cookio">
                <img src="<?= BASE_URL ?>/assets/images/logo-white.svg" alt="Cookio" class="y-footer-logo-img">
            </a>

            <div class="y-footer-motto-title">Know - Love - Share</div>
            <p class="y-footer-motto-desc"><strong>Know:</strong> Hiểu rõ điều đang làm và luôn học hỏi mỗi ngày</p>
            <p class="y-footer-motto-desc"><strong>Love:</strong> Đặt cái tâm, cái tình vào mỗi công thức</p>
            <p class="y-footer-motto-desc"><strong>Share:</strong> Chia sẻ niềm hạnh phúc qua những món ăn</p>
        </div>

        <!-- Cột 2: Khám phá (Loại bỏ các logo/icon theo yêu cầu Ảnh 1) -->
        <div class="y-footer-col">
            <h4 class="y-footer-heading">Khám phá</h4>
            <ul class="y-footer-links">
                <li><a href="<?= BASE_URL ?>/index.php">Công thức mới nhất</a></li>
                <li><a href="<?= BASE_URL ?>/index.php#sectionLatestRecipes" onclick="if (window.location.pathname.endsWith('index.php') || window.location.pathname === '/' || window.location.pathname === '<?= BASE_URL ?>' || window.location.pathname === '<?= BASE_URL ?>/') { const el = document.getElementById('sectionLatestRecipes'); if (el) { el.scrollIntoView({behavior: 'smooth'}); return false; } }">Hôm nay ăn gì?</a></li>
                <li><a href="<?= BASE_URL ?>/views/smart-fridge.php">Tủ lạnh thông minh</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?cat=M%C3%B3n+kho">Món ngon gia đình</a></li>
            </ul>
        </div>

        <!-- Cột 3: Liên hệ (Quang Trung Hà Đông Hà Nội, 0915916336, cookio.vn@gmail.com) -->
        <div class="y-footer-col">
            <h4 class="y-footer-heading">Liên hệ</h4>
            <ul class="y-footer-contact-list">
                <li class="y-contact-item">
                    <span class="y-contact-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    </span>
                    <span>Quang Trung, Hà Đông, Hà Nội</span>
                </li>
                <li class="y-contact-item">
                    <span class="y-contact-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    </span>
                    <span style="color: #e5e7eb; font-weight: 500; user-select: text; cursor: default;">0915916336</span>
                </li>
                <li class="y-contact-item">
                    <span class="y-contact-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    </span>
                    <span style="color: #e5e7eb; font-weight: 500; user-select: text; cursor: default;">cookio.vn@gmail.com</span>
                </li>
            </ul>
        </div>
    </div>
</footer>

<!-- Nút tròn màu vàng cuộn lên đầu trang (Khớp Ảnh 4) -->
<button type="button" class="btn-back-to-top" id="btnBackToTop" aria-label="Cuộn lên đầu trang" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
    ↑
</button>

<!-- Modals toàn hệ thống: Login & Smart Fridge -->
<?php require_once __DIR__ . '/modal-login.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?php if (isset($loadSmartFridgeScript) && $loadSmartFridgeScript): ?>
    <script src="<?= BASE_URL ?>/assets/js/smart-fridge.js"></script>
<?php endif; ?>
</body>
</html>
