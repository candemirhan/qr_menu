<?php
require_once 'config.php';
session_start();

// Auth check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// --- Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Category CRUD
    if ($action === 'save_category') {
        $name = $_POST['name'] ?? '';
        $id = $_POST['id'] ?? null;
        if ($name) {
            $sort_order = $_POST['sort_order'] ?? 0;
            if ($id) {
                $pdo->prepare("UPDATE categories SET name = ?, sort_order = ? WHERE id = ?")->execute([$name, $sort_order, $id]);
            } else {
                $pdo->prepare("INSERT INTO categories (name, sort_order) VALUES (?, ?)")->execute([$name, $sort_order]);
            }
            $message = 'Kategori kaydedildi.';
        }
    } elseif ($action === 'delete_category') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            $message = 'Kategori silindi.';
        }
    }

    // Product CRUD
    elseif ($action === 'save_product') {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $price = $_POST['price'] ?? 0;
        $description = $_POST['description'] ?? '';
        $old_image = $_POST['old_image'] ?? '';

        $image_path = $old_image;

        // Handle File Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $upload_file = 'uploads/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                $image_path = $upload_file;
            }
        }

        if ($name && $category_id) {
            $sort_order = $_POST['sort_order'] ?? 0;
            if ($id) {
                $pdo->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, description = ?, image = ?, sort_order = ? WHERE id = ?")
                    ->execute([$category_id, $name, $price, $description, $image_path, $sort_order, $id]);
            } else {
                $pdo->prepare("INSERT INTO products (category_id, name, price, description, image, sort_order) VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([$category_id, $name, $price, $description, $image_path, $sort_order]);
            }
            $message = 'Ürün kaydedildi.';
        }
    } elseif ($action === 'delete_product') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $message = 'Ürün silindi.';
        }
    }

    // Delete Feedback
    elseif ($action === 'delete_feedback') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
            $message = 'Geri bildirim silindi.';
        }
    }

    // Settings Update
    elseif ($action === 'save_settings') {
        $restaurantName = $_POST['restaurantName'] ?? '';
        $welcomeTitle = $_POST['welcomeTitle'] ?? '';
        $welcomeDescription = $_POST['welcomeDescription'] ?? '';
        $instagramUrl = $_POST['instagramUrl'] ?? '';
        $old_home_image = $_POST['old_home_image'] ?? '';

        $home_image_path = $old_home_image;

        // Handle Home Image Upload
        if (isset($_FILES['home_image']) && $_FILES['home_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['home_image']['name'], PATHINFO_EXTENSION);
            $filename = 'home_' . time() . '.' . $ext;
            $upload_file = 'uploads/' . $filename;
            if (move_uploaded_file($_FILES['home_image']['tmp_name'], $upload_file)) {
                $home_image_path = $upload_file;
            }
        }

        updateSetting('restaurantName', $restaurantName);
        updateSetting('welcomeTitle', $welcomeTitle);
        updateSetting('welcomeDescription', $welcomeDescription);
        updateSetting('instagramUrl', $instagramUrl);
        updateSetting('homeImage', $home_image_path);
        $message = 'Ayarlar güncellendi.';
    }
}

