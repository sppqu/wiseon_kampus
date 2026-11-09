<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Program Studi
        Schema::create('program_studis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_prodi')->unique();
            $table->string('nama_prodi');
            $table->string('jenjang', 10)->default('S1'); // S1, D3, D4, S2
            $table->string('nama_kaprodi')->nullable();
            $table->string('nidn_kaprodi', 20)->nullable();
            $table->string('id_feeder', 64)->nullable()->index();
            $table->timestamps();
        });

        // 2. Dosen
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nidn', 20)->unique();
            $table->string('nip', 30)->nullable();
            $table->string('nama_lengkap');
            $table->string('gelar_depan', 20)->nullable();
            $table->string('gelar_belakang', 30)->nullable();
            $table->foreignId('prodi_id')->nullable()->constrained('program_studis')->nullOnDelete();
            $table->string('no_hp', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->boolean('is_active')->default(true);
            $table->string('id_feeder', 64)->nullable()->index();
            $table->timestamps();
        });

        // 3. Mahasiswa
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 30)->unique();
            $table->string('nama_lengkap');
            $table->foreignId('prodi_id')->nullable()->constrained('program_studis')->nullOnDelete();
            $table->string('angkatan', 4)->default('2024');
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('email')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('status', 20)->default('AKTIF');
            $table->string('id_feeder', 64)->nullable()->index();
            $table->timestamps();
        });

        // 4. Tahun Akademik / Semester
        Schema::create('tahun_akademiks', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // Contoh: 20241 (Ganjil 2024/2025)
            $table->string('nama', 50); // Contoh: 2024/2025 Ganjil
            $table->enum('semester', ['ganjil', 'genap', 'pendek'])->default('ganjil');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('id_feeder', 64)->nullable()->index();
            $table->timestamps();
        });

        // 5. Ruangan Kampus (dengan Koordinat GPS untuk Geofencing)
        Schema::create('ruangans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruangan', 20)->unique();
            $table->string('nama_ruangan');
            $table->string('gedung');
            $table->integer('lantai')->default(1);
            $table->integer('kapasitas')->default(40);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('radius_meter')->default(150); // Toleransi jarak GPS
            $table->timestamps();
        });

        // 6. Mata Kuliah
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mk', 20);
            $table->string('nama_mk');
            $table->integer('sks')->default(2);
            $table->integer('semester_paket')->default(1);
            $table->foreignId('prodi_id')->nullable()->constrained('program_studis')->nullOnDelete();
            $table->text('deskripsi_rps')->nullable();
            $table->string('id_feeder', 64)->nullable()->index();
            $table->timestamps();
        });

        // 7. Kelas Kuliah
        Schema::create('kelas_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->string('nama_kelas', 20); // TI-1A, TI-3B, dsb
            $table->integer('kapasitas')->default(40);
            $table->string('id_feeder', 64)->nullable()->index();
            $table->enum('status_sync_feeder', ['pending', 'synced', 'error'])->default('pending');
            $table->timestamps();
        });

        // 8. Kelas Dosen (Mendukung Team Teaching untuk BKD / Serdos)
        Schema::create('kelas_dosens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliahs')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->decimal('sks_substansi', 4, 2)->default(2.00);
            $table->integer('rencana_pertemuan')->default(16);
            $table->integer('realisasi_pertemuan')->default(0);
            $table->boolean('is_koordinator')->default(true);
            $table->string('id_feeder', 64)->nullable()->index();
            $table->timestamps();
        });

        // 9. Peserta Kelas Kuliah (Mahasiswa yang mengambil KRS kelas)
        Schema::create('peserta_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliahs')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('nilai_huruf', 5)->nullable();
            $table->string('id_feeder', 64)->nullable()->index();
            $table->timestamps();
        });

        // 10. Jadwal Kuliah Mingguan
        Schema::create('jadwal_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliahs')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        // 11. Pertemuan Kuliah (BAP & Presensi Dosen Real-time)
        Schema::create('pertemuan_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_kuliah_id')->nullable()->constrained('jadwal_kuliahs')->nullOnDelete();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliahs')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
            $table->integer('pertemuan_ke'); // 1 sampai 16
            $table->enum('jenis_pertemuan', ['tatap_muka', 'uts', 'uas', 'praktikum'])->default('tatap_muka');
            $table->date('tanggal');
            $table->time('jam_mulai_jadwal')->nullable();
            $table->time('jam_selesai_jadwal')->nullable();
            $table->time('jam_mulai_aktual')->nullable();
            $table->time('jam_selesai_aktual')->nullable();
            $table->text('rencana_materi')->nullable();
            $table->text('realisasi_materi')->nullable();
            $table->string('cpmk')->nullable(); // Capaian Pembelajaran / RPS
            $table->enum('metode', ['luring', 'daring', 'hybrid'])->default('luring');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('jarak_meter')->nullable();
            $table->enum('validasi_lokasi', ['valid', 'luar_radius', 'manual', 'daring'])->default('valid');
            $table->string('foto_bukti')->nullable();
            $table->string('qr_token', 64)->nullable()->index();
            $table->enum('status', ['terjadwal', 'berlangsung', 'selesai', 'dibatalkan', 'diganti'])->default('terjadwal');
            $table->enum('approval_kaprodi', ['pending', 'approved', 'rejected'])->default('approved');
            $table->text('catatan_kaprodi')->nullable();
            $table->string('id_feeder_aktivitas', 64)->nullable()->index();
            $table->enum('status_sync_feeder', ['pending', 'synced', 'error'])->default('pending');
            $table->text('pesan_sync_feeder')->nullable();
            $table->timestamps();
        });

        // 12. Presensi Mahasiswa per Pertemuan
        Schema::create('presensi_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_kuliah_id')->constrained('pertemuan_kuliahs')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->enum('metode_presensi', ['dosen', 'scan_qr', 'manual'])->default('dosen');
            $table->timestamp('waktu_presensi')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
        });

        // 13. Pengesahan BKD Per Semester (Digital Signature & QR Verifikasi)
        Schema::create('pengesahan_bkds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliahs')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->cascadeOnDelete();
            $table->integer('total_pertemuan')->default(0);
            $table->decimal('persen_kehadiran', 5, 2)->default(0);
            $table->enum('status_kelayakan_bkd', ['memenuhi', 'tidak_memenuhi'])->default('memenuhi');
            $table->enum('status_pengesahan', ['draft', 'diajukan', 'disahkan', 'revisi'])->default('disahkan');
            $table->string('disahkan_oleh')->nullable();
            $table->string('nidn_pengesah', 20)->nullable();
            $table->date('tanggal_pengesahan')->nullable();
            $table->string('qr_verifikasi', 64)->unique();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 14. Konfigurasi Feeder PDDikti / SIAKAD
        Schema::create('feeder_configs', function (Blueprint $table) {
            $table->id();
            $table->string('url')->default('http://localhost:8100/ws/live2.php');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->text('token')->nullable();
            $table->timestamp('token_expired_at')->nullable();
            $table->string('kode_pt', 20)->nullable();
            $table->string('nama_pt')->nullable();
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 15. Log Sinkronisasi Feeder
        Schema::create('feeder_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint_action');
            $table->enum('status', ['success', 'error']);
            $table->string('error_code', 50)->nullable();
            $table->text('error_desc')->nullable();
            $table->longText('request_data')->nullable();
            $table->longText('response_data')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeder_logs');
        Schema::dropIfExists('feeder_configs');
        Schema::dropIfExists('pengesahan_bkds');
        Schema::dropIfExists('presensi_mahasiswas');
        Schema::dropIfExists('pertemuan_kuliahs');
        Schema::dropIfExists('jadwal_kuliahs');
        Schema::dropIfExists('peserta_kelas');
        Schema::dropIfExists('kelas_dosens');
        Schema::dropIfExists('kelas_kuliahs');
        Schema::dropIfExists('mata_kuliahs');
        Schema::dropIfExists('ruangans');
        Schema::dropIfExists('tahun_akademiks');
        Schema::dropIfExists('mahasiswas');
        Schema::dropIfExists('dosens');
        Schema::dropIfExists('program_studis');
    }
};
