<?php
// redirect.php - Hauptscript für QR Code Weiterleitungen
require_once 'config.php';

// Shortcode aus URL extrahieren
$shortcode = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($shortcode)) {
    header('Location: admin/');
    exit;
}

try {
    $db = getDB();
    
    // QR Code in Datenbank suchen
    $stmt = $db->prepare("SELECT * FROM qr_codes WHERE shortcode = ? AND aktiv = 1");
    $stmt->execute([$shortcode]);
    $qr = $stmt->fetch();
    
    if (!$qr) {
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code nicht gefunden</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md">
        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">QR Code nicht gefunden</h1>
        <p class="text-gray-600">Der angeforderte QR Code existiert nicht oder wurde deaktiviert.</p>
    </div>
</body>
</html>';
        exit;
    }
    
    // Scan tracken
    $stmt = $db->prepare("INSERT INTO qr_scans (qr_code_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([
        $qr['id'],
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    // Scan-Counter erhöhen
    $stmt = $db->prepare("UPDATE qr_codes SET scans = scans + 1 WHERE id = ?");
    $stmt->execute([$qr['id']]);
    
    // Weiterleitung zur Ziel-URL
    header('Location: ' . $qr['ziel_url'], true, 302);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fehler</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md">
        <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Ein Fehler ist aufgetreten</h1>
        <p class="text-gray-600">Bitte versuche es später erneut.</p>
    </div>
</body>
</html>';
    exit;
}
