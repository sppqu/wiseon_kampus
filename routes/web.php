<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KaprodiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BkdPdfController;
use App\Http\Controllers\MasterDataController;
use Illuminate\Support\Facades\Route;

// Redirect Home ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi Admin & Web Portal Kampus
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/quick-login', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Presensi Mobile Khusus Dosen & Karyawan (Login No. HP & PIN 6 Digit Belakang)
Route::get('/presensi', [AuthController::class, 'showMobileLogin'])->name('presensi.login');
Route::post('/presensi', [AuthController::class, 'mobileLogin'])->name('presensi.login.post');
Route::get('/presensi-mobile', function () {
    if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isDosen()) {
        return redirect()->route('dosen.presensi-mobile');
    }
    return redirect()->route('presensi.login');
})->name('presensi.mobile.alias');

// Halaman Publik Verifikasi QR Keaslian Dokumen BKD
Route::get('/verifikasi-bkd/{token}', [BkdPdfController::class, 'verifikasiQr'])->name('bkd.verifikasi');

// Rute Cetak Portofolio BKD (PDF / View Cetak)
Route::get('/bkd/cetak/{kelas}', [BkdPdfController::class, 'cetakBkd'])->name('bkd.cetak');

// Modul Dosen
Route::middleware(['auth'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [DosenController::class, 'dashboard'])->name('dashboard');
    Route::get('/presensi-mobile', [DosenController::class, 'presensiMobile'])->name('presensi-mobile');
    Route::get('/presensi-harian', [DosenController::class, 'presensiHarian'])->name('presensi-harian');
    Route::post('/presensi-harian/clock-in', [DosenController::class, 'clockIn'])->name('clock-in');
    Route::post('/presensi-harian/clock-out', [DosenController::class, 'clockOut'])->name('clock-out');
    Route::get('/buka-kelas/{pertemuan}', [DosenController::class, 'bukaKelas'])->name('buka-kelas');
    Route::post('/simpan-bap/{pertemuan}', [DosenController::class, 'simpanBap'])->name('simpan-bap');
    Route::post('/tutup-kelas/{pertemuan}', [DosenController::class, 'tutupKelas'])->name('tutup-kelas');
    Route::post('/presensi-mahasiswa/{pertemuan}', [DosenController::class, 'updatePresensiMahasiswa'])->name('presensi-mahasiswa');
    Route::get('/portofolio-bkd/{kelas}', [DosenController::class, 'portofolioBkd'])->name('portofolio-bkd');
    Route::post('/ganti-password', [DosenController::class, 'gantiPassword'])->name('ganti-password');
    Route::post('/update-profil', [DosenController::class, 'updateProfil'])->name('update-profil');
});

// Modul Kaprodi
Route::middleware(['auth'])->prefix('kaprodi')->name('kaprodi.')->group(function () {
    Route::get('/dashboard', [KaprodiController::class, 'dashboard'])->name('dashboard');
    Route::post('/approve-pertemuan/{pertemuan}', [KaprodiController::class, 'approvePertemuan'])->name('approve-pertemuan');
    Route::post('/sahkan-bkd/{kelas}', [KaprodiController::class, 'sahkanBkd'])->name('sahkan-bkd');
});

// Modul Admin BAAK & Feeder PDDikti
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan-kehadiran', [AdminController::class, 'laporanKehadiran'])->name('laporan-kehadiran');
    Route::get('/laporan-kehadiran/export', [AdminController::class, 'exportLaporanCsv'])->name('laporan-kehadiran.export');
    Route::get('/presensi-harian-dosen', [AdminController::class, 'presensiHarianDosen'])->name('presensi-harian-dosen');
    Route::get('/presensi-harian-dosen/export', [AdminController::class, 'exportPresensiHarianCsv'])->name('presensi-harian-dosen.export');
    Route::get('/rekap-kehadiran-dosen', [AdminController::class, 'rekapKehadiranDosen'])->name('rekap-kehadiran-dosen');
    Route::get('/rekap-kehadiran-dosen/export', [AdminController::class, 'exportRekapKehadiranCsv'])->name('rekap-kehadiran-dosen.export');
    Route::get('/rekap-kehadiran-dosen/cetak', [AdminController::class, 'cetakRekapKehadiran'])->name('rekap-kehadiran-dosen.cetak');
    Route::get('/feeder', [AdminController::class, 'feederManager'])->name('feeder');
    Route::get('/pengaturan-presensi', [AdminController::class, 'pengaturanPresensi'])->name('pengaturan-presensi');
    Route::post('/pengaturan-presensi/update', [AdminController::class, 'updatePengaturanPresensi'])->name('pengaturan-presensi.update');
    Route::get('/jadwal-kerja-dosen', [AdminController::class, 'jadwalKerjaDosen'])->name('jadwal-kerja-dosen');
    Route::post('/jadwal-kerja-dosen/massal', [AdminController::class, 'terapkanMassalJadwalKerja'])->name('jadwal-kerja-dosen.massal');
    Route::post('/jadwal-kerja-dosen/{dosen}', [AdminController::class, 'updateJadwalKerjaDosen'])->name('jadwal-kerja-dosen.update');
    Route::post('/jadwal-kerja-dosen/{dosen}/reset', [AdminController::class, 'resetJadwalKerjaDosen'])->name('jadwal-kerja-dosen.reset');
    Route::post('/feeder/update', [AdminController::class, 'updateFeederConfig'])->name('feeder.update');
    Route::post('/feeder/test', [AdminController::class, 'testFeederConnection'])->name('feeder.test');
    Route::post('/feeder/push-batch', [AdminController::class, 'pushFeederBatch'])->name('feeder.push-batch');
    Route::post('/feeder/push-pertemuan/{pertemuan}', [AdminController::class, 'pushSinglePertemuan'])->name('feeder.push-pertemuan');
    Route::post('/feeder/push-kelas/{kelas}', [AdminController::class, 'pushKelasBatch'])->name('feeder.push-kelas');
});

