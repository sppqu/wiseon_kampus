<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_kerja_dosens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->unique()->constrained('dosens')->cascadeOnDelete();
            $table->time('jam_masuk')->default('08:00');
            $table->time('jam_pulang')->default('16:00');
            $table->integer('toleransi_menit')->default(15);
            $table->json('hari_kerja')->nullable(); // e.g. ["Senin", "Selasa", "Rabu", "Kamis", "Jumat"]
            $table->integer('radius_meter')->nullable();
            $table->boolean('is_custom')->default(true);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kerja_dosens');
    }
};
