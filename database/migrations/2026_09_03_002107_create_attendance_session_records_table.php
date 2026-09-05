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
        Schema::create('attendance_session_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'terlambat']);
            $table->timestamp('scanned_at');
            $table->timestamps();
            
            $table->unique(['attendance_session_id', 'santri_id'], 'att_sess_rec_session_santri_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_session_records');
    }
};
