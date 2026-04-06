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
        Schema::create('prestasi_santris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_kitab');
            $table->string('kategori')->nullable(); // e.g., 'hafalan', 'tajwid', etc.
            $table->text('keterangan')->nullable();
            $table->string('status')->default('belum_dihafal'); // 'belum_dihafal', 'sedang_dihafal', 'telah_dihafalkan'
            $table->string('nilai')->nullable(); // e.g., 'Mumtaz', 'Jayyid', etc.
            $table->integer('skor')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('bulan_tahun_selesai')->nullable(); // e.g., 'Safar 1447 H'
            $table->string('ustadz_pembimbing')->nullable();
            $table->string('foto_kitab')->nullable();
            $table->text('catatan_ustadz')->nullable();
            $table->integer('poin')->default(0);
            $table->string('tags')->nullable(); // comma-separated tags
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestasi_santris');
    }
};
