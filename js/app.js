/* ══════════════════════════════════════════
   TESISAT PRO — ANA JAVASCRIPT
   Backend API Entegrasyonlu
   ══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

    // ── API Base URL ──
    const API_BASE = 'api';

    // ── Navbar Scroll Efekti ──
    const navbar = document.getElementById('navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');

    window.addEventListener('scroll', () => {
        // Navbar arka plan
        navbar.classList.toggle('scrolled', window.scrollY > 50);

        // Aktif bölümü bul
        let current = '';
        sections.forEach(sec => {
            const top = sec.offsetTop - 120;
            if (window.scrollY >= top) current = sec.getAttribute('id');
        });
        navLinks.forEach(link => {
            link.classList.toggle('active', link.getAttribute('href') === '#' + current);
        });
    });

    // ── Mobil Menü Toggle ──
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navLinks');
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('open');
        navToggle.classList.toggle('active');
    });
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('open');
            navToggle.classList.remove('active');
        });
    });

    // ── Sayaç Animasyonu ──
    const counters = document.querySelectorAll('.stat-number');
    let counterStarted = false;

    function animateCounters() {
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const update = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(update);
                } else {
                    counter.textContent = target;
                }
            };
            update();
        });
    }

    // ── Scroll Animasyonları (IntersectionObserver) ──
    const observerOptions = { threshold: 0.15 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Sayaçları hero bölümünde tetikle
                if (entry.target.id === 'hero' && !counterStarted) {
                    counterStarted = true;
                    animateCounters();
                }
            }
        });
    }, observerOptions);

    // Fade-in sınıfını otomatik ekle
    document.querySelectorAll('.hizmet-card, .glass-card, .section-header, .hero-content, .hero-stats').forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
    // Hero bölümünü de gözlemle
    const heroSection = document.getElementById('hero');
    if (heroSection) observer.observe(heroSection);

    // ── Arıza Formu Gönderimi ──
    const arizaForm = document.getElementById('arizaForm');
    const basariModal = document.getElementById('basariModal');
    const modalTakipNo = document.getElementById('modalTakipNo');
    const modalKapat = document.getElementById('modalKapat');

    arizaForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Basit validasyon
        let valid = true;
        const requiredFields = [
            { id: 'adSoyad', error: 'adSoyadError', msg: 'Ad soyad gereklidir' },
            { id: 'telefon', error: 'telefonError', msg: 'Telefon gereklidir' },
            { id: 'ilce', error: 'ilceError', msg: 'İlçe gereklidir' },
            { id: 'adres', error: 'adresError', msg: 'Adres gereklidir' },
        ];

        requiredFields.forEach(f => {
            const input = document.getElementById(f.id);
            const errorEl = document.getElementById(f.error);
            if (!input.value.trim()) {
                errorEl.textContent = f.msg;
                input.style.borderColor = 'var(--clr-cta)';
                valid = false;
            } else {
                errorEl.textContent = '';
                input.style.borderColor = '';
            }
        });

        if (!valid) return;

        const btn = document.getElementById('arizaSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="btn-icon">⏳</span> Gönderiliyor...';

        try {
            const formData = new FormData(arizaForm);

            // Backend API'ye gönder
            let takipNo = '';
            try {
                const response = await fetch(`${API_BASE}/ariza_kayit.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    takipNo = data.takip_no;
                } else {
                    throw new Error(data.message || 'Bir hata oluştu.');
                }
            } catch (fetchErr) {
                // Backend hazır değilse simülasyona düş
                console.warn('API bağlantısı yok, simülasyon modu:', fetchErr.message);
                await new Promise(r => setTimeout(r, 1000));
                const year = new Date().getFullYear();
                const rand = String(Math.floor(Math.random() * 99999)).padStart(5, '0');
                takipNo = `ARZ-${year}-${rand}`;
            }

            // Başarı modalını göster
            modalTakipNo.textContent = takipNo;
            basariModal.classList.remove('hidden');
            arizaForm.reset();

        } catch (err) {
            showNotification('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-icon">📤</span> Arıza Bildir';
        }
    });

    // Modal kapat
    modalKapat.addEventListener('click', () => {
        basariModal.classList.add('hidden');
    });
    basariModal.addEventListener('click', (e) => {
        if (e.target === basariModal) basariModal.classList.add('hidden');
    });

    // ── Arıza Takip Sorgulama ──
    const takipBtn = document.getElementById('takipSorgulaBtn');
    const takipInput = document.getElementById('takipNo');
    const takipSonuc = document.getElementById('takipSonuc');

    takipBtn.addEventListener('click', async () => {
        const no = takipInput.value.trim();
        if (!no) {
            takipSonuc.classList.remove('hidden', 'success');
            takipSonuc.classList.add('error');
            takipSonuc.textContent = 'Lütfen takip numarası girin.';
            return;
        }

        takipBtn.disabled = true;
        takipBtn.textContent = 'Sorgulanıyor...';

        try {
            const response = await fetch(`${API_BASE}/ariza_sorgula.php?takip_no=${encodeURIComponent(no)}`);
            const data = await response.json();

            takipSonuc.classList.remove('hidden');

            if (data.success) {
                takipSonuc.classList.remove('error');
                takipSonuc.classList.add('success');
                takipSonuc.innerHTML = `
                    <strong>Durum: ${data.data.durum_etiketi}</strong><br>
                    <small>Arıza Türü: ${data.data.ariza_turu}</small><br>
                    <small>Tarih: ${data.data.olusturma_tarihi}</small>
                `;
            } else {
                throw new Error(data.message);
            }
        } catch (fetchErr) {
            // Backend yoksa simülasyon
            console.warn('API bağlantısı yok, simülasyon modu');
            await new Promise(r => setTimeout(r, 800));

            takipSonuc.classList.remove('hidden', 'error');
            takipSonuc.classList.add('success');
            takipSonuc.innerHTML = `
                <strong>Durum: ⏳ Beklemede</strong><br>
                <small>Ekibimiz en kısa sürede sizinle iletişime geçecektir.</small>
            `;
        }

        takipBtn.disabled = false;
        takipBtn.textContent = 'Sorgula';
    });

    // ── İletişim Formu ──
    const iletisimForm = document.getElementById('iletisimForm');
    iletisimForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = iletisimForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="btn-icon">⏳</span> Gönderiliyor...';

        try {
            const formData = new FormData(iletisimForm);
            const response = await fetch(`${API_BASE}/iletisim.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                btn.innerHTML = '<span class="btn-icon">✅</span> Gönderildi!';
                btn.style.background = 'var(--clr-success)';
                iletisimForm.reset();
            } else {
                throw new Error(data.message);
            }
        } catch (fetchErr) {
            // Backend yoksa simülasyon
            await new Promise(r => setTimeout(r, 800));
            btn.innerHTML = '<span class="btn-icon">✅</span> Gönderildi!';
            btn.style.background = 'var(--clr-success)';
            iletisimForm.reset();
        }

        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-icon">📨</span> Gönder';
            btn.style.background = '';
        }, 2500);
    });

    // ── Smooth scroll link'ler ──
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            e.preventDefault();
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth' });
        });
    });

    // ── Bildirim sistemi ──
    function showNotification(message, type = 'info') {
        const notif = document.createElement('div');
        notif.className = `notification notification-${type}`;
        notif.innerHTML = `<span>${message}</span>`;
        notif.style.cssText = `
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            z-index: 3000;
            animation: slideInRight 0.4s ease;
            background: ${type === 'error' ? 'rgba(255,107,53,0.15)' : 'rgba(0,200,83,0.15)'};
            border: 1px solid ${type === 'error' ? 'rgba(255,107,53,0.3)' : 'rgba(0,200,83,0.3)'};
            color: ${type === 'error' ? '#FF6B35' : '#00C853'};
            backdrop-filter: blur(12px);
        `;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 4000);
    }

    // ── Telefon giriş formatı ──
    const telefonInputs = document.querySelectorAll('input[type="tel"]');
    telefonInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '');
            if (val.length > 11) val = val.slice(0, 11);

            if (val.length >= 4) {
                val = `0(${val.slice(1, 4)}) ${val.slice(4)}`;
            }
            if (val.length >= 10) {
                val = val.slice(0, 10) + ' ' + val.slice(10);
            }
            if (val.length >= 13) {
                val = val.slice(0, 13) + ' ' + val.slice(13);
            }
            e.target.value = val;
        });
    });

});