// Fetch Data for Display
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.sort_order ASC, p.id DESC")->fetchAll();
$feedbacks = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC")->fetchAll();
$restaurantName = getSetting('restaurantName', 'Digital Menu');
$welcomeTitle = getSetting('welcomeTitle', 'Hoş Geldiniz');
$welcomeDescription = getSetting('welcomeDescription', 'En taze lezzetlerimizi keşfetmek için kategorilere göz atın.');
$instagramUrl = getSetting('instagramUrl', '');
$homeImage = getSetting('homeImage', '');
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli</title>
    <script src="assets/js/tailwind.js"></script>
    <script src="assets/js/lucide.js"></script>
    <script defer src="assets/js/alpine.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans"
    x-data="{ activeTab: 'categories', editId: null, editData: {}, isMobileMenuOpen: false }">

    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Brand & Desktop Menu -->
                <div class="flex items-center">
                    <h1 class="text-lg md:text-xl font-bold text-gray-900 shrink-0">Yönetim Paneli</h1>
                    <div class="hidden md:ml-10 md:flex md:space-x-4">
                        <button @click="activeTab = 'categories'; editId = null"
                            :class="activeTab === 'categories' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-100'"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors">Kategoriler</button>
                        <button @click="activeTab = 'products'; editId = null"
                            :class="activeTab === 'products' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-100'"
                            class="px-3 py-2 rounded-md text-sm font-medium transition-colors">Ürünler</button>
                        <button @click="activeTab = 'feedbacks'; editId = null;"
                            :class="activeTab === 'feedbacks' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center">
                            Geri Bildirimler
                            <?php if (count($feedbacks) > 0): ?>
                                <span
                                    class="ml-2 bg-rose-500 text-white text-[10px] px-1.5 py-0.5 rounded-full"><?php echo count($feedbacks); ?></span>
                            <?php endif; ?>
                        </button>
                        <button @click="activeTab = 'settings'; editId = null;"
                            :class="activeTab === 'settings' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center">
                            Ayarlar
                        </button>
                    </div>
                </div>

                <!-- Desktop Right Icons & Mobile Hamburger -->
                <div class="flex items-center space-x-2 md:space-x-4">
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="index.php" target="_blank" class="text-gray-500 hover:text-black transition-colors"
                            title="Siteyi Görüntüle">
                            <i data-lucide="external-link" class="w-5 h-5"></i>
                        </a>
                        <a href="logout.php" class="text-gray-500 hover:text-red-600 transition-colors">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </a>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="md:hidden p-2 rounded-md text-gray-500 hover:text-black hover:bg-gray-100 transition-colors">
                        <i data-lucide="menu" class="w-6 h-6" x-show="!isMobileMenuOpen"></i>
                        <i data-lucide="x" class="w-6 h-6" x-show="isMobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div x-show="isMobileMenuOpen" x-cloak class="md:hidden border-t border-gray-100 bg-white shadow-lg">
            <div class="px-4 py-4 space-y-2">
                <button @click="activeTab = 'categories'; editId = null; isMobileMenuOpen = false"
                    :class="activeTab === 'categories' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-50'"
                    class="w-full text-left px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center">
                    <i data-lucide="layout-grid" class="w-4 h-4 mr-3"></i> Kategoriler
                </button>
                <button @click="activeTab = 'products'; editId = null; isMobileMenuOpen = false"
                    :class="activeTab === 'products' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-50'"
                    class="w-full text-left px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center">
                    <i data-lucide="package" class="w-4 h-4 mr-3"></i> Ürünler
                </button>
                <button @click="activeTab = 'feedbacks'; editId = null; isMobileMenuOpen = false"
                    :class="activeTab === 'feedbacks' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-50'"
                    class="w-full text-left px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center relative">
                    <i data-lucide="message-square" class="w-4 h-4 mr-3"></i> Geri Bildirimler
                    <?php if (count($feedbacks) > 0): ?>
                        <span
                            class="absolute right-4 top-1/2 -translate-y-1/2 bg-rose-500 text-white text-[10px] px-1.5 py-0.5 rounded-full">
                            <?php echo count($feedbacks); ?>
                        </span>
                    <?php endif; ?>
                </button>
                <button @click="activeTab = 'settings'; editId = null; isMobileMenuOpen = false"
                    :class="activeTab === 'settings' ? 'bg-black text-white' : 'text-gray-900 hover:bg-gray-50'"
                    class="w-full text-left px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center">
                    <i data-lucide="settings" class="w-4 h-4 mr-3"></i> Ayarlar
                </button>

                <div class="pt-4 mt-4 border-t border-gray-100 flex items-center justify-between px-2">
                    <a href="index.php" target="_blank" class="flex items-center text-gray-500 text-sm font-medium">
                        <i data-lucide="external-link" class="w-4 h-4 mr-2"></i> Siteyi Gör
                    </a>
                    <a href="logout.php" class="flex items-center text-rose-500 text-sm font-medium">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Çıkış Yap
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        <?php if ($message): ?>
            <div
                class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md flex items-center shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Kategoriler Tab -->
        <div x-show="activeTab === 'categories'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Form -->
                <div class="bg-white p-6 shadow rounded-xl border border-gray-100">
                    <h2 class="text-lg font-bold mb-4" x-text="editId ? 'Kategori Düzenle' : 'Yeni Kategori Ekle'"></h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="save_category">
                        <input type="hidden" name="id" :value="editId">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Adı</label>
                            <input type="text" name="name" required :value="editData.name"
                                class="w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-black focus:border-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama (Küçükten
                                Büyüğe)</label>
                            <input type="number" name="sort_order" :value="editData.sort_order || 0"
                                class="w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-black focus:border-black">
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 transition-colors flex items-center">
                                <i data-lucide="plus" class="w-4 h-4 mr-2" x-show="!editId"></i>
                                <span x-text="editId ? 'Güncelle' : 'Ekle'"></span>
                            </button>
                            <button type="button" @click="editId = null; editData = {}" x-show="editId"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">İptal</button>
                        </div>
                    </form>
                </div>
                <!-- Listing -->
                <div class="bg-white shadow rounded-xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20">
                                        Sıra
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ad</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        İşlemler
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($categories as $cat): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $cat['sort_order']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-4 whitespace-nowrap">
                                            <button
                                                @click="editId = <?php echo $cat['id']; ?>; editData = {name: '<?php echo addslashes($cat['name']); ?>', sort_order: '<?php echo $cat['sort_order']; ?>'}"
                                                class="text-blue-600 hover:text-blue-900 inline-block"><i
                                                    data-lucide="edit-2" class="w-4 h-4"></i></button>
                                            <form method="POST" class="inline"
                                                onsubmit="return confirm('Kategoriyi ve içindeki tüm ürünleri silmek istediğinize emin misiniz?')">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900"><i
                                                        data-lucide="trash-2" class="w-4 h-4"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ürünler Tab -->
        <div x-show="activeTab === 'products'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Form -->
                <div class="md:col-span-1 bg-white p-6 shadow rounded-xl border border-gray-100 h-fit sticky top-6">
                    <h2 class="text-lg font-bold mb-4" x-text="editId ? 'Ürün Düzenle' : 'Yeni Ürün Ekle'"></h2>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" value="save_product">
                        <input type="hidden" name="id" :value="editId">
                        <input type="hidden" name="old_image" :value="editData.image">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select name="category_id" required
                                class="w-full border-gray-300 rounded-md shadow-sm border p-2"
                                x-model="editData.category_id">
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ürün Adı</label>
                            <input type="text" name="name" required x-model="editData.name"
                                class="w-full border-gray-300 rounded-md shadow-sm border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fiyat (₺)</label>
                            <input type="number" step="0.01" name="price" required x-model="editData.price"
                                class="w-full border-gray-300 rounded-md shadow-sm border p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                            <textarea name="description" rows="3" x-model="editData.description"
                                class="w-full border-gray-300 rounded-md shadow-sm border p-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sıralama (Küçükten
                                Büyüğe)</label>
                            <input type="number" name="sort_order" x-model="editData.sort_order"
                                class="w-full border-gray-300 rounded-md shadow-sm border p-2 focus:ring-black focus:border-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Görsel</label>
                            <input type="file" name="image" accept="image/*"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-black hover:file:bg-gray-200 transition-colors">
                        </div>

                        <div class="flex space-x-2 pt-2">
                            <button type="submit"
                                class="flex-1 bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 transition-colors">
                                <span x-text="editId ? 'Güncelle' : 'Ekle'"></span>
                            </button>
                            <button type="button" @click="editId = null; editData = {}" x-show="editId"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">İptal</button>
                        </div>
                    </form>
                </div>
                <!-- Listing -->
                <div class="md:col-span-2 bg-white shadow rounded-xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Görsel
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sıra
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün /
                                        Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fiyat
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                        İşlemler
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($products as $prod): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($prod['image']): ?>
                                                <img src="<?php echo $prod['image']; ?>"
                                                    class="h-10 w-10 md:h-12 md:w-12 rounded-lg object-cover border border-gray-100 shadow-sm"
                                                    alt="">
                                            <?php else: ?>
                                                <div
                                                    class="h-10 w-10 md:h-12 md:w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                                    <i data-lucide="image" class="w-4 h-4 text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            <?php echo $prod['sort_order']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                                            <div class="font-bold text-gray-900">
                                                <?php echo htmlspecialchars($prod['name']); ?>
                                            </div>
                                            <div
                                                class="text-gray-500 text-[10px] md:text-xs mt-0.5 uppercase tracking-wider">
                                                <?php echo htmlspecialchars($prod['category_name']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-sm whitespace-nowrap">
                                            <?php echo number_format($prod['price'], 2); ?> ₺
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-4 whitespace-nowrap">
                                            <button
                                                @click="editId = <?php echo $prod['id']; ?>; editData = {name: '<?php echo addslashes($prod['name']); ?>', category_id: '<?php echo $prod['category_id']; ?>', price: '<?php echo $prod['price']; ?>', description: '<?php echo addslashes($prod['description']); ?>', image: '<?php echo $prod['image']; ?>', sort_order: '<?php echo $prod['sort_order']; ?>'}; window.scrollTo({top: 0, behavior: 'smooth'}); isMobileMenuOpen = false;"
                                                class="text-blue-600 hover:text-blue-900"><i data-lucide="edit-2"
                                                    class="w-4 h-4"></i></button>
                                            <form method="POST" class="inline"
                                                onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900"><i
                                                        data-lucide="trash-2" class="w-4 h-4"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedbacks Tab -->
        <div x-show="activeTab === 'feedbacks'" x-cloak>
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-xl font-bold">Öneri ve Şikayetler (<?php echo count($feedbacks); ?>)</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (empty($feedbacks)): ?>
                    <div class="col-span-full bg-white p-12 text-center rounded-xl border-2 border-dashed border-gray-100">
                        <i data-lucide="inbox" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
                        <p class="text-gray-400">Henüz geri bildirim alınmadı.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedbacks as $fb): ?>
                        <div
                            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col relative overflow-hidden group">
                            <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <form method="POST"
                                    onsubmit="return confirm('Bu geri bildirimi silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="action" value="delete_feedback">
                                    <input type="hidden" name="id" value="<?php echo $fb['id']; ?>">
                                    <button type="submit"
                                        class="text-rose-500 hover:text-rose-700 p-2 bg-rose-50 rounded-xl transition-colors">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 mr-3">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($fb['full_name']); ?></h3>
                                    <p class="text-xs text-gray-400">
                                        <?php echo date('d.m.Y H:i', strtotime($fb['created_at'])); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-4">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $fb['type'] === 'complaint' ? 'bg-rose-100 text-rose-600' : 'bg-blue-100 text-blue-600'; ?>">
                                    <?php echo $fb['type'] === 'complaint' ? 'Şikayet' : 'Öneri'; ?>
                                </span>
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase tracking-wider flex items-center">
                                    <i data-lucide="phone" class="w-3 h-3 mr-1.5"></i>
                                    <?php echo htmlspecialchars($fb['phone']); ?>
                                </span>
                            </div>

                            <div
                                class="bg-slate-50 p-4 rounded-xl text-sm text-gray-600 leading-relaxed italic border border-slate-100/50">
                                "<?php echo htmlspecialchars($fb['message']); ?>"
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ayarlar Tab -->
        <div x-show="activeTab === 'settings'" x-cloak>
            <div class="bg-white p-6 md:p-8 shadow rounded-xl border border-gray-100 max-w-2xl mx-auto md:mx-0">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i data-lucide="settings" class="w-6 h-6 mr-3 text-gray-400"></i>
                    Genel Ayarlar
                </h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="save_settings">
                    <input type="hidden" name="old_home_image" value="<?php echo htmlspecialchars($homeImage); ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mekan İsmi</label>
                        <input type="text" name="restaurantName" required
                            value="<?php echo htmlspecialchars($restaurantName); ?>"
                            class="w-full border-gray-300 rounded-md shadow-sm border p-3 focus:ring-black focus:border-black text-lg">
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Giriş Sayfası (Home)
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Giriş Başlığı</label>
                                <input type="text" name="welcomeTitle" required
                                    value="<?php echo htmlspecialchars($welcomeTitle); ?>"
                                    class="w-full border-gray-300 rounded-md shadow-sm border p-3 focus:ring-black focus:border-black">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Giriş Metni</label>
                                <textarea name="welcomeDescription" rows="3" required
                                    class="w-full border-gray-300 rounded-md shadow-sm border p-3 focus:ring-black focus:border-black"><?php echo htmlspecialchars($welcomeDescription); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Giriş Görseli</label>
                                <?php if ($homeImage): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo $homeImage; ?>"
                                            class="h-32 w-full object-cover rounded-lg border">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="home_image" accept="image/*"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-black hover:file:bg-gray-200 transition-colors">
                            </div>
                            <div class="border-t border-gray-100 pt-6">
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Sosyal Medya
                                </h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Instagram Hesabı
                                        (Link)</label>
                                    <input type="text" name="instagramUrl" placeholder="https://instagram.com/hesabiniz"
                                        value="<?php echo htmlspecialchars($instagramUrl); ?>"
                                        class="w-full border-gray-300 rounded-md shadow-sm border p-3 focus:ring-black focus:border-black">
                                </div>
                            </div>
                        </div> <!-- This is the closing tag for the space-y-4 div -->

                        <button type="submit"
                            class="bg-black text-white px-8 py-3 rounded-md hover:bg-gray-800 transition-colors font-semibold shadow-md w-full">
                            Ayarları Kaydet
                        </button>
                </form>
            </div>
        </div>

    </main>

    <footer class="mt-auto py-8 text-center text-xs text-gray-400 border-t border-gray-100 bg-white">
        Menü Sistemi <span class="font-bold text-gray-900">MCD Yazılım</span> tarafından geliştirilmiştir © 2026
    </footer>

    <script>lucide.createIcons();</script>
</body>

</html>