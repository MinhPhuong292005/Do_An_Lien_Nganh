/**
 * Cookio Admin Portal Interactive Script
 * Replaces native browser window.confirm with custom in-app modal dialog
 */

// Custom In-App Confirmation Modal
function cookioAdminConfirm(options) {
    return new Promise((resolve) => {
        let backdrop = document.getElementById('cookioAdminConfirmModal');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'cookioAdminConfirmModal';
            backdrop.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                z-index: 99999;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                box-sizing: border-box;
            `;
            backdrop.innerHTML = `
                <div style="background: #ffffff; border-radius: 16px; max-width: 440px; width: 100%; padding: 1.75rem; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid #e2e8f0; font-family: 'Plus Jakarta Sans', sans-serif;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <div id="adminConfirmIcon" style="width: 42px; height: 42px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                            ⚠️
                        </div>
                        <div>
                            <h3 id="adminConfirmTitle" style="margin: 0; font-size: 1.18rem; font-weight: 800; color: #1e293b;">Xác nhận hành động</h3>
                            <span id="adminConfirmSub" style="font-size: 0.85rem; color: #64748b;">Hệ thống quản trị Cookio</span>
                        </div>
                    </div>
                    <p id="adminConfirmMsg" style="color: #334155; font-size: 0.95rem; line-height: 1.55; margin: 0 0 1.5rem;">
                        Bạn có chắc chắn muốn thực hiện hành động này không?
                    </p>
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" id="adminConfirmCancelBtn" style="padding: 0.55rem 1.15rem; background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #475569; cursor: pointer; transition: all 0.15s;">
                            Hủy bỏ
                        </button>
                        <button type="button" id="adminConfirmOkBtn" style="padding: 0.55rem 1.25rem; background: #dc2626; border: 1.5px solid #dc2626; border-radius: 8px; font-weight: 700; font-size: 0.88rem; color: #ffffff; cursor: pointer; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25); transition: all 0.15s;">
                            Xác nhận
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(backdrop);
        }

        const titleEl = document.getElementById('adminConfirmTitle');
        const msgEl = document.getElementById('adminConfirmMsg');
        const okBtn = document.getElementById('adminConfirmOkBtn');
        const cancelBtn = document.getElementById('adminConfirmCancelBtn');

        titleEl.textContent = options.title || 'Xác nhận hành động';
        msgEl.innerHTML = options.message || 'Bạn có chắc chắn muốn thực hiện không?';
        okBtn.textContent = options.confirmText || 'Xác nhận';
        if (options.btnColor) {
            okBtn.style.background = options.btnColor;
            okBtn.style.borderColor = options.btnColor;
        } else {
            okBtn.style.background = '#dc2626';
            okBtn.style.borderColor = '#dc2626';
        }

        backdrop.style.display = 'flex';

        function cleanup(result) {
            backdrop.style.display = 'none';
            okBtn.onclick = null;
            cancelBtn.onclick = null;
            backdrop.onclick = null;
            resolve(result);
        }

        okBtn.onclick = () => cleanup(true);
        cancelBtn.onclick = () => cleanup(false);
        backdrop.onclick = (e) => {
            if (e.target === backdrop) cleanup(false);
        };
    });
}

