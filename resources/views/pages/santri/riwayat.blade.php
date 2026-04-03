@extends('layouts.santri')

@section('title', 'Riwayat Transaksi')

@section('content')
<div x-data="riwayatSantri({{ json_encode([
    'currentFilter' => $currentFilter ?? 'all',
    'currentPeriode' => $currentPeriode ?? 'bulan',
    'currentKategori' => $currentKategori ?? 'all'
]) }})" class="pb-24">
    <!-- Header -->
    <header class="w-full pt-12 pb-6 px-5 sticky top-0 z-40 bg-surface/80 backdrop-blur-md">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('santri.home') }}" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
                    <span class="material-symbols-outlined text-primary">arrow_back</span>
                </a>
                <h1 class="font-headline font-bold text-xl text-primary">Riwayat Transaksi</h1>
            </div>
            <button @click="showFilter = !showFilter" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
                <span class="material-symbols-outlined text-primary">filter_list</span>
            </button>
        </div>
    </header>

    <main class="px-5">
        <!-- Month Selector / Summary Section -->
        <section class="mt-4 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <p class="text-on-surface-variant font-semibold text-sm">{{ now()->locale('id')->isoFormat('MMMM YYYY') }}</p>
                <span class="material-symbols-outlined text-sm text-on-surface-variant">expand_more</span>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-surface-container-lowest p-4 rounded-2xl">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 rounded-full bg-error-container flex items-center justify-center">
                            <span class="material-symbols-outlined text-error text-sm">trending_down</span>
                        </div>
                        <span class="text-xs font-medium text-on-surface-variant">Pengeluaran</span>
                    </div>
                    <p class="text-error font-headline font-bold text-base">Rp {{ number_format($pengeluaranBulanIni ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-surface-container-lowest p-4 rounded-2xl">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 rounded-full bg-primary-fixed flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-sm">trending_up</span>
                        </div>
                        <span class="text-xs font-medium text-on-surface-variant">Pemasukan</span>
                    </div>
                    <p class="text-primary font-headline font-bold text-base">Rp {{ number_format($pemasukanBulanIni ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <!-- Filter Chips -->
        <section class="flex gap-2 overflow-x-auto pb-2 mb-4 no-scrollbar">
            <a :href="getFilterUrl('all')" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
               :class="filter === 'all' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'">
                Semua
            </a>
            <a :href="getFilterUrl('keluar')" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
               :class="filter === 'keluar' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'">
                Pengeluaran
            </a>
            <a :href="getFilterUrl('masuk')" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
               :class="filter === 'masuk' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'">
                Pemasukan
            </a>
        </section>

        <!-- Period Filter Dropdown -->
        <div x-show="showFilter" x-cloak class="mb-4 p-3 bg-surface-container-low rounded-xl">
            <p class="text-xs font-semibold text-on-surface-variant mb-2 uppercase">Periode</p>
            <div class="grid grid-cols-2 gap-2">
                <a :href="getPeriodeUrl('hari')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'hari' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Hari Ini
                </a>
                <a :href="getPeriodeUrl('minggu')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'minggu' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Minggu Ini
                </a>
                <a :href="getPeriodeUrl('bulan')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'bulan' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Bulan Ini
                </a>
                <a :href="getPeriodeUrl('tahun')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'tahun' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Tahun Ini
                </a>
            </div>
        </div>

        <!-- Transaction List -->
        <div class="space-y-6">
            @php
                $groupedTransactions = $transaksiList->groupBy(function($item) {
                    $today = \Carbon\Carbon::today();
                    $yesterday = \Carbon\Carbon::yesterday();
                    
                    if ($item->created_at->isSameDay($today)) {
                        return 'Hari Ini';
                    } elseif ($item->created_at->isSameDay($yesterday)) {
                        return 'Kemarin';
                    } else {
                        return $item->created_at->locale('id')->isoFormat('DD MMM YYYY');
                    }
                });
            @endphp

            @forelse($groupedTransactions as $groupName => $transactions)
                <section>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-outline mb-3">{{ $groupName }}</h3>
                    <div class="space-y-3">
                        @foreach($transactions as $transaksi)
                            <!-- Transaction Item -->
                            <div class="flex items-center justify-between p-4 bg-surface-container-lowest rounded-2xl {{ $transaksi->jenis === 'masuk' ? 'border-l-4 border-primary/20' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl {{ $transaksi->jenis === 'masuk' ? 'bg-primary-fixed' : 'bg-surface-container' }} flex items-center justify-center">
                                        <span class="material-symbols-outlined {{ $transaksi->jenis === 'masuk' ? 'text-primary' : 'text-on-surface-variant' }}">
                                            @if($transaksi->kategori === 'top_up')
                                                account_balance_wallet
                                            @elseif($transaksi->kategori === 'kantin')
                                                restaurant_menu
                                            @elseif($transaksi->kategori === 'koperasi')
                                                shopping_bag
                                            @elseif($transaksi->kategori === 'laundry')
                                                local_laundry_service
                                            @elseif($transaksi->kategori === 'fotokopi')
                                                print
                                            @else
                                                {{ $transaksi->jenis === 'masuk' ? 'trending_up' : 'trending_down' }}
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-on-surface text-sm">{{ ucfirst($transaksi->kategori ?? 'Transaksi') }}</h4>
                                        <p class="text-xs text-on-surface-variant mt-0.5">
                                            {{ $transaksi->created_at->format('H:i') }} • {{ $transaksi->jenis === 'masuk' ? 'Pemasukan' : 'Pengeluaran' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold {{ $transaksi->jenis === 'masuk' ? 'text-primary' : 'text-error' }}">
                                        {{ $transaksi->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-primary font-medium bg-primary-fixed/30 px-2 py-0.5 rounded-full inline-block mt-1">
                                        Selesai
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="text-center py-16">
                    <span class="material-symbols-outlined text-6xl text-outline mb-3">receipt_long</span>
                    <p class="text-sm text-on-surface-variant">Belum ada transaksi</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($transaksiList->hasPages())
            <div class="mt-6">
                {{ $transaksiList->links() }}
            </div>
        @endif
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 py-4 bg-surface/80 backdrop-blur-xl shadow-[0_-4px_24px_-4px_rgba(25,28,29,0.06)] rounded-t-[1.5rem]">
        <a href="{{ route('santri.home') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">home</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Beranda</span>
        </a>
        <a href="{{ route('santri.riwayat') }}" class="flex flex-col items-center justify-center bg-primary text-on-primary rounded-[0.75rem] px-4 py-1.5 transition-all">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">history</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Riwayat</span>
        </a>
        <a href="{{ route('santri.profile') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg">person</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Profil</span>
        </a>
    </nav>
</div>

<script>
function riwayatSantri(config) {
    return {
        filter: config.currentFilter,
        periode: config.currentPeriode,
        kategori: config.currentKategori,
        showFilter: false,

        getFilterUrl(type) {
            const url = new URL(window.location.href);
            url.searchParams.set('jenis', type);
            if (type === 'all') url.searchParams.delete('jenis');
            return url.toString();
        },

        getFilterUrlWithKategori(kat) {
            const url = new URL(window.location.href);
            if (this.kategori === kat) {
                url.searchParams.delete('kategori');
            } else {
                url.searchParams.set('kategori', kat);
            }
            url.searchParams.delete('jenis');
            return url.toString();
        },

        getPeriodeUrl(period) {
            const url = new URL(window.location.href);
            url.searchParams.set('periode', period);
            return url.toString();
        }
    }
}
</script>

<style>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
