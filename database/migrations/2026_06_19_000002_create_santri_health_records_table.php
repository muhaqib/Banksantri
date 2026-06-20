<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('checkup_date');
            $table->string('title');
            $table->enum('status', ['sehat', 'sakit', 'sembuh', 'dirawat'])->default('sehat');
            $table->string('location')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->string('blood_pressure')->nullable();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->text('complaint')->nullable();
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'checkup_date']);
            $table->index(['status', 'checkup_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_health_records');
    }
};
