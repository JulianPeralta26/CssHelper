<?php
// backend/get_all_fonts.php
// API: devuelve la lista de nombres de fuentes disponibles (JSON).
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/db.php';

$fonts = [];
if ($pdo) {
    try {
        $dbFonts = $pdo->query("SELECT name FROM fonts ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
        if (is_array($dbFonts)) {
            $fonts = $dbFonts;
        }
    } catch (\Throwable $e) {
        error_log('CSS Helper get_all_fonts: ' . $e->getMessage());
    }
}

// Fusionar con el catálogo por defecto para garantizar que siempre estén disponibles todas las fuentes
$defaultNames = array_column(cssHelperDefaultFonts(), 'name');
if (empty($fonts)) {
    $fonts = $defaultNames;
} else {
    $fonts = array_values(array_unique(array_merge($fonts, $defaultNames)));
}

sort($fonts, SORT_NATURAL | SORT_FLAG_CASE);

echo json_encode($fonts, JSON_UNESCAPED_UNICODE);
