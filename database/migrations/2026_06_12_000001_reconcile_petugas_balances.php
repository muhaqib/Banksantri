<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'petugas')
            ->orderBy('id')
            ->eachById(function ($petugas) {
                $transactionIncome = DB::table('transactions')
                    ->where('petugas_id', $petugas->id)
                    ->sum('nominal');

                $approvedSettlements = DB::table('withdrawal_requests')
                    ->where('petugas_id', $petugas->id)
                    ->where('status', 'approved')
                    ->sum('nominal');

                DB::table('users')
                    ->where('id', $petugas->id)
                    ->update(['saldo' => max(0, $transactionIncome - $approvedSettlements)]);
            });
    }

    public function down(): void
    {
        // A balance reconciliation cannot be reversed safely.
    }
};
