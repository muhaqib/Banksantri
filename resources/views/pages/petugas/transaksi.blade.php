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

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1: NIS Input & Profile -->
        <div class="space-y-6">
            <!-- RFID Input -->
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
                <h2 class="font-headline text-sm font-bold text-primary mb-6 uppercase tracking-widest">Scan Kartu RFID</h2>

                <form @submit.prevent="cariSantri" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Tap Kartu RFID Santri
                        </label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-primary">
                            </div>
                            <input type="text"
                                   x-model="rfidInput"
                                   @keydown.enter.prevent="cariSantri"
                                   @focus="$el.select()"
                                   autofocus
                                   placeholder="Tap kartu atau masukkan kode RFID"
                                   class="w-full bg-surface-container-high border-none rounded-xl py-4 px-5 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all placeholder:text-outline/50 font-medium">
                        </div>
                        <p x-show="errorMessage" class="text-error text-sm mt-2" x-text="errorMessage"></p>
                        <p class="text-xs text-on-surface-variant mt-2">
                            <span class="material-symbols-outlined text-xs align-middle">info</span>
                            RFID reader akan otomatis mendeteksi kartu
                        </p>
                    </div>

                    <button type="submit"
                            :disabled="!rfidInput || loading"
                            class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading" class="material-symbols-outlined">badge</span>
                        <span x-show="loading" class="material-symbols-outlined animate-spin">progress_activity</span>
                        <span x-show="!loading">Verifikasi Santri</span>
                        <span x-show="loading">Memverifikasi...</span>
                    </button>
                </form>
            </section>

            <!-- Santri Profile -->
            <section class="bg-surface-container-lowest p-6 rounded-xl shadow-sm" x-show="santriData">
                <h2 class="font-headline text-sm font-bold text-primary mb-6 uppercase tracking-widest">Identitas Santri</h2>

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
            <form action="{{ route('petugas.transaksi.store') }}" method="POST" class="bg-surface-container-lowest p-6 rounded-xl shadow-sm" @submit="saveFormState()">
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
                                <input type="text"
                                       name="nominal"
                                       x-model="formattedNominal"
                                       @input="handleNominalInput($event.target.value)"
                                       required
                                       class="w-full bg-surface-container-high border-none rounded-xl py-4 pl-12 pr-4 font-headline text-2xl font-bold text-primary focus:ring-0 focus:bg-surface-container-highest transition-colors"
                                       placeholder="0">
                            </div>
                            <input type="hidden" name="nominal" :value="form.nominal">
                        </div>
                        <div class="grid grid-cols-3 gap-2">
    <template x-for="nominal in [2000, 5000, 10000, 15000, 20000, 50000]" :key="nominal">
        <button type="button"
                @click="setNominal(nominal)"
                :class="form.nominal == nominal
                    ? 'bg-primary text-white'
                    : 'bg-surface-container-low text-on-surface'"
                class="py-2 px-3 text-xs font-bold rounded-lg hover:bg-primary/10 hover:text-primary transition-colors">
            Rp <span x-text="formatRupiah(nominal)"></span>
        </button>
    </template>
</div>

                        <!-- Kategori (Auto-selected based on petugas jabatan) -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kategori Transaksi</label>
                            <div class="p-4 bg-primary/10 rounded-xl border-2 border-primary/30">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">{{ $kategoriIcon }}</span>
                                        <div>
                                            <p class="font-headline text-lg font-bold text-primary" x-text="kategoriLabel"></p>
                                            <p class="text-xs text-on-surface-variant">{{ $kategoriLabel }}</p>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-primary">check_circle</span>
                                </div>
                            </div>
                            <!-- Hidden input with server-side value as primary source -->
                            <input type="hidden" name="kategori" id="kategoriInput" value="{{ $kategoriValue }}" required>
                            <!-- Alpine.js will update this if needed -->
                            <p class="text-xs text-on-surface-variant mt-2">
                                <span class="material-symbols-outlined text-xs align-middle">info</span>
                                Anda sebagai ({{ auth()->user()->jabatan ?? 'N/A' }})
                            </p>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Catatan (Opsional)</label>
                            <textarea name="keterangan"
                                      x-model="form.keterangan"
                                      @input="saveFormState()"
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
                                    :disabled="pin.length !== 6 || !form.nominal"
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
                <span class="material-symbols-outlined text-5xl text-outline mb-4">badge</span>
                <p class="text-on-surface-variant font-medium">Belum ada data santri</p>
                <p class="text-sm text-outline mt-1">Tap kartu RFID atau masukkan kode RFID untuk memulai transaksi</p>
            </div>
        </div>
    </div>
