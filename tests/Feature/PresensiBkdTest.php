<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\PertemuanKuliah;
use App\Models\PengesahanBkd;
use App\Models\PresensiHarianDosen;
use Tests\TestCase;

class PresensiBkdTest extends TestCase
{
    public function test_halaman_login_bisa_diakses(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Portal Presensi & BKD Dosen', false);
    }

    public function test_quick_login_sebagai_dosen(): void
    {
        $response = $this->get('/quick-login?role=dosen');
        $response->assertRedirect('/dosen/dashboard');

        $this->assertAuthenticated();
        $this->assertEquals('dosen', auth()->user()->role);
    }

    public function test_dashboard_dosen_menampilkan_jadwal_dan_bkd(): void
    {
        $user = User::where('role', 'dosen')->first();
        $response = $this->actingAs($user)->get('/dosen/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dr. Hendra Pratama, M.Kom.');
        $response->assertSee('Pemenuhan Syarat BKD', false);
    }

    public function test_halaman_buka_kelas_presensi_gps_dan_bap(): void
    {
        $dosen = Dosen::first();
        $pertemuan = PertemuanKuliah::where('dosen_id', $dosen->id)->first();

        $response = $this->actingAs($dosen->user)->get("/dosen/buka-kelas/{$pertemuan->id}");
        $response->assertStatus(200);
        $response->assertSee('Validasi Geofencing GPS Kampus', false);
        $response->assertSee('Berita Acara Perkuliahan', false);
    }

    public function test_portofolio_bkd_dan_cetak_dokumen(): void
    {
        $dosen = Dosen::first();
        $kelas = KelasKuliah::first();

        $response = $this->actingAs($dosen->user)->get("/dosen/portofolio-bkd/{$kelas->id}");
        $response->assertStatus(200);
        $response->assertSee('Portofolio Beban Kerja Dosen', false);

        $responseCetak = $this->get("/bkd/cetak/{$kelas->id}");
        $responseCetak->assertStatus(200);
        $responseCetak->assertSee('Berita Acara Perkuliahan & Presensi Mengajar Dosen', false);
    }

    public function test_verifikasi_qr_keaslian_dokumen_bkd(): void
    {
        $pengesahan = PengesahanBkd::first();
        $response = $this->get("/verifikasi-bkd/{$pengesahan->qr_verifikasi}");

        $response->assertStatus(200);
        $response->assertSee('Dokumen Portofolio BKD Sah & Terverifikasi', false);
        $response->assertSee($pengesahan->qr_verifikasi);
    }

    public function test_feeder_pddikti_manager_bisa_diakses_admin(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/feeder');

        $response->assertStatus(200);
        $response->assertSee('Integrasi Web Service Feeder', false);
    }

    public function test_admin_dashboard_monitoring_kelas_dan_bkd(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Monitoring Perkuliahan & Geofencing GPS Dosen', false);
        $response->assertSee('Kesiapan BKD', false);
    }

    public function test_laporan_kehadiran_dosen_bisa_diakses_admin(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/laporan-kehadiran');

        $response->assertStatus(200);
        $response->assertSee('Laporan Kehadiran & Portofolio BKD Dosen', false);
        $response->assertSee('Rekapitulasi Realisasi Mengajar & BKD', false);
    }

