-- ══════════════════════════════════════════
-- TESISAT PRO — VERİTABANI ŞEMASI
-- MySQL 5.7+ / MariaDB 10.3+
-- ══════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS tesisat_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_turkish_ci;

USE tesisat_db;

-- ── Arıza Bildirimleri ──
CREATE TABLE IF NOT EXISTS ariza_bildirimleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takip_no VARCHAR(20) NOT NULL UNIQUE,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    ilce VARCHAR(50) NOT NULL,
    adres TEXT NOT NULL,
    ariza_turu ENUM('su_kacagi','tikaniklik','dogalgaz','kombi','diger') NOT NULL,
    aciklama TEXT DEFAULT NULL,
    aciliyet ENUM('normal','acil','cok_acil') NOT NULL DEFAULT 'normal',
    durum ENUM('beklemede','inceleniyor','cozuldu') NOT NULL DEFAULT 'beklemede',
    olusturma_tarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_takip (takip_no),
    INDEX idx_durum (durum),
    INDEX idx_tarih (olusturma_tarihi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ── İletişim Mesajları ──
CREATE TABLE IF NOT EXISTS iletisim_mesajlari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(20) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    mesaj TEXT NOT NULL,
    okundu TINYINT(1) NOT NULL DEFAULT 0,
    olusturma_tarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_okundu (okundu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ── Hizmetler ──
CREATE TABLE IF NOT EXISTS hizmetler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(100) NOT NULL,
    aciklama TEXT NOT NULL,
    ikon VARCHAR(50) DEFAULT '🔧',
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    sira INT NOT NULL DEFAULT 0,
    INDEX idx_aktif_sira (aktif, sira)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ── Varsayılan Hizmetler ──
INSERT INTO hizmetler (baslik, aciklama, ikon, sira) VALUES
('Su Kaçağı Tespiti', 'Kameralı ve termal cihazlarla kırıp dökmeden su kaçağı noktasını tespit ediyoruz.', '💧', 1),
('Tıkanıklık Açma', 'Lavabo, tuvalet, gider ve kanalizasyon tıkanıklıklarını açma hizmeti.', '🚽', 2),
('Doğalgaz Tesisatı', 'Doğalgaz hattı döşeme, kaçak kontrolü ve onaylı gaz tesisatı hizmetleri.', '🔥', 3),
('Kombi & Kalorifer', 'Kombi bakım, onarım, kalorifer petek temizliği ve ısıtma sistemi kurulumu.', '❄️', 4),
('Su Tesisatı', 'Temiz su ve pis su tesisatı döşeme, tamir ve yenileme işlemleri.', '🚿', 5),
('Banyo & Mutfak Tadilat', 'Banyo ve mutfak tesisat yenileme, tadilat ve dekorasyon projeleri.', '🛁', 6);
