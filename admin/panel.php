<?php
/* ══════════════════════════════════════════
   TESISAT PRO — ADMİN PANELİ
   ══════════════════════════════════════════ */
session_start();

// Oturum kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$db = getDB();

// ── Durum güncelleme işlemi ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['durum_guncelle'])) {
    $id = (int)($_POST['ariza_id'] ?? 0);
    $yeni_durum = trim($_POST['yeni_durum'] ?? '');
    $gecerli_durumlar = ['beklemede', 'inceleniyor', 'cozuldu'];

    if ($id > 0 && in_array($yeni_durum, $gecerli_durumlar)) {
        $stmt = $db->prepare('UPDATE ariza_bildirimleri SET durum = :durum WHERE id = :id');
        $stmt->execute([':durum' => $yeni_durum, ':id' => $id]);
        $basari_mesaj = 'Durum başarıyla güncellendi.';
    }
}

// ── İstatistikler ──
$toplam    = $db->query('SELECT COUNT(*) FROM ariza_bildirimleri')->fetchColumn();
$beklemede = $db->query("SELECT COUNT(*) FROM ariza_bildirimleri WHERE durum = 'beklemede'")->fetchColumn();
$inceleniyor = $db->query("SELECT COUNT(*) FROM ariza_bildirimleri WHERE durum = 'inceleniyor'")->fetchColumn();
$cozuldu   = $db->query("SELECT COUNT(*) FROM ariza_bildirimleri WHERE durum = 'cozuldu'")->fetchColumn();
$mesajlar  = $db->query('SELECT COUNT(*) FROM iletisim_mesajlari WHERE okundu = 0')->fetchColumn();

// ── Arıza listesi ──
$filtre = trim($_GET['filtre'] ?? 'hepsi');
$arama  = trim($_GET['arama'] ?? '');

$sql = 'SELECT * FROM ariza_bildirimleri';
$params = [];

$where = [];
if ($filtre !== 'hepsi' && in_array($filtre, ['beklemede','inceleniyor','cozuldu'])) {
    $where[] = 'durum = :filtre';
    $params[':filtre'] = $filtre;
}
if (!empty($arama)) {
    $where[] = '(takip_no LIKE :arama OR ad_soyad LIKE :arama2 OR telefon LIKE :arama3)';
    $params[':arama']  = "%{$arama}%";
    $params[':arama2'] = "%{$arama}%";
    $params[':arama3'] = "%{$arama}%";
}
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY olusturma_tarihi DESC LIMIT 50';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$arizalar = $stmt->fetchAll();

// Durum renkleri
$durum_renk = [
    'beklemede'   => '#F59E0B',
    'inceleniyor' => '#3B82F6',
    'cozuldu'     => '#10B981',
];
$durum_etiket = [
    'beklemede'   => '⏳ Beklemede',
    'inceleniyor' => '🔍 İnceleniyor',
    'cozuldu'     => '✅ Çözüldü',
];
$aciliyet_renk = [
    'normal'   => '#10B981',
    'acil'     => '#F59E0B',
    'cok_acil' => '#EF4444',
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli — Tesisat Pro</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0A1628;
            color: #E0E0E0;
            min-height: 100vh;
        }

        /* ── Üst Bar ── */
        .topbar {
            background: rgba(13,27,42,0.95);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
        }
        .topbar-logo span { color: #1E90FF; }
        .topbar-logo small {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            font-weight: 400;
            color: #8899AA;
            margin-left: 12px;
            padding: 3px 10px;
            background: rgba(30,144,255,0.1);
            border-radius: 20px;
        }

        .topbar-actions { display: flex; align-items: center; gap: 16px; }
        .topbar-user {
            font-size: 13px;
            color: #8899AA;
        }
        .btn-logout {
            padding: 8px 18px;
            border-radius: 8px;
            background: rgba(255,107,53,0.15);
            border: 1px solid rgba(255,107,53,0.3);
            color: #FF6B35;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-logout:hover {
            background: rgba(255,107,53,0.25);
        }

        /* ── Ana İçerik ── */
        .dashboard { max-width: 1300px; margin: 0 auto; padding: 32px; }

        /* ── İstatistik Kartları ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 24px;
            text-align: center;
            transition: all 0.3s;
        }
        .stat-card:hover {
            border-color: rgba(30,144,255,0.2);
            transform: translateY(-3px);
        }

        .stat-card .stat-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .stat-card .stat-label {
            font-size: 12px;
            color: #8899AA;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Filtre ve Arama ── */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
        }
        .filter-tab {
            padding: 8px 18px;
            border-radius: 8px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            color: #8899AA;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .filter-tab:hover, .filter-tab.active {
            background: rgba(30,144,255,0.15);
            border-color: rgba(30,144,255,0.3);
            color: #1E90FF;
        }

        .search-box {
            display: flex;
            gap: 8px;
        }
        .search-box input {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: #fff;
            font-size: 13px;
            outline: none;
            width: 220px;
            transition: border 0.3s;
        }
        .search-box input:focus { border-color: #1E90FF; }
        .search-box button {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, #1E90FF, #00CED1);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .search-box button:hover { transform: translateY(-1px); }

        /* ── Tablo ── */
        .table-container {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: rgba(255,255,255,0.04);
            padding: 14px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #8899AA;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        tbody td {
            padding: 14px 16px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }

        tbody tr:hover {
            background: rgba(30,144,255,0.04);
        }

        tbody tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .durum-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .durum-form select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.06);
            color: #fff;
            font-size: 12px;
            cursor: pointer;
            outline: none;
        }
        .durum-form select option {
            background: #0A1628;
        }

        .durum-form button {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            background: #1E90FF;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .durum-form button:hover { background: #00CED1; }

        .success-bar {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.2);
            border-radius: 10px;
            padding: 12px 20px;
            margin-bottom: 20px;
            color: #10B981;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #556677;
        }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .toolbar { flex-direction: column; align-items: stretch; }
            .dashboard { padding: 16px; }
            .table-container { overflow-x: auto; }
            table { min-width: 800px; }
        }
    </style>
