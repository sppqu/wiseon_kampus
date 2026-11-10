<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perguruan_tinggis', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('singkatan');
        });
    }

    public function down(): void
    {
        Schema::table('perguruan_tinggis', function (Blueprint $table) {
            $table->dropColumn('logo');
        });
    }
};
