<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitabs', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kategori');
            $table->string('gambar')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('prestasi_santris', function (Blueprint $table) {
            $table->foreignId('kitab_id')->nullable()->after('santri_id')->constrained('kitabs')->nullOnDelete();
            $table->foreignId('pembimbing_id')->nullable()->after('kitab_id')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('progress')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('prestasi_santris', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kitab_id');
            $table->dropConstrainedForeignId('pembimbing_id');
            $table->dropColumn('progress');
        });

        Schema::dropIfExists('kitabs');
    }
};