</div>

<script>
function transaksiForm() {
    return {
        rfidInput: '',
        loading: false,
        santriData: null,
        pin: [],
        showPin: false,
        errorMessage: '',
        kategoriLabel: '{{ $kategoriLabel }}',
        formattedNominal: '',
        form: {
            nominal: '',
            kategori: '{{ $kategoriValue }}',
            keterangan: ''
        },

        init() {
            // Initialize kategori from server-side value
            this.form.kategori = '{{ $kategoriValue }}';
            
            // Sync hidden input with Alpine.js
            this.$nextTick(() => {
                const kategoriInput = document.getElementById('kategoriInput');
                if (kategoriInput) {
                    kategoriInput.value = this.form.kategori;
                }
            });
            
            // Clear saved form state if page loaded without errors (successful submission)
            @if(!$errors->any())
                localStorage.removeItem('transaksi_nominal');
                localStorage.removeItem('transaksi_kategori');
                localStorage.removeItem('transaksi_keterangan');
            @endif

            // Initialize from URL params if exists
            const urlParams = new URLSearchParams(window.location.search);
            const rfid = urlParams.get('rfid');
            if (rfid) {
                this.rfidInput = rfid;
                this.cariSantri();
            }

            // Restore form state from localStorage (only if there are errors)
            @if($errors->any())
                this.restoreFormState();
            @endif

            // Auto-focus on RFID input when no santri data
            if (!this.santriData) {
                this.$nextTick(() => {
                    const rfidInput = document.querySelector('input[x-model="rfidInput"]');
                    if (rfidInput) {
                        rfidInput.focus();
                    }
                });
            }
        },

        saveFormState() {
            // Save form data to localStorage
            localStorage.setItem('transaksi_nominal', this.form.nominal);
            localStorage.setItem('transaksi_kategori', this.form.kategori);
            localStorage.setItem('transaksi_keterangan', this.form.keterangan);
        },

        restoreFormState() {
            // Restore form data from localStorage
            const nominal = localStorage.getItem('transaksi_nominal');
            const kategori = localStorage.getItem('transaksi_kategori');
            const keterangan = localStorage.getItem('transaksi_keterangan');

            if (nominal) {
                this.form.nominal = nominal;
                this.formattedNominal = new Intl.NumberFormat('id-ID').format(nominal);
            }
            if (kategori) this.form.kategori = kategori;
            if (keterangan) this.form.keterangan = keterangan;
        },

        async cariSantri() {
            if (!this.rfidInput.trim()) return;

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
                    body: JSON.stringify({ rfid_code: this.rfidInput })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.santriData = data.data;
                    this.form.nominal = '';
                    this.formattedNominal = '';
                    this.form.kategori = '';
                    this.form.keterangan = '';
                    this.pin = [];
                    this.showPin = false;
                    this.rfidInput = ''; // Clear RFID input after successful scan
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

        formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        },

        handleNominalInput(value) {
            // Remove non-numeric characters
            const rawValue = value.replace(/[^0-9]/g, '');
            
            // Update the raw numeric value
            this.form.nominal = rawValue;
            
            // Format with dots for display
            if (rawValue) {
                this.formattedNominal = new Intl.NumberFormat('id-ID').format(rawValue);
            } else {
                this.formattedNominal = '';
            }
            
            this.saveFormState();
        },

        setNominal(value) {
            this.form.nominal = value;
            this.formattedNominal = new Intl.NumberFormat('id-ID').format(value);
            this.saveFormState();
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
