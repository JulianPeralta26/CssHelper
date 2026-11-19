// js/fonts.js
// Lógica del Buscador de Fuentes (Chips dinámicos + Preview Triple + Favoritos)
(function() {
    const searchMessage = document.getElementById('searchMessage');
    const fontLinkCode = document.getElementById('fontLinkCode');
    const fontImportCode = document.getElementById('fontImportCode');
    const fontCssRuleCode = document.getElementById('fontCssRuleCode');
    const dynamicFontLoader = document.getElementById('dynamicFontLoader');

    const previewNormal = document.getElementById('previewNormal');
    const previewBold   = document.getElementById('previewBold');
    const previewItalic = document.getElementById('previewItalic');
    const fontPreviewCustomText = document.getElementById('fontPreviewCustomText');
    const fontChipsGrid = document.getElementById('fontChipsGrid');

    if (!fontChipsGrid) return;

    // Aplica el texto del input a los 3 previews
    function applyPreviewText(text) {
        const t = (text && text.trim()) ? text : 'Hola Mundo';
        if (previewNormal) previewNormal.textContent = t;
        if (previewBold)   previewBold.textContent   = t;
        if (previewItalic) previewItalic.textContent = t;
    }

    if (fontPreviewCustomText) {
        fontPreviewCustomText.addEventListener('input', () => {
            applyPreviewText(fontPreviewCustomText.value);
        });
    }

    // Aplica la familia de fuente a los 3 previews
    function applyFontFamily(fontFamily) {
        if (previewNormal) previewNormal.style.fontFamily = fontFamily;
        if (previewBold)   previewBold.style.fontFamily   = fontFamily;
        if (previewItalic) previewItalic.style.fontFamily = fontFamily;
    }

    function fetchFontData(fontName) {
        if (searchMessage) searchMessage.style.display = 'none';
        fetch(`backend/get_font.php?name=${encodeURIComponent(fontName)}&t=${Date.now()}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    if (searchMessage) {
                        searchMessage.textContent = data.error;
                        searchMessage.style.display = 'block';
                    }
                } else {
                    if (fontLinkCode)    fontLinkCode.value    = data.link_code;
                    if (fontImportCode)  fontImportCode.value  = data.import_code;
                    if (fontCssRuleCode) fontCssRuleCode.value = data.css_rule;

                    // Cargar la fuente dinámicamente en el documento
                    if (dynamicFontLoader) {
                        dynamicFontLoader.textContent = data.import_code;
                    }

                    // Extraer font-family del css_rule y aplicar a los 3 previews
                    const match = data.css_rule ? data.css_rule.match(/font-family:\s*([^;]+)/i) : null;
                    const family = match ? match[1].trim() : `'${fontName}', sans-serif`;
                    applyFontFamily(family);
                    if (fontPreviewCustomText) {
                        applyPreviewText(fontPreviewCustomText.value);
                    }
                }
            })
            .catch(() => {
                if (searchMessage) {
                    searchMessage.textContent = "No se pudo cargar la fuente. Revisa que el servidor local esté encendido.";
                    searchMessage.style.display = 'block';
                }
            });
    }

    // Cargar chips de fuentes desde backend (con cache-buster para asegurar la lista completa de 10 fuentes)
    fetch('backend/get_all_fonts.php?t=' + Date.now())
        .then(res => res.json())
        .then(fonts => {
            fontChipsGrid.innerHTML = '';
            if (!fonts || fonts.error || !Array.isArray(fonts) || fonts.length === 0) {
                fontChipsGrid.innerHTML = '<span class="font-chips-error">No hay fuentes disponibles todavía.</span>';
                return;
            }

            fonts.forEach((font, index) => {
                const item = document.createElement('div');
                item.className = 'font-chip-item';
                item.setAttribute('data-font-name', font);

                const btn = document.createElement('button');
                btn.className = 'font-chip-btn' + (index === 0 ? ' active' : '');
                btn.setAttribute('aria-pressed', index === 0 ? 'true' : 'false');
                btn.textContent = font;
                btn.type = 'button';
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.font-chip-btn').forEach(b => {
                        b.classList.remove('active');
                        b.setAttribute('aria-pressed', 'false');
                    });
                    btn.classList.add('active');
                    btn.setAttribute('aria-pressed', 'true');
                    fetchFontData(font);
                });

                // Botón de estrella para favoritos
                const starBtn = document.createElement('button');
                starBtn.type = 'button';
                starBtn.className = 'font-star-btn';
                starBtn.innerHTML = '★';
                starBtn.title = 'Marcar como favorita';
                starBtn.setAttribute('aria-label', `Marcar ${font} como favorita`);
                starBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (window.FavoritesManager && typeof window.FavoritesManager.toggleFont === 'function') {
                        window.FavoritesManager.toggleFont(font, starBtn);
                    }
                });

                item.appendChild(btn);
                item.appendChild(starBtn);
                fontChipsGrid.appendChild(item);
            });

            // Cargar la primera fuente por defecto
            if (fonts.length > 0) {
                fetchFontData(fonts[0]);
            }

            // Actualizar estado de estrellas si FavoritesManager ya cargó
            if (window.FavoritesManager && typeof window.FavoritesManager.renderFontChips === 'function') {
                window.FavoritesManager.renderFontChips();
            }
        })
        .catch(() => {
            fontChipsGrid.innerHTML = '<span class="font-chips-error">No se pudieron cargar las fuentes. Revisa que el servidor local esté encendido.</span>';
        });
})();
