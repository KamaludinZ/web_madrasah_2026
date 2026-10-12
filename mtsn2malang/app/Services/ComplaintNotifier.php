<?php

namespace App\Services;

use App\Filament\Resources\ComplaintResource;
use App\Filament\Resources\SaranResource;
use App\Models\Complaint;
use App\Models\Setting;
use App\Support\MailSettings;
use App\Support\WhatsappGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Notifikasi email & WhatsApp untuk pengaduan (whistleblowing / masyarakat)
 * dan saran. Penerima, saluran, dan template diatur di Pengaturan Website →
 * tab Notifikasi.
 *
 *  - Pengaduan baru  → petugas + pelapor (tanda terima berisi nomor tiket)
 *  - Status/tanggapan pengaduan berubah → pelapor
 *  - Saran baru      → petugas saja
 */
class ComplaintNotifier
{
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';

    /** Template bawaan — dipakai selama admin belum mengubahnya. */
    public const TEMPLATES = [
        // Pengaduan baru → petugas
        'notif_tpl_officer_new_subject' => '[Pengaduan Baru] {tiket} — {subjek}',
        'notif_tpl_officer_new_email'   => "Assalamu'alaikum,\n\nAda {jenis} baru yang masuk melalui website.\n\nNo. Tiket : {tiket}\nPelapor   : {nama}\nSubjek    : {subjek}\nTanggal   : {tanggal}\n\nIsi laporan:\n{isi}\n\nSilakan tindak lanjuti melalui panel admin:\n{link_admin}\n\n— Sistem {nama_madrasah}",
        'notif_tpl_officer_new_wa'      => "*{jenis} Baru*\n\nNo. Tiket: *{tiket}*\nPelapor: {nama}\nSubjek: {subjek}\n\n{isi_singkat}\n\nTindak lanjuti: {link_admin}",

        // Saran baru → petugas
        'notif_tpl_officer_saran_subject' => '[Saran Baru] {tiket} — {subjek}',
        'notif_tpl_officer_saran_email'   => "Assalamu'alaikum,\n\nAda saran baru yang masuk melalui website.\n\nNo. Tiket : {tiket}\nPengirim  : {nama}\nSubjek    : {subjek}\nTanggal   : {tanggal}\n\nIsi saran:\n{isi}\n\nLihat di panel admin:\n{link_admin}\n\n— Sistem {nama_madrasah}",
        'notif_tpl_officer_saran_wa'      => "*Saran Baru*\n\nNo. Tiket: *{tiket}*\nPengirim: {nama}\nSubjek: {subjek}\n\n{isi_singkat}\n\nLihat: {link_admin}",

        // Pengaduan diterima → pelapor
        'notif_tpl_reporter_new_subject' => 'Pengaduan Anda Kami Terima — {tiket}',
        'notif_tpl_reporter_new_email'   => "Yth. {nama},\n\nTerima kasih, {jenis} Anda telah kami terima dan akan segera ditindaklanjuti.\n\nNo. Tiket : {tiket}\nSubjek    : {subjek}\nTanggal   : {tanggal}\n\nSimpan nomor tiket di atas untuk memantau perkembangan laporan Anda:\n{link_lacak}\n\nHormat kami,\n{nama_madrasah}",
        'notif_tpl_reporter_new_wa'      => "Yth. {nama},\n\nTerima kasih, {jenis} Anda telah kami terima.\n\nNo. Tiket: *{tiket}*\nSubjek: {subjek}\n\nPantau perkembangannya di:\n{link_lacak}\n\n— {nama_madrasah}",

        // Status / tanggapan diperbarui → pelapor
        'notif_tpl_reporter_update_subject' => 'Perkembangan Pengaduan {tiket}: {status}',
        'notif_tpl_reporter_update_email'   => "Yth. {nama},\n\nAda perkembangan pada {jenis} Anda.\n\nNo. Tiket : {tiket}\nSubjek    : {subjek}\nStatus    : {status}\n\nTanggapan petugas:\n{tanggapan}\n\nDetail dapat dilihat di:\n{link_lacak}\n\nHormat kami,\n{nama_madrasah}",
        'notif_tpl_reporter_update_wa'      => "Yth. {nama},\n\nPerkembangan {jenis} Anda:\nNo. Tiket: *{tiket}*\nStatus: *{status}*\n\nTanggapan:\n{tanggapan}\n\nDetail: {link_lacak}\n\n— {nama_madrasah}",
    ];

    public const PLACEHOLDERS = [
        '{nama}'          => 'Nama pelapor/pengirim (Anonim bila kosong)',
        '{tiket}'         => 'Nomor tiket',
        '{jenis}'         => 'Jenis laporan (Whistleblowing / Pengaduan Masyarakat / Saran)',
        '{subjek}'        => 'Subjek',
        '{isi}'           => 'Isi lengkap',
        '{isi_singkat}'   => 'Isi dipotong 200 karakter',
        '{status}'        => 'Status terkini',
        '{tanggapan}'     => 'Tanggapan petugas',
        '{tanggal}'       => 'Tanggal laporan dibuat',
        '{link_lacak}'    => 'Link pelacakan tiket untuk pelapor',
        '{link_admin}'    => 'Link ke laporan di panel admin (untuk petugas)',
        '{nama_madrasah}' => 'Nama website',
    ];

