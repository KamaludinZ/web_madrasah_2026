<x-mail::message>
# Kode Verifikasi Login

Seseorang (semoga Anda) sedang masuk ke panel admin website **{{ \App\Models\Setting::get('site_name', config('app.name')) }}**.

Masukkan kode berikut untuk melanjutkan:

<x-mail::panel>
<div style="font-size: 28px; font-weight: bold; letter-spacing: 8px; text-align: center;">{{ $otp }}</div>
</x-mail::panel>

Kode berlaku selama **{{ $expiryMinutes }} menit** dan hanya bisa dipakai sekali.

Jika Anda tidak sedang login, abaikan email ini dan segera ganti password akun Anda.

Terima kasih,<br>
{{ \App\Models\Setting::get('site_name', config('app.name')) }}
</x-mail::message>
