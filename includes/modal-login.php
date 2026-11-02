<dialog id="loginModal" class="login-modal">
    <button type="button" class="close-modal" data-close-modal aria-label="Đóng">&times;</button>
    
    <div class="login-modal-header" style="text-align: center; margin-bottom: 1.5rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: #fff7ed; border-radius: 50%; margin-bottom: 0.6rem; border: 1.5px solid #fed7aa;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                <line x1="6" y1="17" x2="18" y2="17"/>
            </svg>
        </div>
        <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--text-main, #1e293b); margin: 0 0 0.25rem;">Đăng Nhập Cookio</h2>
        <p style="font-size: 0.88rem; color: var(--text-muted, #64748b); margin: 0;">Khám phá & chia sẻ công thức nấu ăn ngon cùng cộng đồng bếp</p>
    </div>

    <!-- AUTH TABS -->
    <div class="auth-tabs" style="margin-bottom: 1.25rem;">
        <button type="button" class="auth-tab active" data-auth-tab="login" onclick="switchAuthTab('login')">Đăng nhập</button>
        <button type="button" class="auth-tab" data-auth-tab="register" onclick="switchAuthTab('register')">Đăng ký mới</button>
    </div>

    <!-- FORM LOGIN -->
    <form id="formLogin" method="post" action="<?= BASE_URL ?>/actions/auth_action.php">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="redirect_to" id="loginRedirectTo" value="">
        
        <label style="font-size: 0.88rem; font-weight: 600; margin-bottom: 0.5rem; display: block; color: var(--text-main, #1e293b);">
            Tên tài khoản:
            <input type="text" id="loginUsernameInput" name="username" placeholder="Nhập tên tài khoản" required autofocus style="margin-top: 0.35rem; width: 100%;">
        </label>
        
        <label style="font-size: 0.88rem; font-weight: 600; margin-bottom: 0.75rem; display: block; color: var(--text-main, #1e293b); position: relative;">
            Mật khẩu:
            <div style="position: relative; margin-top: 0.35rem;">
                <input type="password" id="loginPasswordInput" name="password" placeholder="Nhập mật khẩu" required style="width: 100%; padding-right: 42px;">
                <button type="button" onclick="togglePasswordVisibility('loginPasswordInput', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; display: flex; align-items: center; justify-content: center;" aria-label="Hiện/Ẩn mật khẩu">
                    <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg class="eye-closed" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                </button>
            </div>
        </label>

        <!-- REMEMBER ME CHECKBOX -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <label style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.84rem; cursor: pointer; color: var(--text-muted, #64748b); user-select: none;">
                <input type="checkbox" id="chkRememberAccount" checked style="width: auto; margin: 0; accent-color: #ea580c;">
                <span>Ghi nhớ đăng nhập</span>
            </label>
        </div>

        <button type="submit" class="button button-create" style="width: 100%; padding: 0.8rem; font-weight: 700; font-size: 0.95rem; border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25);">
            Đăng nhập
        </button>

        <p style="text-align: center; margin: 1.2rem 0 0; font-size: 0.85rem; color: var(--text-muted, #64748b);">
            Chưa có tài khoản? 
            <a href="javascript:void(0)" onclick="switchAuthTab('register')" style="color: #ea580c; font-weight: 700; text-decoration: none;">Đăng ký ngay</a>
        </p>
    </form>

    <!-- FORM REGISTER -->
    <form id="formRegister" class="hidden" method="post" action="<?= BASE_URL ?>/actions/auth_action.php">
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="redirect_to" id="registerRedirectTo" value="">
        
        <label style="font-size: 0.88rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: var(--text-main, #1e293b);">
            Tên tài khoản mong muốn:
            <input type="text" name="username" placeholder="Ví dụ: bep_nha_minh" required style="margin-top: 0.25rem; width: 100%;">
        </label>
        <label style="font-size: 0.88rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: var(--text-main, #1e293b);">
            Số điện thoại (tùy chọn):
            <input type="tel" name="phone" placeholder="0901234567" style="margin-top: 0.25rem; width: 100%;">
        </label>
        <label style="font-size: 0.88rem; font-weight: 600; margin-bottom: 0.35rem; display: block; color: var(--text-main, #1e293b);">
            Mật khẩu:
            <input type="password" id="regPasswordInput" name="password" placeholder="Tạo mật khẩu" required style="margin-top: 0.25rem; width: 100%;">
        </label>
        <label style="font-size: 0.88rem; font-weight: 600; margin-bottom: 0.75rem; display: block; color: var(--text-main, #1e293b);">
            Xác nhận mật khẩu:
            <input type="password" id="regConfirmPasswordInput" name="confirm_password" placeholder="Nhập lại mật khẩu" required style="margin-top: 0.25rem; width: 100%;">
        </label>
        
        <button type="submit" class="button button-create" style="width: 100%; padding: 0.8rem; font-weight: 700; font-size: 0.95rem; border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.25); margin-top: 0.5rem;">
            Tạo tài khoản mới
        </button>

        <p style="text-align: center; margin: 1.2rem 0 0; font-size: 0.85rem; color: var(--text-muted, #64748b);">
            Đã có tài khoản? 
            <a href="javascript:void(0)" onclick="switchAuthTab('login')" style="color: #ea580c; font-weight: 700; text-decoration: none;">Đăng nhập ngay</a>
        </p>
    </form>

    <script>
    function togglePasswordVisibility(inputId, btn) {
        var input = document.getElementById(inputId);
        if (!input) return;
        var isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        var openEye = btn.querySelector('.eye-open');
        var closedEye = btn.querySelector('.eye-closed');
        if (openEye && closedEye) {
            openEye.style.display = isPass ? 'none' : 'block';
            closedEye.style.display = isPass ? 'block' : 'none';
        }
    }
    </script>
</dialog>
