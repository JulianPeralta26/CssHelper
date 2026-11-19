// js/favorites.js - Gestión de fuentes y colores favoritos vinculados al usuario y backend
const FavoritesManager = (function() {
    let favoriteFonts = new Set();
    let favoriteColors = [];
    let filterOnlyFavFonts = false;

    // Inicializar al cargar el DOM
    document.addEventListener('DOMContentLoaded', () => {
        loadFavoriteFonts();
        loadFavoriteColors();
        setupColorFavButton();
        setupFontFavFilterButton();
    });

    function openLoginPrompt(message) {
        if (typeof showToast === 'function') {
            showToast(message || 'Inicia sesión para guardar tus favoritos.');
        }
        const loginToggleBtn = document.getElementById('loginToggleBtn');
        const loginMenu = document.getElementById('loginMenu');
        if (loginToggleBtn && loginMenu && !loginMenu.classList.contains('show')) {
            loginToggleBtn.click();
        }
    }

    // ==========================================
    // FUENTES FAVORITAS
    // ==========================================
    function loadFavoriteFonts() {
        fetch('backend/favorites.php?action=get_fonts&t=' + Date.now())
            .then(res => {
                if (res.status === 401) {
                    favoriteFonts.clear();
                    renderFontChipsFavorites();
                    return null;
                }
                return res.json();
            })
            .then(data => {
                if (data && Array.isArray(data.favorites)) {
                    favoriteFonts = new Set(data.favorites.map(f => f.name));
                    renderFontChipsFavorites();
                }
            })
            .catch(() => {});
    }

    function toggleFontFavorite(fontName, starBtn) {
        fetch('backend/favorites.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_font', font_name: fontName })
        })
        .then(res => {
            if (res.status === 401) {
                openLoginPrompt('Inicia sesión para guardar tus fuentes favoritas.');
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return;
            if (data.success) {
                if (data.is_favorite) {
                    favoriteFonts.add(fontName);
                } else {
                    favoriteFonts.delete(fontName);
                }
                renderFontChipsFavorites();
                if (typeof showToast === 'function') {
                    showToast(data.message);
                }
            } else if (data.error) {
                if (typeof showToast === 'function') {
                    showToast(data.error);
                }
            }
        })
        .catch(() => {
            if (typeof showToast === 'function') {
                showToast('Error al conectar con el servidor.');
            }
        });
    }

    function renderFontChipsFavorites() {
        const chips = document.querySelectorAll('.font-chip-item');
        chips.forEach(item => {
            const fontName = item.getAttribute('data-font-name');
            const star = item.querySelector('.font-star-btn');
            const isFav = favoriteFonts.has(fontName);

            if (star) {
                if (isFav) {
                    star.classList.add('is-favorite');
                    star.setAttribute('aria-label', `Quitar ${fontName} de favoritos`);
                    star.title = 'Quitar de favoritos';
                } else {
                    star.classList.remove('is-favorite');
                    star.setAttribute('aria-label', `Marcar ${fontName} como favorita`);
                    star.title = 'Marcar como favorita';
                }
            }

            // Si está activo el filtro de solo favoritos
            if (filterOnlyFavFonts) {
                item.style.display = isFav ? 'inline-flex' : 'none';
            } else {
                item.style.display = 'inline-flex';
            }
        });
    }

    function setupFontFavFilterButton() {
        const btnFilter = document.getElementById('btnToggleFavFilter');
        if (!btnFilter) return;

        btnFilter.addEventListener('click', () => {
            filterOnlyFavFonts = !filterOnlyFavFonts;
            btnFilter.classList.toggle('active', filterOnlyFavFonts);
            btnFilter.setAttribute('aria-pressed', filterOnlyFavFonts ? 'true' : 'false');
            renderFontChipsFavorites();
        });
    }

    // ==========================================
    // COLORES FAVORITOS
    // ==========================================
    function loadFavoriteColors() {
        fetch('backend/favorites.php?action=get_colors&t=' + Date.now())
            .then(res => {
                if (res.status === 401) {
                    favoriteColors = [];
                    renderColorFavoritesList();
                    return null;
                }
                return res.json();
            })
            .then(data => {
                if (data && Array.isArray(data.favorites)) {
                    favoriteColors = data.favorites;
                    renderColorFavoritesList();
                }
            })
            .catch(() => {});
    }

    function setupColorFavButton() {
        const btnFav = document.getElementById('btnFavColor');
        if (!btnFav) return;

        btnFav.addEventListener('click', () => {
            const hexInput = document.getElementById('hexCode');
            const rgbInput = document.getElementById('rgbCode');
            const hslInput = document.getElementById('hslCode');

            const hex = hexInput ? hexInput.value : '';
            const rgb = rgbInput ? rgbInput.value : '';
            const hsl = hslInput ? hslInput.value : '';

            if (!hex) return;

            fetch('backend/favorites.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_color',
                    hex, rgb, hsl,
                    name: hex
                })
            })
            .then(res => {
                if (res.status === 401) {
                    openLoginPrompt('Inicia sesión para guardar tus colores favoritos.');
                    return null;
                }
                return res.json();
            })
            .then(data => {
                if (!data) return;
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast(data.message);
                    }
                    loadFavoriteColors();
                } else if (data.error) {
                    if (typeof showToast === 'function') {
                        showToast(data.error);
                    }
                }
            })
            .catch(() => {
                if (typeof showToast === 'function') {
                    showToast('Error al guardar color favorito.');
                }
            });
        });
    }

    function renderColorFavoritesList() {
        const container = document.getElementById('colorFavsList');
        if (!container) return;

        container.innerHTML = '';
        if (favoriteColors.length === 0) {
            container.innerHTML = '<span class="no-favs-hint">Sin colores favoritos aún</span>';
            return;
        }

        favoriteColors.forEach(col => {
            const swatch = document.createElement('div');
            swatch.className = 'fav-color-swatch-item';
            swatch.title = `Clic para usar ${col.color_hex}.`;
            swatch.style.backgroundColor = col.color_hex;

            // Al hacer clic, cargar este color en el selector
            swatch.addEventListener('click', () => {
                applyColorToPicker(col.color_hex);
                if (typeof showToast === 'function') {
                    showToast(`Color ${col.color_hex} seleccionado`);
                }
            });

            // Botón eliminar al pasar el cursor o botón de eliminar miniatura
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'fav-swatch-remove-btn';
            removeBtn.innerHTML = '×';
            removeBtn.title = `Eliminar ${col.color_hex}`;
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteColorFavorite(col.id, col.color_hex);
            });

            swatch.appendChild(removeBtn);
            container.appendChild(swatch);
        });
    }

    function deleteColorFavorite(id, hex) {
        fetch('backend/favorites.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_color', id, hex })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                if (typeof showToast === 'function') {
                    showToast(data.message);
                }
                loadFavoriteColors();
            }
        })
        .catch(() => {});
    }

    function applyColorToPicker(hex) {
        if (typeof window.setColorFromHex === 'function') {
            window.setColorFromHex(hex);
        } else {
            const hexInput = document.getElementById('hexCode');
            const preview = document.getElementById('colorPreview');
            if (hexInput) hexInput.value = hex;
            if (preview) {
                preview.style.backgroundColor = hex;
                preview.style.boxShadow = `0 0 28px ${hex}80, 0 4px 15px rgba(0,0,0,0.15)`;
            }
        }
    }

    // Objeto público
    const api = {
        reloadAll: function() {
            loadFavoriteFonts();
            loadFavoriteColors();
        },
        toggleFont: toggleFontFavorite,
        isFontFavorite: function(fontName) {
            return favoriteFonts.has(fontName);
        },
        renderFontChips: renderFontChipsFavorites
    };

    window.FavoritesManager = api;
    return api;
})();
