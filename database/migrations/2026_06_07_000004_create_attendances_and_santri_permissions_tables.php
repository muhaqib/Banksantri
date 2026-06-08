<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('permission_number')->unique();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->string('kamar');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['santri_id', 'start_date', 'end_date']);
            $table->index(['kamar', 'start_date', 'end_date']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->string('kamar');
            $table->date('attendance_date');
            $table->enum('status', ['hadir', 'ghoib', 'izin']);
            $table->enum('method', ['rfid', 'manual', 'permission', 'automatic']);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->unique(['santri_id', 'attendance_date']);
            $table->index(['kamar', 'attendance_date', 'status']);
            $table->index(['attendance_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('santri_permissions');
    }
};
