<x-mail::message>
# Assalamu'alaikum {{ $subscriberName }}

Ada informasi terbaru dari **MTsN 2 Kota Malang** yang mungkin Anda minati.

## {{ $newsTitle }}

{{ $newsExcerpt }}

<x-mail::button :url="$newsUrl">
Baca Berita Selengkapnya
</x-mail::button>

Jika Anda tidak ingin menerima update berita berikutnya, klik tautan berikut:

<x-mail::button :url="$unsubscribeUrl" color="error">
Berhenti Berlangganan
</x-mail::button>

Terima kasih atas dukungan Anda,<br>
{{ \App\Models\Setting::get('site_name', config('app.name')) }}
</x-mail::message>
