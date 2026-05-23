<?php
/* ══════════════════════════════════════════
   TESISAT PRO — İLETİŞİM FORMU API
   POST: İletişim mesajı kaydeder
   ══════════════════════════════════════════ */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST metodu kabul edilir.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    $db = getDB();

    $ad_soyad = trim($_POST['ad_soyad'] ?? '');
    $telefon  = trim($_POST['telefon'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $mesaj    = trim($_POST['mesaj'] ?? '');

    // ── Doğrulama ──
    $hatalar = [];

    if (empty($ad_soyad)) {
        $hatalar[] = 'Ad soyad alanı zorunludur.';
    }

    if (empty($telefon)) {
        $hatalar[] = 'Telefon alanı zorunludur.';
    }

    if (empty($mesaj)) {
        $hatalar[] = 'Mesaj alanı zorunludur.';
    } elseif (mb_strlen($mesaj) > 2000) {
        $hatalar[] = 'Mesaj en fazla 2000 karakter olabilir.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = 'Geçersiz e-posta adresi.';
    }

    if (!empty($hatalar)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => implode(' ', $hatalar)]);
        exit;
    }

    // ── Veritabanına kaydet ──
    $stmt = $db->prepare('
        INSERT INTO iletisim_mesajlari (ad_soyad, telefon, email, mesaj)
        VALUES (:ad_soyad, :telefon, :email, :mesaj)
    ');

    $stmt->execute([
        ':ad_soyad' => htmlspecialchars($ad_soyad, ENT_QUOTES, 'UTF-8'),
        ':telefon'  => htmlspecialchars($telefon, ENT_QUOTES, 'UTF-8'),
        ':email'    => $email ?: null,
        ':mesaj'    => htmlspecialchars($mesaj, ENT_QUOTES, 'UTF-8'),
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.'
    ]);

} catch (PDOException $e) {
    error_log('İletişim kayıt hatası: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası. Lütfen tekrar deneyin.']);
}
