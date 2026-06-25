<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\DashboardContent;
use App\Models\DashboardContentAssignment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $base = DashboardContent::published()->with('creator');
        $petugas = Auth::user()->fresh();

        return view('pages.petugas.dashboard', [
            'announcements' => (clone $base)->where('type', 'announcement')->latest('published_at')->limit(5)->get(),
            'news' => Blog::published()->latest('published_at')->limit(6)->get(),
            'todos' => DashboardContentAssignment::query()
                ->with('dashboardContent')
                ->where('user_id', auth()->id())
                ->where('is_completed', false)
                ->whereHas('dashboardContent', fn ($query) => $query->published()->where('type', 'todo'))
                ->join('dashboard_contents', 'dashboard_contents.id', '=', 'dashboard_content_assignments.dashboard_content_id')
                ->orderByRaw('dashboard_contents.due_date IS NULL')
                ->orderBy('dashboard_contents.due_date')
                ->select('dashboard_content_assignments.*')
                ->limit(8)
                ->get(),
            'saldoDigital' => $petugas->saldo ?? 0,
            'penghasilanHariIni' => Transaction::where('petugas_id', $petugas->id)
                ->whereDate('created_at', today())
                ->sum('nominal'),
            'activeRole' => 'petugas',
        ]);
    }

    public function completeTodo(DashboardContentAssignment $assignment)
    {
        abort_unless($assignment->user_id === auth()->id(), 403);
        abort_unless($assignment->dashboardContent?->type === 'todo', 404);

        $assignment->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Tugas ditandai selesai.');
    }

    public function finance()
    {
        $petugas = Auth::user()->fresh();

        // Get real data
        $transaksiHariIni = Transaction::where('petugas_id', $petugas->id)
            ->whereDate('created_at', today())
            ->count();

        $totalNominal = Transaction::where('petugas_id', $petugas->id)
            ->whereDate('created_at', today())
            ->sum('nominal');

        // Calculate success rate (transactions without errors)
        $totalTransaksi = Transaction::where('petugas_id', $petugas->id)->count();
        $successRate = $totalTransaksi > 0 ? 100 : 100; // For now, all are successful

        // Get recent transactions
        $transaksiTerakhir = Transaction::with('santri')
            ->where('petugas_id', $petugas->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get weekly transaction data (last 7 days)
        $weeklyData = [];
        $days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Transaction::where('petugas_id', $petugas->id)
                ->whereDate('created_at', $date)
                ->count();

            $weeklyData[] = [
                'name' => $days[$date->dayOfWeek],
                'value' => $count,
                'date' => $date->format('Y-m-d'),
            ];
        }

        return view('pages.petugas.finance-dashboard', [
            'saldoDigital' => $petugas->saldo ?? 0,
            'penghasilanHariIni' => $totalNominal, // Assuming all transactions are income for petugas
            'transaksiHariIni' => $transaksiHariIni,
            'totalNominal' => $totalNominal,
            'successRate' => $successRate,
            'transaksiTerakhir' => $transaksiTerakhir,
            'weeklyData' => $weeklyData,
            'activeRole' => 'petugas',
        ]);
    }
}
