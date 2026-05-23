<?php
/* ══════════════════════════════════════════
   TESISAT PRO — ARIZA KAYIT API
   POST: Yeni arıza bildirimi kaydeder
   ══════════════════════════════════════════ */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Sadece POST isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST metodu kabul edilir.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    $db = getDB();

    // Gelen verileri al ve temizle
    $ad_soyad   = trim($_POST['ad_soyad'] ?? '');
    $telefon    = trim($_POST['telefon'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $ilce       = trim($_POST['ilce'] ?? '');
    $adres      = trim($_POST['adres'] ?? '');
    $ariza_turu = trim($_POST['ariza_turu'] ?? '');
    $aciklama   = trim($_POST['aciklama'] ?? '');
    $aciliyet   = trim($_POST['aciliyet'] ?? 'normal');

    // ── Doğrulama ──
    $hatalar = [];

    if (empty($ad_soyad)) {
        $hatalar[] = 'Ad soyad alanı zorunludur.';
    } elseif (mb_strlen($ad_soyad) > 100) {
        $hatalar[] = 'Ad soyad en fazla 100 karakter olabilir.';
    }

    if (empty($telefon)) {
        $hatalar[] = 'Telefon alanı zorunludur.';
    } elseif (!preg_match('/^[0-9\s\(\)\+\-]{7,20}$/', $telefon)) {
        $hatalar[] = 'Geçersiz telefon numarası.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = 'Geçersiz e-posta adresi.';
    }

    if (empty($ilce)) {
        $hatalar[] = 'İlçe alanı zorunludur.';
    }

    if (empty($adres)) {
        $hatalar[] = 'Adres alanı zorunludur.';
    }

    $gecerli_turler = ['su_kacagi', 'tikaniklik', 'dogalgaz', 'kombi', 'diger'];
    if (empty($ariza_turu) || !in_array($ariza_turu, $gecerli_turler)) {
        $hatalar[] = 'Geçerli bir arıza türü seçiniz.';
    }

    $gecerli_aciliyet = ['normal', 'acil', 'cok_acil'];
    if (!in_array($aciliyet, $gecerli_aciliyet)) {
        $aciliyet = 'normal';
    }

    if (!empty($hatalar)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => implode(' ', $hatalar)]);
        exit;
    }

    // ── Takip numarası oluştur ──
    $yil = date('Y');
    do {
        $rastgele = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        $takip_no = "ARZ-{$yil}-{$rastgele}";
        // Benzersizlik kontrolü
        $kontrol = $db->prepare('SELECT COUNT(*) FROM ariza_bildirimleri WHERE takip_no = ?');
        $kontrol->execute([$takip_no]);
    } while ($kontrol->fetchColumn() > 0);

    // ── Veritabanına kaydet ──
    $stmt = $db->prepare('
        INSERT INTO ariza_bildirimleri 
            (takip_no, ad_soyad, telefon, email, ilce, adres, ariza_turu, aciklama, aciliyet)
        VALUES 
            (:takip_no, :ad_soyad, :telefon, :email, :ilce, :adres, :ariza_turu, :aciklama, :aciliyet)
    ');

    $stmt->execute([
        ':takip_no'   => $takip_no,
        ':ad_soyad'   => htmlspecialchars($ad_soyad, ENT_QUOTES, 'UTF-8'),
        ':telefon'    => htmlspecialchars($telefon, ENT_QUOTES, 'UTF-8'),
        ':email'      => $email ?: null,
        ':ilce'       => htmlspecialchars($ilce, ENT_QUOTES, 'UTF-8'),
        ':adres'      => htmlspecialchars($adres, ENT_QUOTES, 'UTF-8'),
        ':ariza_turu' => $ariza_turu,
        ':aciklama'   => htmlspecialchars($aciklama, ENT_QUOTES, 'UTF-8'),
        ':aciliyet'   => $aciliyet,
    ]);

    http_response_code(201);
    echo json_encode([
        'success'  => true,
        'message'  => 'Arıza bildiriminiz başarıyla kaydedildi.',
        'takip_no' => $takip_no,
    ]);

} catch (PDOException $e) {
    error_log('Arıza kayıt hatası: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası. Lütfen tekrar deneyin.']);
} catch (Exception $e) {
    error_log('Beklenmeyen hata: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Beklenmeyen bir hata oluştu.']);
}
