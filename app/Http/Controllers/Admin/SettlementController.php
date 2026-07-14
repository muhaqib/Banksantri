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

        $petugasList = User::where('role', 'petugas')
            ->orderBy('name')
            ->get();

        return view('pages.admin.settlement', [
            'pendingRequests' => $pendingRequests,
            'settlementHistory' => $settlementHistory,
            'petugasList' => $petugasList,
            'activeRole' => 'admin',
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

            return redirect()->route('admin.settlement')->with('success', 'Penarikan tunai disetujui');
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

        return redirect()->route('admin.settlement')->with('success', 'Penarikan tunai ditolak');
    }

    public function directWithdraw(Request $request)
    {
        $validated = $request->validate([
            'petugas_id' => 'required|exists:users,id',
            'nominal' => 'required|integer|min:1000',
            'catatan' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $petugas = User::findOrFail($validated['petugas_id']);
            
            if ($petugas->role !== 'petugas') {
                throw new \Exception('User tersebut bukan petugas.');
            }

            if ($petugas->saldo < $validated['nominal']) {
                throw new \Exception('Saldo petugas tidak mencukupi untuk melakukan penarikan.');
            }

            // Deduct balance
            $petugas->update([
                'saldo' => $petugas->saldo - $validated['nominal']
            ]);

            // Log the withdrawal
            WithdrawalRequest::create([
                'petugas_id' => $petugas->id,
                'nominal' => $validated['nominal'],
                'catatan' => $validated['catatan'] ?? 'Penarikan langsung oleh admin',
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.settlement')->with('success', 'Penarikan langsung berhasil dilakukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.settlement')->with('error', $e->getMessage());
        }
    }
}
