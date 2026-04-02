<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->unsignedBigInteger('nominal');
            $table->string('sumber_dana')->nullable();
            $table->string('keperluan')->nullable();
            $table->text('keterangan');
            $table->unsignedBigInteger('saldo_sebelum');
            $table->unsignedBigInteger('saldo_setelah');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_transactions');
    }
};
