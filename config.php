<?php
// Konfiguration für teil-es.de
// Zugangsdaten werden aus der .env-Datei geladen – NICHT hier eintragen!

// .env-Datei einlesen
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Erst eine Ebene oberhalb des Document Root versuchen (sicherer),
// dann als Fallback das aktuelle Verzeichnis
$envFile = dirname($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) . '/.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/.env';
}
loadEnv($envFile);

// Konfigurationskonstanten aus .env befüllen
define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME']    ?? '');
define('DB_USER',    $_ENV['DB_USER']    ?? '');
define('DB_PASS',    $_ENV['DB_PASS']    ?? '');
define('DB_CHARSET', 'utf8mb4');
define('BASE_URL',   rtrim($_ENV['BASE_URL'] ?? 'https://teil-es.de', '/'));

// Session-Sicherheit
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Nur über HTTPS

// Fehler-Reporting – auf Production deaktiviert
error_reporting(0);
ini_set('display_errors', 0);

// Datenbankverbindung (Singleton)
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die("Datenbankverbindung fehlgeschlagen.");
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
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        session_regenerate_id(true);
        return true;
    }

    return false;
}

// Logout durchführen
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
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
        $db     = getDB();
        $stmt   = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        return $result['count'] == 0;
    } catch (PDOException $e) {
        return true; // Tabelle existiert noch nicht → Setup nötig
    }
}

// Passwort ändern
function changePassword($userId, $newPassword) {
    try {
        $db   = getDB();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $userId]);
    } catch (Exception $e) {
        return false;
    }
}
