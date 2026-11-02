const picker = document.querySelector('#ingredientPicker');
const resultsContainer = document.querySelector('#fridgeResults');
const message = document.querySelector('#fridgeMessage');
const noMatchBox = document.querySelector('#noMatchMessage');
const customInput = document.querySelector('#customIngredientInput');
const btnAdd = document.querySelector('#btnAddIngredient');
const btnClearAll = document.querySelector('#btnClearAll');
const customGroup = document.querySelector('#customGroup');
const customChips = document.querySelector('#customChips');

// In-memory data store for ultra-fast filtering
let fridgeData = [];
try {
    const dataEl = document.getElementById('smartFridgeData');
    if (dataEl && dataEl.textContent) {
        fridgeData = JSON.parse(dataEl.textContent);
    }
} catch (e) {
    console.warn('Could not parse smart fridge data:', e);
}

const initialCardsHtml = resultsContainer ? resultsContainer.innerHTML : '';

const SYNONYM_PAIRS = [
    ['thịt heo', 'thịt lợn', 'heo', 'lợn'],
    ['đậu phụ', 'đậu hũ', 'tàu hũ'],
    ['chả giò', 'nem rán', 'nem'],
    ['ngô', 'bắp'],
    ['mè', 'vừng'],
    ['dứa', 'khóm', 'thơm'],
    ['mộc nhĩ', 'nấm mèo'],
    ['hàu', 'hào'],
    ['ngao', 'nghêu'],
    ['rau mùi', 'ngò rí', 'ngò']
];

function isIngredientMatched(ingredients, target) {
    if (ingredients.includes(target)) return true;
    for (const group of SYNONYM_PAIRS) {
        if (group.some(w => target.includes(w) || w.includes(target))) {
            if (group.some(w => ingredients.includes(w))) {
                return true;
            }
        }
    }
    return false;
}

