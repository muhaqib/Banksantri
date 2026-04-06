@extends('layouts.app')

@section('header-title', 'Terminal Transaksi')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div x-data="transaksiForm()" x-init="init()">
    <!-- Page Header -->
    <header class="mb-8">
        <h1 class="font-headline text-3xl font-extrabold text-primary tracking-tight">Terminal Transaksi</h1>
        <p class="text-on-surface-variant mt-1">Lakukan verifikasi identitas dan nominal pembayaran dengan aman.</p>
    </header>

    <!-- Success Message -->

    <!-- Error Messages -->
    @if($errors->any())
        <div class="mb-6 p-4 bg-error-container rounded-xl border border-error/20">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
                <div class="flex-1">
                    <p class="font-bold text-error mb-2">Terjadi Kesalahan</p>
                    <ul class="text-sm text-on-error-container space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Column 1: NIS Input & Profile -->
        <div class="space-y-6">
            <!-- NIS Input -->
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
                <h2 class="font-headline text-sm font-bold text-primary mb-6 uppercase tracking-widest">Cari Santri</h2>
                
                <form @submit.prevent="cariSantri" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Nomor Induk Santri (NIS)
                        </label>
                        <input type="text"
                               x-model="nisInput"
                               @keydown.enter.prevent="cariSantri"
                               placeholder="Contoh: 12345"
                               class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                        <p x-show="errorMessage" class="text-error text-sm mt-2" x-text="errorMessage"></p>
                    </div>
                    
                    <button type="submit"
                            :disabled="!nisInput || loading"
                            class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading" class="material-symbols-outlined">search</span>
                        <span x-show="loading" class="material-symbols-outlined animate-spin">progress_activity</span>
                        <span x-show="!loading">Cari Santri</span>
                        <span x-show="loading">Mencari...</span>
                    </button>
                </form>
            </section>

            <!-- Santri Profile -->
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-sm" x-show="santriData">
                <h2 class="font-headline text-sm font-bold text-primary mb-6 uppercase tracking-widest">Identitas Santri</h2>
                
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="relative">
                        <div class="w-24 h-24 bg-primary-fixed rounded-full flex items-center justify-center ring-4 ring-primary-fixed">
                            <span class="material-symbols-outlined text-primary text-5xl" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-primary text-on-primary w-8 h-8 rounded-full flex items-center justify-center border-4 border-surface-container-lowest">
                            <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">verified</span>
                        </div>
                    </div>
                    
                    <div>
                        <p class="font-headline text-xl font-bold text-on-surface" x-text="santriData?.nama"></p>
                        <p class="text-sm font-medium text-on-surface-variant">NIS: <span x-text="santriData?.nis"></span></p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-surface-container-low">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Saldo Tersedia</p>
                    <div class="bg-primary-container/10 p-4 rounded-xl">
                        <p class="font-headline text-2xl font-extrabold text-primary">Rp <span x-text="formatNumber(santriData?.saldo || 0)"></span></p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Column 2: Transaction Form -->
        <div class="lg:col-span-2" x-show="santriData">
            <form action="{{ route('petugas.transaksi.store') }}" method="POST" class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
                @csrf
                <input type="hidden" name="santri_id" :value="santriData?.id">
                
                <h2 class="font-headline text-sm font-bold text-primary mb-6 uppercase tracking-widest">Detail Transaksi</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left: Transaction Details -->
                    <div class="space-y-5">
                        <!-- Nominal -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nominal (IDR)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold">Rp</span>
                                <input type="number"
                                       name="nominal"
                                       x-model="form.nominal"
                                       required
                                       min="1000"
                                       step="1000"
                                       class="w-full bg-surface-container-high border-none rounded-xl py-4 pl-12 pr-4 font-headline text-2xl font-bold text-primary focus:ring-0 focus:bg-surface-container-highest transition-colors"
                                       placeholder="0">
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kategori</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button"
                                        @click="form.kategori = 'kantin'; $refs.kategoriInput.value = 'kantin'"
                                        :class="form.kategori === 'kantin' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'"
                                        class="px-4 py-3 rounded-xl text-sm font-semibold text-left flex items-center justify-between transition-colors">
                                    <span>Kantin</span>
                                    <span x-show="form.kategori === 'kantin'" class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                                <button type="button"
                                        @click="form.kategori = 'koperasi'; $refs.kategoriInput.value = 'koperasi'"
                                        :class="form.kategori === 'koperasi' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'"
                                        class="px-4 py-3 rounded-xl text-sm font-semibold text-left flex items-center justify-between transition-colors">
                                    <span>Koperasi Kitab</span>
                                    <span x-show="form.kategori === 'koperasi'" class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                                <button type="button"
                                        @click="form.kategori = 'laundry'; $refs.kategoriInput.value = 'laundry'"
                                        :class="form.kategori === 'laundry' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'"
                                        class="px-4 py-3 rounded-xl text-sm font-semibold text-left flex items-center justify-between transition-colors">
                                    <span>Laundry</span>
                                    <span x-show="form.kategori === 'laundry'" class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                                <button type="button"
                                        @click="form.kategori = 'lainnya'; $refs.kategoriInput.value = 'lainnya'"
                                        :class="form.kategori === 'lainnya' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant'"
                                        class="px-4 py-3 rounded-xl text-sm font-semibold text-left flex items-center justify-between transition-colors">
                                    <span>Lainnya</span>
                                    <span x-show="form.kategori === 'lainnya'" class="material-symbols-outlined text-sm">check_circle</span>
                                </button>
                            </div>
                            <input type="hidden" name="kategori" x-ref="kategoriInput" :value="form.kategori" required>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Catatan (Opsional)</label>
                            <textarea name="keterangan"
                                      x-model="form.keterangan"
                                      rows="3"
                                      class="w-full bg-surface-container-high border-none rounded-xl p-4 text-sm focus:ring-0 focus:bg-surface-container-highest transition-colors resize-none"
                                      placeholder="Contoh: Beli makanan di kantin"></textarea>
                        </div>
                    </div>

                    <!-- Right: PIN Verification -->
                    <div class="space-y-6">
                        <div class="bg-surface-container-low p-6 rounded-xl">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-headline text-sm font-bold text-primary uppercase tracking-widest">Otorisasi PIN</h3>
                                
                                <!-- Toggle Show/Hide PIN -->
                                <button type="button"
                                        @click="showPin = !showPin"
                                        class="text-xs font-semibold text-primary flex items-center gap-1 hover:bg-primary/10 px-2 py-1 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-sm" x-text="showPin ? 'visibility_off' : 'visibility'"></span>
                                    <span x-text="showPin ? 'Sembunyikan' : 'Lihat PIN'"></span>
                                </button>
                            </div>
                            
                            <!-- PIN Display (Show/Hide) -->
                            <div class="mb-4 p-3 bg-white rounded-xl">
                                <div class="flex justify-center gap-2">
                                    <template x-for="i in 6">
                                        <div class="w-10 h-12 flex items-center justify-center bg-surface-container-high rounded-lg border-2"
                                             :class="pin[i-1] ? 'border-primary' : 'border-surface-container-highest'">
                                            <span class="font-headline text-2xl font-bold"
                                                  :class="showPin && pin[i-1] ? 'text-primary' : 'text-transparent'"
                                                  x-text="pin[i-1] || '0'"></span>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-xs text-center text-on-surface-variant mt-2">
                                    <span x-show="showPin">✓ PIN terlihat untuk verifikasi</span>
                                    <span x-show="!showPin">• PIN disembunyikan (titik-titik)</span>
                                </p>
                            </div>

                            <!-- Numeric Keypad -->
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <template x-for="num in 9">
                                    <button type="button"
                                            @click="addPinNumber(num)"
                                            class="h-14 bg-white rounded-xl font-headline text-xl font-bold text-on-surface hover:bg-primary hover:text-on-primary transition-colors"
                                            x-text="num">
                                    </button>
                                </template>
                                <button type="button" class="h-14 bg-surface-container-low rounded-xl"></button>
                                <button type="button"
                                        @click="addPinNumber(0)"
                                        class="h-14 bg-white rounded-xl font-headline text-xl font-bold text-on-surface hover:bg-primary hover:text-on-primary transition-colors">
                                    0
                                </button>
                                <button type="button"
                                        @click="clearPin()"
                                        class="h-14 bg-error/10 rounded-xl flex items-center justify-center text-error hover:bg-error hover:text-on-error transition-colors">
                                    <span class="material-symbols-outlined">backspace</span>
                                </button>
                            </div>

                            <!-- Hidden PIN Input -->
                            <input type="hidden" name="pin" :value="pin.join('')" id="pinInput" required>

                            <!-- Submit Button -->
                            <button type="submit"
                                    :disabled="pin.length !== 6 || !form.nominal || !form.kategori"
                                    class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">send</span>
                                <span>Process Transaction</span>
                            </button>

                            <p class="text-[10px] text-center text-on-surface-variant mt-3">
                                *Transaksi bersifat final
                            </p>
                        </div>

                        <!-- Info Card -->
                        <div class="bg-primary/5 p-4 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-primary text-sm">info</span>
                                <div class="text-xs text-on-surface-variant">
                                    <p class="font-bold mb-1">Informasi:</p>
                                    <p>Klik <strong>"Lihat PIN"</strong> untuk memastikan PIN yang dimasukkan benar sebelum submit.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Empty State -->
        <div x-show="!santriData && !loading" class="lg:col-span-2 flex items-center justify-center bg-surface-container-lowest p-12 rounded-xl shadow-sm text-center">
            <div>
                <span class="material-symbols-outlined text-5xl text-outline mb-4">search</span>
                <p class="text-on-surface-variant font-medium">Belum ada data santri</p>
                <p class="text-sm text-outline mt-1">Masukkan NIS dan klik Cari untuk memulai transaksi</p>
            </div>
        </div>
    </div>
