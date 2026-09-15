<?php

namespace App\Services;

use App\Models\Achievement;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use Throwable;

class AchievementImportService
{
    /**
     * Kolom template, urut sesuai kolom yang diharapkan di file Excel.
     */
    public const COLUMNS = [
        'student_name'     => 'Nama Siswa',
        'competition_name' => 'Nama Lomba / Kompetisi',
        'level'            => 'Tingkat (Regional/Provinsi/Nasional/Internasional)',
        'rank'             => 'Peringkat / Juara',
        'organizer'        => 'Penyelenggara',
        'academic_year'    => 'Tahun Pelajaran',
        'achievement_date' => 'Tanggal Prestasi (dd-mm-yyyy)',
    ];

    private const LEVEL_MAP = [
        'internasional' => 'internasional',
        'international' => 'internasional',
        'provinsi'      => 'provinsi',
        'nasional'      => 'nasional',
        'regional'      => 'regional',
        'kota'          => 'regional',
        'kabupaten'     => 'regional',
    ];

    /**
     * Tulis file template xlsx ke stream output aktif.
     * Dipanggil dari dalam closure response()->streamDownload().
     */
    public function writeTemplate(): void
    {
        $writer = new Writer();
        $writer->openToFile('php://output');

        $writer->addRow(Row::fromValues(array_values(self::COLUMNS), (new Style())->setFontBold()));

        $writer->addRow(Row::fromValues([
            'Ahmad Fauzan',
            'Olimpiade Sains Nasional (OSN) Matematika',
            'Nasional',
            'Juara 1',
            'Kemdikbud',
            '2024/2025',
            '17-08-2024',
        ]));

        $writer->close();
    }

    /**
     * @return array{created: int, errors: array<int, string>, warnings: array<int, string>}
     */
    public function import(string $absolutePath): array
    {
        $created  = 0;
        $errors   = [];
        $warnings = [];

        $reader = new Reader();
        $reader->open($absolutePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            $rowNumber = 0;

            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;

                if ($rowNumber === 1) {
                    continue; // baris header
                }

                $cells = $row->toArray();

                if (collect($cells)->every(fn ($value) => blank($value))) {
                    continue; // baris kosong
                }

                [$studentName, $competitionName, $levelRaw, $rank, $organizer, $academicYear, $dateRaw] =
                    array_pad(array_slice($cells, 0, 7), 7, null);

                $studentName     = is_string($studentName) ? trim($studentName) : $studentName;
                $competitionName = is_string($competitionName) ? trim($competitionName) : $competitionName;
                $rank            = is_string($rank) ? trim($rank) : $rank;
                $academicYear    = is_string($academicYear) ? trim((string) $academicYear) : $academicYear;
                $organizer       = is_string($organizer) ? trim($organizer) : $organizer;

                if (blank($studentName) || blank($competitionName) || blank($rank) || blank($academicYear)) {
                    $errors[] = "Baris {$rowNumber}: Nama Siswa, Nama Lomba, Peringkat, dan Tahun Pelajaran wajib diisi.";
                    continue;
                }

                $level = $this->normalizeLevel($levelRaw);

                if (! $level) {
                    $errors[] = "Baris {$rowNumber}: Tingkat \"{$levelRaw}\" tidak dikenali. Gunakan Regional, Provinsi, Nasional, atau Internasional.";
                    continue;
                }

                $achievementDate = null;

                if (filled($dateRaw)) {
                    $achievementDate = $this->normalizeDate($dateRaw);

                    if (! $achievementDate) {
                        $warnings[] = "Baris {$rowNumber}: Tanggal Prestasi \"{$dateRaw}\" tidak dikenali, dikosongkan.";
                    }
                }

                try {
                    Achievement::create([
                        'student_name'     => $studentName,
                        'competition_name' => $competitionName,
                        'level'            => $level,
                        'rank'             => $rank,
                        'organizer'        => $organizer ?: null,
                        'academic_year'    => $academicYear,
                        'achievement_date' => $achievementDate,
                    ]);

                    $created++;
                } catch (Throwable $e) {
                    $errors[] = "Baris {$rowNumber}: Gagal disimpan — {$e->getMessage()}";
                }
            }

            break; // hanya proses sheet pertama
        }

        $reader->close();

        return ['created' => $created, 'errors' => $errors, 'warnings' => $warnings];
    }

    private function normalizeLevel(mixed $raw): ?string
    {
        if (! is_string($raw) && ! is_numeric($raw)) {
            return null;
        }

        $value = Str::lower(trim((string) $raw));

        foreach (self::LEVEL_MAP as $needle => $level) {
            if (Str::contains($value, $needle)) {
                return $level;
            }
        }

        return null;
    }

    private function normalizeDate(mixed $raw): ?string
    {
        if ($raw instanceof DateTimeInterface) {
            return Carbon::instance($raw)->format('Y-m-d');
        }

        if (is_int($raw) || is_float($raw)) {
            // Sel diformat angka biasa (bukan tanggal) oleh Excel: tetap dibaca
            // sebagai serial date Excel (epoch 30 Des 1899) agar tidak hilang.
            return Carbon::create(1899, 12, 30)->addDays((int) $raw)->format('Y-m-d');
        }

        $raw = trim((string) $raw);

        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-m-y', 'd/m/y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
}
