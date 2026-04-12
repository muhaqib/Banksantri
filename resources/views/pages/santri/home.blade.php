@extends('layouts.santri')

@section('title', 'Home')

@section('content')
<div x-data="santriHome()" class="pb-24">
    <!-- Top Navigation Header -->
    <header class="w-full pt-4 pb-2 flex items-center justify-between px-5 sticky top-0 z-40 bg-surface">
        <div class="flex items-center gap-3">
            
            <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center ring-4 ring-white/30">
                    @if(auth()->user()->foto)
                        <img src="{{ Storage::url(auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover rounded-full">
                    @else
                        <span class="material-symbols-outlined text-white text-5xl">account_circle</span>
                    @endif
                </div>
            <div>
                <span class="block text-xs font-medium text-on-surface-variant opacity-70">Assalamu'alaikum,</span>
                <h1 class="font-headline text-xl font-bold tracking-tight text-primary">{{ auth()->user()->name ?? 'Santri' }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button class="relative hover:opacity-80 transition-opacity">
                <span class="material-symbols-outlined text-primary">notifications</span>
                <span class="absolute top-0 right-0 w-2 h-2 bg-error rounded-full"></span>
            </button>
        </div>
    </header>

    <main class="px-5 space-y-6">
        <!-- Alert Banner (Low Balance) -->
        <section x-show="saldo <= 10000" x-cloak>
            <div class="bg-error-container text-on-error-container p-4 rounded-xl flex items-center gap-3">
                <span class="material-symbols-outlined text-error">warning</span>
                <div class="flex-1">
                    <p class="text-xs font-semibold leading-tight">Saldo Menipis! Segera Isi Ulang</p>
                    <p class="text-[10px] opacity-80 uppercase tracking-wider">Sisa Saldo: Rp {{ number_format($saldo ?? 0, 0, ',', '.') }}</p>
                </div>
                <span class="material-symbols-outlined text-error text-lg">chevron_right</span>
            </div>
        </section>

        <!-- Main Balance Card -->
        <section>
            <div class="relative overflow-hidden bg-gradient-to-br from-primary to-primary-container rounded-[1.5rem] p-6 shadow-xl shadow-primary/10">
                <!-- Abstract Texture Overlay -->
                <div class="absolute inset-0 opacity-10 pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                
                <div class="relative z-10 space-y-8">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <span class="font-label text-[10px] font-bold uppercase tracking-[0.2em] text-primary-fixed">Saldo Utama</span>
                            <div class="flex items-baseline gap-1">
                                <span class="font-headline text-on-primary text-4xl font-extrabold tracking-tighter">{{ number_format($saldo ?? 0, 0, ',', '.') }}</span>
                                <span class="text-on-primary-container text-sm font-medium opacity-80">IDR</span>
                            </div>
                        </div>
                        <div class="bg-surface-container-lowest/10 p-2 rounded-lg backdrop-blur-md">
                            <span class="material-symbols-outlined text-white">account_balance_wallet</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-5 rounded-sm bg-white/20"></span>
                            <span class="text-white/60 font-mono text-xs">**** {{ substr(auth()->user()->nis ?? '0000', -4) }}</span>
                        </div>
                        <a href="{{ route('santri.topup') }}" class="bg-primary-fixed-dim text-on-primary-fixed-variant px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition-opacity">
                            Isi Saldo
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="bg-surface-container-low rounded-[1.25rem] p-5">
            <div class="grid grid-cols-4 gap-4">
                <a href="{{ route('santri.riwayat') }}" class="flex flex-col items-center gap-2 group">
                    <div class="w-12 h-12 bg-surface-container-lowest rounded-xl flex items-center justify-center text-primary shadow-sm group-active:scale-90 transition-transform">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">history</span>
                    </div>
                    <span class="text-[10px] font-bold text-on-surface-variant tracking-tighter font-headline">Riwayat</span>
                </a>
                <a href="{{ route('santri.topup') }}" class="flex flex-col items-center gap-2 group">
                    <div class="w-12 h-12 bg-surface-container-lowest rounded-xl flex items-center justify-center text-primary shadow-sm group-active:scale-90 transition-transform">
                        <span class="material-symbols-outlined">add_circle</span>
                    </div>
                    <span class="text-[10px] font-bold text-on-surface-variant tracking-tighter font-headline">Top Up</span>
                </a>
                <a href="{{ route('santri.prestasi') }}" class="flex flex-col items-center gap-2 group">
                    <div class="w-12 h-12 bg-surface-container-lowest rounded-xl flex items-center justify-center text-primary shadow-sm group-active:scale-90 transition-transform">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                    </div>
                    <span class="text-[10px] font-bold text-on-surface-variant tracking-tighter font-headline">Prestasi</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-2 group">
                    <div class="w-12 h-12 bg-surface-container-lowest rounded-xl flex items-center justify-center text-primary shadow-sm group-active:scale-90 transition-transform">
                        <span class="material-symbols-outlined">send</span>
                    </div>
                    <span class="text-[10px] font-bold text-on-surface-variant tracking-tighter font-headline">Tanya Ustadz</span>
                </a>
            </div>
        </section>

        <!-- Special Quick-Action: Sadaqah -->
        <section>
            <div class="bg-tertiary-container/30 rounded-2xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-tertiary-container text-on-tertiary-container rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined">volunteer_activism</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-on-tertiary-container font-headline">Sedekah Jumat</h4>
                        <p class="text-[10px] text-on-tertiary-container/70">Berkah untuk sesama</p>
                    </div>
                </div>
                <button class="bg-tertiary text-on-tertiary text-[10px] px-3 py-1.5 rounded-full font-bold">SOON</button>
            </div>
        </section>

        <!-- Recent History -->
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-headline font-bold text-on-surface tracking-tight">Riwayat Terakhir</h3>
                <a href="{{ route('santri.riwayat') }}" class="text-xs font-semibold text-primary">Lihat Semua</a>
            </div>
            
            <div class="space-y-3">
                @forelse($transaksiTerakhir ?? [] as $transaksi)
                    <!-- Transaction Item -->
                    <div class="bg-surface-container-lowest p-4 rounded-xl flex items-center justify-between transition-transform active:scale-[0.98]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 {{ $transaksi->jenis === 'masuk' ? 'bg-primary/10 text-primary' : 'bg-error/10 text-error' }} rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined">
                                    @if($transaksi->kategori === 'top_up' || $transaksi->kategori === 'tarik uang')
                                        account_balance_wallet
                                    @elseif($transaksi->kategori === 'kantin')
                                        restaurant_menu
                                    @elseif($transaksi->kategori === 'koperasi')
                                        shopping_bag
                                    @elseif($transaksi->kategori === 'laundry')
                                        local_laundry_service
                                    @elseif($transaksi->kategori === 'fotokopi')
                                        print
                                    @elseif($transaksi->kategori === 'syirkah')
                                        store
                                    @elseif($transaksi->kategori === 'beli kitab')
                                        menu_book
                                    @elseif($transaksi->kategori === 'mart')
                                        storefront
                                    @else
                                        {{ $transaksi->jenis === 'masuk' ? 'trending_up' : 'trending_down' }}
                                    @endif
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-bold leading-tight">{{ $transaksi->kategori ?? 'Transaksi' }}</p>
                                <p class="text-[10px] text-on-surface-variant opacity-60">{{ $transaksi->created_at->format('d M, H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $transaksi->jenis === 'masuk' ? 'text-primary' : 'text-error' }}">
                                {{ $transaksi->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-on-surface-variant opacity-60">Selesai</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">history</span>
                        <p class="text-sm text-on-surface-variant">Belum ada transaksi</p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <!-- Bottom Navigation Bar -->
    <x-santri.bottom-nav />
</div>

<script>
function santriHome() {
    return {
        saldo: {{ $saldo ?? 0 }}
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