</div>

<script>
function transaksiForm() {
    return {
        nisInput: '',
        loading: false,
        santriData: null,
        pin: [],
        showPin: false,
        errorMessage: '',
        form: {
            nominal: '',
            kategori: '',
            keterangan: ''
        },

        init() {
            // Initialize from URL params if exists
            const urlParams = new URLSearchParams(window.location.search);
            const nis = urlParams.get('nis');
            if (nis) {
                this.nisInput = nis;
                this.cariSantri();
            }
        },

        async cariSantri() {
            if (!this.nisInput.trim()) return;
            
            this.loading = true;
            this.santriData = null;
            this.errorMessage = '';
            
            try {
                const response = await fetch('{{ route("petugas.transaksi.scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nis: this.nisInput })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    this.santriData = data.data;
                    this.form.nominal = '';
                    this.form.kategori = '';
                    this.form.keterangan = '';
                    this.pin = [];
                    this.showPin = false;
                } else {
                    this.errorMessage = data.message || 'Santri tidak ditemukan';
                }
            } catch (error) {
                this.errorMessage = 'Terjadi kesalahan: ' + error.message;
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },

        addPinNumber(num) {
            if (this.pin.length < 6) {
                this.pin.push(num.toString());
            }
        },

        clearPin() {
            this.pin = [];
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
