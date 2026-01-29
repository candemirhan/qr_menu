<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'digital_menu');
define('DB_USER', 'root');
define('DB_PASS', '');

// Attempt to connect to MySQL
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Show a styled error page if connection fails
    ?>
    <!DOCTYPE html>
    <html lang="tr">

    <head>
        <meta charset="UTF-8">
        <script src="https://cdn.tailwindcss.com"></script>
        <title>Bağlantı Hatası</title>
    </head>

    <body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-red-100 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-2">Veritabanı Bağlantı Hatası</h1>
            <p class="text-gray-500 text-sm mb-6">Sunucu ile bağlantı kurulamadı. Lütfen <code
                    class="bg-gray-100 px-1 rounded">config.php</code> dosyasındaki bilgilerin doğruluğundan emin olun.</p>
            <div class="bg-gray-50 rounded-lg p-3 text-left">
                <p class="text-xs font-mono text-gray-400 mb-1">Hata Detayı:</p>
                <p class="text-xs font-mono text-gray-600 break-words"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
        </div>
    </body>

    </html>
    <?php
    die();
}

// Global Variables
$baseUrl = ''; // If in a subfolder, e.g., '/menu'

// Footer Protection Logic
ob_start(function ($buffer) {
    // Only check if it looks like an HTML page
    if (strpos($buffer, '<body') !== false) {
        // Robust check for "MCD Yazılım" or "MCD Yazilim" with any whitespace/tags between them
        $pattern = '/MCD\s+Yazılım/ui';
        if (!preg_match($pattern, $buffer) && stripos($buffer, 'MCD Yazilim') === false) {
            return '<!DOCTYPE html>
            <html lang="tr">
            <head>
                <meta charset="UTF-8">
                <title>Sistem Hatası</title>
                <style>
                    body { background: black; color: #ff0000; height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; font-family: sans-serif; text-align: center; }
                    .warning { font-size: 5vw; font-weight: 900; padding: 2rem; line-height: 1.2; text-transform: uppercase; }
                </style>
            </head>
            <body>
                <div class="warning">EMEĞE SAYGI LÜTFEN FOOTER BİLGİLERİNİ DEĞİŞTİRMEYİN!</div>
            </body>
            </html>';
        }
    }
    return $buffer;
});

// Settings Helpers
function getSetting($key, $default = '')
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

function updateSetting($key, $value)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
    return $stmt->execute([$key, $value]);
}
?>