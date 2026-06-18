@extends('layouts.app')

@section('header-title', 'Dashboard Admin')
@php $activeRole = 'admin'; @endphp

@section('content')
<div>
    <!-- Page Heading -->
    <div class="flex items-end justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Selamat Berkhidmah</h2>
            <p class="text-on-surface-variant text-sm mt-1">Berkhidmah dengan Ikhlas, Menjemput berkah tanpa batas.</p>
        </div>
        <div class="bg-surface-container-low px-4 py-2 rounded-xl flex items-center gap-2">
            <span class="material-symbols-outlined text-sm text-primary">calendar_today</span>
            <span class="text-xs font-semibold text-primary">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <!-- Bento Financial Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-4 mt-4 gap-6">
        <!-- Total Top Up Santri Hari Ini -->
        <div class="bg-surface-container-lowest p-6 rounded-xl flex flex-col justify-between hover:bg-surface-container transition-colors group shadow-sm">
            <div class="flex justify-between items-start">
                <div class="p-2 bg-green-100 rounded-lg text-green-600">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">add_circle</span>
                </div>
                <span class="text-xs font-bold text-green-600">Hari Ini</span>
            </div>
            <div class="mt-4">
                <p class="text-xs font-medium text-on-surface-variant uppercase tracking-widest">Total Top Up Santri</p>
                <h3 class="font-headline font-bold text-2xl text-green-600 mt-1">Rp {{ number_format($totalTopUpHariIni ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Total Transaksi Hari Ini -->
        <div class="bg-surface-container-lowest p-6 rounded-xl flex flex-col justify-between hover:bg-surface-container transition-colors group shadow-sm">
            <div class="flex justify-between items-start">
                <div class="p-2 bg-red-100 rounded-lg text-red-600">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">payments</span>
                </div>
                <span class="text-xs font-bold text-red-600">Hari Ini</span>
            </div>
            <div class="mt-4">
                <p class="text-xs font-medium text-on-surface-variant uppercase tracking-widest">Total Transaksi</p>
                <h3 class="font-headline font-bold text-2xl text-red-600 mt-1">Rp {{ number_format($totalTransaksiHariIni ?? 0, 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Saldo Saat Ini -->
        <div class="bg-gradient-to-br from-primary to-primary-container relative overflow-hidden p-6 rounded-xl flex flex-col justify-between group shadow-xl shadow-primary/20">
            <div class="absolute inset-0 bg-gradient-to-br from-primary to-primary-container opacity-90"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div class="p-2 bg-white/20 backdrop-blur-md rounded-lg text-white">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_balance</span>
                </div>
            </div>
            <div class="relative z-10 mt-4">
                <p class="text-xs font-medium text-primary-fixed font-medium mb-1">Saldo Saat Ini</p>
                <h3 class="font-headline font-extrabold text-3xl text-white mt-1">Rp {{ number_format($saldoKasUtama ?? 0, 0, ',', '.') }}</h3>
                <p class="text-xs mt-2 text-primary-fixed-dim">Dana terverifikasi siap untuk penarikan tunai</p>
            </div>
            <div class="absolute -right-12 -top-12 w-32 h-32 rounded-full bg-primary-fixed opacity-5 blur-3xl"></div>
        </div>

        <!-- Jumlah Transaksi -->
        <div class="bg-surface-container-lowest p-6 rounded-xl flex flex-col justify-between hover:bg-surface-container transition-colors group shadow-sm">
            <div class="flex justify-between items-start">
                <div class="p-2 bg-secondary-container rounded-lg text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">sync_alt</span>
                </div>
                <span class="text-xs font-bold text-secondary">{{ $transaksiHariIni ?? 0 }}</span>
            </div>
            <div class="mt-4">
                <p class="text-xs font-medium text-on-surface-variant uppercase tracking-widest">Jumlah Transaksi</p>
                <h3 class="font-headline font-bold text-2xl text-on-surface mt-1">{{ $transaksiHariIni ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <!-- Main Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
        <!-- Left Column: Actions & Trends -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Top Up Verification Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Permintaan Top Up Tertunda -->
                <div class="bg-surface-container-lowest rounded-xl p-6 border border-primary/10 hover:border-primary/30 transition-all shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="material-symbols-outlined text-primary p-2 bg-primary-fixed rounded-full" style="font-variation-settings: 'FILL' 1;">pending_actions</span>
                        <div>
                            <h4 class="font-headline font-bold text-lg text-primary">Top Up Tertunda</h4>
                            <p class="text-xs text-on-surface-variant">Menunggu verifikasi</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-surface-container-high rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-on-surface-variant uppercase">Total Tertunda</span>
                                <span class="text-2xl font-headline font-bold text-primary">{{ $pendingTopUpCount ?? 0 }}</span>
                            </div>
                            <p class="text-xs text-on-surface-variant">Permintaan yang perlu diverifikasi</p>
                        </div>
                        <a href="{{ route('admin.topup') }}" class="block w-full bg-primary text-on-primary font-bold py-4 rounded-xl shadow-lg shadow-primary/10 hover:shadow-primary/20 transition-all active:scale-95 text-center flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified</span>
                            <span>Verifikasi Sekarang</span>
                        </a>
                    </div>
                </div>

                <!-- Aktivitas Top Up Terbaru -->
                <div class="bg-surface-container-lowest rounded-xl p-6 border border-secondary/10 hover:border-secondary/30 transition-all shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="material-symbols-outlined text-secondary p-2 bg-secondary-container rounded-full" style="font-variation-settings: 'FILL' 1;">history</span>
                        <div>
                            <h4 class="font-headline font-bold text-lg text-secondary">Aktivitas Top Up</h4>
                            <p class="text-xs text-on-surface-variant">Riwayat verifikasi terbaru</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse(($recentTopUps ?? [])->take(3) as $topUp)
                            <div class="flex items-center justify-between p-3 bg-surface-container-high rounded-lg">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full {{ $topUp->status === 'approved' ? 'bg-primary/10 text-primary' : 'bg-error/10 text-error' }} flex items-center justify-center">
                                        <span class="material-symbols-outlined text-sm">{{ $topUp->status === 'approved' ? 'check_circle' : 'cancel' }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-on-surface">{{ $topUp->santri->name ?? 'Santri' }}</p>
                                        <p class="text-[10px] text-on-surface-variant">{{ $topUp->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <p class="text-xs font-bold text-on-surface">Rp {{ number_format($topUp->nominal, 0, ',', '.') }}</p>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <span class="material-symbols-outlined text-3xl text-on-surface-variant mb-2">inbox</span>
                                <p class="text-xs text-on-surface-variant">Belum ada aktivitas</p>
                            </div>
                        @endforelse
                        <a href="{{ route('admin.topup') }}" class="block w-full text-center text-xs font-bold text-primary hover:underline py-2">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Transaction Trends Chart -->
            <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h3 class="font-headline font-bold text-xl text-primary">Tren Transaksi Mingguan</h3>
                        <p class="text-xs text-on-surface-variant">Perbandingan arus top up dan transaksi</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Top Up Santri</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Transaksi</span>
                        </div>
                    </div>
                </div>

                <!-- Visual representation of a chart -->
                <div class="h-64 flex items-end justify-between gap-4">
                    @foreach($weeklyTrends ?? [] as $day)
                        <div class="flex-1 space-y-2">
                            <div class="flex items-end justify-center gap-1 h-48">
                                <div class="w-3 bg-green-500 rounded-t-sm transition-all hover:opacity-80" 
                                     style="height: {{ $day['topup_percent'] }}%"
                                     title="Top Up: Rp {{ number_format($day['topup'], 0, ',', '.') }}">
                                </div>
                                <div class="w-3 bg-red-500 rounded-t-sm transition-all hover:opacity-80" 
                                     style="height: {{ $day['transaksi_percent'] }}%"
                                     title="Transaksi: Rp {{ number_format($day['transaksi'], 0, ',', '.') }}">
                                </div>
                            </div>
                            <p class="text-center text-[10px] font-bold text-on-surface-variant">{{ $day['name'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Petugas Performance Table -->
            <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
                <div class="p-6 border-b border-surface-container flex justify-between items-center">
                    <h3 class="font-headline font-bold text-xl text-primary">Kinerja Petugas</h3>
                    <a href="{{ route('admin.petugas.index') }}" class="text-xs font-bold text-primary hover:underline">Lihat laporan lengkap</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">Total Transaksi</th>
                                <th class="px-6 py-4 text-right">Jumlah Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-outline-variant/10">
                            @forelse($petugasList ?? [] as $index => $petugas)
                                <tr class="hover:bg-surface transition-colors group">
                                    <td class="px-6 py-4 font-mono font-semibold text-primary">#P{{ 440 + (method_exists($petugasList, 'firstItem') ? ($petugasList->firstItem() - 1) : 0) + $index }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                                {{ substr($petugas->name, 0, 2) }}
                                            </div>
                                            <span class="font-bold text-on-surface">{{ $petugas->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant">{{ $petugas->total_transaksi ?? 0 }} transaksi</td>
                                    <td class="px-6 py-4 text-right font-headline font-bold text-on-surface">
                                        Rp {{ number_format($petugas->total_nominal ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-4xl mb-2">group_off</span>
                                        <p>Belum ada data petugas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                @if($petugasList->hasPages())
                    <div class="p-6 border-t border-surface-container">
                        {{ $petugasList->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Withdrawal Activity -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm top-24">
                <div class="p-6 border-b border-surface-container">
                    <h3 class="font-headline font-bold text-xl text-primary">Penarikan Tunai Terbaru</h3>
                    <p class="text-xs text-on-surface-variant">Permintaan pencairan dana petugas</p>
                </div>
                <div class="p-4 space-y-4">
                    @forelse($pendingRequests ?? [] as $request)
                        <!-- Pending Withdrawal -->
                        <div class="p-4 rounded-xl border-l-4 border-tertiary bg-tertiary/5 hover:bg-tertiary/10 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-[10px] font-bold text-tertiary uppercase tracking-wider">Menunggu Persetujuan</p>
                                <span class="text-[10px] text-on-surface-variant">{{ $request->created_at->diffForHumans() }}</span>
                            </div>
                            <h4 class="font-bold text-sm text-on-surface">{{ $request->petugas->name }}</h4>
                            <p class="text-lg font-headline font-bold text-on-surface my-1">Rp {{ number_format($request->nominal, 0, ',', '.') }}</p>
                            <div class="flex gap-2 mt-4">
                                <form action="{{ route('admin.settlement.approve', $request->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-primary text-on-primary py-2 rounded-lg text-xs font-bold active:scale-95 transition-all">
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ route('admin.settlement.reject', $request->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-surface-container-high text-on-surface py-2 rounded-lg text-xs font-bold active:scale-95 transition-all">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined text-4xl text-primary mb-2" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <p class="text-sm text-on-surface-variant">Tidak ada penarikan tunai pending</p>
                        </div>
                    @endforelse
                    
                    <a href="{{ route('admin.settlement') }}" class="block w-full py-3 text-xs font-bold text-primary border border-primary/20 rounded-xl hover:bg-primary/5 transition-colors text-center">
                        Lihat Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