    public function test_export_laporan_kehadiran_csv(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/laporan-kehadiran/export');

        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function test_simpan_bap_presensi_gps_dan_foto(): void
    {
        $dosen = Dosen::first();
        $pertemuan = PertemuanKuliah::where('dosen_id', $dosen->id)->where('status', 'terjadwal')->first();

        if (!$pertemuan) {
            $pertemuan = PertemuanKuliah::where('dosen_id', $dosen->id)->first();
        }

        $response = $this->actingAs($dosen->user)->post("/dosen/simpan-bap/{$pertemuan->id}", [
            'rencana_materi' => 'Materi Uji Coba RPS Otomatis',
            'realisasi_materi' => 'Praktikum kode dan diskusi kelompok aktif.',
            'cpmk' => 'CPMK-1',
            'metode' => 'luring',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $response->assertSessionHas('success');
        $pertemuan->refresh();
        $this->assertEquals('berlangsung', $pertemuan->status);
        $this->assertEquals('valid', $pertemuan->validasi_lokasi);
    }

    public function test_pengaturan_presensi_bisa_diakses_dan_diupdate(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/pengaturan-presensi');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Kebijakan Presensi', false);
        $response->assertSee('Geofencing GPS Kampus', false);

        $postResponse = $this->actingAs($admin)->post('/admin/pengaturan-presensi/update', [
            'radius_meter' => 250,
            'radius_kantor_meter' => 200,
            'jam_masuk_kantor' => '08:00',
            'jam_pulang_kantor' => '16:00',
            'toleransi_menit_kantor' => 15,
            'minimal_hadir_persen' => 80,
            'target_pertemuan_bkd' => 16,
            'toleransi_menit' => 20,
            'wajib_foto_bukti' => 1,
            'mode_geofencing' => 'strict',
            'kampus_lat' => -6.2088,
            'kampus_lon' => 106.8456,
        ]);

        $postResponse->assertSessionHas('success');
    }

    public function test_dosen_bisa_presensi_masuk_dan_pulang_kantor(): void
    {
        $dosen = Dosen::first();
        PresensiHarianDosen::where('dosen_id', $dosen->id)->whereDate('tanggal', now()->format('Y-m-d'))->delete();

        // 1. Akses halaman presensi kantor
        $response = $this->actingAs($dosen->user)->get('/dosen/presensi-harian');
        $response->assertStatus(200);
        $response->assertSee('Presensi Masuk & Pulang Kantor', false);

        // 2. Clock-In
        $inResponse = $this->actingAs($dosen->user)->post('/dosen/presensi-harian/clock-in', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
        $inResponse->assertSessionHas('success');

        // 3. Clock-Out
        $outResponse = $this->actingAs($dosen->user)->post('/dosen/presensi-harian/clock-out', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
        $outResponse->assertSessionHas('success');
    }

    public function test_admin_bisa_monitoring_presensi_harian_dosen_dan_export(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/presensi-harian-dosen');
        $response->assertStatus(200);
        $response->assertSee('Presensi Masuk & Pulang Kantor Dosen', false);

        $exportResponse = $this->actingAs($admin)->get('/admin/presensi-harian-dosen/export');
        $exportResponse->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $exportResponse->headers->get('content-type'));
    }

    public function test_admin_bisa_mengatur_dan_mereset_jadwal_kerja_individual_dosen(): void
    {
        $admin = User::where('role', 'admin')->first();
        $dosen = Dosen::first();

        // 1. Buka Halaman Jadwal Kerja per Dosen
        $response = $this->actingAs($admin)->get('/admin/jadwal-kerja-dosen');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Kerja & Hari Kerja per Dosen', false);

        // 2. Simpan Pengaturan Individual Khusus untuk Dosen Ini
        $updateResponse = $this->actingAs($admin)->post("/admin/jadwal-kerja-dosen/{$dosen->id}", [
            'jam_masuk' => '07:30',
            'jam_pulang' => '15:30',
            'toleransi_menit' => 10,
            'hari_kerja' => ['Senin', 'Rabu', 'Jumat'],
            'keterangan' => 'Dosen Peneliti Khusus',
        ]);
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_kerja_dosens', [
            'dosen_id' => $dosen->id,
            'jam_masuk' => '07:30',
            'jam_pulang' => '15:30',
            'toleransi_menit' => 10,
        ]);

        // 3. Reset Jadwal Dosen Kembali ke Standar
        $resetResponse = $this->actingAs($admin)->post("/admin/jadwal-kerja-dosen/{$dosen->id}/reset");
        $resetResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('jadwal_kerja_dosens', [
            'dosen_id' => $dosen->id,
        ]);
    }

    public function test_admin_bisa_terapkan_massal_jadwal_kerja_dosen(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post('/admin/jadwal-kerja-dosen/massal', [
            'target_prodi_id' => 'semua',
            'jam_masuk' => '08:15',
            'jam_pulang' => '16:45',
            'toleransi_menit' => 20,
            'hari_kerja' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            'keterangan' => 'Shift Massal Semester Ganjil',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_halaman_presensi_mobile_dosen_bisa_diakses(): void
    {
        $dosenUser = User::where('role', 'dosen')->first();

        $response = $this->actingAs($dosenUser)->get('/dosen/presensi-mobile');
        $response->assertStatus(200);
        $response->assertSee('Aplikasi Presensi Kampus');
        $response->assertSee('Live Kamera Depan');
        $response->assertSee('Koordinat dan Radius Lokasi');
        $response->assertSee('Ganti Password');
        $response->assertSee('Profil Dosen');
        $response->assertDontSee('fallbackFileInput');
        $response->assertDontSee('Dashboard Admin');
    }

    public function test_dosen_bisa_ganti_password_via_mobile(): void
    {
        $dosenUser = User::where('role', 'dosen')->first();

        // Coba ganti password dengan password saat ini yang benar
        $response = $this->actingAs($dosenUser)->post('/dosen/ganti-password', [
            'current_password' => 'password',
            'password' => 'dosenBaru123!',
            'password_confirmation' => 'dosenBaru123!',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('dosenBaru123!', $dosenUser->fresh()->password));

        // Kembalikan ke 'password' agar test berikutnya tidak terganggu
        $dosenUser->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);
    }

    public function test_admin_bisa_mengakses_laporan_rekap_kehadiran_dosen(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/rekap-kehadiran-dosen');
        $response->assertStatus(200);
        $response->assertSee('Laporan Rekapitulasi Kehadiran Dosen');
        $response->assertSee('Hari Efektif');
        $response->assertSee('Total Masuk');
        $response->assertSee('Export CSV / Excel');
    }

    public function test_admin_bisa_export_rekap_kehadiran_dosen_csv(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/rekap-kehadiran-dosen/export');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_admin_bisa_cetak_rekap_kehadiran_dosen(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/rekap-kehadiran-dosen/cetak');
        $response->assertStatus(200);
        $response->assertSee('LAPORAN REKAPITULASI KEHADIRAN DOSEN');
    }

    public function test_dosen_bisa_login_menggunakan_nomor_hp_dan_password_6_digit_terakhir(): void
    {
        $dosen = Dosen::first();
        $cleanPhone = preg_replace('/[^0-9]/', '', $dosen->no_hp);
        $sixDigits = substr($cleanPhone, -6);

        $response = $this->post('/login', [
            'login' => $dosen->no_hp,
            'password' => $sixDigits,
        ]);

        $response->assertRedirect('/dosen/dashboard');
        $this->assertAuthenticatedAs($dosen->user);
    }

    public function test_dosen_bisa_login_hanya_menginput_nomor_hp_tanpa_password(): void
    {
        $dosen = Dosen::where('no_hp', '!=', '')->skip(1)->first() ?: Dosen::first();

        $response = $this->post('/login', [
            'login' => $dosen->no_hp,
            'password' => '',
        ]);

        $response->assertRedirect('/dosen/dashboard');
        $this->assertAuthenticatedAs($dosen->user);
    }

    public function test_login_nomor_hp_gagal_jika_tidak_terdaftar(): void
    {
        $response = $this->post('/login', [
            'login' => '089999999999',
            'password' => '123456',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_halaman_presensi_mobile_login_bisa_diakses(): void
    {
        $response = $this->get('/presensi');
        $response->assertStatus(200);
        $response->assertSee('Aplikasi Presensi Mobile');
        $response->assertSee('Nomor HP Terdaftar');
    }

    public function test_dosen_bisa_login_ke_presensi_mobile_via_nomor_hp_dan_6_digit(): void
    {
        $dosen = Dosen::first();
        $cleanPhone = preg_replace('/[^0-9]/', '', $dosen->no_hp);
        $sixDigits = substr($cleanPhone, -6);

        $response = $this->post('/presensi', [
            'no_hp' => $dosen->no_hp,
            'password' => $sixDigits,
        ]);

        $response->assertRedirect('/dosen/presensi-mobile');
        $this->assertAuthenticatedAs($dosen->user);
    }

    public function test_guest_presensi_mobile_dialihkan_ke_halaman_presensi_login(): void
    {
        $response = $this->get('/presensi-mobile');
        $response->assertRedirect('/presensi');

        $response2 = $this->get('/dosen/presensi-mobile');
        $response2->assertRedirect('/presensi');
    }
}
