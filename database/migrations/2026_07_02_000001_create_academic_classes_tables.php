<?php

use App\Support\TarbiyahClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pondok_classes', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('homeroom_teacher')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->boolean('uses_monthly_exam')->default(true);
            $table->boolean('uses_semester_exam')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('formal_classes', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('next_class_id')->nullable()->constrained('formal_classes')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        foreach (TarbiyahClass::DEFAULT_LEVELS as $index => $level) {
            DB::table('pondok_classes')->insertOrIgnore([
                'name' => $level,
                'sort_order' => ($index + 1) * 10,
                'uses_monthly_exam' => true,
                'uses_semester_exam' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('formal_classes');
        Schema::dropIfExists('pondok_classes');
    }
};
