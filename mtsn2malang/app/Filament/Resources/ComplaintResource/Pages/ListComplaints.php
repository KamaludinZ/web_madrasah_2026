<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use App\Models\Complaint;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListComplaints extends ListRecords
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_rekap_excel')
                ->label('Download Rekap Excel')
                ->icon('heroicon-m-arrow-down-tray')
                ->action(fn() => $this->downloadRekapExcel()),
        ];
    }

    protected function downloadRekapExcel(): StreamedResponse
    {
        $fileName = 'rekap-pengaduan-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar karakter Indonesia terbaca baik di Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No Tiket',
                'Jenis',
                'Nama Pelapor',
                'Email',
                'Telepon',
                'Subjek',
                'Tanggapan',
                'Status',
                'Tanggal Pengaduan',
                'Tanggal Tanggapan',
            ]);

            Complaint::query()
                ->orderByDesc('created_at')
                ->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->ticket_number,
                            $row->type,
                            $row->name,
                            $row->email,
                            $row->phone,
                            $row->subject,
                            $row->response,
                            $row->status,
                            optional($row->created_at)?->format('Y-m-d H:i:s'),
                            optional($row->responded_at)?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, $headers);
    }
}
