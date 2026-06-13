<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_contents', function (Blueprint $table) {
            $table->boolean('assign_to_all')->default(false)->after('due_date')->index();
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_contents', function (Blueprint $table) {
            $table->dropColumn('assign_to_all');
        });
    }
};
