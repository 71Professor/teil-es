<?php
require_once '../config.php';

// Setup nur mit gültigem Token zugänglich (SETUP_TOKEN in .env setzen)
$setupToken = $_ENV['SETUP_TOKEN'] ?? '';
if ($setupToken === '' || ($_GET['token'] ?? '') !== $setupToken) {
    http_response_code(404);
    exit('Not found.');
}

// Wenn bereits ein User existiert, zum Login weiterleiten
if (!needsSetup()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = false;

// Setup-Formular verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validierung
    if (empty($username)) {
        $error = 'Bitte Benutzernamen eingeben';
    } elseif (strlen($username) < 3) {
        $error = 'Benutzername muss mindestens 3 Zeichen lang sein';
    } elseif (empty($password)) {
        $error = 'Bitte Passwort eingeben';
    } elseif (strlen($password) < 8) {
        $error = 'Passwort muss mindestens 8 Zeichen lang sein';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwörter stimmen nicht überein';
    } else {
        try {
            $db = getDB();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
            
            if ($stmt->execute([$username, $passwordHash])) {
                $success = true;
                // Automatisch einloggen
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['username'] = $username;
                session_regenerate_id(true);
                
                // Nach 2 Sekunden zum Admin-Bereich weiterleiten
                header('refresh:2;url=index.php');
            } else {
                $error = 'Fehler beim Erstellen des Accounts';
            }
        } catch (Exception $e) {
            $error = 'Datenbankfehler: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - QR Code Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        
        <?php if ($success): ?>
            <!-- Erfolgs-Meldung -->
            <div class="text-center">
                <div class="inline-block p-4 bg-green-100 rounded-full mb-4">
                    <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Setup erfolgreich!</h1>
                <p class="text-gray-600 mb-4">Dein Admin-Account wurde erstellt.</p>
                <p class="text-sm text-gray-500">Du wirst automatisch weitergeleitet...</p>
                <div class="mt-6">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                </div>
            </div>
        <?php else: ?>
            <!-- Setup-Formular -->
            <div class="text-center mb-8">
                <div class="inline-block p-4 bg-indigo-100 rounded-full mb-4">
                    <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Willkommen! 👋</h1>
                <p class="text-gray-600 mt-2">Erstelle deinen Admin-Account</p>
            </div>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Benutzername
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        autofocus
                        minlength="3"
                        maxlength="50"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="z.B. admin"
                    >
                    <p class="mt-1 text-sm text-gray-500">Mindestens 3 Zeichen</p>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Passwort
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="Sicheres Passwort wählen"
                    >
                    <p class="mt-1 text-sm text-gray-500">Mindestens 8 Zeichen</p>
                </div>
                
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                        Passwort bestätigen
                    </label>
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        placeholder="Passwort wiederholen"
                    >
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Sicherheitshinweise:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Verwende ein starkes, einzigartiges Passwort</li>
                                <li>Notiere dir die Zugangsdaten sicher</li>
                                <li>Du kannst das Passwort später ändern</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition transform hover:scale-105"
                >
                    Admin-Account erstellen
                </button>
            </form>
        <?php endif; ?>
        
    </div>
</body>
</html>
