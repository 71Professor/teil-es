<?php
// api.php - REST API für QR Code Management
header('Content-Type: application/json');
require_once '../config.php';
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $db = getDB();
    
    switch ($action) {
        case 'list':
            // Alle QR Codes auflisten
            $stmt = $db->query("SELECT * FROM qr_codes ORDER BY erstellt_am DESC");
            $codes = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $codes]);
            break;
            
        case 'get':
            // Einzelnen QR Code abrufen
            $id = $_GET['id'] ?? 0;
            $stmt = $db->prepare("SELECT * FROM qr_codes WHERE id = ?");
            $stmt->execute([$id]);
            $code = $stmt->fetch();
            echo json_encode(['success' => true, 'data' => $code]);
            break;
            
        case 'create':
            // Neuen QR Code erstellen
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Shortcode generieren falls leer
            if (empty($data['shortcode'])) {
                $data['shortcode'] = substr(md5(uniqid(rand(), true)), 0, 5);
            }
            
            $stmt = $db->prepare("INSERT INTO qr_codes (shortcode, ziel_url, titel, beschreibung) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['shortcode'],
                $data['ziel_url'],
                $data['titel'] ?? '',
                $data['beschreibung'] ?? ''
            ]);
            
            $newId = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM qr_codes WHERE id = ?");
            $stmt->execute([$newId]);
            $newCode = $stmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $newCode]);
            break;
            
        case 'update':
            // QR Code aktualisieren
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            
            $stmt = $db->prepare("UPDATE qr_codes SET ziel_url = ?, titel = ?, beschreibung = ? WHERE id = ?");
            $stmt->execute([
                $data['ziel_url'],
                $data['titel'] ?? '',
                $data['beschreibung'] ?? '',
                $id
            ]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'toggle':
            // QR Code aktivieren/deaktivieren
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            $stmt = $db->prepare("UPDATE qr_codes SET aktiv = NOT aktiv WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;
            
        case 'delete':
            // QR Code löschen
            $id = $_POST['id'] ?? $_GET['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM qr_codes WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;
            
        case 'stats':
            // Statistiken für einen QR Code
            $id = $_GET['id'] ?? 0;
            
            $stmt = $db->prepare("
                SELECT 
                    DATE(scan_timestamp) as datum,
                    COUNT(*) as anzahl
                FROM qr_scans 
                WHERE qr_code_id = ?
                GROUP BY DATE(scan_timestamp)
                ORDER BY datum DESC
                LIMIT 30
            ");
            $stmt->execute([$id]);
            $stats = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unbekannte Aktion']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
