@extends('layouts.app')
@section('title', 'Pilih Soal Ujian')

@section('content')
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-list-check"></i> Pilih Soal untuk: {{ $ujian->nama }}</h6>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col"><strong>Mapel:</strong> {{ $ujian->mapel->nama ?? '-' }}</div>
            <div class="col"><strong>Soal terpilih:</strong> <span class="badge bg-primary" id="countSelected">{{ $ujian->soal->count() }}</span></div>
        </div>
    </div>
</div>

<form action="{{ route('guru.ujian.soalSync', $ujian) }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                            </th>
                            <th>Soal</th>
                            <th>Tipe</th>
                            <th>Tingkat</th>
                            <th>Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $selectedIds = $ujian->soal->pluck('id')->toArray(); @endphp
                        @forelse($soalTersedia as $s)
                        <tr>
                            <td>
                                <input type="checkbox" name="soal_ids[]" value="{{ $s->id }}" class="soal-check"
                                    {{ in_array($s->id, $selectedIds) ? 'checked' : '' }} onchange="updateCount()">
                            </td>
                            <td>{{ Str::limit(strip_tags($s->soal), 100) }}</td>
                            <td>
                                @if($s->tipe == 'pg') <span class="badge bg-primary">PG</span>
                                @elseif($s->tipe == 'pg_kompleks') <span class="badge bg-info">PG Kompleks</span>
                                @elseif($s->tipe == 'isian') <span class="badge bg-warning">Isian</span>
                                @else <span class="badge bg-success">Essay</span>
                                @endif
                            </td>
                            <td>
                                @if($s->tingkat_kesulitan == 'mudah') <span class="badge bg-success">Mudah</span>
                                @elseif($s->tingkat_kesulitan == 'sedang') <span class="badge bg-warning">Sedang</span>
                                @else <span class="badge bg-danger">Sulit</span>
                                @endif
                            </td>
                            <td>{{ $s->bobot }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada soal untuk mapel ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Pilihan Soal</button>
        <a href="{{ route('guru.ujian.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleAll(el) {
    document.querySelectorAll('.soal-check').forEach(cb => cb.checked = el.checked);
    updateCount();
}
function updateCount() {
    document.getElementById('countSelected').textContent = document.querySelectorAll('.soal-check:checked').length;
}
</script>
@endpush
