<?php
// Datenbank-Konfiguration für all-inkl.com
// WICHTIG: Diese Werte mit deinen echten Zugangsdaten ersetzen!

define('DB_HOST', 'localhost');  // Bei all-inkl meist 'localhost'
define('DB_NAME', 'deinedatenbank');  // Deine Datenbank bei all-inkl
define('DB_USER', 'deinusername');     // Dein DB-Username
define('DB_PASS', 'deinpasswort');     // Dein DB-Passwort
define('DB_CHARSET', 'utf8mb4');

// Basis-URL deiner Installation (OHNE trailing slash!)
define('BASE_URL', 'https://deine-domain.de/qr-dynamisch');

// Session-Sicherheit
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Fehler-Reporting (auf Production auf 0 setzen!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datenbankverbindung herstellen
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Session-Sicherheit
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Nur über HTTPS

// Fehler-Reporting (auf Production auf 0 setzen!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datenbankverbindung herstellen
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

session_start();

// Login-Status prüfen
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Login durchführen
function login($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Session-Regeneration gegen Session-Fixation
        session_regenerate_id(true);
        return true;
    }
    
    return false;
}

// Logout durchführen
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Login erzwingen
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Prüfen ob Setup notwendig ist
function needsSetup() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        return $result['count'] == 0;
    } catch (PDOException $e) {
        // Tabelle existiert wahrscheinlich nicht = Setup nötig
        return true;
    }
}

// Passwort ändern
function changePassword($userId, $newPassword) {
    try {
        $db = getDB();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $userId]);
    } catch (Exception $e) {
        return false;
    }
}
