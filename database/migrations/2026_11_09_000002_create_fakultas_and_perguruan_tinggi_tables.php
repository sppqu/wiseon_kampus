<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Fakultas
        Schema::create('fakultas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_fakultas', 20)->unique();
            $table->string('nama_fakultas');
            $table->string('dekan')->nullable();
            $table->string('gedung')->nullable();
            $table->timestamps();
        });

        // 2. Tambah fakultas_id & akreditasi ke program_studis
        Schema::table('program_studis', function (Blueprint $table) {
            $table->foreignId('fakultas_id')->nullable()->after('id')->constrained('fakultas')->nullOnDelete();
            $table->string('akreditasi', 20)->default('Baik Sekali')->after('jenjang');
        });

        // 3. Tabel Profil Institusi Perguruan Tinggi (Bisa Diedit Manual)
        Schema::create('perguruan_tinggis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pt', 20)->default('031001');
            $table->string('nama_pt')->default('Universitas Teknologi Nusantara');
            $table->string('singkatan', 20)->default('UTN');
            $table->string('akreditasi', 30)->default('Unggul (BAN-PT)');
            $table->string('sk_pendirian')->default('SK Mendikbud No. 142/E/O/2012');
            $table->date('tanggal_sk')->nullable();
            $table->string('nama_rektor')->default('Prof. Dr. H. Faisal Anwar, M.Eng.');
            $table->string('nip_rektor', 30)->nullable();
            $table->string('email')->default('info@kampus.ac.id');
            $table->string('telepon', 30)->default('(021) 789-0123');
            $table->string('website')->default('https://kampus.ac.id');
            $table->text('alamat')->default('Jl. Kampus Merdeka No. 101, Gedung Perkuliahan Terpadu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perguruan_tinggis');
        Schema::table('program_studis', function (Blueprint $table) {
            $table->dropForeign(['fakultas_id']);
            $table->dropColumn(['fakultas_id', 'akreditasi']);
        });
        Schema::dropIfExists('fakultas');
    }
};
