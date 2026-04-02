<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index()
    {
        $pendingRequests = WithdrawalRequest::with('petugas')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $settlementHistory = WithdrawalRequest::with(['petugas', 'approver'])
            ->where('status', '!=', 'pending')
            ->orderBy('approved_at', 'desc')
            ->limit(20)
            ->get();

        return view('pages.admin.settlement', [
            'pendingRequests' => $pendingRequests,
            'settlementHistory' => $settlementHistory
        ]);
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $request = WithdrawalRequest::findOrFail($id);
            
            if ($request->status !== 'pending') {
                throw new \Exception('Request sudah diproses');
            }

            // Check if petugas has enough balance
            $petugas = User::findOrFail($request->petugas_id);
            if ($petugas->saldo < $request->nominal) {
                throw new \Exception('Saldo petugas tidak mencukupi');
            }

            // Reduce petugas saldo
            $petugas->update([
                'saldo' => $petugas->saldo - $request->nominal
            ]);

            // Update request status
            $request->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            DB::commit();

            return redirect()->route('admin.settlement')->with('success', 'Settlement disetujui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.settlement')->with('error', $e->getMessage());
        }
    }

    public function reject($id)
    {
        $request = WithdrawalRequest::findOrFail($id);
        
        if ($request->status !== 'pending') {
            return redirect()->route('admin.settlement')->with('error', 'Request sudah diproses');
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return redirect()->route('admin.settlement')->with('success', 'Settlement ditolak');
    }
}
