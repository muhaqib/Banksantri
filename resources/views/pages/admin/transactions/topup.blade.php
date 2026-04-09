@extends('layouts.app')

@section('header-title', 'Top Up Saldo Santri')
@php $activeRole = 'admin'; @endphp

@section('content')
<div x-data="topUpForm('{{ $nis ?? '' }}')" x-init="init()">
    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Top Up Saldo Santri</h2>
        <p class="text-on-surface-variant text-sm mt-1">Tambahkan saldo ke rekening santri dengan aman dan terverifikasi.</p>
    </div>

    <!-- Top Up Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Santri Search & Profile -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Search Form -->
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
                <h2 class="font-headline text-sm font-bold text-primary mb-6 uppercase tracking-widest">Cari Santri</h2>

                <form @submit.prevent="cariSantri" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Nama atau NIS Santri
                        </label>
                        <div class="relative group">
                            <input type="text"
                                   x-model="searchInput"
                                   @input="autoSearch"
                                   @keydown.enter.prevent="cariSantri"
                                   placeholder="Contoh: Ahmad atau 12345"
                                   class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                            <div class="absolute left-0 top-0 h-full w-0.5 bg-primary rounded-l-xl opacity-0 group-focus-within:opacity-100 transition-opacity"></div>
                        </div>
                        <p x-show="errorMessage" class="text-error text-sm mt-2" x-text="errorMessage"></p>
                    </div>

                    <button type="submit"
                            :disabled="!searchInput || loading"
                            class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading" class="material-symbols-outlined">search</span>
                        <span x-show="loading" class="material-symbols-outlined animate-spin">progress_activity</span>
                        <span x-show="!loading">Cari Santri</span>
                        <span x-show="loading">Mencari...</span>
                    </button>
                </form>
            </section>

            <!-- Santri Profile Card -->
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-sm" x-show="santriData">
                <h2 class="font-headline text-sm font-bold text-primary mb-6 uppercase tracking-widest">Data Santri</h2>
                
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="relative">
                        <template x-if="santriData?.foto_url">
                            <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-primary-fixed">
                                <img :src="santriData.foto_url" :alt="santriData?.nama" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <template x-if="!santriData?.foto_url">
                            <div class="w-24 h-24 bg-primary-fixed rounded-full flex items-center justify-center ring-4 ring-primary-fixed">
                                <span class="material-symbols-outlined text-primary text-5xl" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                            </div>
                        </template>
                        <div class="absolute -bottom-1 -right-1 bg-primary text-on-primary w-8 h-8 rounded-full flex items-center justify-center border-4 border-surface-container-lowest">
                            <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">verified</span>
                        </div>
                    </div>
                    
                    <div>
                        <p class="font-headline text-xl font-bold text-on-surface" x-text="santriData?.nama"></p>
                        <p class="text-sm font-medium text-on-surface-variant">NIS: <span x-text="santriData?.nis"></span></p>
                        <p class="text-xs text-on-surface-variant" x-text="santriData?.email"></p>
                    </div>
                </div>

                <!-- Saldo -->
                <div class="mt-8 pt-6 border-t border-surface-container-low">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Saldo Saat Ini</p>
                    <div class="bg-primary-container/10 p-4 rounded-xl">
                        <p class="font-headline text-2xl font-extrabold text-primary">Rp <span x-text="formatNumber(santriData?.saldo || 0)"></span></p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right: Top Up Form -->
        <div class="lg:col-span-7">
            <section class="bg-surface-container-lowest p-8 rounded-xl shadow-sm" x-show="santriData">
                <div class="flex items-center gap-3 mb-8">
                    <span class="material-symbols-outlined text-primary p-2 bg-primary-fixed rounded-full" style="font-variation-settings: 'FILL' 1;">add_card</span>
                    <div>
                        <h3 class="font-headline font-bold text-xl text-primary">Form Top Up</h3>
                        <p class="text-xs text-on-surface-variant">Tambah saldo ke rekening santri</p>
                    </div>
                </div>

                <form action="{{ route('admin.transactions.topup.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="nis" x-model="santriData?.nis">
                    
                    <!-- Santri Info (Readonly) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Santri</label>
                            <input type="text" x-model="santriData?.nama" readonly
                                   class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">NIS</label>
                            <input type="text" x-model="santriData?.nis" readonly
                                   class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface font-medium">
                        </div>
                    </div>

                    <!-- Nominal -->
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Nominal Top Up (IDR) <span class="text-error">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold">Rp</span>
                            <input type="number"
                                   name="nominal"
                                   required
                                   min="10000"
                                   step="10000"
                                   class="w-full bg-surface-container-high border-none rounded-xl py-4 pl-12 pr-4 font-headline text-2xl font-bold text-primary focus:ring-0 focus:bg-surface-container-highest transition-all"
                                   placeholder="0">
                            <div class="absolute left-0 top-0 h-full w-0.5 bg-primary rounded-l-xl opacity-0 group-focus-within:opacity-100 transition-opacity"></div>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-2">Minimum top up: Rp 10.000</p>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-4 gap-3">
                        <button type="button"
                                @click="$el.form.querySelector('input[name=nominal]').value = 50000"
                                class="px-3 py-3 text-sm font-semibold bg-surface-container-low text-on-surface-variant rounded-xl hover:bg-surface-container-high transition-colors">
                            Rp 50.000
                        </button>
                        <button type="button"
                                @click="$el.form.querySelector('input[name=nominal]').value = 100000"
                                class="px-3 py-3 text-sm font-semibold bg-surface-container-low text-on-surface-variant rounded-xl hover:bg-surface-container-high transition-colors">
                            Rp 100.000
                        </button>
                        <button type="button"
                                @click="$el.form.querySelector('input[name=nominal]').value = 200000"
                                class="px-3 py-3 text-sm font-semibold bg-surface-container-low text-on-surface-variant rounded-xl hover:bg-surface-container-high transition-colors">
                            Rp 200.000
                        </button>
                        <button type="button"
                                @click="$el.form.querySelector('input[name=nominal]').value = 500000"
                                class="px-3 py-3 text-sm font-semibold bg-primary text-on-primary rounded-xl hover:bg-primary-container transition-colors">
                            Rp 500.000
                        </button>
                    </div>

                    <!-- Sumber Dana -->
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Sumber Dana <span class="text-error">*</span>
                        </label>
                        <select name="sumber_dana"
                                required
                                class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all font-medium">
                            <option value="">Pilih Sumber Dana</option>
                            <option value="orang_tua">Cash</option>
                            <option value="donatur">Trasfer Bank</option>
                        </select>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Keterangan <span class="text-error">*</span>
                        </label>
                        <textarea name="keterangan"
                                  required
                                  rows="3"
                                  class="w-full bg-surface-container-high border-none rounded-xl p-4 text-sm focus:bg-surface-container-highest focus:ring-0 transition-all resize-none"
                                  placeholder="Contoh: Setoran dari orang tua santri untuk uang saku bulan ini"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/10 hover:shadow-primary/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <span>Proses Top Up</span>
                    </button>

                    <p class="text-[10px] text-center text-on-surface-variant uppercase tracking-widest font-bold">
                        Transaksi akan tercatat otomatis dalam riwayat santri
                    </p>
                </form>
            </section>

            <!-- Empty State -->
            <div x-show="!santriData" class="bg-surface-container-lowest p-12 rounded-xl shadow-sm text-center">
                <span class="material-symbols-outlined text-5xl text-outline mb-4">account_balance_wallet</span>
                <p class="text-on-surface-variant font-medium">Belum ada santri yang dipilih</p>
                <p class="text-sm text-outline mt-1">Cari santri dengan NIS untuk melanjutkan top up</p>
            </div>
        </div>
    </div>
</div>

<script>
function topUpForm(initialNis = '') {
    return {
        searchInput: initialNis,
        loading: false,
        santriData: null,
        errorMessage: '',
        searchTimeout: null,

        init() {
            // Auto-search if NIS is provided in URL
            if (this.searchInput && this.searchInput.trim()) {
                this.cariSantri();
            }
        },

        autoSearch() {
            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            // Auto-submit after 500ms of no typing (but keep cursor)
            if (this.searchInput.trim().length >= 2) {
                this.searchTimeout = setTimeout(() => {
                    this.cariSantri();
                }, 500);
            }
        },

        async cariSantri() {
            if (!this.searchInput.trim()) return;

            this.loading = true;
            this.santriData = null;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("admin.transactions.search-santri") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ search: this.searchInput })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.santriData = data.data;
                } else {
                    this.errorMessage = data.message || 'Santri tidak ditemukan';
                }
            } catch (error) {
                this.errorMessage = 'Terjadi kesalahan saat mencari santri';
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
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
