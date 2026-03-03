<?php

namespace App\Exports;

use App\Models\HasilUjian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilUjianExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected int $ujianId;
    protected ?int $kelasId;

    public function __construct(int $ujianId, ?int $kelasId = null)
    {
        $this->ujianId = $ujianId;
        $this->kelasId = $kelasId;
    }

    public function collection()
    {
        $query = HasilUjian::where('ujian_id', $this->ujianId)
            ->with(['siswa.kelas', 'siswa.jurusan', 'ujian.mapel'])
            ->orderByDesc('nilai_akhir');

        if ($this->kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $this->kelasId));
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Jurusan', 'Mata Pelajaran', 'Skor PG', 'Skor Isian', 'Skor Essay', 'Nilai Akhir', 'Status'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->siswa->nis ?? '-',
            $row->siswa->nama ?? '-',
            $row->siswa->kelas?->nama ?? '-',
            $row->siswa->jurusan?->nama ?? '-',
            $row->ujian->mapel?->nama ?? '-',
            $row->skor_pg,
            $row->skor_isian,
            $row->skor_essay,
            $row->nilai_akhir,
            $row->status_kelulusan === 'lulus' ? 'LULUS' : ($row->status_kelulusan === 'belum_dinilai' ? 'BELUM DINILAI' : 'TIDAK LULUS'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
