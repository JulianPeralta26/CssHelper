<?php
// backend/favorites.php
// API para gestionar fuentes y colores favoritos vinculados a la base de datos y al usuario en sesión.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Validar que el usuario esté logueado
$username = $_SESSION['logged_user'] ?? '';
if (empty($username)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Inicia sesión para guardar tus favoritos.', 
        'require_login' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener el ID del usuario en la base de datos
$userId = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $userId = $stmt->fetchColumn() ?: null;

        // Si el usuario existe en sesión pero no en la tabla users, crearlo para garantizar integridad referencial
        if (!$userId) {
            $defaultHash = password_hash('123', PASSWORD_BCRYPT);
            $stmtNewUser = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmtNewUser->execute(['username' => $username, 'password' => $defaultHash]);
            $userId = (int)$pdo->lastInsertId();
        }
    } catch (\Throwable $e) {
        error_log('Favorites user lookup: ' . $e->getMessage());
    }
}
if (!$userId) {
    $userId = 1;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$rawInput = file_get_contents('php://input');
$input = [];
if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}
if (empty($action) && isset($input['action'])) {
    $action = $input['action'];
}

// Archivo JSON de respaldo si MySQL no está encendido en local o si falla la conexión
$fallbackFile = __DIR__ . '/favorites_storage.json';
function getFallbackData($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) return $data;
    }
    return ['fonts' => [], 'colors' => []];
}
function saveFallbackData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

