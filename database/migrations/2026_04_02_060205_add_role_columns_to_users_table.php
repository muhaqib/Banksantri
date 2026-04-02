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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'petugas', 'santri'])->default('santri')->after('password');
            $table->string('nis')->nullable()->unique()->after('role');
            $table->string('pin', 6)->nullable()->after('nis');
            $table->unsignedBigInteger('saldo')->default(0)->after('pin');
            $table->string('rfid_code')->nullable()->unique()->after('saldo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nis', 'pin', 'saldo', 'rfid_code']);
        });
    }
};
