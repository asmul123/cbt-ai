<?php

namespace App\Services;

use App\Models\Soal;
use App\Models\Opsi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BankSoalService
{
    /**
     * Buat soal baru dengan opsi
     */
    public function buatSoal(array $data, array $opsiData = []): Soal
    {
        return DB::transaction(function () use ($data, $opsiData) {
            $soal = Soal::create($data);

            foreach ($opsiData as $opsi) {
                $soal->opsi()->create($opsi);
            }

            return $soal->load('opsi');
        });
    }

    /**
     * Update soal beserta opsi
     */
    public function updateSoal(Soal $soal, array $data, array $opsiData = []): Soal
    {
        return DB::transaction(function () use ($soal, $data, $opsiData) {
            $soal->update($data);

            if (!empty($opsiData)) {
                $soal->opsi()->delete();
                foreach ($opsiData as $opsi) {
                    $soal->opsi()->create($opsi);
                }
            }

            return $soal->load('opsi');
        });
    }

    /**
     * Import soal dari Excel
     */
    public function importFromExcel(UploadedFile $file, int $mapelId, int $guruId): array
    {
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['File kosong']];
        }

        $rows = $data[0];
        $success = 0;
        $failed = 0;
        $errors = [];

        // Skip header row
        array_shift($rows);

        foreach ($rows as $index => $row) {
            try {
                $rowNum = $index + 2;

                if (empty($row[0])) continue; // Skip empty rows

                $soalData = [
                    'mapel_id' => $mapelId,
                    'guru_id' => $guruId,
                    'tipe_soal' => $this->mapTipeSoal($row[1] ?? 'pg'),
                    'soal' => $row[0],
                    'tingkat_kesulitan' => $this->mapTingkat($row[7] ?? 'sedang'),
                    'kompetensi_dasar' => $row[8] ?? null,
                    'bobot' => floatval($row[9] ?? 1),
                    'status' => 'draft',
                ];

                $opsiData = [];
                $labels = ['A', 'B', 'C', 'D', 'E'];
                $jawabanBenar = strtoupper(trim($row[6] ?? 'A'));

                for ($i = 0; $i < 5; $i++) {
                    $teks = $row[$i + 2] ?? null;
                    if ($teks) {
                        $opsiData[] = [
                            'label' => $labels[$i],
                            'teks' => $teks,
                            'is_benar' => str_contains($jawabanBenar, $labels[$i]),
                            'urutan' => $i,
                        ];
                    }
                }

                $this->buatSoal($soalData, $opsiData);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Baris {$rowNum}: " . $e->getMessage();
            }
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    private function mapTipeSoal(string $tipe): string
    {
        return match (strtolower(trim($tipe))) {
            'pg', 'pilihan ganda' => 'pg',
            'pg kompleks', 'pg_kompleks' => 'pg_kompleks',
            'isian', 'isian singkat' => 'isian',
            'essay', 'uraian' => 'essay',
            default => 'pg',
        };
    }

    private function mapTingkat(string $tingkat): string
    {
        return match (strtolower(trim($tingkat))) {
            'mudah', 'easy' => 'mudah',
            'sulit', 'hard', 'susah' => 'sulit',
            default => 'sedang',
        };
    }

    /**
     * Upload gambar soal
     */
    public function uploadGambar(UploadedFile $file): string
    {
        return $file->store('soal/gambar', 'public');
    }

    /**
     * Duplikat soal
     */
    public function duplikatSoal(Soal $soal): Soal
    {
        return DB::transaction(function () use ($soal) {
            $newSoal = $soal->replicate();
            $newSoal->status = 'draft';
            $newSoal->save();

            foreach ($soal->opsi as $opsi) {
                $newOpsi = $opsi->replicate();
                $newOpsi->soal_id = $newSoal->id;
                $newOpsi->save();
            }

            return $newSoal->load('opsi');
        });
    }
}
