<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance - MTsN 2 Kota Malang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f5a2a 0%, #1a7a3c 50%, #f97316 100%); color: white; }
        .container { text-align: center; padding: 2rem; max-width: 600px; }
        .icon { font-size: 5rem; margin-bottom: 1.5rem; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; }
        p { font-size: 1.1rem; opacity: 0.85; margin-bottom: 2rem; line-height: 1.6; }
        .countdown { display: flex; justify-content: center; gap: 1.5rem; margin: 2rem 0; }
        .countdown-item { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 1rem; padding: 1rem 1.5rem; min-width: 80px; }
        .countdown-number { font-size: 2.5rem; font-weight: 800; display: block; }
        .countdown-label { font-size: 0.75rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; }
        .contact { margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.1); border-radius: 1rem; }
        .contact a { color: #fbbf24; text-decoration: none; }
        .contact a:hover { text-decoration: underline; }
        .logo { width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 1.5rem; font-weight: 800; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">M2</div>
        <div class="icon">🔧</div>
        <h1>Sedang Maintenance</h1>
        <p>Website MTsN 2 Kota Malang sedang dalam pemeliharaan untuk meningkatkan layanan. Kami akan segera kembali!</p>

        @if(isset($endTime))
        <div class="countdown" id="countdown">
            <div class="countdown-item">
                <span class="countdown-number" id="hours">00</span>
                <span class="countdown-label">Jam</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-number" id="minutes">00</span>
                <span class="countdown-label">Menit</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-number" id="seconds">00</span>
                <span class="countdown-label">Detik</span>
            </div>
        </div>
        @endif

        <div class="contact">
            <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">Butuh bantuan segera? Hubungi kami:</p>
            <p style="margin-bottom: 0;">
                📞 <a href="tel:0341711500">(0341) 711500</a> &nbsp;|&nbsp;
                📧 <a href="mailto:mtsnmalang2adm@gmail.com">mtsnmalang2adm@gmail.com</a>
            </p>
        </div>
    </div>

    @if(isset($endTime))
    <script>
        const endTime = new Date('{{ $endTime }}').getTime();
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endTime - now;
            if (distance < 0) { document.getElementById('countdown').innerHTML = '<p>Segera online!</p>'; return; }
            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
    @endif
</body>
</html>
