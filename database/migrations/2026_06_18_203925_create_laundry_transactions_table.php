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
        Schema::create('laundry_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('laundry_subscription_id')->nullable()->constrained('laundry_subscriptions')->nullOnDelete();
            $table->string('payment_type');
            $table->date('laundry_date');
            $table->decimal('weight_kg', 6, 2);
            $table->unsignedInteger('price_per_kg')->default(0);
            $table->unsignedInteger('total_price')->default(0);
            $table->unsignedInteger('total_clothes')->default(0);
            $table->json('clothes_detail')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'laundry_date']);
            $table->index(['payment_type', 'laundry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_transactions');
    }
};
