<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $guruRole = Role::create(['name' => 'guru']);
        $proktorRole = Role::create(['name' => 'proktor']);
        $siswaRole = Role::create(['name' => 'siswa']);

        // Create Permissions
        $permissions = [
            'manage-jurusan', 'manage-kelas', 'manage-mapel',
            'manage-guru', 'manage-siswa', 'manage-users',
            'create-soal', 'edit-soal', 'delete-soal', 'view-soal', 'import-soal',
            'create-ujian', 'edit-ujian', 'delete-ujian', 'view-ujian',
            'generate-token', 'publish-ujian',
            'ikut-ujian', 'submit-ujian',
            'monitor-ujian',
            'nilai-essay', 'view-hasil',
            'view-analisis',
            'export-laporan',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm]);
        }

        $adminRole->givePermissionTo(Permission::all());
        $guruRole->givePermissionTo([
            'create-soal', 'edit-soal', 'delete-soal', 'view-soal', 'import-soal',
            'create-ujian', 'edit-ujian', 'view-ujian', 'publish-ujian',
            'nilai-essay', 'view-hasil', 'view-analisis', 'export-laporan',
        ]);
        $proktorRole->givePermissionTo(['monitor-ujian', 'view-ujian', 'view-hasil']);
        $siswaRole->givePermissionTo(['ikut-ujian', 'submit-ujian']);

        // Admin
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@cbt.local',
            'password' => Hash::make('admin123'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Jurusan
        $jurusanData = [
            ['kode' => 'TKJ', 'nama' => 'Teknik Komputer dan Jaringan'],
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak'],
            ['kode' => 'DKV', 'nama' => 'Desain Komunikasi Visual'],
            ['kode' => 'MM', 'nama' => 'Multimedia'],
            ['kode' => 'AKL', 'nama' => 'Akuntansi dan Keuangan Lembaga'],
        ];
        $jurusanList = [];
        foreach ($jurusanData as $j) {
            $jurusanList[$j['kode']] = Jurusan::create($j);
        }

        // Kelas
        foreach ($jurusanList as $kode => $jurusan) {
            foreach (['X', 'XI', 'XII'] as $tingkat) {
                for ($i = 1; $i <= 2; $i++) {
                    Kelas::create([
                        'nama' => "{$tingkat} {$kode} {$i}",
                        'tingkat' => $tingkat,
                        'jurusan_id' => $jurusan->id,
                        'tahun_ajaran' => '2025/2026',
                    ]);
                }
            }
        }

        // Mapel
        $mapelData = [
            ['kode' => 'MTK', 'nama' => 'Matematika', 'jurusan_id' => null],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia', 'jurusan_id' => null],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris', 'jurusan_id' => null],
            ['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam', 'jurusan_id' => null],
            ['kode' => 'KJD', 'nama' => 'Komputer dan Jaringan Dasar', 'jurusan_id' => $jurusanList['TKJ']->id],
            ['kode' => 'ASJ', 'nama' => 'Administrasi Sistem Jaringan', 'jurusan_id' => $jurusanList['TKJ']->id],
            ['kode' => 'PBO', 'nama' => 'Pemrograman Berorientasi Objek', 'jurusan_id' => $jurusanList['RPL']->id],
            ['kode' => 'PBW', 'nama' => 'Pemrograman Berbasis Web', 'jurusan_id' => $jurusanList['RPL']->id],
            ['kode' => 'DG', 'nama' => 'Desain Grafis', 'jurusan_id' => $jurusanList['DKV']->id],
        ];
        $mapelList = [];
        foreach ($mapelData as $m) {
            $mapelList[$m['kode']] = Mapel::create($m);
        }

        // Guru
        $guruDataList = [
            ['nip' => '198501012010011001', 'nama' => 'Budi Santoso', 'mapel' => 'MTK', 'username' => 'budi.guru'],
            ['nip' => '198702032012012002', 'nama' => 'Siti Aminah', 'mapel' => 'BIN', 'username' => 'siti.guru'],
            ['nip' => '199003052014011003', 'nama' => 'Ahmad Fauzi', 'mapel' => 'KJD', 'username' => 'ahmad.guru'],
        ];
        foreach ($guruDataList as $g) {
            $user = User::create([
                'name' => $g['nama'],
                'username' => $g['username'],
                'email' => $g['username'] . '@cbt.local',
                'password' => Hash::make('guru123'),
                'is_active' => true,
            ]);
            $user->assignRole('guru');
            Guru::create([
                'user_id' => $user->id,
                'nip' => $g['nip'],
                'nama' => $g['nama'],
                'mapel_id' => $mapelList[$g['mapel']]->id,
            ]);
        }

        // Proktor
        $proktor = User::create([
            'name' => 'Proktor Ujian',
            'username' => 'proktor',
            'email' => 'proktor@cbt.local',
            'password' => Hash::make('proktor123'),
            'is_active' => true,
        ]);
        $proktor->assignRole('proktor');

        // Siswa sample
        $kelasTKJ = Kelas::where('nama', 'XII TKJ 1')->first();
        $jurusanTKJ = $jurusanList['TKJ'];
        for ($i = 1; $i <= 5; $i++) {
            $nis = str_pad($i, 6, '0', STR_PAD_LEFT);
            $user = User::create([
                'name' => "Siswa TKJ {$i}",
                'username' => "siswa{$nis}",
                'email' => "siswa{$nis}@cbt.local",
                'password' => Hash::make($nis),
                'is_active' => true,
            ]);
            $user->assignRole('siswa');
            Siswa::create([
                'user_id' => $user->id,
                'nis' => $nis,
                'nama' => "Siswa TKJ {$i}",
                'jenis_kelamin' => $i % 2 === 0 ? 'P' : 'L',
                'kelas_id' => $kelasTKJ->id,
                'jurusan_id' => $jurusanTKJ->id,
            ]);
        }
    }
}