</head>
<body>

    <!-- Üst Bar -->
    <div class="topbar">
        <div class="topbar-logo">
            🔧 Tesisat<span>Pro</span>
            <small>Admin Paneli</small>
        </div>
        <div class="topbar-actions">
            <span class="topbar-user">👤 <?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></span>
            <a href="logout.php" class="btn-logout">Çıkış Yap</a>
        </div>
    </div>

    <div class="dashboard">

        <?php if (!empty($basari_mesaj)): ?>
            <div class="success-bar">✅ <?= htmlspecialchars($basari_mesaj) ?></div>
        <?php endif; ?>

        <!-- İstatistik Kartları -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#1E90FF"><?= $toplam ?></div>
                <div class="stat-label">Toplam Arıza</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#F59E0B"><?= $beklemede ?></div>
                <div class="stat-label">Beklemede</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#3B82F6"><?= $inceleniyor ?></div>
                <div class="stat-label">İnceleniyor</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#10B981"><?= $cozuldu ?></div>
                <div class="stat-label">Çözüldü</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#FF6B35"><?= $mesajlar ?></div>
                <div class="stat-label">Okunmamış Mesaj</div>
            </div>
        </div>

        <!-- Filtre & Arama -->
        <div class="toolbar">
            <div class="filter-tabs">
                <a href="panel.php?filtre=hepsi" class="filter-tab <?= $filtre === 'hepsi' ? 'active' : '' ?>">Hepsi</a>
                <a href="panel.php?filtre=beklemede" class="filter-tab <?= $filtre === 'beklemede' ? 'active' : '' ?>">⏳ Beklemede</a>
                <a href="panel.php?filtre=inceleniyor" class="filter-tab <?= $filtre === 'inceleniyor' ? 'active' : '' ?>">🔍 İnceleniyor</a>
                <a href="panel.php?filtre=cozuldu" class="filter-tab <?= $filtre === 'cozuldu' ? 'active' : '' ?>">✅ Çözüldü</a>
            </div>
            <form class="search-box" method="GET" action="panel.php">
                <input type="hidden" name="filtre" value="<?= htmlspecialchars($filtre) ?>">
                <input type="text" name="arama" placeholder="Takip no, ad veya telefon..." value="<?= htmlspecialchars($arama) ?>">
                <button type="submit">Ara</button>
            </form>
        </div>

        <!-- Arıza Tablosu -->
        <div class="table-container">
            <?php if (empty($arizalar)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p>Henüz arıza bildirimi bulunmuyor.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Takip No</th>
                            <th>Ad Soyad</th>
                            <th>Telefon</th>
                            <th>İlçe</th>
                            <th>Arıza Türü</th>
                            <th>Aciliyet</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($arizalar as $a): ?>
                            <tr>
                                <td><strong style="color:#1E90FF"><?= htmlspecialchars($a['takip_no']) ?></strong></td>
                                <td><?= htmlspecialchars($a['ad_soyad']) ?></td>
                                <td><?= htmlspecialchars($a['telefon']) ?></td>
                                <td><?= htmlspecialchars($a['ilce']) ?></td>
                                <td><?= htmlspecialchars($a['ariza_turu']) ?></td>
                                <td>
                                    <span class="badge" style="background: <?= $aciliyet_renk[$a['aciliyet']] ?>22; color: <?= $aciliyet_renk[$a['aciliyet']] ?>">
                                        <?= htmlspecialchars($a['aciliyet']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?= $durum_renk[$a['durum']] ?>22; color: <?= $durum_renk[$a['durum']] ?>">
                                        <?= $durum_etiket[$a['durum']] ?? $a['durum'] ?>
                                    </span>
                                </td>
                                <td style="font-size:12px; color:#8899AA">
                                    <?= date('d.m.Y H:i', strtotime($a['olusturma_tarihi'])) ?>
                                </td>
                                <td>
                                    <form method="POST" class="durum-form">
                                        <input type="hidden" name="durum_guncelle" value="1">
                                        <input type="hidden" name="ariza_id" value="<?= $a['id'] ?>">
                                        <select name="yeni_durum">
                                            <option value="beklemede" <?= $a['durum']==='beklemede' ? 'selected' : '' ?>>Beklemede</option>
                                            <option value="inceleniyor" <?= $a['durum']==='inceleniyor' ? 'selected' : '' ?>>İnceleniyor</option>
                                            <option value="cozuldu" <?= $a['durum']==='cozuldu' ? 'selected' : '' ?>>Çözüldü</option>
                                        </select>
                                        <button type="submit">Güncelle</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Otomatik yenileme (30 saniye)
        setTimeout(() => location.reload(), 30000);
    </script>
</body>
</html>
