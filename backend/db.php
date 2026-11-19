<?php
// backend/db.php
// Conexión PDO con auto-sincronización de esquema y catálogo de fuentes.
// Si MySQL no está disponible, $pdo queda en null y la suite opera con datos de respaldo (fonts_data.php y favorites_storage.json).
require_once __DIR__ . '/fonts_data.php';

// Valores por defecto de XAMPP / MariaDB; ajustables por variables de entorno.
$host = getenv('CSSHELPER_DB_HOST') ?: 'localhost';
$port = getenv('CSSHELPER_DB_PORT') ?: null;
$socket = getenv('CSSHELPER_DB_SOCKET') ?: null;
$user = getenv('CSSHELPER_DB_USER') ?: 'root';
$pass = getenv('CSSHELPER_DB_PASS') ?: '';
$charset = 'utf8mb4';

$pdo = null;

try {
    $dsn = "mysql:host=$host;charset=$charset";
    if (!empty($port)) {
        $dsn .= ";port=$port";
    }
    if (!empty($socket)) {
        $dsn .= ";unix_socket=$socket";
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Asegurar que exista la base de datos y seleccionarla
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `css_helper` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `css_helper`");

    // 1. Tabla de usuarios
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Asegurar que exista el usuario admin (contraseña: 123)
    $stmtCheckAdmin = $pdo->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    $stmtCheckAdmin->execute();
    if (!$stmtCheckAdmin->fetchColumn()) {
        $adminHash = password_hash('123', PASSWORD_BCRYPT);
        $stmtInsertAdmin = $pdo->prepare("INSERT INTO users (username, password) VALUES ('admin', :password)");
        $stmtInsertAdmin->execute(['password' => $adminHash]);
    }

    // 2. Tabla del catálogo de fuentes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fonts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            link_code TEXT NOT NULL,
            import_code TEXT NOT NULL,
            css_rule VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Sincronizar automáticamente todas las fuentes predeterminadas en la tabla
    $stmtSyncFont = $pdo->prepare("
        INSERT INTO fonts (name, link_code, import_code, css_rule) 
        VALUES (:name, :link_code, :import_code, :css_rule)
        ON DUPLICATE KEY UPDATE 
            link_code = VALUES(link_code),
            import_code = VALUES(import_code),
            css_rule = VALUES(css_rule)
    ");
    foreach (cssHelperDefaultFonts() as $f) {
        $stmtSyncFont->execute([
            'name' => $f['name'],
            'link_code' => $f['link_code'],
            'import_code' => $f['import_code'],
            'css_rule' => $f['css_rule']
        ]);
    }

    // 3. Tabla de fuentes favoritas (relación N:M users <-> fonts)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorite_fonts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            font_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_favfonts_user 
                FOREIGN KEY (user_id) REFERENCES users(id) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_favfonts_font 
                FOREIGN KEY (font_id) REFERENCES fonts(id) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE KEY uq_user_font (user_id, font_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // 4. Tabla de colores favoritos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorite_colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            color_hex VARCHAR(7) NOT NULL,
            color_rgb VARCHAR(30) NULL,
            color_hsl VARCHAR(30) NULL,
            color_name VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_favcolors_user 
                FOREIGN KEY (user_id) REFERENCES users(id) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX idx_user_colors (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

} catch (\Throwable $e) {
    // Registro técnico silencioso en el log del servidor
    error_log('CSS Helper: base de datos no disponible, se usa la lista de respaldo. ' . $e->getMessage());
    $pdo = null;
}
