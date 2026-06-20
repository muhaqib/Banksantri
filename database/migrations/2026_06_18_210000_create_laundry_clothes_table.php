<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_clothes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('icon')->default('checkroom');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('laundry_clothes')->insert([
            ['key' => 'kemeja', 'label' => 'Kemeja', 'icon' => 'checkroom', 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'celana', 'label' => 'Celana', 'icon' => 'apparel', 'sort_order' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'sarung', 'label' => 'Sarung', 'icon' => 'texture', 'sort_order' => 30, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'jaket', 'label' => 'Jaket', 'icon' => 'dry_cleaning', 'sort_order' => 40, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'kaos', 'label' => 'Kaos', 'icon' => 'styler', 'sort_order' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'mukena', 'label' => 'Mukena', 'icon' => 'woman', 'sort_order' => 60, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'jilbab', 'label' => 'Jilbab', 'icon' => 'face_3', 'sort_order' => 70, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'handuk', 'label' => 'Handuk', 'icon' => 'layers', 'sort_order' => 80, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_clothes');
    }
};
