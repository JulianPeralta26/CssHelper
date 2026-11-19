// js/auth.js - Manejo del desplegable y autenticación de usuario
(function() {
    document.addEventListener('DOMContentLoaded', () => {
        const loginDropdown = document.getElementById('loginDropdown');
        const loginToggleBtn = document.getElementById('loginToggleBtn');
        const loginMenu = document.getElementById('loginMenu');
        const loginForm = document.getElementById('loginForm');
        const loginError = document.getElementById('loginError');
        const loginLoggedView = document.getElementById('loginLoggedView');
        const loggedUsername = document.getElementById('loggedUsername');
        const logoutBtn = document.getElementById('logoutBtn');
        const userAvatarIcon = document.getElementById('userAvatarIcon');

        if (!loginToggleBtn || !loginMenu) return;

        // Abrir / Cerrar dropdown al hacer clic en el botón
        loginToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = loginMenu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                loginMenu.classList.add('show');
                loginToggleBtn.setAttribute('aria-expanded', 'true');
                const userInput = document.getElementById('loginUsername');
                if (userInput && !loginLoggedView.classList.contains('show')) {
                    setTimeout(() => userInput.focus(), 100);
                }
            }
        });

        // Prevenir que clics dentro del menú lo cierren
        loginMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Cerrar al hacer clic fuera
        document.addEventListener('click', () => {
            closeAllDropdowns();
        });

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });

        function closeAllDropdowns() {
            if (loginMenu) {
                loginMenu.classList.remove('show');
                loginToggleBtn.setAttribute('aria-expanded', 'false');
            }
        }

        // Consultar estado de sesión al cargar
        checkAuthStatus();

        function checkAuthStatus() {
            fetch('backend/auth.php?action=status')
                .then(res => res.json())
                .then(data => {
                    if (data && data.logged_in) {
                        setLoggedInUI(data.username);
                    } else {
                        setLoggedOutUI();
                    }
                })
                .catch(() => {
                    setLoggedOutUI();
                });
        }

        function setLoggedInUI(username) {
            if (loginForm) loginForm.style.display = 'none';
            if (loginLoggedView) {
                loginLoggedView.style.display = 'block';
                loginLoggedView.classList.add('show');
            }
            if (loggedUsername) loggedUsername.textContent = username;
            if (userAvatarIcon) {
                userAvatarIcon.classList.add('is-logged');
                loginToggleBtn.title = `Conectado como ${username}`;
            }
            if (loginError) {
                loginError.style.display = 'none';
                loginError.textContent = '';
            }
        }

        function setLoggedOutUI() {
            if (loginForm) loginForm.style.display = 'flex';
            if (loginLoggedView) {
                loginLoggedView.style.display = 'none';
                loginLoggedView.classList.remove('show');
            }
            if (userAvatarIcon) {
                userAvatarIcon.classList.remove('is-logged');
                loginToggleBtn.title = 'Iniciar sesión';
            }
            if (loginError) {
                loginError.style.display = 'none';
                loginError.textContent = '';
            }
        }

        // Enviar formulario de Login
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const usernameInput = document.getElementById('loginUsername');
                const passwordInput = document.getElementById('loginPassword');
                const submitBtn = document.getElementById('loginSubmitBtn');

                const username = usernameInput ? usernameInput.value.trim() : '';
                const password = passwordInput ? passwordInput.value : '';

                if (!username || !password) {
                    showError('Completa todos los campos');
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Accediendo...';
                }

                fetch('backend/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login', username, password })
                })
                .then(res => res.json().then(data => ({ status: res.status, data })))
                .then(({ status, data }) => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Entrar';
                    }

                    if (status === 200 && data.success) {
                        setLoggedInUI(data.username);
                        if (passwordInput) passwordInput.value = '';
                        if (typeof showToast === 'function') {
                            showToast(data.message || `¡Bienvenido, ${data.username}!`);
                        }
                        if (window.FavoritesManager && typeof window.FavoritesManager.reloadAll === 'function') {
                            window.FavoritesManager.reloadAll();
                        }
                        setTimeout(() => closeAllDropdowns(), 800);
                    } else {
                        showError(data.error || 'Credenciales incorrectas');
                    }
                })
                .catch(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Entrar';
                    }
                    showError('Error de conexión.');
                });
            });
        }

        // Logout
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                logoutBtn.disabled = true;
                fetch('backend/auth.php?action=logout')
                    .then(res => res.json())
                    .then(data => {
                        logoutBtn.disabled = false;
                        setLoggedOutUI();
                        if (window.FavoritesManager && typeof window.FavoritesManager.reloadAll === 'function') {
                            window.FavoritesManager.reloadAll();
                        }
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Sesión cerrada');
                        }
                        closeAllDropdowns();
                    })
                    .catch(() => {
                        logoutBtn.disabled = false;
                        setLoggedOutUI();
                        closeAllDropdowns();
                    });
            });
        }

        function showError(msg) {
            if (loginError) {
                loginError.textContent = msg;
                loginError.style.display = 'block';
            }
        }
    });
})();
