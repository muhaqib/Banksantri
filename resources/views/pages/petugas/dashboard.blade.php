@extends('layouts.app')

@section('header-title', 'Dashboard Petugas')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div x-data="petugasDashboard()">
    <!-- Hero Layout: Bento Grid Style -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <!-- Digital Balance Card (Primary Anchor) -->
        <div class="lg:col-span-7 relative overflow-hidden rounded-xl p-8 flex flex-col justify-between min-h-[260px] bg-gradient-to-br from-primary to-primary-container text-on-primary shadow-xl shadow-primary/20">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-3xl" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                        </div>
                        <div>
                            <p class="text-primary-fixed font-medium">{{ auth()->user()->name ?? 'Petugas' }}</p>
                            <p class="text-xs text-primary-fixed-dim">ID: {{ auth()->user()->id ?? '-' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('petugas.transaksi') }}" class="bg-white text-primary px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl active:scale-95 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">add</span>
                        <span>Transaksi Baru</span>
                    </a>
                </div>
                
                <p class="text-primary-fixed font-medium mb-1 opacity-90">Saldo Digital Anda </p>
                <h3 class="text-5xl font-extrabold font-headline tracking-tighter">Rp {{ number_format($saldoDigital ?? 0, 0, ',', '.') }}</h3>
                <p class="text-sm mt-4 text-primary-fixed-dim">Verified funds ready for settlement</p>
            </div>
            
            <div class="relative z-10 flex gap-4 mt-8">
                <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/5">
                    <span class="material-symbols-outlined text-sm">schedule</span>
                    <span class="text-xs">Last updated: {{ now()->format('H:i') }}</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/5">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <span class="text-xs">Account Status: Active</span>
                </div>
            </div>
            
            <!-- Decorative Background Element -->
            <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-primary-fixed opacity-5 blur-3xl"></div>
        </div>

        <!-- Stats & Quick Actions -->
        <div class="lg:col-span-5 grid grid-cols-2 gap-4">
            <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">trending_up</span>
                    <p class="text-xs text-on-surface-variant font-medium">Penghasilan Hari Ini</p>
                </div>
                <p class="text-2xl font-bold text-on-surface">Rp {{ number_format($penghasilanHariIni ?? 0, 0, ',', '.') }}</p>
            </div>
            
            <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">payments</span>
                    <p class="text-xs text-on-surface-variant font-medium">Transaksi Hari Ini</p>
                </div>
                <p class="text-2xl font-bold text-on-surface">{{ $transaksiHariIni ?? 0 }}</p>
            </div>
            
            <div class="bg-surface-container-lowest rounded-xl p-6 shadow-sm col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">analytics</span>
                        <p class="text-xs text-on-surface-variant font-medium">Total Nominal Dikelola</p>
                    </div>
                </div>
                <p class="text-3xl font-bold text-primary">Rp {{ number_format($totalNominal ?? 0, 0, ',', '.') }}</p>
            </div>
            
            <!-- Tarik Tunai Quick Action -->
            <a href="{{ route('petugas.tarik-tunai') }}" class="bg-tertiary-container/20 rounded-xl p-6 flex flex-col justify-center group cursor-pointer hover:bg-tertiary-container/30 transition-colors col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-on-tertiary-container font-bold mb-1">Tarik Tunai</p>
                        <p class="text-sm text-on-tertiary-container/80">Convert to physical cash</p>
                    </div>
                    <span class="material-symbols-outlined text-tertiary transition-transform group-hover:translate-x-1">download</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Transaction Chart -->
    <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm mb-6">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="font-headline font-bold text-xl text-primary">Weekly Transaction Trends</h3>
                <p class="text-xs text-on-surface-variant">Your transaction activity over the week</p>
            </div>
            <div class="flex gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-primary"></span>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Transactions</span>
                </div>
            </div>
        </div>
        
        <!-- Visual representation of a chart -->
        <div class="h-48 flex items-end justify-between gap-4">
            <template x-for="day in weeklyData">
                <div class="flex-1 space-y-2">
                    <div class="flex items-end justify-center h-40">
                        <div class="w-6 bg-primary rounded-t-sm transition-all hover:bg-primary-container" :style="'height: ' + (day.value / maxWeeklyValue * 100) + '%'"></div>
                    </div>
                    <p class="text-center text-[10px] font-bold text-on-surface-variant" x-text="day.name"></p>
                </div>
            </template>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Recent Transactions</h3>
            <a href="{{ route('petugas.riwayat') }}" class="text-xs font-bold text-primary hover:underline">View All</a>
        </div>
        <div class="divide-y divide-outline-variant/10">
            @forelse($transaksiTerakhir ?? [] as $transaksi)
                <div class="p-4 hover:bg-surface transition-colors group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                {{ $transaksi->jenis === 'masuk' ? 'bg-primary/10 text-primary' : 'bg-error/10 text-error' }}">
                                <span class="material-symbols-outlined">
                                    {{ $transaksi->jenis === 'masuk' ? 'account_balance' : 'shopping_cart' }}
                                </span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface">{{ $transaksi->santri_name ?? 'Santri' }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $transaksi->kategori }} • {{ $transaksi->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold {{ $transaksi->jenis === 'masuk' ? 'text-primary' : 'text-error' }}">
                                {{ $transaksi->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-on-surface-variant">{{ $transaksi->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <span class="material-symbols-outlined text-4xl text-outline mb-2">receipt_long</span>
                    <p class="text-sm text-on-surface-variant">Belum ada transaksi hari ini</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function petugasDashboard() {
    return {
        weeklyData: @json($weeklyData ?? []),
        get maxWeeklyValue() {
            if (this.weeklyData.length === 0) return 1;
            const max = Math.max(...this.weeklyData.map(d => d.value));
            return max > 0 ? max : 1;
        }
    }
}
</script>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
