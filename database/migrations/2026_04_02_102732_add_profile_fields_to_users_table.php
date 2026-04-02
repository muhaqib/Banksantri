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
            $table->string('foto')->nullable()->after('email');
            $table->string('no_hp')->nullable()->after('foto');
            $table->text('alamat')->nullable()->after('no_hp');
            $table->string('tempat_lahir')->nullable()->after('alamat');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('nama_wali')->nullable()->after('tanggal_lahir');
            $table->string('no_hp_wali')->nullable()->after('nama_wali');
            $table->string('asal_sekolah')->nullable()->after('no_hp_wali');
            $table->string('kelas')->nullable()->after('asal_sekolah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'foto',
                'no_hp',
                'alamat',
                'tempat_lahir',
                'tanggal_lahir',
                'nama_wali',
                'no_hp_wali',
                'asal_sekolah',
                'kelas'
            ]);
        });
    }
};
