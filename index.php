<?php
require_once 'config.php';

$message = '';
$status = '';

// Handle Feedback Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_feedback') {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $type = $_POST['type'] ?? '';
    $content = $_POST['message'] ?? '';

    if ($full_name && $phone && $type && $content) {
        $stmt = $pdo->prepare("INSERT INTO feedback (full_name, phone, type, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$full_name, $phone, $type, $content])) {
            $status = 'success';
            $message = 'Geri bildiriminiz başarıyla iletildi. Teşekkür ederiz!';
        } else {
            $status = 'error';
            $message = 'Bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

// Fetch Categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC");
$categories = $stmt->fetchAll();

// Fetch Products (if category is selected)
$selectedCategoryId = isset($_GET['cat']) ? (int) $_GET['cat'] : null;
$view = isset($_GET['view']) ? $_GET['view'] : 'home';

$products = [];
if ($selectedCategoryId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY sort_order ASC, id DESC");
    $stmt->execute([$selectedCategoryId]);
    $products = $stmt->fetchAll();
    $view = 'category';
}

// Settings
$restaurantName = getSetting('restaurantName', 'Digital Menu');
$welcomeTitle = getSetting('welcomeTitle', 'Hoş Geldiniz');
$welcomeDescription = getSetting('welcomeDescription', 'En taze lezzetlerimizi keşfetmek için kategorilere göz atın.');
$homeImage = getSetting('homeImage', '');
$instagramUrl = getSetting('instagramUrl', '');
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurantName); ?> - Dijital Menü</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@300;400;600;700&display=swap"
        rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="assets/js/tailwind.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="assets/js/lucide.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .hero-gradient {
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.8) 100%);
        }

        /* Slide Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(-50px);
            }
        }

        .content-slide-in {
            animation: slideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .content-slide-out {
            animation: slideOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
    <!-- Alpine.js -->
    <script defer src="assets/js/alpine.js"></script>
</head>

<body class="bg-[#f8fafc] text-slate-900" x-data="{ 
    isSidebarOpen: false, 
    isModalOpen: false, 
    selectedProduct: null,
    openProduct(product) {
        this.selectedProduct = product;
        this.isModalOpen = true;
    }
}">

    <div class="flex h-screen overflow-hidden">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="isSidebarOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 md:hidden"
            @click="isSidebarOpen = false" x-cloak></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transform transition-all duration-300 ease-in-out md:translate-x-0 md:static md:inset-auto"
            :class="isSidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'">
            <div class="flex items-center justify-between h-20 px-8 border-b border-slate-50">
                <a href="index.php"
                    class="text-2xl font-display font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-600 tracking-tight">
                    <?php echo htmlspecialchars($restaurantName); ?>
                </a>
                <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <nav class="p-6 space-y-1.5 overflow-y-auto h-[calc(100vh-5rem)]">
                <a href="index.php"
                    class="group flex items-center w-full px-4 py-3.5 rounded-xl text-sm font-semibold transition-all duration-200 <?php echo $view === 'home' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?>">
                    <i data-lucide="home" class="w-4 h-4 mr-3"></i>
                    <span class="flex-1">Ana Sayfa</span>
                </a>

                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-8 mb-4 px-3">
                    Kategoriler
                </div>
                <?php foreach ($categories as $cat): ?>
                    <a href="?cat=<?php echo $cat['id']; ?>"
                        class="group flex items-center w-full px-4 py-3.5 rounded-xl text-sm font-semibold transition-all duration-200 <?php echo $selectedCategoryId === (int) $cat['id'] ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'; ?>"
                        @click="isSidebarOpen = false">
                        <span class="flex-1"><?php echo htmlspecialchars($cat['name']); ?></span>
                        <i data-lucide="chevron-right"
                            class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity translate-x-1"></i>
                    </a>
                <?php endforeach; ?>

                <div class="border-t border-slate-50 mt-8 pt-6">
                    <a href="?view=feedback"
                        class="group flex items-center w-full px-4 py-3.5 rounded-xl text-sm font-semibold transition-all duration-200 <?php echo $view === 'feedback' ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'; ?>"
                        @click="isSidebarOpen = false">
                        <i data-lucide="message-square-plus"
                            class="w-4 h-4 mr-3 <?php echo $view === 'feedback' ? 'text-blue-500' : ''; ?>"></i>
                        <span class="flex-1">Öneri ve Şikayet</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">

            <!-- Mobile Header -->
            <header
                class="md:hidden bg-white/80 backdrop-blur-md border-b border-slate-200 h-16 flex items-center px-6 justify-between shrink-0 z-30">
                <button @click="isSidebarOpen = true"
                    class="text-slate-600 hover:text-slate-900 p-2 -ml-2 bg-slate-50 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="font-display font-bold text-lg text-slate-900">
                    <?php echo htmlspecialchars($restaurantName); ?>
                </div>
            </header>

            <!-- Content Scroll Area -->
            <main class="flex-1 overflow-y-auto" id="main-scroll">
                <div id="content-area" class="content-slide-in">
                    <?php if ($message): ?>
                        <div class="max-w-xl mx-auto mt-8 px-6">
                            <div
                                class="p-4 rounded-2xl <?php echo $status === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'; ?> flex items-center shadow-sm">
                                <i data-lucide="<?php echo $status === 'success' ? 'check-circle' : 'alert-circle'; ?>"
                                    class="w-5 h-5 mr-3 shrink-0"></i>
                                <span class="font-medium"><?php echo $message; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($view === 'home'): ?>
                        <!-- Home Hero Page -->
                        <div class="min-h-full flex flex-col items-center justify-center p-6 md:p-12">
                            <div class="max-w-4xl w-full">
                                <div class="relative h-[400px] md:h-[500px] rounded-[3rem] overflow-hidden shadow-2xl">
                                    <?php if ($homeImage): ?>
                                        <img src="<?php echo $homeImage; ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-slate-200 flex items-center justify-center">
                                            <i data-lucide="image" class="w-20 h-20 text-slate-300"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="absolute inset-0 hero-gradient flex flex-col justify-end p-8 md:p-16">
                                        <h2
                                            class="text-4xl md:text-6xl font-display font-bold text-white mb-4 tracking-tight">
                                            <?php echo htmlspecialchars($welcomeTitle); ?>
                                        </h2>
                                        <p class="text-slate-200 text-lg md:text-xl font-light max-w-2xl leading-relaxed">
                                            <?php echo htmlspecialchars($welcomeDescription); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($view === 'feedback'): ?>
                        <!-- Feedback Form -->
                        <div class="max-w-2xl mx-auto p-6 md:p-12">
                            <div class="mb-10 text-center">
                                <h2 class="text-3xl font-display font-bold text-slate-900 tracking-tight">Öneri ve Şikayet
                                </h2>
                                <p class="text-slate-400 mt-2">Deneyiminizi geliştirmek için görüşleriniz bizim için çok
                                    değerli.</p>
                            </div>

                            <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-xl border border-slate-50">
                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="submit_feedback">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2 px-1">İsim
                                                Soyisim</label>
                                            <input type="text" name="full_name" required placeholder="John Doe"
                                                class="w-full bg-slate-50 border-transparent rounded-2xl p-4 focus:bg-white focus:ring-2 focus:ring-slate-900 transition-all outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2 px-1">Telefon
                                                Numarası</label>
                                            <input type="tel" name="phone" required placeholder="05XX XXX XX XX"
                                                class="w-full bg-slate-50 border-transparent rounded-2xl p-4 focus:bg-white focus:ring-2 focus:ring-slate-900 transition-all outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2 px-1">Geri Bildirim
                                            Türü</label>
                                        <select name="type" required
                                            class="w-full bg-slate-50 border-transparent rounded-2xl p-4 focus:bg-white focus:ring-2 focus:ring-slate-900 transition-all outline-none appearance-none cursor-pointer">
                                            <option value="suggestion">Öneri</option>
                                            <option value="complaint">Şikayet</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-slate-700 mb-2 px-1">Mesajınız</label>
                                        <textarea name="message" rows="5" required placeholder="Görüşünüzü detaylandırın..."
                                            class="w-full bg-slate-50 border-transparent rounded-2xl p-4 focus:bg-white focus:ring-2 focus:ring-slate-900 transition-all outline-none resize-none"></textarea>
                                    </div>

                                    <button type="submit"
                                        class="w-full bg-slate-900 text-white rounded-2xl py-4 font-bold text-lg hover:bg-black hover:shadow-lg transition-all flex items-center justify-center group">
                                        Gönder
                                        <i data-lucide="send"
                                            class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Category Page -->
                        <div class="max-w-6xl mx-auto p-6 md:p-12">
                            <!-- Desktop Header -->
                            <div class="hidden md:block mb-12">
                                <?php
                                $currentCatName = "Menü";
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $selectedCategoryId) {
                                        $currentCatName = $cat['name'];
                                        break;
                                    }
                                }
                                ?>
                                <div class="flex items-end justify-between">
                                    <div>
                                        <h2 class="text-4xl font-display font-bold text-slate-900 tracking-tight">
                                            <?php echo htmlspecialchars($currentCatName); ?>
                                        </h2>
                                        <p class="text-slate-400 mt-2 text-lg font-light italic">Lezzetli seçeneklerimizi
                                            keşfedin.</p>
                                    </div>
                                    <div class="h-0.5 flex-1 bg-slate-100 mb-4 mx-8 rounded-full hidden lg:block"></div>
                                </div>
                            </div>

                            <!-- Product Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                <?php if (empty($products)): ?>
                                    <div
                                        class="text-center py-32 col-span-full bg-white rounded-3xl border-2 border-dashed border-slate-100">
                                        <div
                                            class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                            <i data-lucide="package-open" class="w-10 h-10 text-slate-200"></i>
                                        </div>
                                        <h3 class="text-slate-900 font-bold text-lg">Ürün Bulunmuyor</h3>
                                        <p class="text-slate-400 mt-2">Bu kategoriye henüz ürün eklenmemiş.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <div class="group bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                                            @click="openProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                            <!-- Image Container -->
                                            <div class="h-64 bg-slate-50 relative overflow-hidden">
                                                <?php if ($product['image']): ?>
                                                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i data-lucide="utensils-cross-lines" class="w-12 h-12 text-slate-200"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <!-- Price Tag -->
                                                <div class="absolute bottom-4 right-4 glass-effect px-4 py-2 rounded-2xl shadow-lg">
                                                    <span class="font-display font-bold text-slate-900">
                                                        <?php echo number_format($product['price'], 0); ?><span
                                                            class="text-xs ml-1">₺</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Content -->
                                            <div class="p-8 flex flex-col flex-1">
                                                <div class="mb-4">
                                                    <h3
                                                        class="font-display font-bold text-xl text-slate-900 group-hover:text-black transition-colors">
                                                        <?php echo htmlspecialchars($product['name']); ?>
                                                    </h3>
                                                </div>
                                                <p class="text-slate-500 text-sm leading-relaxed mb-6 flex-1 line-clamp-3">
                                                    <?php echo htmlspecialchars($product['description'] ? $product['description'] : "Sizin için özenle hazırlanan, taze ve enfes lezzetimiz."); ?>
                                                </p>
                                                <div
                                                    class="h-1 w-12 bg-slate-100 rounded-full group-hover:bg-slate-900 transition-colors">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>

            <!-- Product Detail Modal -->
            <div x-show="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="isModalOpen = false"></div>

                <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden">
                    <button @click="isModalOpen = false"
                        class="absolute top-6 right-6 z-10 p-2 bg-white/80 backdrop-blur rounded-full text-slate-900 shadow-lg hover:scale-110 transition-transform">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>

                    <div class="flex flex-col md:flex-row h-full">
                        <!-- Left: Image -->
                        <div class="w-full md:w-1/2 h-64 md:h-auto bg-slate-50">
                            <template x-if="selectedProduct && selectedProduct.image">
                                <img :src="selectedProduct.image" class="w-full h-full object-cover">
                            </template>
                            <template x-if="selectedProduct && !selectedProduct.image">
                                <div class="w-full h-full flex items-center justify-center">
                                    <i data-lucide="utensils-cross-lines" class="w-16 h-16 text-slate-200"></i>
                                </div>
                            </template>
                        </div>

                        <!-- Right: Details -->
                        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                            <h2 class="text-3xl font-display font-bold text-slate-900 mb-4"
                                x-text="selectedProduct ? selectedProduct.name : ''"></h2>
                            <div class="inline-block glass-effect px-4 py-2 rounded-2xl shadow-sm mb-6 w-fit">
                                <span class="text-2xl font-display font-bold text-slate-900">
                                    <span
                                        x-text="selectedProduct ? parseFloat(selectedProduct.price).toFixed(0) : ''"></span><span
                                        class="text-sm ml-1">₺</span>
                                </span>
                            </div>
                            <div class="h-1 w-16 bg-slate-100 rounded-full mb-6"></div>
                            <p class="text-slate-500 text-lg leading-relaxed overflow-y-auto max-h-60 pr-2"
                                x-text="selectedProduct && selectedProduct.description ? selectedProduct.description : 'Sizin için özenle hazırlanan, taze ve enfes lezzetimiz.'">
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer
                class="bg-white/50 backdrop-blur-sm border-t border-slate-100 py-6 px-8 flex flex-col md:flex-row items-center justify-between text-xs text-slate-400 gap-4 shrink-0">
                <div class="order-2 md:order-1">
                    © 2026 Tüm Hakları Saklıdır.
                </div>
                <div class="flex items-center gap-1.5 order-1 md:order-2">
                    Bu Menü Sistemi <span class="font-bold text-slate-900 bg-slate-100 px-2 py-1 rounded">MCD
                        Yazılım</span> tarafından yapılmıştır
                </div>
            </footer>
        </div>

        <?php if ($instagramUrl): ?>
            <!-- Instagram Floating Button -->
            <a href="<?php echo htmlspecialchars($instagramUrl); ?>" target="_blank"
                class="fixed bottom-24 md:bottom-12 right-6 z-40 bg-gradient-to-tr from-[#f9ce34] via-[#ee2a7b] to-[#6228d7] text-white p-4 rounded-2xl shadow-xl hover:scale-110 active:scale-95 transition-all duration-300 flex items-center justify-center group">
                <i data-lucide="instagram" class="w-7 h-7"></i>
                <span
                    class="max-w-0 overflow-hidden whitespace-nowrap group-hover:max-w-xs group-hover:ml-3 transition-all duration-500 font-bold text-sm">Takip
                    Et</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Initialize Lucide Icons & Transitions -->
    <script>
        lucide.createIcons();

        // Smooth Category Navigation
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (!link) return;

            const url = new URL(link.href);
            if (url.origin !== window.location.origin || !url.searchParams.has('cat') && !url.searchParams.has('view')) return;

            // Only intercept for internal menu links
            e.preventDefault();
            const contentArea = document.getElementById('content-area');
            const mainScroll = document.getElementById('main-scroll');

            // 1. Start Slide Out
            contentArea.classList.remove('content-slide-in');
            contentArea.classList.add('content-slide-out');

            // 2. Fetch new content
            fetch(link.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');
                    const newContent = newDoc.getElementById('content-area').innerHTML;
                    const newTitle = newDoc.title;
                    const newSidebar = newDoc.querySelector('nav').innerHTML;

                    setTimeout(() => {
                        // 3. Update Content & URL
                        contentArea.innerHTML = newContent;
                        document.title = newTitle;
                        document.querySelector('nav').innerHTML = newSidebar;
                        window.history.pushState({}, '', link.href);

                        // 4. Reset & Animate In
                        contentArea.classList.remove('content-slide-out');
                        contentArea.classList.add('content-slide-in');
                        mainScroll.scrollTo({ top: 0, behavior: 'smooth' });

                        // 5. Re-init Icons
                        lucide.createIcons();
                    }, 300);
                });
        });

        // Handle Back/Forward
        window.addEventListener('popstate', () => window.location.reload());
    </script>
</body>

</html>