@extends('layouts.app')
@section('title', 'Penilaian Essay')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Penilaian Essay: {{ $ujian->nama_ujian }}</h5>
    <a href="{{ route('admin.hasil.show', $ujian) }}" class="btn btn-secondary btn-sm">Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@php
    $belumDinilai = $jawabanEssay->whereNull('skor');
    $sudahDinilai = $jawabanEssay->whereNotNull('skor');
@endphp

@if($belumDinilai->count() > 0)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle"></i> Terdapat <strong>{{ $belumDinilai->count() }}</strong> jawaban essay yang belum dinilai.
</div>
@else
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i> Semua jawaban essay sudah dinilai.
</div>
@endif

@forelse($jawabanEssay as $jawaban)
<div class="card mb-3 {{ $jawaban->skor !== null ? 'border-success' : 'border-warning' }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <span class="fw-semibold">{{ $jawaban->pesertaUjian->siswa->nama ?? 'Siswa' }}</span>
            <small class="text-muted">({{ $jawaban->pesertaUjian->siswa->nis ?? '-' }})</small>
        </span>
        <span class="badge {{ $jawaban->skor !== null ? 'bg-success' : 'bg-warning' }}">
            {{ $jawaban->skor !== null ? 'Sudah Dinilai: ' . $jawaban->skor . '/' . $jawaban->soal->bobot : 'Belum Dinilai' }}
        </span>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <small class="text-muted fw-semibold">Soal (Bobot: {{ $jawaban->soal->bobot }}):</small>
            <div class="p-2 bg-light rounded">{!! $jawaban->soal->soal !!}</div>
        </div>
        <div class="mb-3">
            <small class="text-muted fw-semibold">Jawaban Siswa:</small>
            <div class="p-2 bg-light rounded">{!! nl2br(e($jawaban->jawaban ?? 'Tidak dijawab')) !!}</div>
        </div>
        <form action="{{ route('admin.hasil.simpanEssay', $jawaban) }}" method="POST" class="d-flex align-items-center gap-3">
            @csrf
            <div class="input-group" style="max-width: 280px;">
                <span class="input-group-text">Skor (max: {{ $jawaban->soal->bobot }})</span>
                <input type="number" name="skor" class="form-control" min="0" max="{{ $jawaban->soal->bobot }}" step="0.1" value="{{ $jawaban->skor }}" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Simpan Skor</button>
        </form>
    </div>
</div>
@empty
<div class="alert alert-info">Tidak ada jawaban essay untuk ujian ini.</div>
@endforelse
@endsection
