# CBT Ujian - Computer Based Test

Aplikasi **Computer Based Test (CBT)** berbasis web untuk pelaksanaan ujian online di lingkungan sekolah. Dibangun menggunakan **Laravel 11**, mendukung multi-role (Admin, Guru, Proktor, Siswa), dilengkapi fitur anti-cheat, randomisasi soal, penilaian otomatis, dan monitoring real-time.

---

## Fitur Utama

- **Multi Role**: Admin, Guru, Proktor, Siswa
- **Manajemen Soal**: Pilihan Ganda, PG Kompleks, Isian Singkat, Essay (mendukung gambar & rumus matematika via CKEditor 5 + MathJax)
- **Manajemen Ujian**: Buat ujian, atur waktu, KKM, token akses, publish/draft
- **Randomisasi Soal & Opsi**: Urutan soal dan opsi diacak per-siswa secara deterministik
- **Anti-Cheat System**: Deteksi tab-switch, fullscreen enforcement, log pelanggaran
- **Monitoring Real-time**: Proktor & Admin dapat memantau peserta secara live
- **Penilaian**: Otomatis untuk PG/Isian, manual untuk Essay (admin & guru)
- **Ruang Ujian**: Manajemen ruang, distribusi siswa, penugasan proktor
- **Import Data Excel**: Import massal siswa, guru, proktor, kelas, ruang ujian, distribusi ruang
- **Export Laporan**: Excel, PDF, Berita Acara
- **Analisis Soal**: Statistik tingkat kesulitan & daya pembeda

---

## Persyaratan Sistem

| Komponen | Versi Minimum |
|----------|---------------|
| PHP | 8.2+ |
| Composer | 2.x |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Node.js | 18+ (opsional, untuk Vite) |
| Web Server | Apache / Nginx |

> **Rekomendasi**: Gunakan [XAMPP](https://www.apachefriends.org/) (PHP 8.2) untuk kemudahan instalasi di Windows.

### Ekstensi PHP yang Diperlukan

- `php-mbstring`
- `php-xml`
- `php-zip`
- `php-gd`
- `php-mysql`
- `php-fileinfo`
- `php-bcmath`

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/cbt-ai.git
cd cbt-ai
```

### 2. Install Dependensi PHP

```bash
composer install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` sesuai konfigurasi server Anda:

```dotenv
APP_NAME="CBT Ujian"
APP_URL=http://localhost:8080
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cbt_ai
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Buat Database

Buat database MySQL dengan nama sesuai konfigurasi `.env`:

```sql
CREATE DATABASE cbt_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Jalankan Migrasi & Seeder

```bash
php artisan migrate --seed
```

Seeder akan membuat data awal berupa:
- **Roles & Permissions** (admin, guru, proktor, siswa)
- **Akun Admin**: `admin` / `admin123`
- **3 Guru**: `budi.guru` / `guru123`, `siti.guru` / `guru123`, `ahmad.guru` / `guru123`
- **1 Proktor**: `proktor` / `proktor123`
- **5 Siswa contoh**: `siswa000001` / `000001` s.d. `siswa000005` / `000005`
- **5 Jurusan**, **30 Kelas**, dan **9 Mata Pelajaran**

### 6. Buat Symbolic Link Storage

```bash
php artisan storage:link
```

### 7. Jalankan Aplikasi

**Menggunakan built-in server Laravel:**

```bash
php artisan serve --port=8080
```

**Menggunakan XAMPP:**

Letakkan folder project di `htdocs/cbt-ai`, lalu akses melalui browser:

```
http://localhost/cbt-ai/public
```

> **Tips XAMPP**: Agar URL lebih bersih, buat Virtual Host di Apache yang mengarah ke folder `public/`.

---

## Akun Default

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Guru | `budi.guru` | `guru123` |
| Proktor | `proktor` | `proktor123` |
| Siswa | `siswa000001` | `000001` |

---

## Import Data Massal

Aplikasi mendukung import data dari file Excel (`.xlsx`, `.xls`, `.csv`) melalui menu **Admin > Import Data**:

| Data | Format Kolom |
|------|-------------|
| Kelas | Nama Kelas \| Tingkat (X/XI/XII) \| Jurusan \| Tahun Ajaran |
| Ruang Ujian | Kode \| Nama \| Kapasitas \| Lokasi |
| Guru | NIP \| Nama \| Username \| Email \| Mapel \| No HP \| Alamat \| Password |
| Proktor | Nama \| Username \| Kode Ruang \| Password |
| Siswa | NIS \| NISN \| Nama \| JK \| Kelas \| Jurusan \| No HP \| Alamat |
| Distribusi Ruang | NIS/Kelas \| Kode Ruang |

Template Excel dapat diunduh langsung dari halaman import.

**Urutan import yang disarankan:**
1. Kelas → 2. Ruang Ujian → 3. Guru → 4. Proktor → 5. Siswa → 6. Distribusi Ruang

---

## Struktur Role & Hak Akses

### Admin
- Kelola seluruh master data (jurusan, kelas, mapel, guru, siswa, ruang, proktor)
- Kelola ujian (CRUD, publish, assign ruang)
- Monitor ujian real-time
- Lihat hasil & nilai, penilaian essay
- Import/export data

### Guru
- Kelola bank soal (CRUD, import dari Excel)
- Kelola ujian (CRUD, publish, generate token)
- Nilai essay
- Lihat hasil & analisis soal
- Export laporan (Excel, PDF, Berita Acara)

### Proktor
- Monitor ujian di ruang yang ditugaskan
- Buka/reset/selesaikan peserta ujian

### Siswa
- Ikuti ujian dengan token akses
- Lihat riwayat & nilai ujian

---

## Teknologi

- **Backend**: Laravel 11, PHP 8.2
- **Frontend**: Bootstrap 5.3, Bootstrap Icons, Blade Templates
- **Database**: MySQL / MariaDB
- **Rich Text Editor**: CKEditor 5
- **Rumus Matematika**: MathJax 3
- **Export**: Maatwebsite Excel 3.1, DomPDF
- **Authorization**: Spatie Laravel Permission 6

---

## Perintah Artisan Berguna

```bash
# Clear semua cache
php artisan optimize:clear

# Lihat daftar route
php artisan route:list

# Reset database (HATI-HATI: menghapus semua data)
php artisan migrate:fresh --seed

# Maintenance mode
php artisan down
php artisan up
```

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Error 500 setelah clone | Pastikan `composer install` dan `php artisan key:generate` sudah dijalankan |
| Halaman blank / error class not found | Jalankan `composer dump-autoload` |
| Gambar tidak muncul | Jalankan `php artisan storage:link` |
| Error permission denied (Linux) | `chmod -R 775 storage bootstrap/cache` |
| Session / cache error | `php artisan optimize:clear` |
| Import Excel gagal | Pastikan ekstensi `php-zip` dan `php-gd` aktif |

---

## Lisensi

Aplikasi ini menggunakan framework [Laravel](https://laravel.com) yang dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).
