@extends('layouts.app')
@section('title', 'Nilai Essay')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Penilaian Essay: {{ $ujian->nama }}</h5>
    <a href="{{ route('guru.hasil.show', $ujian) }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

@forelse($jawabanEssay as $jawaban)
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
        <span class="fw-semibold">{{ $jawaban->pesertaUjian->siswa->user->name ?? 'Siswa' }} ({{ $jawaban->pesertaUjian->siswa->nis ?? '-' }})</span>
        <span class="badge {{ $jawaban->skor !== null ? 'bg-success' : 'bg-warning' }}">{{ $jawaban->skor !== null ? 'Sudah Dinilai' : 'Belum Dinilai' }}</span>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <small class="text-muted">Soal:</small>
            <div class="p-2 bg-light rounded">{!! nl2br(e($jawaban->soal->soal)) !!}</div>
        </div>
        <div class="mb-3">
            <small class="text-muted">Jawaban Siswa:</small>
            <div class="p-2 bg-light rounded">{!! nl2br(e($jawaban->jawaban ?? '-')) !!}</div>
        </div>
        <form action="{{ route('guru.hasil.simpanEssay', $jawaban) }}" method="POST" class="d-flex align-items-center gap-3">
            @csrf
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text">Skor (max: {{ $jawaban->soal->bobot }})</span>
                <input type="number" name="skor" class="form-control" min="0" max="{{ $jawaban->soal->bobot }}" step="0.1" value="{{ $jawaban->skor }}" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Simpan Nilai</button>
        </form>
    </div>
</div>
@empty
<div class="alert alert-info">Tidak ada jawaban essay yang perlu dinilai.</div>
@endforelse
@endsection
