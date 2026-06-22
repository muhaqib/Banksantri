<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarbiyah_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('class_level');
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['class_level', 'name']);
            $table->index(['class_level', 'is_active', 'sort_order']);
        });

        Schema::create('tarbiyah_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('tarbiyah_subjects')->cascadeOnDelete();
            $table->string('class_level');
            $table->unsignedTinyInteger('semester');
            $table->string('academic_year')->nullable();
            $table->decimal('score', 5, 2);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['santri_id', 'subject_id', 'class_level', 'semester', 'academic_year'], 'tarbiyah_grades_unique');
            $table->index(['santri_id', 'class_level', 'semester']);
            $table->index(['class_level', 'semester', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarbiyah_grades');
        Schema::dropIfExists('tarbiyah_subjects');
    }
};
