<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProgramStudi;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Ruangan;
use App\Models\MataKuliah;
use App\Models\KelasKuliah;
use App\Models\KelasDosen;
use App\Models\PesertaKelas;
use App\Models\JadwalKuliah;
use App\Models\PertemuanKuliah;
use App\Models\PresensiMahasiswa;
use App\Models\PengesahanBkd;
use App\Models\FeederConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User Admin, Kaprodi, Dosen
        $userAdmin = User::create([
            'name' => 'Administrator BAAK',
            'email' => 'admin@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $userKaprodi = User::create([
            'name' => 'Prof. Dr. Ir. Budi Santoso, M.Eng.',
            'email' => 'kaprodi@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'kaprodi',
            'is_active' => true,
        ]);

        $userDosen1 = User::create([
            'name' => 'Dr. Hendra Pratama, M.Kom.',
            'email' => 'dosen@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);

        $userDosen2 = User::create([
            'name' => 'Siti Rahmawati, S.Kom., M.T.',
            'email' => 'dosen2@kampus.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);

        // 1.5 Buat Profil Perguruan Tinggi
        \App\Models\PerguruanTinggi::firstOrCreate(['kode_pt' => '031001'], [
            'nama_pt' => 'Universitas Teknologi Nusantara',
            'singkatan' => 'UTN',
            'akreditasi' => 'Unggul (BAN-PT)',
            'sk_pendirian' => 'SK Mendikbud No. 142/E/O/2012',
            'tanggal_sk' => '2012-08-12',
            'nama_rektor' => 'Prof. Dr. H. Faisal Anwar, M.Eng.',
            'nip_rektor' => '196503151990031002',
            'email' => 'info@kampus.ac.id',
            'telepon' => '(021) 789-0123',
            'website' => 'https://kampus.ac.id',
            'alamat' => 'Jl. Kampus Merdeka No. 101, Gedung Perkuliahan Terpadu',
        ]);

        // 1.8 Buat Fakultas
        $fti = \App\Models\Fakultas::firstOrCreate(['kode_fakultas' => 'FTI'], [
            'nama_fakultas' => 'Fakultas Teknologi Informasi',
            'dekan' => 'Dr. Ir. Ahmad Dahlan, M.Sc.',
            'gedung' => 'Gedung Rektorat Baru Lt. 2-3',
        ]);

        $feb = \App\Models\Fakultas::firstOrCreate(['kode_fakultas' => 'FEB'], [
            'nama_fakultas' => 'Fakultas Ekonomi dan Bisnis',
            'dekan' => 'Prof. Dr. Sri Mulyani, S.E., M.B.A.',
            'gedung' => 'Gedung B Lt. 1-4',
        ]);

        $fkip = \App\Models\Fakultas::firstOrCreate(['kode_fakultas' => 'FKIP'], [
            'nama_fakultas' => 'Fakultas Keguruan dan Ilmu Pendidikan',
            'dekan' => 'Dr. Hasan Basri, M.Pd.',
            'gedung' => 'Gedung C Lt. 1-3',
        ]);

        // 2. Program Studi
        $prodiTI = ProgramStudi::create([
            'fakultas_id' => $fti->id,
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'jenjang' => 'S1',
            'akreditasi' => 'Unggul',
            'nama_kaprodi' => 'Prof. Dr. Ir. Budi Santoso, M.Eng.',
            'nidn_kaprodi' => '0015087501',
            'id_feeder' => (string) Str::uuid(),
        ]);

        $prodiSI = ProgramStudi::create([
            'fakultas_id' => $fti->id,
            'kode_prodi' => '57201',
            'nama_prodi' => 'Sistem Informasi',
            'jenjang' => 'S1',
            'akreditasi' => 'Baik Sekali',
            'nama_kaprodi' => 'Dr. Maya Safitri, M.M.S.I.',
            'nidn_kaprodi' => '0020048002',
            'id_feeder' => (string) Str::uuid(),
        ]);

        // 3. Dosen Profile
        $dosen1 = Dosen::create([
            'user_id' => $userDosen1->id,
            'nidn' => '0412088801',
            'nip' => '198808122015041001',
            'nama_lengkap' => 'Hendra Pratama',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.Kom.',
            'prodi_id' => $prodiTI->id,
            'no_hp' => '081234567890',
            'email' => 'dosen@kampus.ac.id',
            'jenis_kelamin' => 'L',
            'is_active' => true,
            'id_feeder' => (string) Str::uuid(),
        ]);

        $dosen2 = Dosen::create([
            'user_id' => $userDosen2->id,
            'nidn' => '0425099002',
            'nip' => '199009252019032002',
            'nama_lengkap' => 'Siti Rahmawati',
            'gelar_depan' => '',
            'gelar_belakang' => 'S.Kom., M.T.',
            'prodi_id' => $prodiTI->id,
            'no_hp' => '081298765432',
            'email' => 'dosen2@kampus.ac.id',
            'jenis_kelamin' => 'P',
            'is_active' => true,
            'id_feeder' => (string) Str::uuid(),
        ]);

        // 4. Tahun Akademik
        $ta = TahunAkademik::create([
            'kode' => '20241',
            'nama' => '2024/2025 Ganjil',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2024-09-01',
            'tanggal_selesai' => '2025-01-31',
            'is_active' => true,
            'id_feeder' => (string) Str::uuid(),
        ]);

        // 5. Ruangan Kampus
        $ruangLab = Ruangan::create([
            'kode_ruangan' => 'LAB-KOMP-01',
            'nama_ruangan' => 'Laboratorium Komputer 1',
            'gedung' => 'Gedung Rektorat Baru Lt. 3',
            'lantai' => 3,
            'kapasitas' => 35,
            'latitude' => -6.20880000,
            'longitude' => 106.84560000,
            'radius_meter' => 200,
        ]);

        $ruangTeori = Ruangan::create([
            'kode_ruangan' => 'R-302',
            'nama_ruangan' => 'Ruang Teori 302',
            'gedung' => 'Gedung Perkuliahan Terpadu',
            'lantai' => 3,
            'kapasitas' => 45,
            'latitude' => -6.20880000,
            'longitude' => 106.84560000,
            'radius_meter' => 200,
        ]);

        // 6. Mata Kuliah
        $mkWeb = MataKuliah::create([
            'kode_mk' => 'TIF-301',
            'nama_mk' => 'Pemrograman Web Lanjut & API',
            'sks' => 3,
            'semester_paket' => 5,
            'prodi_id' => $prodiTI->id,
            'deskripsi_rps' => 'Mata kuliah ini membahas arsitektur web modern, RESTful API, otentikasi JWT/OAuth, dan integrasi frontend-backend skala institusi.',
            'id_feeder' => (string) Str::uuid(),
        ]);

        $mkAI = MataKuliah::create([
            'kode_mk' => 'TIF-302',
            'nama_mk' => 'Kecerdasan Buatan & Sistem Pakar',
            'sks' => 3,
            'semester_paket' => 5,
            'prodi_id' => $prodiTI->id,
            'deskripsi_rps' => 'Mata kuliah dasar kecerdasan buatan, machine learning, heuristik, dan sistem berbasis pengetahuan.',
            'id_feeder' => (string) Str::uuid(),
        ]);

        // 7. Kelas Kuliah
        $kelasWeb = KelasKuliah::create([
            'tahun_akademik_id' => $ta->id,
            'mata_kuliah_id' => $mkWeb->id,
            'nama_kelas' => 'TI-5A',
            'kapasitas' => 30,
            'id_feeder' => (string) Str::uuid(),
            'status_sync_feeder' => 'synced',
        ]);

        $kelasAI = KelasKuliah::create([
            'tahun_akademik_id' => $ta->id,
            'mata_kuliah_id' => $mkAI->id,
            'nama_kelas' => 'TI-5B',
            'kapasitas' => 30,
            'id_feeder' => (string) Str::uuid(),
            'status_sync_feeder' => 'synced',
        ]);

        // 8. Kelas Dosen (Team Teaching untuk BKD / Serdos)
        // Dr. Hendra Pratama (Koordinator - 16 Pertemuan)
        KelasDosen::create([
            'kelas_kuliah_id' => $kelasWeb->id,
            'dosen_id' => $dosen1->id,
            'sks_substansi' => 3.00,
            'rencana_pertemuan' => 16,
            'realisasi_pertemuan' => 14,
            'is_koordinator' => true,
            'id_feeder' => (string) Str::uuid(),
        ]);

        // Team Teaching di Kelas AI (Dosen 1 & Dosen 2 berbagi beban SKS)
        KelasDosen::create([
            'kelas_kuliah_id' => $kelasAI->id,
            'dosen_id' => $dosen1->id,
            'sks_substansi' => 1.50,
            'rencana_pertemuan' => 8,
            'realisasi_pertemuan' => 8,
            'is_koordinator' => true,
            'id_feeder' => (string) Str::uuid(),
        ]);

        KelasDosen::create([
            'kelas_kuliah_id' => $kelasAI->id,
            'dosen_id' => $dosen2->id,
            'sks_substansi' => 1.50,
            'rencana_pertemuan' => 8,
            'realisasi_pertemuan' => 7,
            'is_koordinator' => false,
            'id_feeder' => (string) Str::uuid(),
        ]);

        // 9. Mahasiswa dan Peserta Kelas
        $daftarNamaMhs = [
            'Ahmad Fauzi', 'Bima Sakti', 'Citra Dewi', 'Dimas Pratama', 'Eka Ramadhani',
            'Fadli Rahman', 'Gita Permata', 'Hafizh Syahputra', 'Indah Puspitasari', 'Joko Susanto',
            'Kartika Sari', 'Lukman Hakim', 'Muhammad Rizky', 'Nurul Hidayah', 'Octavia Putri',
            'Panji Gemilang', 'Qori Amelia', 'Rahmat Dani', 'Salsa Bella', 'Tri Wahyudi'
        ];

        $mahasiswaList = [];
        foreach ($daftarNamaMhs as $idx => $nama) {
            $nim = '202255201' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
            $mhs = Mahasiswa::create([
                'nim' => $nim,
                'nama_lengkap' => $nama,
                'prodi_id' => $prodiTI->id,
                'angkatan' => '2022',
                'jenis_kelamin' => ($idx % 2 === 0) ? 'L' : 'P',
                'email' => strtolower(str_replace(' ', '', $nama)) . '@student.kampus.ac.id',
                'status' => 'AKTIF',
                'id_feeder' => (string) Str::uuid(),
            ]);
            $mahasiswaList[] = $mhs;

            PesertaKelas::create([
                'kelas_kuliah_id' => $kelasWeb->id,
                'mahasiswa_id' => $mhs->id,
                'id_feeder' => (string) Str::uuid(),
            ]);
        }

        // 10. Jadwal Kuliah
        $hariHariIni = now()->isoFormat('dddd'); // Misal 'Senin'
        $hariList = ['Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat'];
        $hariAktif = $hariList[$hariHariIni] ?? 'Senin';

        $jadwalWeb = JadwalKuliah::create([
            'kelas_kuliah_id' => $kelasWeb->id,
            'dosen_id' => $dosen1->id,
            'ruangan_id' => $ruangLab->id,
            'hari' => $hariAktif,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:30:00',
        ]);

        // 11. Buat Riwayat Pertemuan Kuliah (Pertemuan 1 sampai 14 sudah selesai, siap menuju 16 syarat BKD)
        $materiList = [
            1 => ['Pengenalan Arsitektur Client-Server & RESTful Web Service', 'Konsep API, HTTP Request/Response, JSON serialization'],
            2 => ['Routing, Controller, and Middleware di Laravel', 'Pembuatan Route group, Resource controller, dan auth middleware'],
            3 => ['Database Modeling, Migration & Eloquent ORM', 'Relasi One-to-Many, Many-to-Many, dan Eager Loading'],
            4 => ['Autentikasi Token & JWT Service', 'Implementasi Stateful vs Stateless Auth, token expiration'],
            5 => ['Validasi Input Request & Form Handling', 'Custom Request validation rules dan sanitasi data'],
            6 => ['Upload File, Storage & Media Handling', 'Penyimpanan bukti digital dan manipulasi dokumen'],
            7 => ['Review Materi Perkuliahan & Mini Project Web Service', 'Evaluasi implementasi API modul 1-6'],
            8 => ['Evaluasi Tengah Semester (UTS)', 'Ujian Teori & Praktikum Pemrograman Web'],
            9 => ['Integrasi Web Service Eksternal (PDDikti Neo Feeder)', 'Protokol JSON-RPC, HTTP Client Guzzle, dan logging'],
            10 => ['Arsitektur Geofencing & GPS Location Tracking', 'Validasi radius Haversine formula dan koordinat'],
            11 => ['QR Code Dinamis & Keamanan Session Presensi', 'Enkripsi payload token QR dan validasi timeout'],
            12 => ['Penyusunan Berita Acara Perkuliahan (BAP) Otomatis', 'Formatting data BAP terstandar BAN-PT / BKD'],
            13 => ['Digital Signature & QR Verifikasi Portofolio BKD', 'Pengesahan Kaprodi berbasis QR verification code'],
            14 => ['Ekspor Dokumen Bundling Portofolio BKD (PDF)', 'Rendering DomPDF terstandar ukuran A4 untuk SISTER'],
        ];

        foreach ($materiList as $pKe => $materi) {
            $isUts = ($pKe === 8);
            $tgl = now()->subWeeks(15 - $pKe)->format('Y-m-d');

            $ptm = PertemuanKuliah::create([
                'jadwal_kuliah_id' => $jadwalWeb->id,
                'kelas_kuliah_id' => $kelasWeb->id,
                'dosen_id' => $dosen1->id,
                'ruangan_id' => $ruangLab->id,
                'pertemuan_ke' => $pKe,
                'jenis_pertemuan' => $isUts ? 'uts' : 'tatap_muka',
                'tanggal' => $tgl,
                'jam_mulai_jadwal' => '08:00:00',
                'jam_selesai_jadwal' => '10:30:00',
                'jam_mulai_aktual' => '08:05:00',
                'jam_selesai_aktual' => '10:25:00',
                'rencana_materi' => $materi[0],
                'realisasi_materi' => $materi[1],
                'cpmk' => 'CPMK-' . ceil($pKe / 4) . ': Mampu merancang & mengimplementasikan sistem institusi terpadu.',
                'metode' => 'luring',
                'latitude' => -6.20880120,
                'longitude' => 106.84560150,
                'jarak_meter' => 12,
                'validasi_lokasi' => 'valid',
                'foto_bukti' => null,
                'qr_token' => Str::random(32),
                'status' => 'selesai',
                'approval_kaprodi' => 'approved',
                'id_feeder_aktivitas' => (string) Str::uuid(),
                'status_sync_feeder' => 'synced',
                'pesan_sync_feeder' => 'Tersinkron ke Feeder PDDikti.',
            ]);

            // Presensi mahasiswa per pertemuan
            foreach ($mahasiswaList as $mIdx => $mhs) {
                $statusHadir = ($mIdx === 5 && $pKe % 3 === 0) ? 'izin' : (($mIdx === 9 && $pKe % 4 === 0) ? 'sakit' : 'hadir');
                PresensiMahasiswa::create([
                    'pertemuan_kuliah_id' => $ptm->id,
                    'mahasiswa_id' => $mhs->id,
                    'status' => $statusHadir,
                    'metode_presensi' => 'scan_qr',
                    'waktu_presensi' => $tgl . ' 08:15:00',
                ]);
            }
        }

        // Pertemuan ke-15 (Hari Ini / Terjadwal - siap untuk presensi dosen)
        PertemuanKuliah::create([
            'jadwal_kuliah_id' => $jadwalWeb->id,
            'kelas_kuliah_id' => $kelasWeb->id,
            'dosen_id' => $dosen1->id,
            'ruangan_id' => $ruangLab->id,
            'pertemuan_ke' => 15,
            'jenis_pertemuan' => 'tatap_muka',
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai_jadwal' => '08:00:00',
            'jam_selesai_jadwal' => '10:30:00',
            'rencana_materi' => 'Simulasi Pelaporan BKD & Sinkronisasi Portofolio Mengajar ke PDDikti Feeder',
            'cpmk' => 'CPMK-4: Mampu menguji dan mendokumentasikan kelayakan sistem sesuai standar audit Dikti.',
            'metode' => 'luring',
            'status' => 'terjadwal',
            'approval_kaprodi' => 'pending',
            'status_sync_feeder' => 'pending',
        ]);

        // Pertemuan ke-16 (UAS - terjadwal minggu depan untuk menggenapkan 16 pertemuan BKD)
        PertemuanKuliah::create([
            'jadwal_kuliah_id' => $jadwalWeb->id,
            'kelas_kuliah_id' => $kelasWeb->id,
            'dosen_id' => $dosen1->id,
            'ruangan_id' => $ruangLab->id,
            'pertemuan_ke' => 16,
            'jenis_pertemuan' => 'uas',
            'tanggal' => now()->addDays(7)->format('Y-m-d'),
            'jam_mulai_jadwal' => '08:00:00',
            'jam_selesai_jadwal' => '10:30:00',
            'rencana_materi' => 'Evaluasi Akhir Semester (UAS) & Ujian Proyek Mandiri',
            'cpmk' => 'CPMK-4: Evaluasi komprehensif capaian pembelajaran semester.',
            'metode' => 'luring',
            'status' => 'terjadwal',
            'approval_kaprodi' => 'pending',
            'status_sync_feeder' => 'pending',
        ]);

        // 12. Lembar Pengesahan BKD
        PengesahanBkd::create([
            'kelas_kuliah_id' => $kelasWeb->id,
            'dosen_id' => $dosen1->id,
            'tahun_akademik_id' => $ta->id,
            'total_pertemuan' => 14,
            'persen_kehadiran' => 87.5,
            'status_kelayakan_bkd' => 'memenuhi',
            'status_pengesahan' => 'disahkan',
            'disahkan_oleh' => 'Prof. Dr. Ir. Budi Santoso, M.Eng.',
            'nidn_pengesah' => '0015087501',
            'tanggal_pengesahan' => now()->format('Y-m-d'),
            'qr_verifikasi' => 'VERIF-BKD-TIF301-20241-' . strtoupper(Str::random(10)),
            'catatan' => 'Berkas BAP dan Presensi telah diperiksa dan disetujui untuk pemenuhan BKD Semester Ganjil 2024/2025.',
        ]);

        // 13.5 Asisten Dosen
        \App\Models\AsistenDosen::create([
            'nim' => '202255201001',
            'nama' => 'Ahmad Fauzi',
            'email' => 'ahmadfauzi@student.kampus.ac.id',
            'no_hp' => '081234567801',
            'mata_kuliah_id' => $mkWeb->id,
            'dosen_id' => $dosen1->id,
            'kelas_kuliah_id' => $kelasWeb->id,
            'nama_kelas' => 'TI-5A',
            'status' => 'Aktif',
        ]);

        \App\Models\AsistenDosen::create([
            'nim' => '202255201002',
            'nama' => 'Bima Sakti',
            'email' => 'bimasakti@student.kampus.ac.id',
            'no_hp' => '081234567802',
            'mata_kuliah_id' => $mkAI->id,
            'dosen_id' => $dosen2->id,
            'kelas_kuliah_id' => $kelasAI->id,
            'nama_kelas' => 'TI-5B',
            'status' => 'Aktif',
        ]);

        \App\Models\AsistenDosen::create([
            'nim' => '202255201003',
            'nama' => 'Citra Dewi',
            'email' => 'citradewi@student.kampus.ac.id',
            'no_hp' => '081234567803',
            'mata_kuliah_id' => $mkWeb->id,
            'dosen_id' => $dosen1->id,
            'kelas_kuliah_id' => $kelasWeb->id,
            'nama_kelas' => 'TI-3A',
            'status' => 'Aktif',
        ]);

        // 14. Feeder Config
        FeederConfig::create([
            'url' => 'http://localhost:8100/ws/live2.php',
            'username' => 'feeder_admin',
            'password' => 'feeder_secret',
            'kode_pt' => '031001',
            'nama_pt' => 'Universitas Teknologi Nusantara',
            'mode' => 'sandbox',
            'is_active' => true,
        ]);
    }
}
