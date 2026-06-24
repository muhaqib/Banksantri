<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarbiyah_monthly_exams', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->date('exam_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('exam_date');
        });

        Schema::create('tarbiyah_monthly_grades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('monthly_exam_id')->constrained('tarbiyah_monthly_exams')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->string('class_level');
            $table->string('subject');
            $table->decimal('score', 5, 2);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['monthly_exam_id', 'santri_id', 'subject'], 'tarbiyah_monthly_grades_unique');
            $table->index(['monthly_exam_id', 'class_level']);
            $table->index(['santri_id', 'class_level']);
        });

        Schema::table('prestasi_santris', function (Blueprint $table): void {
            $table->foreignId('tarbiyah_monthly_exam_id')
                ->nullable()
                ->after('pembimbing_id')
                ->constrained('tarbiyah_monthly_exams')
                ->nullOnDelete();

            $table->unique(['santri_id', 'tarbiyah_monthly_exam_id'], 'prestasi_tarbiyah_monthly_unique');
        });
    }

    public function down(): void
    {
        Schema::table('prestasi_santris', function (Blueprint $table): void {
            $table->dropUnique('prestasi_tarbiyah_monthly_unique');
            $table->dropConstrainedForeignId('tarbiyah_monthly_exam_id');
        });

        Schema::dropIfExists('tarbiyah_monthly_grades');
        Schema::dropIfExists('tarbiyah_monthly_exams');
    }
};