// Modul Data Master (CRUD Lengkap & Impor CSV)
Route::middleware(['auth'])->prefix('master')->name('master.')->group(function () {
    // 1. Perguruan Tinggi (dengan Edit Manual)
    Route::get('/perguruan-tinggi', [MasterDataController::class, 'perguruanTinggi'])->name('perguruan-tinggi');
    Route::post('/perguruan-tinggi/update', [MasterDataController::class, 'updatePerguruanTinggi'])->name('perguruan-tinggi.update');

    // 2. Fakultas (Tambah, Edit, Hapus)
    Route::get('/fakultas', [MasterDataController::class, 'fakultas'])->name('fakultas');
    Route::post('/fakultas', [MasterDataController::class, 'storeFakultas'])->name('fakultas.store');
    Route::put('/fakultas/{fakulta}', [MasterDataController::class, 'updateFakultas'])->name('fakultas.update');
    Route::delete('/fakultas/{fakulta}', [MasterDataController::class, 'destroyFakultas'])->name('fakultas.destroy');

    // 3. Program Studi (Tambah, Edit, Hapus)
    Route::get('/program-studi', [MasterDataController::class, 'programStudi'])->name('program-studi');
    Route::post('/program-studi', [MasterDataController::class, 'storeProgramStudi'])->name('program-studi.store');
    Route::put('/program-studi/{programStudi}', [MasterDataController::class, 'updateProgramStudi'])->name('program-studi.update');
    Route::delete('/program-studi/{programStudi}', [MasterDataController::class, 'destroyProgramStudi'])->name('program-studi.destroy');

    // 4. Dosen (Tambah, Edit, Hapus, Impor & Template CSV)
    Route::get('/dosen', [MasterDataController::class, 'dosen'])->name('dosen');
    Route::post('/dosen', [MasterDataController::class, 'storeDosen'])->name('dosen.store');
    Route::put('/dosen/{dosen}', [MasterDataController::class, 'updateDosen'])->name('dosen.update');
    Route::delete('/dosen/{dosen}', [MasterDataController::class, 'destroyDosen'])->name('dosen.destroy');
    Route::post('/dosen/import', [MasterDataController::class, 'importDosen'])->name('dosen.import');
    Route::get('/dosen/template', [MasterDataController::class, 'downloadTemplateDosen'])->name('dosen.template');

    // 5. Asisten Dosen (Tambah, Edit, Hapus, Impor & Template CSV)
    Route::get('/asisten-dosen', [MasterDataController::class, 'asistenDosen'])->name('asisten-dosen');
    Route::post('/asisten-dosen', [MasterDataController::class, 'storeAsistenDosen'])->name('asisten-dosen.store');
    Route::put('/asisten-dosen/{asistenDosen}', [MasterDataController::class, 'updateAsistenDosen'])->name('asisten-dosen.update');
    Route::delete('/asisten-dosen/{asistenDosen}', [MasterDataController::class, 'destroyAsistenDosen'])->name('asisten-dosen.destroy');
    Route::post('/asisten-dosen/import', [MasterDataController::class, 'importAsistenDosen'])->name('asisten-dosen.import');
    Route::get('/asisten-dosen/template', [MasterDataController::class, 'downloadTemplateAsistenDosen'])->name('asisten-dosen.template');

    // 6. Mata Kuliah (Tambah, Edit, Hapus, Impor & Template CSV)
    Route::get('/mata-kuliah', [MasterDataController::class, 'mataKuliah'])->name('mata-kuliah');
    Route::post('/mata-kuliah', [MasterDataController::class, 'storeMataKuliah'])->name('mata-kuliah.store');
    Route::put('/mata-kuliah/{mataKuliah}', [MasterDataController::class, 'updateMataKuliah'])->name('mata-kuliah.update');
    Route::delete('/mata-kuliah/{mataKuliah}', [MasterDataController::class, 'destroyMataKuliah'])->name('mata-kuliah.destroy');
    Route::post('/mata-kuliah/import', [MasterDataController::class, 'importMataKuliah'])->name('mata-kuliah.import');
    Route::get('/mata-kuliah/template', [MasterDataController::class, 'downloadTemplateMataKuliah'])->name('mata-kuliah.template');

    // 7. Ruang (Tambah, Edit, Hapus, Impor & Template CSV)
    Route::get('/ruang', [MasterDataController::class, 'ruang'])->name('ruang');
    Route::post('/ruang', [MasterDataController::class, 'storeRuang'])->name('ruang.store');
    Route::put('/ruang/{ruang}', [MasterDataController::class, 'updateRuang'])->name('ruang.update');
    Route::delete('/ruang/{ruang}', [MasterDataController::class, 'destroyRuang'])->name('ruang.destroy');
    Route::post('/ruang/import', [MasterDataController::class, 'importRuang'])->name('ruang.import');
    Route::get('/ruang/template', [MasterDataController::class, 'downloadTemplateRuang'])->name('ruang.template');

    // 8. Mahasiswa & Bobot Nilai
    Route::get('/mahasiswa', [MasterDataController::class, 'mahasiswa'])->name('mahasiswa');
    Route::get('/bobot-nilai', [MasterDataController::class, 'bobotNilai'])->name('bobot-nilai');
});
