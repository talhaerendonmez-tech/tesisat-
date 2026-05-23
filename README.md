# 🔧 Tesisat Pro — Profesyonel Tesisat Hizmetleri Web Sitesi

Profesyonel tesisat hizmetleri sunan bir şirket için hazırlanmış, modern ve responsive tek sayfa (SPA) web sitesidir. Arıza bildirimi, arıza takip, iletişim formu ve yönetim paneli içerir.

---

## 📁 Dosya Yapısı

```
tesisat_site/
│
├── index.html              → Ana sayfa (Frontend)
├── 404.html                → Özel hata sayfası
├── .htaccess               → Apache yapılandırması
├── .gitignore              → Git ayarları
├── robots.txt              → Arama motoru yönergeleri
├── sitemap.xml             → Site haritası
├── manifest.json           → PWA manifest dosyası
├── README.md               → Bu dosya
│
├── css/
│   └── style.css           → Ana stil dosyası
│
├── js/
│   └── app.js              → Ana JavaScript dosyası (API entegrasyonlu)
│
├── images/
│   ├── README.md           → Gerekli görseller listesi
│   ├── favicon-16.png      → Favicon (16x16)
│   ├── favicon-32.png      → Favicon (32x32)
│   ├── apple-touch-icon.png→ Apple ikon (180x180)
│   ├── icon-192.png        → PWA ikon (192x192)
│   ├── icon-512.png        → PWA ikon (512x512)
│   └── og-image.jpg        → Sosyal medya görseli (1200x630)
│
├── config/
│   └── db.php              → Veritabanı bağlantı ayarları (PDO)
│
├── api/
│   ├── ariza_kayit.php     → Arıza bildirim API (POST)
│   ├── ariza_sorgula.php   → Arıza sorgulama API (GET)
│   └── iletisim.php        → İletişim formu API (POST)
│
├── admin/
│   ├── index.php           → Admin giriş sayfası
│   ├── panel.php           → Admin yönetim paneli
│   └── logout.php          → Oturum kapatma
│
└── sql/
    └── veritabani.sql      → MySQL veritabanı şeması
```

---

## 📋 Gereksinimler

| Bileşen        | Minimum Sürüm |
|----------------|---------------|
| PHP            | 7.4+          |
| MySQL/MariaDB  | 5.7+ / 10.3+  |
| Apache         | 2.4+          |
| Web Tarayıcı   | Modern (Chrome, Firefox, Edge, Safari) |

---

## 🚀 Kurulum

### 1. Dosyaları Sunucuya Yükleyin
Tüm proje dosyalarını web sunucusunun kök dizinine (ör. `htdocs/` veya `public_html/`) kopyalayın.

### 2. Veritabanını Oluşturun
MySQL'e bağlanın ve SQL dosyasını çalıştırın:

```bash
mysql -u root -p < sql/veritabani.sql
```

Veya phpMyAdmin üzerinden `sql/veritabani.sql` dosyasını içe aktarın.

### 3. Veritabanı Ayarlarını Yapın
`config/db.php` dosyasını düzenleyin:

```php
define('DB_HOST', 'localhost');      // Veritabanı sunucusu
define('DB_NAME', 'tesisat_db');     // Veritabanı adı
define('DB_USER', 'root');           // Kullanıcı adı
define('DB_PASS', '');               // Şifre
```

### 4. Dosya İzinlerini Ayarlayın (Linux)
```bash
chmod 755 -R ./
chmod 644 config/db.php
chmod 644 .htaccess
```

### 5. Apache Modüllerini Aktifleştirin
```bash
a2enmod rewrite headers deflate expires
sudo systemctl restart apache2
```

### 6. Admin Paneline Giriş
- URL: `https://siteniz.com/admin/`
- Kullanıcı: `admin`
- Şifre: `tesisat2026`

> ⚠️ **ÖNEMLİ:** Canlıya almadan önce admin şifresini mutlaka değiştirin!

---

## ✨ Özellikler

### Frontend
- 🎨 Modern koyu tema (Dark Mode) tasarım
- 📱 Tam responsive (mobil uyumlu)
- ✨ Glassmorphism kart tasarımları
- 🎭 Scroll animasyonları ve sayaç efektleri
- 💧 Animasyonlu su damlası parçacıkları
- 📋 Arıza bildirim formu (validasyonlu)
- 📍 Arıza takip sorgulama
- 📞 Acil çağrı butonu
- ✉️ İletişim formu
- 🔍 SEO optimizasyonu (meta tag, Open Graph, sitemap)
- 📲 PWA desteği (manifest.json)

### Backend (PHP API)
- 🔐 PDO ile güvenli veritabanı bağlantısı
- 📝 Prepared statements (SQL Injection koruması)
- ✅ Sunucu tarafı doğrulama
- 🔑 CSRF token desteği
- 📡 RESTful JSON API yanıtları
- 🆔 Benzersiz takip numarası üretimi

### Admin Paneli
- 🔒 Session tabanlı oturum yönetimi
- 📊 İstatistik kartları (toplam, beklemede, çözüldü)
- 📋 Arıza listesi ve filtreleme
- 🔍 Arama fonksiyonu
- ✏️ Durum güncelleme
- 🔄 Otomatik yenileme (30 saniye)

### Güvenlik
- 🛡️ Güvenlik başlıkları (X-XSS-Protection, X-Frame-Options vb.)
- 🔒 Hassas dizinlere erişim engeli
- 🗜️ GZIP sıkıştırma
- 📦 Statik dosya önbellekleme
- 🚫 Dizin listeleme engeli

---

## 📡 API Endpointleri

### Arıza Bildir
```
POST /api/ariza_kayit.php
Content-Type: multipart/form-data

Parametreler:
  - ad_soyad (zorunlu)
  - telefon (zorunlu)
  - email (opsiyonel)
  - ilce (zorunlu)
  - adres (zorunlu)
  - ariza_turu (zorunlu): su_kacagi | tikaniklik | dogalgaz | kombi | diger
  - aciklama (opsiyonel)
  - aciliyet: normal | acil | cok_acil

Yanıt: { success: true, takip_no: "ARZ-2026-XXXXX" }
```

### Arıza Sorgula
```
GET /api/ariza_sorgula.php?takip_no=ARZ-2026-XXXXX

Yanıt: { success: true, data: { durum, durum_etiketi, ariza_turu, ... } }
```

### İletişim Mesajı
```
POST /api/iletisim.php
Content-Type: multipart/form-data

Parametreler:
  - ad_soyad (zorunlu)
  - telefon (zorunlu)
  - mesaj (zorunlu)

Yanıt: { success: true, message: "..." }
```

---

## 🎨 Tasarım Renk Paleti

| Değişken         | Renk       | Kullanım            |
|-----------------|------------|---------------------|
| `--clr-bg`      | `#0D1B2A`  | Ana arka plan       |
| `--clr-primary` | `#1E90FF`  | Birincil renk       |
| `--clr-accent`  | `#00CED1`  | Vurgu rengi         |
| `--clr-cta`     | `#FF6B35`  | Aksiyon butonu      |
| `--clr-success` | `#00C853`  | Başarı rengi        |
| `--clr-text`    | `#E0E0E0`  | Metin rengi         |

---

## 📝 Lisans

Bu proje özel kullanım için geliştirilmiştir. Tüm hakları saklıdır.

---

## 📞 İletişim

- **Telefon:** 0 (555) 123 45 67
- **E-posta:** info@tesisatpro.com
- **Adres:** İstanbul, Türkiye
