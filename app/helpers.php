<?php

/**
 * Clean and sanitize HTML content from rich text editor.
 *
 * @param string|null $html
 * @return string|null
 */
/**
 * Master data helpers dengan caching 1 jam.
 * Otomatis di-invalidate saat data berubah (lihat masing-masing controller/model).
 */
function cached_mapel_aktif(): \Illuminate\Support\Collection
{
    return \Illuminate\Support\Facades\Cache::remember(
        'master:mapel_aktif',
        now()->addHour(),
        fn () => \App\Models\Mapel::where('is_active', true)->orderBy('nama')->get()
    );
}

function cached_kelas_all(): \Illuminate\Support\Collection
{
    return \Illuminate\Support\Facades\Cache::remember(
        'master:kelas_all',
        now()->addHour(),
        fn () => \App\Models\Kelas::orderBy('nama')->get()
    );
}

function cached_kelas_aktif(): \Illuminate\Support\Collection
{
    return \Illuminate\Support\Facades\Cache::remember(
        'master:kelas_aktif',
        now()->addHour(),
        fn () => \App\Models\Kelas::with('jurusan')->where('is_active', true)->orderBy('nama')->get()
    );
}

function cached_jurusan_aktif(): \Illuminate\Support\Collection
{
    return \Illuminate\Support\Facades\Cache::remember(
        'master:jurusan_aktif',
        now()->addHour(),
        fn () => \App\Models\Jurusan::where('is_active', true)->orderBy('nama')->get()
    );
}

function cached_jurusan_all(): \Illuminate\Support\Collection
{
    return \Illuminate\Support\Facades\Cache::remember(
        'master:jurusan_all',
        now()->addHour(),
        fn () => \App\Models\Jurusan::orderBy('nama')->get()
    );
}

/**
 * Invalidate semua cache master data.
 * Panggil setelah create/update/delete pada Mapel, Kelas, atau Jurusan.
 */
function forget_master_cache(): void
{
    \Illuminate\Support\Facades\Cache::forget('master:mapel_aktif');
    \Illuminate\Support\Facades\Cache::forget('master:kelas_all');
    \Illuminate\Support\Facades\Cache::forget('master:kelas_aktif');
    \Illuminate\Support\Facades\Cache::forget('master:jurusan_aktif');
    \Illuminate\Support\Facades\Cache::forget('master:jurusan_all');
}

function clean_html(?string $html): ?string
{
    if (is_null($html) || trim($html) === '') {
        return null;
    }

    // Allow common HTML tags used in rich text editors
    $allowed = '<p><br><strong><b><em><i><u><s><strike><del><sub><sup>'
        . '<h1><h2><h3><h4><h5><h6>'
        . '<ul><ol><li>'
        . '<table><thead><tbody><tr><th><td>'
        . '<a><img><figure><figcaption>'
        . '<blockquote><pre><code><hr>'
        . '<span><div>';

    return strip_tags($html, $allowed);
}
