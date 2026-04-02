<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');
            $table->enum('jenis', ['masuk', 'keluar'])->default('keluar');
            $table->unsignedBigInteger('nominal');
            $table->enum('kategori', ['kantin', 'koperasi', 'laundry', 'fotokopi', 'lainnya']);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('saldo_sebelum');
            $table->unsignedBigInteger('saldo_setelah');
            $table->timestamps();

            $table->index(['santri_id', 'created_at']);
            $table->index(['petugas_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
