<?php

namespace App\Jobs;

use App\Models\Complaint;
use App\Services\ComplaintNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Kirim notifikasi pengaduan/saran di background — pelapor tidak perlu
 * menunggu SMTP / gateway WhatsApp saat menekan tombol kirim.
 */
class SendComplaintNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public readonly int $complaintId,
        public readonly string $event,
    ) {
    }

    public function handle(ComplaintNotifier $notifier): void
    {
        $complaint = Complaint::find($this->complaintId);

        if ($complaint) {
            $notifier->handle($complaint, $this->event);
        }
    }
}
