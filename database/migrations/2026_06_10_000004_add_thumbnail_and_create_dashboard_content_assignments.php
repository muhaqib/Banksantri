<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('dashboard_contents', 'thumbnail_url')) {
            Schema::table('dashboard_contents', function (Blueprint $table) {
                $table->string('thumbnail_url')->nullable()->after('summary');
            });
        }

        if (! Schema::hasTable('dashboard_content_assignments')) {
            Schema::create('dashboard_content_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dashboard_content_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('is_completed')->default(false)->index();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['dashboard_content_id', 'user_id'], 'dashboard_assignment_content_user_unique');
            });
        } else {
            Schema::table('dashboard_content_assignments', function (Blueprint $table) {
                $table->unique(['dashboard_content_id', 'user_id'], 'dashboard_assignment_content_user_unique');
            });
        }

        $petugasIds = DB::table('users')->where('role', 'petugas')->pluck('id');
        $todoIds = DB::table('dashboard_contents')->where('type', 'todo')->pluck('id');
        $now = now();

        foreach ($todoIds as $todoId) {
            foreach ($petugasIds as $petugasId) {
                DB::table('dashboard_content_assignments')->insertOrIgnore([
                    'dashboard_content_id' => $todoId,
                    'user_id' => $petugasId,
                    'is_completed' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_content_assignments');

        Schema::table('dashboard_contents', function (Blueprint $table) {
            $table->dropColumn('thumbnail_url');
        });
    }
};
