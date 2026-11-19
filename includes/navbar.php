<?php
// includes/navbar.php
$currentPage = $currentPage ?? '';
?>
<header class="page-header">
    <div class="header-container">
        <a href="index.php" class="brand-logo" aria-label="CSS Helper, ir al inicio">
            <span class="logo-symbol" aria-hidden="true">&lt;/&gt;</span>
            <span class="logo-text">CSS Helper</span>
        </a>
        <div class="nav-right-actions">
            <nav class="main-nav" aria-label="Navegación principal">
                <a href="index.php" class="nav-link <?= ($currentPage === 'index') ? 'active' : '' ?>" <?= ($currentPage === 'index') ? 'aria-current="page"' : '' ?>>Inicio</a>
                <a href="paletas.php" class="nav-link <?= ($currentPage === 'paletas') ? 'active' : '' ?>" <?= ($currentPage === 'paletas') ? 'aria-current="page"' : '' ?>>Paletas</a>
                <a href="herramientas.php" class="nav-link <?= ($currentPage === 'herramientas') ? 'active' : '' ?>" <?= ($currentPage === 'herramientas') ? 'aria-current="page"' : '' ?>>Herramientas</a>
                <a href="snipets.php" class="nav-link <?= ($currentPage === 'snipets') ? 'active' : '' ?>" <?= ($currentPage === 'snipets') ? 'aria-current="page"' : '' ?>>Snippets</a>
            </nav>
            <button type="button" id="themeToggleBtn" class="theme-toggle-btn" aria-label="Cambiar a modo oscuro" title="Cambiar a modo oscuro">
                <span class="theme-icon icon-sun" aria-hidden="true">☀️</span>
                <span class="theme-icon icon-moon" aria-hidden="true">🌙</span>
            </button>

            <!-- Desplegable de L  <!-- Toast de Notificación Global Compartido -->
    <div id="toastNotification" class="toast-notification" role="status" aria-live="polite"></div>

    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
ogin en la esquina superior derecha -->
            <div class="login-dropdown" id="loginDropdown">
                <button type="button" id="loginToggleBtn" class="login-toggle-btn" aria-haspopup="true" aria-expanded="false" title="Iniciar sesión" aria-label="Cuenta de usuario">
                    <span class="user-avatar-icon" id="userAvatarIcon" aria-hidden="true">👤</span>
                </button>

                <div class="login-menu-card" id="loginMenu" role="dialog" aria-label="Iniciar Sesión">
                    <!-- Vista Formulario de Login -->
                    <form id="loginForm" class="login-form">
                        <div class="login-menu-header">
                            <span class="login-menu-title">Iniciar Sesión</span>
                        </div>  <!-- Toast de Notificación Global Compartido -->
    <div id="toastNotification" class="toast-notification" role="status" aria-live="polite"></div>

    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>


                        <div id="loginError" class="login-error-msg" role="alert" style="display: none;"></div>

                        <div class="login-field-group">
                            <label for="loginUsername" class="login-label">Usuario</label>
                            <input type="text" id="loginUsername" name="username" class="login-input"  autocomplete="username" required>
                        </div>

                        <div class="login-field-group">
                            <label for="loginPassword" class="login-label">Contraseña</label>
                            <input type="password" id="loginPassword" name="password" class="login-input"  autocomplete="current-password" required>
                        </div>

                        <button type="submit" id="loginSubmitBtn" class="login-submit-btn">Entrar</button>
                    </form>

                    <!-- Vista Usuario Conectado -->
                    <div id="loginLoggedView" class="login-logged-view" style="display: none;">
                        <div class="login-logged-header">
                            <span class="logged-avatar">👤</span>
                            <div class="logged-user-details">
                                <span class="logged-welcome">Conectado como</span>
                                <strong id="loggedUsername" class="logged-username">admin</strong>
                            </div>
                        </div>  <!-- Toast de Notificación Global Compartido -->
    <div id="toastNotification" class="toast-notification" role="status" aria-live="polite"></div>

    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>

                        <button type="button" id="logoutBtn" class="logout-btn">Cerrar Sesión</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
