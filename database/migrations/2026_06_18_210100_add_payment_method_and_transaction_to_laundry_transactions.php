<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundry_transactions', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('payment_type');
            $table->foreignId('transaction_id')->nullable()->after('laundry_subscription_id')->constrained('transactions')->nullOnDelete();
            $table->index(['payment_method', 'laundry_date']);
        });
    }

    public function down(): void
    {
        Schema::table('laundry_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaction_id');
            $table->dropIndex(['payment_method', 'laundry_date']);
            $table->dropColumn('payment_method');
        });
    }
};
