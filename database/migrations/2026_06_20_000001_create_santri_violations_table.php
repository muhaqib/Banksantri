<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis_pelanggaran');
            $table->dateTime('waktu');
            $table->unsignedTinyInteger('pengurangan_point');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'waktu']);
            $table->index('waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_violations');
    }
};
