<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('santri_status')->default('aktif')->after('role')->index();
            $table->timestamp('alumni_at')->nullable()->after('santri_status');
            $table->string('kamar_terakhir')->nullable()->after('alumni_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['santri_status', 'alumni_at', 'kamar_terakhir']);
        });
    }
};
