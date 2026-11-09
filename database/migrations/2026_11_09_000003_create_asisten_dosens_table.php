<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asisten_dosens', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 30)->index();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->foreignId('mata_kuliah_id')->nullable()->constrained('mata_kuliahs')->nullOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('kelas_kuliah_id')->nullable()->constrained('kelas_kuliahs')->nullOnDelete();
            $table->string('nama_kelas', 30)->nullable();
            $table->enum('status', ['Aktif', 'Selesai', 'Nonaktif'])->default('Aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asisten_dosens');
    }
};
