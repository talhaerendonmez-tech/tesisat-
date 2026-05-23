<?php
/* ══════════════════════════════════════════
   TESISAT PRO — ARIZA SORGULAMA API
   GET: Takip numarasıyla arıza durumu sorgulama
   ══════════════════════════════════════════ */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Sadece GET isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece GET metodu kabul edilir.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    $db = getDB();

    $takip_no = trim($_GET['takip_no'] ?? '');

    // Doğrulama
    if (empty($takip_no)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Takip numarası gereklidir.']);
        exit;
    }

    // Format kontrolü: ARZ-YYYY-XXXXX
    if (!preg_match('/^ARZ-\d{4}-\d{5}$/', $takip_no)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Geçersiz takip numarası formatı.']);
        exit;
    }

    // Veritabanında ara
    $stmt = $db->prepare('
        SELECT takip_no, ariza_turu, aciliyet, durum, olusturma_tarihi, guncelleme_tarihi
        FROM ariza_bildirimleri
        WHERE takip_no = :takip_no
        LIMIT 1
    ');
    $stmt->execute([':takip_no' => $takip_no]);
    $ariza = $stmt->fetch();

    if (!$ariza) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Bu takip numarasına ait arıza bildirimi bulunamadı.'
        ]);
        exit;
    }

    // Durum etiketleri
    $durum_etiketleri = [
        'beklemede'   => '⏳ Beklemede',
        'inceleniyor' => '🔍 İnceleniyor',
        'cozuldu'     => '✅ Çözüldü',
    ];

    // Arıza türü etiketleri
    $tur_etiketleri = [
        'su_kacagi'  => '💧 Su Kaçağı',
        'tikaniklik' => '🚽 Tıkanıklık',
        'dogalgaz'   => '🔥 Doğalgaz Arızası',
        'kombi'      => '❄️ Kombi Arızası',
        'diger'      => '🔧 Diğer',
    ];

    echo json_encode([
        'success' => true,
        'data'    => [
            'takip_no'          => $ariza['takip_no'],
            'ariza_turu'        => $tur_etiketleri[$ariza['ariza_turu']] ?? $ariza['ariza_turu'],
            'aciliyet'          => $ariza['aciliyet'],
            'durum'             => $ariza['durum'],
            'durum_etiketi'     => $durum_etiketleri[$ariza['durum']] ?? $ariza['durum'],
            'olusturma_tarihi'  => $ariza['olusturma_tarihi'],
            'guncelleme_tarihi' => $ariza['guncelleme_tarihi'],
        ]
    ]);

} catch (PDOException $e) {
    error_log('Arıza sorgulama hatası: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası. Lütfen tekrar deneyin.']);
}
