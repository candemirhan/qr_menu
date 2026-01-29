# 🍽️ Dijital Menü Sistemi (PHP & MySQL)

Bu proje, kafe ve restoranlar için tasarlanmış, modern, yönetilebilir ve tamamen duyarlı (responsive) bir dijital menü sistemidir. Node.js/React mimarisinden PHP/MySQL mimarisine, tüm paylaşımlı hosting (cPanel, Plesk vb.) ortamlarında sorunsuz çalışacak şekilde taşınmıştır.

## ✨ Öne Çıkan Özellikler

### 📱 Kullanıcı Arayüzü (Menu)
- **Modern Tasarım:** Tailwind CSS ve Google Fonts (Inter & Outfit) ile premium "Glassmorphism" estetiği.
- **Dinamik Ana Sayfa:** Admin panelinden değiştirilebilen karşılama başlığı, metni ve büyük kahraman (hero) görseli.
- **Ürün Detay Popup:** Ürünlere tıklandığında açılan, büyük görsel ve detaylı açıklama sunan modal penceresi.
- **Sıralama:** Kategoriler ve ürünler admin panelinde belirlenen sıraya göre listelenir.
- **Öneri ve Şikayet:** Müşterilerin doğrudan geri bildirim gönderebileceği entegre form.
- **Instagram Entegrasyonu:** Admin panelinden aktif edilebilen, şık animasyonlu Instagram "Takip Et" butonu.

### 🔐 Yönetim Paneli (Admin)
- **Tek Ekran Deneyimi:** Alpine.js ile sayfa yenilemeden sekmeler arası geçiş.
- **Kategori Yönetimi:** Sınırsız kategori ekleme, düzenleme ve sıralama.
- **Ürün Yönetimi:** Resim yükleme, fiyatlandırma, detaylı açıklama ve kategorilere atama.
- **Geri Bildirim Takibi:** Gelen öneri ve şikayetlerin okunması, yönetilmesi ve silinmesi.
- **Gelişmiş Ayarlar:** Mekan ismi, ana sayfa içerikleri ve sosyal medya linkleri kolayca güncellenebilir.

### 🛡️ Güvenlik ve Koruma
- **Footer Koruması:** Sistem yapımcı bilgilerinin (MCD Yazılım) silinmesini önleyen "Emeğe Saygı" mekanizması.
- **Oturum Yönetimi:** PHP Sessions ile güvenli admin girişi.
- **PDO Veritabanı:** SQL Injection saldırılarına karşı güvenli veritabanı iletişimi.

---

## 🛠️ Kurulum Adımları

1. **Veritabanı Hazırlığı:**
   - Hosting panelinizden yeni bir MySQL veritabanı ve kullanıcısı oluşturun.
   - `database.sql` dosyasındaki sorguları veritabanınızda çalıştırın.

2. **Dosya Düzenleme:**
   - `config.php` dosyasını açın ve aşağıdaki kısımları kendi veritabanı bilgilerinizle doldurun:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'veritabani_adi');
     define('DB_USER', 'kullanici_adi');
     define('DB_PASS', 'sifreniz');
     ```

3. **Yükleme:**
   - Tüm dosyaları sunucunuzun ana dizinine veya bir alt klasöre (örneğin `/menu`) yükleyin.
   - `uploads/` klasörünün **yazılabilir (write permission - 755 veya 777)** olduğundan emin olun.

4. **Giriş:**
   - Yönetim paneline `siteadresi.com/login.php` üzerinden erişebilirsiniz.
   - **Kullanıcı Adı:** `admin`
   - **Şifre:** `admin123`

---

## 🎨 Teknoloji Yığını
- **Backend:** PHP 7.4+ (PDO ile)
- **Database:** MySQL
- **Frontend:** HTML5, Vanilla JavaScript, Alpine.js
- **Styling:** Tailwind CSS (CDN)
- **Icons:** Lucide Icons

---
**Geliştiren:** [MCD Yazılım](https://github.com/mcdyazilim)
*Bu yazılım Emeğe Saygı prensibi çerçevesinde geliştirilmiştir. Lütfen yapımcı bilgilerini değiştirmeyin.*
