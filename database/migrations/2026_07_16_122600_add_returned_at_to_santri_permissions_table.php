<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri_permissions', function (Blueprint $table) {
            $table->dateTime('returned_at')->nullable()->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('santri_permissions', function (Blueprint $table) {
            $table->dropColumn('returned_at');
        });
    }
};
