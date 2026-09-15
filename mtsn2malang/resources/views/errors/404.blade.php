<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan | MTsN 2 Kota Malang</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f9fafb; color: #1f2937; }
        .container { text-align: center; padding: 2rem; max-width: 500px; }
        .number { font-size: 8rem; font-weight: 800; color: #1a7a3c; line-height: 1; margin-bottom: 1rem; }
        h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 1rem; color: #111827; }
        p { color: #6b7280; margin-bottom: 2rem; line-height: 1.6; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #1a7a3c; color: white; border: none; border-radius: 0.75rem; text-decoration: none; font-family: inherit; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: #0f5a2a; }
        .btn-outline { background: transparent; border: 2px solid #1a7a3c; color: #1a7a3c; margin-left: 0.75rem; }
        .btn-outline:hover { background: #1a7a3c; color: white; }
        .illustration { font-size: 4rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="illustration" aria-hidden="true">🔍</div>
        <div class="number">404</div>
        <h1>Halaman Tidak Ditemukan</h1>
        <p>Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman telah dipindahkan, dihapus, atau URL yang Anda masukkan salah.</p>
        <div>
            <a href="/" class="btn"><span aria-hidden="true">🏠</span> Kembali ke Beranda</a>
            <button type="button" onclick="history.back()" class="btn btn-outline"><span aria-hidden="true">←</span> Kembali</button>
        </div>
    </div>
</body>
</html>