    private const STATUS_LABELS = [
        'pending'  => 'Menunggu',
        'process'  => 'Diproses',
        'resolved' => 'Selesai',
        'rejected' => 'Ditolak',
    ];

    private const TYPE_LABELS = [
        'whistleblowing' => 'Whistleblowing',
        'masyarakat'     => 'Pengaduan Masyarakat',
        'saran'          => 'Saran',
    ];

    public function handle(Complaint $complaint, string $event): void
    {
        $isSaran = $complaint->type === 'saran';

        if ($this->emailEnabled()) {
            // Queue worker tidak melihat config dari request web — terapkan
            // pengaturan SMTP sekali di awal, bukan per email (purge mailer).
            MailSettings::apply();
        }

        if ($event === self::EVENT_CREATED) {
            $this->notifyOfficers($complaint, $isSaran ? 'officer_saran' : 'officer_new');
            if (! $isSaran) {
                $this->notifyReporter($complaint, 'reporter_new');
            }
        } elseif ($event === self::EVENT_UPDATED && ! $isSaran) {
            $this->notifyReporter($complaint, 'reporter_update');
        }
    }

    private function notifyOfficers(Complaint $complaint, string $template): void
    {
        if ($this->emailEnabled()) {
            foreach (static::parseList(Setting::get('notif_officer_emails')) as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->sendEmail($email, $complaint, $template);
                }
            }
        }

        if ($this->whatsappEnabled()) {
            foreach (static::parseList(Setting::get('notif_officer_whatsapp')) as $number) {
                $this->sendWhatsapp($number, $complaint, $template);
            }
        }
    }

    private function notifyReporter(Complaint $complaint, string $template): void
    {
        if (! (bool) Setting::get('notif_reporter_enabled', true)) {
            return;
        }

        if ($this->emailEnabled() && filter_var($complaint->email, FILTER_VALIDATE_EMAIL)) {
            $this->sendEmail($complaint->email, $complaint, $template);
        }

        if ($this->whatsappEnabled() && filled($complaint->phone)) {
            $this->sendWhatsapp($complaint->phone, $complaint, $template);
        }
    }

    /** Satu penerima gagal tidak boleh menggagalkan penerima lainnya. */
    private function sendEmail(string $to, Complaint $complaint, string $template): void
    {
        try {
            $subject = $this->render(static::template("notif_tpl_{$template}_subject"), $complaint);
            $body    = $this->render(static::template("notif_tpl_{$template}_email"), $complaint);

            Mail::raw($body, fn ($message) => $message->to($to)->subject($subject));
        } catch (\Throwable $e) {
            Log::warning('Notifikasi email pengaduan gagal', ['ticket' => $complaint->ticket_number, 'to' => $to, 'error' => $e->getMessage()]);
        }
    }

    private function sendWhatsapp(string $number, Complaint $complaint, string $template): void
    {
        try {
            WhatsappGateway::send($number, $this->render(static::template("notif_tpl_{$template}_wa"), $complaint));
        } catch (\Throwable $e) {
            Log::warning('Notifikasi WhatsApp pengaduan gagal', ['ticket' => $complaint->ticket_number, 'to' => $number, 'error' => $e->getMessage()]);
        }
    }

    private function emailEnabled(): bool
    {
        return (bool) Setting::get('notif_email_enabled', false);
    }

    private function whatsappEnabled(): bool
    {
        return (bool) Setting::get('notif_whatsapp_enabled', false) && WhatsappGateway::isConfigured();
    }

    public static function template(string $key): string
    {
        $value = Setting::get($key);

        return filled($value) ? (string) $value : (self::TEMPLATES[$key] ?? '');
    }

    public function render(string $template, Complaint $complaint): string
    {
        $isSaran = $complaint->type === 'saran';

        $trackUrl = $isSaran
            ? route('survei.saran.lacak', ['tiket' => $complaint->ticket_number])
            : route('pengaduan.pelacakan', ['ticket' => $complaint->ticket_number]);

        $adminUrl = $isSaran
            ? SaranResource::getUrl('edit', ['record' => $complaint->id])
            : ComplaintResource::getUrl('edit', ['record' => $complaint->id]);

        return strtr($template, [
            '{nama}'          => filled($complaint->name) ? $complaint->name : 'Anonim',
            '{tiket}'         => (string) $complaint->ticket_number,
            '{jenis}'         => self::TYPE_LABELS[$complaint->type] ?? 'Laporan',
            '{subjek}'        => (string) $complaint->subject,
            '{isi}'           => (string) $complaint->content,
            '{isi_singkat}'   => Str::limit((string) $complaint->content, 200),
            '{status}'        => self::STATUS_LABELS[$complaint->status] ?? (string) $complaint->status,
            '{tanggapan}'     => filled($complaint->response) ? $complaint->response : '-',
            '{tanggal}'       => optional($complaint->created_at)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            '{link_lacak}'    => $trackUrl,
            '{link_admin}'    => $adminUrl,
            '{nama_madrasah}' => (string) Setting::get('site_name', 'MTsN 2 Kota Malang'),
        ]);
    }

    /** Pisahkan isian daftar (per baris / koma / titik koma) menjadi array bersih. */
    public static function parseList(?string $value): array
    {
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', (string) $value)))));
    }
}