function getMatchMode() {
    const checkedRadio = document.querySelector('input[name="match_mode"]:checked');
    return checkedRadio ? checkedRadio.value : 'any';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderRecipeCard(recipe, matchedCount, totalSelected) {
    const isFull = matchedCount === totalSelected;
    const bgStyle = isFull ? 'background:#dcfce7; color:#15803d;' : 'background:#fff7ed; color:#ea580c;';
    const icon = isFull ? '✅' : '✨';
    const rawImg = recipe.image_url || '';
    const imgSrc = rawImg 
        ? (rawImg.startsWith('http') ? rawImg : '/' + rawImg.replace(/^\/+/, '')) 
        : '/assets/images/default-recipe.jpg';
    const previewIng = (recipe.ingredients || '').replace(/\r\n|\n|\r/g, ', ').substring(0, 75) + '...';

    return `
    <article class="recipe-card fridge-recipe" data-ingredients="${escapeHtml(recipe.ingredients || '')}">
        <div class="recipe-card-media">
            <a href="/views/recipe-detail.php?id=${recipe.id}">
                <img src="${imgSrc}" alt="${escapeHtml(recipe.title)}" loading="lazy">
            </a>
            <span class="card-category-badge">${escapeHtml(recipe.category || 'Món chính')}</span>
        </div>
        <div class="recipe-card-body">
            <div class="match-badge-container" style="margin-bottom: 0.5rem; min-height: 22px;">
                <span style="display:inline-block; font-size:0.78rem; font-weight:700; ${bgStyle} padding:0.2rem 0.65rem; border-radius:99px;">
                    ${icon} Khớp ${matchedCount}/${totalSelected} nguyên liệu
                </span>
            </div>
            <div class="card-meta-row">
                <span>⏱️ ${escapeHtml(recipe.cooking_time || '30 phút')}</span>
                <span>👥 ${escapeHtml(recipe.servings || '2 - 4 người')}</span>
            </div>
            <h3>
                <a href="/views/recipe-detail.php?id=${recipe.id}">
                    ${escapeHtml(recipe.title)}
                </a>
            </h3>
            <p style="font-size: 0.85rem; color: #4b5563; line-height: 1.5; margin-bottom: 1rem; background: #fdfbf7; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #f1ebe1;">
                <strong>Nguyên liệu:</strong> ${escapeHtml(previewIng)}
            </p>
            <a class="button button-create full-width" style="text-align: center;" href="/views/recipe-detail.php?id=${recipe.id}">
                Xem cách nấu ngay &rarr;
            </a>
        </div>
    </article>
    `;
}

function filterRecipes() {
    const selected = [...(picker?.querySelectorAll('input:checked') || [])].map((input) => input.value.trim().toLowerCase());
    const mode = getMatchMode();

    if (selected.length === 0) {
        if (resultsContainer) {
            resultsContainer.innerHTML = initialCardsHtml;
        }
        if (message) {
            message.textContent = 'Hãy chọn ít nhất một nguyên liệu ở trên để bắt đầu tìm món phù hợp.';
            message.className = 'notice';
        }
        if (noMatchBox) noMatchBox.classList.add('hidden');
        return;
    }

    const matches = [];

    fridgeData.forEach((recipe) => {
        const ingredients = (recipe.ingredients || '').toLowerCase();
        let matchedCount = 0;
        selected.forEach((item) => {
            if (isIngredientMatched(ingredients, item)) {
                matchedCount++;
            }
        });

        const isMatch = (mode === 'all')
            ? (matchedCount === selected.length)
            : (matchedCount > 0);

        if (isMatch) {
            matches.push({ recipe, matchedCount });
        }
    });

    // Sort by most matched ingredients first
    matches.sort((a, b) => b.matchedCount - a.matchedCount);

    const visibleCount = matches.length;

    if (resultsContainer) {
        if (visibleCount > 0) {
            // Render up to top 48 matches for smooth scrolling
            const cardsHtml = matches.slice(0, 48).map(m => renderRecipeCard(m.recipe, m.matchedCount, selected.length)).join('');
            resultsContainer.innerHTML = cardsHtml;
        } else {
            resultsContainer.innerHTML = '';
        }
    }

    if (message) {
        const modeText = mode === 'all' ? 'khớp trọn vẹn tất cả' : 'chứa ít nhất một';
        message.textContent = `Tìm thấy ${visibleCount} món ${modeText} nguyên liệu bạn đã chọn.`;
        message.className = visibleCount > 0 ? 'notice success' : 'notice';
    }

    if (noMatchBox) {
        noMatchBox.classList.toggle('hidden', visibleCount > 0);
    }
}

function addCustomIngredient() {
    const val = customInput?.value.trim();
    if (!val) return;

    // Check if already exists
    const existing = [...(picker?.querySelectorAll('input') || [])].some(
        (input) => input.value.toLowerCase() === val.toLowerCase()
    );

    if (existing) {
        if (window.showToast) {
            window.showToast('Nguyên liệu này đã có trong danh sách!', 'info');
        } else {
            alert('Nguyên liệu này đã có trong danh sách!');
        }
        customInput.value = '';
        return;
    }

    if (customGroup) customGroup.classList.remove('hidden');

    const label = document.createElement('label');
    label.className = 'fridge-chip';
    label.innerHTML = `<input type="checkbox" value="${escapeHtml(val)}" checked><span>${escapeHtml(val)}</span>`;
    
    if (customChips) {
        customChips.appendChild(label);
    } else if (picker) {
        picker.appendChild(label);
    }

    customInput.value = '';
    if (window.showToast) {
        window.showToast(`Đã thêm nguyên liệu "${val}"!`);
    }
    filterRecipes();
}

function clearAllIngredients() {
    if (picker) {
        picker.querySelectorAll('input:checked').forEach((input) => {
            input.checked = false;
        });
    }
    filterRecipes();
    if (window.showToast) {
        window.showToast('Đã bỏ chọn tất cả nguyên liệu.');
    }
}

picker?.addEventListener('change', filterRecipes);
document.querySelectorAll('input[name="match_mode"]').forEach((radio) => {
    radio.addEventListener('change', filterRecipes);
});
btnClearAll?.addEventListener('click', clearAllIngredients);
btnAdd?.addEventListener('click', addCustomIngredient);
customInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        addCustomIngredient();
    }
});
