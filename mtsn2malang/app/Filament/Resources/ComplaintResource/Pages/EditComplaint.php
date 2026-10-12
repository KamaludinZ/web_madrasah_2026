<?php
namespace App\Filament\Resources\ComplaintResource\Pages;
use App\Filament\Resources\ComplaintResource;
use App\Jobs\SendComplaintNotificationJob;
use App\Services\ComplaintNotifier;
use Filament\Resources\Pages\EditRecord;
class EditComplaint extends EditRecord
{
    protected static string $resource = ComplaintResource::class;

    /** Kabari pelapor hanya bila status atau tanggapan benar-benar berubah. */
    protected function afterSave(): void
    {
        if ($this->record->wasChanged(['status', 'response'])) {
            SendComplaintNotificationJob::dispatch($this->record->id, ComplaintNotifier::EVENT_UPDATED);
        }
    }
}
