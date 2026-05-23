<?php
/* ══════════════════════════════════════════
   TESISAT PRO — ADMİN GİRİŞ SAYFASI
   ══════════════════════════════════════════ */
session_start();

// Zaten giriş yapmışsa panele yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: panel.php');
    exit;
}

// ── Admin bilgileri (canlıda veritabanından alınmalı) ──
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', password_hash('tesisat2026', PASSWORD_DEFAULT));

$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici = trim($_POST['kullanici'] ?? '');
    $sifre     = trim($_POST['sifre'] ?? '');

    if ($kullanici === ADMIN_USER && password_verify($sifre, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $kullanici;
        $_SESSION['login_time'] = time();
        header('Location: panel.php');
        exit;
    } else {
        $hata = 'Kullanıcı adı veya şifre hatalı.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi — Tesisat Pro</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(160deg, #0A1628 0%, #0f2744 50%, #0D1B2A 100%);
            color: #E0E0E0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 48px 36px;
            text-align: center;
        }

        .login-logo {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        .login-title span {
            background: linear-gradient(135deg, #1E90FF, #00CED1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            font-size: 14px;
            color: #8899AA;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #AAB;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        .form-group input:focus {
            border-color: #1E90FF;
            box-shadow: 0 0 0 3px rgba(30,144,255,0.15);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #1E90FF, #00CED1);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(30,144,255,0.3);
        }

        .error-msg {
            background: rgba(255,107,53,0.1);
            border: 1px solid rgba(255,107,53,0.2);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #FF6B35;
            margin-bottom: 20px;
        }

        .login-footer {
            margin-top: 24px;
            font-size: 12px;
            color: #556677;
        }

        .login-footer a {
            color: #1E90FF;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">🔧</div>
            <h1 class="login-title">Tesisat<span>Pro</span></h1>
            <p class="login-subtitle">Yönetim Paneli Girişi</p>

            <?php if ($hata): ?>
                <div class="error-msg"><?= htmlspecialchars($hata) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="kullanici">Kullanıcı Adı</label>
                    <input type="text" id="kullanici" name="kullanici" placeholder="admin" required autofocus>
                </div>
                <div class="form-group">
                    <label for="sifre">Şifre</label>
                    <input type="password" id="sifre" name="sifre" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-login">Giriş Yap</button>
            </form>

            <div class="login-footer">
                <a href="/">← Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
</body>
</html>
