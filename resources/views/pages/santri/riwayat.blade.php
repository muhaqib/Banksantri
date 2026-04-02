@extends('layouts.santri')

@section('title', 'Riwayat Transaksi')

@section('content')
<div x-data="riwayatSantri()" class="pb-20">
    <!-- Header -->
    <header class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white pt-12 pb-6 px-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('santri.home') }}" class="p-2 hover:bg-white hover:bg-opacity-10 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-xl font-bold">Riwayat Transaksi</h1>
        </div>
    </header>

    <!-- Filter Tabs -->
    <div class="px-6 py-4 bg-white border-b border-gray-200">
        <div class="flex space-x-2">
            <button @click="filter = 'all'" 
                    :class="filter === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                Semua
            </button>
            <button @click="filter = 'masuk'" 
                    :class="filter === 'masuk' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                Masuk
            </button>
            <button @click="filter === 'keluar'" 
                    :class="filter === 'keluar' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                Keluar
            </button>
        </div>

        <!-- Period Filter -->
        <div class="mt-4">
            <select x-model="period" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                <option value="hari">Hari Ini</option>
                <option value="minggu">Minggu Ini</option>
                <option value="bulan">Bulan Ini</option>
                <option value="tahun">Tahun Ini</option>
            </select>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="px-6 py-4 space-y-3">
        @forelse($transaksiList ?? [] as $transaksi)
            <div x-show="shouldShow('{{ $transaksi->jenis }}')" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center
                                {{ $transaksi->jenis === 'masuk' ? 'bg-green-100' : 'bg-red-100' }}">
                                <svg class="w-6 h-6 {{ $transaksi->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="{{ $transaksi->jenis === 'masuk' ? 'M7 11l5-5m0 0l5 5m-5-5v12' : 'M17 13l-5 5m0 0l-5-5m5 5V6' }}">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $transaksi->kategori ?? 'Transaksi' }}</h4>
                                <p class="text-sm text-gray-500">{{ $transaksi->keterangan ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $transaksi->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold {{ $transaksi->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaksi->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Saldo: Rp {{ number_format($transaksi->saldo_setelah ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Transaction Details -->
                <div class="px-4 pb-4">
                    <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500">Petugas</span>
                            <p class="font-medium text-gray-900">{{ $transaksi->petugas_name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-500">Metode</span>
                            <p class="font-medium text-gray-900">RFID</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-500">Belum ada transaksi</p>
            </div>
        @endforelse
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 py-4 bg-surface/80 backdrop-blur-xl shadow-[0_-4px_24px_-4px_rgba(25,28,29,0.06)] rounded-t-[1.5rem]">
        <a href="{{ route('santri.home') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">home</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Beranda</span>
        </a>
        <a href="{{ route('santri.riwayat') }}" class="flex flex-col items-center justify-center bg-primary text-on-primary rounded-[0.75rem] px-4 py-1.5 transition-all">
            <span class="material-symbols-outlined text-lg">history</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Riwayat</span>
        </a>
        <a href="{{ route('santri.profile') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg">person</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Profil</span>
        </a>
    </nav>
</div>

<script>
function riwayatSantri() {
    return {
        filter: 'all',
        period: 'bulan',
        shouldShow(type) {
            if (this.filter === 'all') return true;
            return this.filter === type;
        }
    }
}
</script>
@endsection