switch ($action) {
    // ==========================================
    // FUENTES FAVORITAS
    // ==========================================
    case 'get_fonts':
        $favFonts = [];
        $gotFromDb = false;

        if ($pdo && $userId) {
            try {
                $stmt = $pdo->prepare("
                    SELECT f.id, f.name 
                    FROM favorite_fonts ff
                    JOIN fonts f ON ff.font_id = f.id
                    WHERE ff.user_id = :user_id
                    ORDER BY f.name ASC
                ");
                $stmt->execute(['user_id' => $userId]);
                $favFonts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $gotFromDb = true;
            } catch (\Throwable $e) {
                error_log('Error getting fav fonts: ' . $e->getMessage());
            }
        }

        if (!$gotFromDb) {
            $data = getFallbackData($fallbackFile);
            $favFonts = $data['fonts'][$username] ?? [];
        }

        echo json_encode(['favorites' => $favFonts], JSON_UNESCAPED_UNICODE);
        break;

    case 'toggle_font':
        $fontName = trim($input['font_name'] ?? $_POST['font_name'] ?? '');
        if ($fontName === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre de fuente no especificado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $isFavorite = false;
        $savedInDb = false;

        if ($pdo && $userId) {
            try {
                // Obtener ID de la fuente
                $stmtFont = $pdo->prepare("SELECT id FROM fonts WHERE name = :name LIMIT 1");
                $stmtFont->execute(['name' => $fontName]);
                $fontId = $stmtFont->fetchColumn();

                // Si la fuente no está en la tabla, insertarla desde los datos por defecto
                if (!$fontId) {
                    foreach (cssHelperDefaultFonts() as $df) {
                        if (strcasecmp($df['name'], $fontName) === 0) {
                            $insFont = $pdo->prepare("
                                INSERT INTO fonts (name, link_code, import_code, css_rule) 
                                VALUES (:name, :link_code, :import_code, :css_rule)
                            ");
                            $insFont->execute([
                                'name' => $df['name'],
                                'link_code' => $df['link_code'],
                                'import_code' => $df['import_code'],
                                'css_rule' => $df['css_rule']
                            ]);
                            $fontId = (int)$pdo->lastInsertId();
                            break;
                        }
                    }
                }

                if ($fontId) {
                    // Verificar si ya existe en favoritos
                    $check = $pdo->prepare("SELECT id FROM favorite_fonts WHERE user_id = :user_id AND font_id = :font_id LIMIT 1");
                    $check->execute(['user_id' => $userId, 'font_id' => $fontId]);
                    $existingId = $check->fetchColumn();

                    if ($existingId) {
                        $del = $pdo->prepare("DELETE FROM favorite_fonts WHERE id = :id");
                        $del->execute(['id' => $existingId]);
                        $isFavorite = false;
                    } else {
                        $ins = $pdo->prepare("INSERT INTO favorite_fonts (user_id, font_id) VALUES (:user_id, :font_id)");
                        $ins->execute(['user_id' => $userId, 'font_id' => $fontId]);
                        $isFavorite = true;
                    }
                    $savedInDb = true;
                }
            } catch (\Throwable $e) {
                error_log('Error toggling font: ' . $e->getMessage());
                $savedInDb = false;
            }
        }

        // Si MySQL no está activo o falló la consulta, sincronizar con el almacenamiento JSON de respaldo
        if (!$savedInDb) {
            $data = getFallbackData($fallbackFile);
            if (!isset($data['fonts'][$username])) {
                $data['fonts'][$username] = [];
            }
            $index = -1;
            foreach ($data['fonts'][$username] as $i => $item) {
                if (strcasecmp($item['name'], $fontName) === 0) {
                    $index = $i;
                    break;
                }
            }
            if ($index >= 0) {
                array_splice($data['fonts'][$username], $index, 1);
                $isFavorite = false;
            } else {
                $data['fonts'][$username][] = ['id' => rand(100, 999), 'name' => $fontName];
                $isFavorite = true;
            }
            saveFallbackData($fallbackFile, $data);
        }

        echo json_encode([
            'success' => true,
            'font_name' => $fontName,
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? "«{$fontName}» guardada en favoritos." : "«{$fontName}» eliminada de favoritos."
        ], JSON_UNESCAPED_UNICODE);
        break;

    // ==========================================
    // COLORES FAVORITOS
    // ==========================================
    case 'get_colors':
        $favColors = [];
        $gotColorsFromDb = false;

        if ($pdo && $userId) {
            try {
                $stmt = $pdo->prepare("
                    SELECT id, color_hex, color_rgb, color_hsl, color_name 
                    FROM favorite_colors 
                    WHERE user_id = :user_id 
                    ORDER BY id DESC 
                    LIMIT 20
                ");
                $stmt->execute(['user_id' => $userId]);
                $favColors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $gotColorsFromDb = true;
            } catch (\Throwable $e) {
                error_log('Error getting fav colors: ' . $e->getMessage());
            }
        }

        if (!$gotColorsFromDb) {
            $data = getFallbackData($fallbackFile);
            $favColors = $data['colors'][$username] ?? [];
        }

        echo json_encode(['favorites' => $favColors], JSON_UNESCAPED_UNICODE);
        break;

    case 'save_color':
        $hex = strtoupper(trim($input['hex'] ?? $_POST['hex'] ?? ''));
        $rgb = trim($input['rgb'] ?? $_POST['rgb'] ?? '');
        $hsl = trim($input['hsl'] ?? $_POST['hsl'] ?? '');
        $name = trim($input['name'] ?? $_POST['name'] ?? '');

        if (!preg_match('/^#[0-9A-F]{6}$/i', $hex)) {
            http_response_code(400);
            echo json_encode(['error' => 'Código HEX inválido.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $newId = null;
        $savedColorInDb = false;

        if ($pdo && $userId) {
            try {
                $check = $pdo->prepare("SELECT id FROM favorite_colors WHERE user_id = :user_id AND color_hex = :hex LIMIT 1");
                $check->execute(['user_id' => $userId, 'hex' => $hex]);
                $existing = $check->fetchColumn();

                if (!$existing) {
                    $stmt = $pdo->prepare("
                        INSERT INTO favorite_colors (user_id, color_hex, color_rgb, color_hsl, color_name) 
                        VALUES (:user_id, :hex, :rgb, :hsl, :name)
                    ");
                    $stmt->execute([
                        'user_id' => $userId,
                        'hex' => $hex,
                        'rgb' => $rgb,
                        'hsl' => $hsl,
                        'name' => $name ?: $hex
                    ]);
                    $newId = (int)$pdo->lastInsertId();
                } else {
                    $newId = (int)$existing;
                }
                $savedColorInDb = true;
            } catch (\Throwable $e) {
                error_log('Error saving color: ' . $e->getMessage());
                $savedColorInDb = false;
            }
        }

        if (!$savedColorInDb) {
            $data = getFallbackData($fallbackFile);
            if (!isset($data['colors'][$username])) {
                $data['colors'][$username] = [];
            }
            $exists = false;
            foreach ($data['colors'][$username] as $item) {
                if (strcasecmp($item['color_hex'], $hex) === 0) {
                    $exists = true;
                    $newId = $item['id'];
                    break;
                }
            }
            if (!$exists) {
                $newId = rand(100, 999);
                array_unshift($data['colors'][$username], [
                    'id' => $newId,
                    'color_hex' => $hex,
                    'color_rgb' => $rgb,
                    'color_hsl' => $hsl,
                    'color_name' => $name ?: $hex
                ]);
                saveFallbackData($fallbackFile, $data);
            }
        }

        echo json_encode([
            'success' => true,
            'id' => $newId,
            'hex' => $hex,
            'message' => "Color {$hex} guardado en tus favoritos."
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'delete_color':
        $colorId = (int)($input['id'] ?? $_POST['id'] ?? 0);
        $hex = trim($input['hex'] ?? $_POST['hex'] ?? '');

        if ($colorId <= 0 && empty($hex)) {
            http_response_code(400);
            echo json_encode(['error' => 'Identificador de color no especificado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $deletedInDb = false;
        if ($pdo && $userId) {
            try {
                if ($colorId > 0) {
                    $stmt = $pdo->prepare("DELETE FROM favorite_colors WHERE id = :id AND user_id = :user_id");
                    $stmt->execute(['id' => $colorId, 'user_id' => $userId]);
                } else {
                    $stmt = $pdo->prepare("DELETE FROM favorite_colors WHERE color_hex = :hex AND user_id = :user_id");
                    $stmt->execute(['hex' => $hex, 'user_id' => $userId]);
                }
                $deletedInDb = true;
            } catch (\Throwable $e) {
                error_log('Error deleting fav color: ' . $e->getMessage());
            }
        }

        $data = getFallbackData($fallbackFile);
        if (isset($data['colors'][$username])) {
            $data['colors'][$username] = array_values(array_filter($data['colors'][$username], function($item) use ($colorId, $hex) {
                if ($colorId > 0 && $item['id'] == $colorId) return false;
                if (!empty($hex) && strcasecmp($item['color_hex'], $hex) === 0) return false;
                return true;
            }));
            saveFallbackData($fallbackFile, $data);
        }

        echo json_encode(['success' => true, 'message' => 'Color eliminado de favoritos.'], JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida.'], JSON_UNESCAPED_UNICODE);
        break;
}
