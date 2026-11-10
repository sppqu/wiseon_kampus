<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Fakultas;
use App\Models\ProgramStudi;
use App\Models\PerguruanTinggi;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Ruangan;
use App\Models\AsistenDosen;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_halaman_root_redirect_ke_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_menu_master_data_dapat_diakses(): void
    {
        $user = User::first();

        $routes = [
            '/master/perguruan-tinggi',
            '/master/fakultas',
            '/master/program-studi',
            '/master/dosen',
            '/master/asisten-dosen',
            '/master/mahasiswa',
            '/master/mata-kuliah',
            '/master/ruang',
            '/master/bobot-nilai',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_perguruan_tinggi_dapat_diedit_manual(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::first();
        $fakeLogo = \Illuminate\Http\UploadedFile::fake()->image('logo_kampus.png', 200, 200);

        $response = $this->actingAs($user)->post('/master/perguruan-tinggi/update', [
            'nama_pt' => 'Universitas Teknologi Nusantara Update',
            'kode_pt' => '031001',
            'singkatan' => 'UTN',
            'logo' => $fakeLogo,
            'akreditasi' => 'Unggul (BAN-PT)',
            'sk_pendirian' => 'SK Mendikbud No. 142/E/O/2012',
            'tanggal_sk' => '2012-08-12',
            'nama_rektor' => 'Prof. Dr. H. Faisal Anwar, M.Eng.',
            'email' => 'info@kampus.ac.id',
            'telepon' => '(021) 789-0123',
            'website' => 'https://kampus.ac.id',
            'alamat' => 'Jl. Kampus Merdeka No. 101, Gedung Perkuliahan Terpadu',
        ]);

        $response->assertSessionHas('success');
        $pt = \App\Models\PerguruanTinggi::first();
        $this->assertEquals('Universitas Teknologi Nusantara Update', $pt->nama_pt);
        $this->assertNotNull($pt->logo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($pt->logo);

        // Bersihkan logo dummy setelah test
        $this->actingAs($user)->post('/master/perguruan-tinggi/update', [
            'nama_pt' => 'Universitas Teknologi Nusantara',
            'kode_pt' => '031001',
            'singkatan' => 'UTN',
            'hapus_logo' => 1,
            'akreditasi' => 'Unggul (BAN-PT)',
            'sk_pendirian' => 'SK Mendikbud No. 142/E/O/2012',
            'tanggal_sk' => '2012-08-12',
            'nama_rektor' => 'Prof. Dr. H. Faisal Anwar, M.Eng.',
            'email' => 'info@kampus.ac.id',
            'telepon' => '(021) 789-0123',
            'website' => 'https://kampus.ac.id',
            'alamat' => 'Jl. Kampus Merdeka No. 101, Gedung Perkuliahan Terpadu',
        ]);
        $this->assertNull($pt->fresh()->logo);
    }

    public function test_fakultas_bisa_ditambah_dan_diedit(): void
    {
        $user = User::first();
        $kodeFak = 'FH' . substr(uniqid(), -5);

        // 1. Tambah Fakultas
        $responseStore = $this->actingAs($user)->post('/master/fakultas', [
            'kode_fakultas' => $kodeFak,
            'nama_fakultas' => 'Fakultas Hukum',
            'dekan' => 'Dr. Budi Santoso, S.H., M.H.',
            'gedung' => 'Gedung D Lt. 1-2',
        ]);
        $responseStore->assertSessionHas('success');
        $this->assertDatabaseHas('fakultas', ['kode_fakultas' => $kodeFak]);

        // 2. Edit Fakultas
        $fakultas = Fakultas::where('kode_fakultas', $kodeFak)->first();
        $responseUpdate = $this->actingAs($user)->put("/master/fakultas/{$fakultas->id}", [
            'kode_fakultas' => $kodeFak,
            'nama_fakultas' => 'Fakultas Hukum & Bisnis',
            'dekan' => 'Dr. Budi Santoso, S.H., M.H.',
            'gedung' => 'Gedung D Lt. 1-3',
        ]);
        $responseUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('fakultas', ['nama_fakultas' => 'Fakultas Hukum & Bisnis']);
    }

    public function test_program_studi_bisa_ditambah_dan_diedit(): void
    {
        $user = User::first();
        $fakultas = Fakultas::first();
        $kodeProdi = 'P' . rand(10000, 99999);

        // 1. Tambah Prodi
        $responseStore = $this->actingAs($user)->post('/master/program-studi', [
            'fakultas_id' => $fakultas->id,
            'kode_prodi' => $kodeProdi,
            'nama_prodi' => 'Teknik Komputer',
            'jenjang' => 'S1',
            'akreditasi' => 'Unggul',
            'nama_kaprodi' => 'Haryanto, M.T.',
            'nidn_kaprodi' => '0011223344',
        ]);
        $responseStore->assertSessionHas('success');
        $this->assertDatabaseHas('program_studis', ['kode_prodi' => $kodeProdi]);

        // 2. Edit Prodi
        $prodi = ProgramStudi::where('kode_prodi', $kodeProdi)->first();
        $responseUpdate = $this->actingAs($user)->put("/master/program-studi/{$prodi->id}", [
            'fakultas_id' => $fakultas->id,
            'kode_prodi' => $kodeProdi,
            'nama_prodi' => 'Teknik Komputer & Jaringan',
            'jenjang' => 'S1',
            'akreditasi' => 'Unggul',
            'nama_kaprodi' => 'Haryanto, M.T.',
            'nidn_kaprodi' => '0011223344',
        ]);
        $responseUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('program_studis', ['nama_prodi' => 'Teknik Komputer & Jaringan']);
    }

    public function test_dosen_bisa_ditambah_diedit_dan_diimpor(): void
    {
        $user = User::first();
        $prodi = ProgramStudi::first();
        $nidnRandom = '04' . rand(10000000, 99999999);
        $emailRandom = 'dosen.' . Str::random(6) . '@kampus.ac.id';

        // 1. Tambah Dosen Baru
        $responseStore = $this->actingAs($user)->post('/master/dosen', [
            'nidn' => $nidnRandom,
            'nip' => '199112152020011003',
            'nama_lengkap' => 'Rian Hidayat',
            'gelar_depan' => '',
            'gelar_belakang' => 'M.Cs.',
            'prodi_id' => $prodi->id,
            'jenis_kelamin' => 'L',
            'email' => $emailRandom,
            'no_hp' => '081234567899',
        ]);
        $responseStore->assertSessionHas('success');
        $this->assertDatabaseHas('dosens', ['nidn' => $nidnRandom]);

        // 2. Edit Dosen
        $dosen = Dosen::where('nidn', $nidnRandom)->first();
        $responseUpdate = $this->actingAs($user)->put("/master/dosen/{$dosen->id}", [
            'nidn' => $nidnRandom,
            'nip' => '199112152020011003',
            'nama_lengkap' => 'Rian Hidayat Nugraha',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.Cs.',
            'prodi_id' => $prodi->id,
            'jenis_kelamin' => 'L',
            'email' => $emailRandom,
            'no_hp' => '081234567899',
        ]);
        $responseUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('dosens', ['nama_lengkap' => 'Rian Hidayat Nugraha']);

        // 3. Unduh Template CSV
        $responseTemplate = $this->actingAs($user)->get('/master/dosen/template');
        $responseTemplate->assertStatus(200);

        // 4. Impor Data Dosen CSV
        $importNidn = '04' . rand(10000000, 99999999);
        $importEmail = 'import.' . Str::random(6) . '@kampus.ac.id';
        $csvContent = "nidn,nip,nama_lengkap,gelar_depan,gelar_belakang,kode_prodi,jenis_kelamin,email,no_hp\n" .
                      "{$importNidn},198501202014021001,Anisa Rahmawati,Dr.,M.Sc.,55201,P,{$importEmail},081211223344\n";

        $file = UploadedFile::fake()->createWithContent('import_dosen.csv', $csvContent);
        $responseImport = $this->actingAs($user)->post('/master/dosen/import', [
            'file_dosen' => $file,
        ]);
        $responseImport->assertSessionHas('success');
        $this->assertDatabaseHas('dosens', ['nidn' => $importNidn]);
    }

    public function test_asisten_dosen_bisa_ditambah_diedit_dan_diimpor(): void
    {
        $user = User::first();
        $mk = MataKuliah::first();
        $dosen = Dosen::first();
        $nim = '2023' . rand(100000, 999999);

        // 1. Tambah Asdos
        $responseStore = $this->actingAs($user)->post('/master/asisten-dosen', [
            'nim' => $nim,
            'nama' => 'Muhammad Fikri',
            'email' => 'fikri@student.kampus.ac.id',
            'no_hp' => '081299887766',
            'mata_kuliah_id' => $mk->id,
            'dosen_id' => $dosen->id,
            'nama_kelas' => 'TI-3A',
            'status' => 'Aktif',
        ]);
        $responseStore->assertSessionHas('success');
        $this->assertDatabaseHas('asisten_dosens', ['nim' => $nim]);

        // 2. Edit Asdos
        $asdos = AsistenDosen::where('nim', $nim)->first();
        $responseUpdate = $this->actingAs($user)->put("/master/asisten-dosen/{$asdos->id}", [
            'nim' => $nim,
            'nama' => 'Muhammad Fikri Alamsyah',
            'email' => 'fikri@student.kampus.ac.id',
            'no_hp' => '081299887766',
            'mata_kuliah_id' => $mk->id,
            'dosen_id' => $dosen->id,
            'nama_kelas' => 'TI-3A',
            'status' => 'Aktif',
        ]);
        $responseUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('asisten_dosens', ['nama' => 'Muhammad Fikri Alamsyah']);

        // 3. Unduh Template CSV
        $responseTemplate = $this->actingAs($user)->get('/master/asisten-dosen/template');
        $responseTemplate->assertStatus(200);

        // 4. Impor Asdos CSV
        $importNim = '2023' . rand(100000, 999999);
        $csvContent = "nim,nama,email,no_hp,kode_mk,nidn_dosen,nama_kelas,status\n" .
                      "{$importNim},Rina Marlina,rina@student.kampus.ac.id,081233445566,{$mk->kode_mk},{$dosen->nidn},TI-3B,Aktif\n";

        $file = UploadedFile::fake()->createWithContent('import_asdos.csv', $csvContent);
        $responseImport = $this->actingAs($user)->post('/master/asisten-dosen/import', [
            'file_asdos' => $file,
        ]);
        $responseImport->assertSessionHas('success');
        $this->assertDatabaseHas('asisten_dosens', ['nim' => $importNim]);
    }

    public function test_mata_kuliah_bisa_ditambah_diedit_dan_diimpor(): void
    {
        $user = User::first();
        $prodi = ProgramStudi::first();
        $kodeMk = 'MK' . rand(100, 999);

        // 1. Tambah MK
        $responseStore = $this->actingAs($user)->post('/master/mata-kuliah', [
            'kode_mk' => $kodeMk,
            'nama_mk' => 'Pengolahan Citra Digital',
            'sks' => 3,
            'semester_paket' => 5,
            'prodi_id' => $prodi->id,
            'deskripsi_rps' => 'Pembahasan transformasi citra, filtering, dan segmentasi.',
        ]);
        $responseStore->assertSessionHas('success');
        $this->assertDatabaseHas('mata_kuliahs', ['kode_mk' => $kodeMk]);

        // 2. Edit MK
        $mk = MataKuliah::where('kode_mk', $kodeMk)->first();
        $responseUpdate = $this->actingAs($user)->put("/master/mata-kuliah/{$mk->id}", [
            'kode_mk' => $kodeMk,
            'nama_mk' => 'Pengolahan Citra Digital & Computer Vision',
            'sks' => 3,
            'semester_paket' => 5,
            'prodi_id' => $prodi->id,
            'deskripsi_rps' => 'Pembahasan transformasi citra dan computer vision.',
        ]);
        $responseUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('mata_kuliahs', ['nama_mk' => 'Pengolahan Citra Digital & Computer Vision']);

        // 3. Unduh Template CSV
        $responseTemplate = $this->actingAs($user)->get('/master/mata-kuliah/template');
        $responseTemplate->assertStatus(200);

        // 4. Impor MK CSV
        $importKode = 'MK' . rand(100, 999);
        $csvContent = "kode_mk,nama_mk,sks,semester_paket,kode_prodi,deskripsi_rps\n" .
                      "{$importKode},Keamanan Siber & Kriptografi,3,6,{$prodi->kode_prodi},Prinsip keamanan informasi dan enkripsi.\n";

        $file = UploadedFile::fake()->createWithContent('import_mk.csv', $csvContent);
        $responseImport = $this->actingAs($user)->post('/master/mata-kuliah/import', [
            'file_mk' => $file,
        ]);
        $responseImport->assertSessionHas('success');
        $this->assertDatabaseHas('mata_kuliahs', ['kode_mk' => $importKode]);
    }

    public function test_ruang_bisa_ditambah_diedit_dan_diimpor(): void
    {
        $user = User::first();
        $kodeRuang = 'R' . rand(100, 999);

        // 1. Tambah Ruang
        $responseStore = $this->actingAs($user)->post('/master/ruang', [
            'kode_ruangan' => $kodeRuang,
            'nama_ruangan' => 'Ruang Teori ' . $kodeRuang,
            'gedung' => 'Gedung Terpadu',
            'lantai' => 2,
            'kapasitas' => 50,
            'latitude' => -6.20880000,
            'longitude' => 106.84560000,
            'radius_meter' => 150,
        ]);
        $responseStore->assertSessionHas('success');
        $this->assertDatabaseHas('ruangans', ['kode_ruangan' => $kodeRuang]);

        // 2. Edit Ruang
        $ruang = Ruangan::where('kode_ruangan', $kodeRuang)->first();
        $responseUpdate = $this->actingAs($user)->put("/master/ruang/{$ruang->id}", [
            'kode_ruangan' => $kodeRuang,
            'nama_ruangan' => 'Ruang Teori & Multimedia ' . $kodeRuang,
            'gedung' => 'Gedung Terpadu',
            'lantai' => 2,
            'kapasitas' => 60,
            'latitude' => -6.20880000,
            'longitude' => 106.84560000,
            'radius_meter' => 200,
        ]);
        $responseUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('ruangans', ['nama_ruangan' => 'Ruang Teori & Multimedia ' . $kodeRuang]);

        // 3. Unduh Template CSV
        $responseTemplate = $this->actingAs($user)->get('/master/ruang/template');
        $responseTemplate->assertStatus(200);

        // 4. Impor Ruang CSV
        $importKodeRuang = 'R' . rand(100, 999);
        $csvContent = "kode_ruangan,nama_ruangan,gedung,lantai,kapasitas,latitude,longitude,radius_meter\n" .
                      "{$importKodeRuang},Laboratorium Robotika,Gedung Rektorat Baru,4,30,-6.20880000,106.84560000,150\n";

        $file = UploadedFile::fake()->createWithContent('import_ruang.csv', $csvContent);
        $responseImport = $this->actingAs($user)->post('/master/ruang/import', [
            'file_ruang' => $file,
        ]);
        $responseImport->assertSessionHas('success');
        $this->assertDatabaseHas('ruangans', ['kode_ruangan' => $importKodeRuang]);
    }
}
