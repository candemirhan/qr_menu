<?php
require_once 'config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple hardcoded auth for demo/setup (Should use DB in production)
    // Username: admin, Password: admin123
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Geçersiz kullanıcı adı veya şifre.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Yönetim Paneli</title>
    <script src="assets/js/tailwind.js"></script>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Yönetim Paneli</h1>
            <p class="text-gray-500 mt-2">Lütfen kimliğinizi doğrulayın.</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md mb-6 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kullanıcı Adı</label>
                <input type="text" name="username" required
                    class="w-full border-gray-300 rounded-md shadow-sm border p-3 focus:ring-black focus:border-black"
                    placeholder="admin">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
                <input type="password" name="password" required
                    class="w-full border-gray-300 rounded-md shadow-sm border p-3 focus:ring-black focus:border-black"
                    placeholder="••••••••">
            </div>
            <button type="submit"
                class="w-full bg-black text-white font-semibold py-3 rounded-md hover:bg-gray-800 transition-colors">
                Giriş Yap
            </button>
        </form>

        <p class="mt-8 text-center text-xs text-gray-400">
            Powered by <span class="font-semibold text-gray-600">MCD Yazılım</span> © 2026
        </p>
    </div>
</body>

</html>