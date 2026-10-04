{{-- Baris hak cipta & tautan bawah — dipakai footer situs dan footer Portal. --}}
<div class="border-t border-green-700 py-4">
    <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-3 text-sm text-green-100">
        <p>&copy; {{ date('Y') }} MTsN 2 Kota Malang. Hak Cipta Dilindungi.</p>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Beranda</a>
            <a href="{{ route('tentang.kontak') }}" class="hover:text-white transition-colors">Kontak</a>
            <a href="{{ route('layanan.maklumat') }}" class="hover:text-white transition-colors">Maklumat</a>
            <a href="{{ route('survei.form') }}" class="hover:text-white transition-colors">Survei</a>
        </div>
    </div>
</div>
