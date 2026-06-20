@extends('layouts.app')

@section('header-title', 'Laundry')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div x-data="laundryForm()" x-init="init()" class="space-y-6">
    <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Transaksi Laundry</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Fitur khusus laundry, terpisah dari transaksi keuangan umum.</p>
        </div>
        <a href="{{ route('petugas.laundry.history') }}" class="inline-flex w-fit items-center gap-2 rounded-xl bg-surface-container-high px-5 py-3 text-sm font-bold text-on-surface">
            <span class="material-symbols-outlined text-lg">history</span>
            Riwayat Laundry
        </a>
    </header>

    @if($errors->any())
        <div class="rounded-xl border border-error/20 bg-error-container p-4 text-on-error-container">
            <p class="font-bold text-error">Transaksi laundry gagal diproses</p>
            <ul class="mt-1 text-sm font-semibold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <section class="space-y-6 xl:col-span-4">
            <div class="rounded-xl border-l-4 border-primary bg-surface-container-lowest p-6 shadow-sm">
                <div class="flex rounded-xl bg-surface-container-high p-1">
                    <button type="button" @click="setPaymentType('tunai')" :class="paymentType === 'tunai' ? 'bg-primary text-on-primary' : 'text-on-surface-variant'" class="flex-1 rounded-lg px-4 py-2 text-sm font-bold transition">Tunai</button>
                    <button type="button" @click="setPaymentType('bulanan')" :class="paymentType === 'bulanan' ? 'bg-primary text-on-primary' : 'text-on-surface-variant'" class="flex-1 rounded-lg px-4 py-2 text-sm font-bold transition">Bulanan</button>
                </div>

                <div x-show="paymentType === 'tunai'" class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" @click="paymentMethod = 'cash'; pin = []" :class="paymentMethod === 'cash' ? 'border-primary bg-primary/10 text-primary' : 'border-outline-variant/30 bg-white text-on-surface'" class="rounded-xl border px-4 py-3 text-sm font-bold">Cash</button>
                    <button type="button" @click="paymentMethod = 'saldo_tabungan'" :class="paymentMethod === 'saldo_tabungan' ? 'border-primary bg-primary/10 text-primary' : 'border-outline-variant/30 bg-white text-on-surface'" class="rounded-xl border px-4 py-3 text-sm font-bold">Saldo Tabungan</button>
                </div>

                <div class="relative mt-5">
                    <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Cari Santri</label>
                    <input x-model="searchQuery" @input.debounce.250ms="searchSantri" @focus="searchSantri" placeholder="Nama, NIS, atau RFID" class="input-field mt-2">
                    <div x-show="searchOpen" @click.outside="searchOpen = false" x-cloak class="absolute z-20 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-outline-variant/20 bg-white shadow-xl">
                        <template x-for="item in searchResults" :key="item.id">
                            <button type="button" @click="selectSantri(item)" class="flex w-full items-center gap-3 border-b border-outline-variant/10 px-4 py-3 text-left hover:bg-surface-container-low">
                                <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-primary-fixed">
                                    <template x-if="item.foto_url"><img :src="item.foto_url" class="h-full w-full object-cover"></template>
                                    <template x-if="!item.foto_url"><span class="material-symbols-outlined text-primary">account_circle</span></template>
                                </div>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-bold text-on-surface" x-text="item.nama"></span>
                                    <span class="block text-xs text-on-surface-variant" x-text="`NIS ${item.nis || '-'} · RFID ${item.rfid_code || '-'}`"></span>
                                </span>
                            </button>
                        </template>
                        <p x-show="!searchLoading && searchResults.length === 0" class="px-4 py-5 text-center text-sm font-semibold text-on-surface-variant">
                            <span x-text="paymentType === 'bulanan' ? 'Tidak ada santri terdaftar bulan ini.' : 'Santri tidak ditemukan.'"></span>
                        </p>
                    </div>
                </div>

                <form @submit.prevent="scanSantri" class="mt-4 flex gap-2">
                    <input x-model="rfidInput" placeholder="Tap RFID langsung" class="input-field flex-1">
                    <button :disabled="loading || !rfidInput" class="rounded-xl bg-primary px-5 font-bold text-on-primary disabled:opacity-50">
                        <span class="material-symbols-outlined" :class="loading && 'animate-spin'" x-text="loading ? 'progress_activity' : 'badge'"></span>
                    </button>
                </form>
                <p x-show="errorMessage" x-text="errorMessage" class="mt-3 text-sm font-semibold text-error"></p>
            </div>

            <div x-show="santriData" x-cloak class="rounded-xl bg-surface-container-lowest p-6 text-center shadow-sm">
                <template x-if="santriData?.foto_url">
                    <img :src="santriData.foto_url" :alt="santriData.nama" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-primary-fixed">
                </template>
                <template x-if="!santriData?.foto_url">
                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-primary-fixed ring-4 ring-primary-fixed">
                        <span class="material-symbols-outlined text-5xl text-primary">account_circle</span>
                    </div>
                </template>
                <h3 class="mt-4 font-headline text-xl font-bold text-on-surface" x-text="santriData?.nama"></h3>
                <p class="text-sm text-on-surface-variant">NIS: <span x-text="santriData?.nis || '-'"></span></p>
                <p class="mt-2 text-sm font-bold text-primary" x-show="paymentType === 'tunai' && paymentMethod === 'saldo_tabungan'">Saldo Rp <span x-text="formatNumber(santriData?.saldo || 0)"></span></p>

                <div class="mt-6 rounded-xl bg-surface-container-low p-4 text-left">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Kuota Bulanan</p>
                        <strong x-text="santriData?.subscription ? formatKg(santriData.subscription.remaining_kg) : 'Belum daftar'"></strong>
                    </div>
                    <div class="mt-4 h-3 overflow-hidden rounded-full bg-surface-container-high">
                        <div class="h-full rounded-full bg-primary transition-all" :style="`width: ${quotaPercent}%`"></div>
                    </div>
                    <div class="mt-2 flex justify-between text-xs font-semibold text-on-surface-variant">
                        <span x-text="santriData?.subscription ? `${formatKg(santriData.subscription.used_kg)} terpakai` : 'Perlu daftar ulang bulan ini'"></span>
                        <span x-text="santriData?.subscription ? `${formatKg(santriData.subscription.quota_kg)} / bulan` : '{{ $defaultQuotaKg }} Kg / bulan'"></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="xl:col-span-8">
            <form x-ref="form" action="{{ route('petugas.laundry.store') }}" method="POST" class="rounded-xl bg-surface-container-lowest p-6 shadow-sm" @submit.prevent="handleSubmit">
                @csrf
                <input type="hidden" name="santri_id" :value="santriData?.id">
                <input type="hidden" name="payment_type" :value="paymentType">
                <input type="hidden" name="payment_method" :value="paymentMethod">
                <input type="hidden" name="pin" :value="pin.join('')">

                <div x-show="!santriData" class="py-20 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined mb-3 text-5xl text-outline">local_laundry_service</span>
                    <p class="font-semibold">Pilih santri terlebih dahulu untuk memulai laundry.</p>
                </div>

                <div x-show="santriData" x-cloak class="space-y-7">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Tanggal
                            <input type="date" name="laundry_date" x-model="laundryDate" @change="refreshSelectedSantri" required class="input-field mt-2">
                        </label>
                        <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Harga per Kg
                            <input type="number" name="price_per_kg" x-model.number="pricePerKg" :readonly="paymentType === 'bulanan'" required min="0" class="input-field mt-2">
                        </label>
                        <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Berat Laundry
                            <input type="number" name="weight_kg" x-model.number="weightKg" required min="0.1" step="0.1" class="input-field mt-2">
                        </label>
                    </div>

                    <div x-show="paymentType === 'bulanan'" class="rounded-xl bg-primary/5 p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-primary">Tracker Kuota</p>
                                <p class="mt-1 text-sm text-on-surface-variant">Pemakaian bulan ini habis otomatis saat bulan berganti.</p>
                            </div>
                            <strong :class="quotaRemainingAfter < 0 ? 'text-error' : 'text-primary'" x-text="formatKg(quotaRemainingAfter)"></strong>
                        </div>
                        <div class="mt-4 h-4 overflow-hidden rounded-full bg-white">
                            <div class="h-full rounded-full transition-all" :class="quotaRemainingAfter < 0 ? 'bg-error' : 'bg-primary'" :style="`width: ${quotaAfterPercent}%`"></div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="font-headline text-sm font-bold uppercase tracking-widest text-primary">Rincian Baju</h2>
                                <p class="text-xs font-bold text-on-surface-variant">Total: <span x-text="totalClothes"></span> pcs</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input x-model="clothSearch" placeholder="Cari rincian" class="input-field h-10 w-full md:w-56">
                                <button type="button" @click="clothPage = Math.max(1, clothPage - 1)" class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container-high">
                                    <span class="material-symbols-outlined text-lg">chevron_left</span>
                                </button>
                                <button type="button" @click="clothPage = Math.min(clothTotalPages, clothPage + 1)" class="flex h-10 w-10 items-center justify-center rounded-lg bg-surface-container-high">
                                    <span class="material-symbols-outlined text-lg">chevron_right</span>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <template x-for="item in paginatedClothes" :key="item.key">
                                <div class="rounded-xl border border-outline-variant/20 bg-surface-container-low p-3 text-center">
                                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-primary/5 text-primary">
                                        <span class="material-symbols-outlined" x-text="item.icon"></span>
                                    </div>
                                    <p class="mt-2 text-xs font-bold" x-text="item.label"></p>
                                    <div class="mt-3 flex items-center justify-center gap-3">
                                        <button type="button" @click="decrementCloth(item.key)" class="flex h-7 w-7 items-center justify-center rounded-full bg-surface-container-high">
                                            <span class="material-symbols-outlined text-xs">remove</span>
                                        </button>
                                        <span class="w-6 text-center text-sm font-bold" x-text="clothes[item.key] || 0"></span>
                                        <button type="button" @click="incrementCloth(item.key)" class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-on-primary">
                                            <span class="material-symbols-outlined text-xs">add</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <template x-for="item in clothItems" :key="`hidden-${item.key}`">
                            <input type="hidden" :name="`clothes[${item.key}]`" :value="clothes[item.key] || 0">
                        </template>
                        <p class="text-center text-xs font-semibold text-on-surface-variant">Halaman <span x-text="clothPage"></span> dari <span x-text="clothTotalPages"></span></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl bg-surface-container-low p-5 text-center">
                            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Total Baju</p>
                            <p class="mt-2 font-headline text-3xl font-extrabold text-on-surface"><span x-text="totalClothes"></span> pcs</p>
                        </div>
                        <div class="rounded-xl bg-surface-container-low p-5 text-center">
                            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Pembayaran</p>
                            <p class="mt-2 font-headline text-2xl font-extrabold text-primary" x-text="paymentLabel"></p>
                        </div>
                        <div x-show="paymentType === 'tunai'" class="rounded-xl bg-primary/5 p-5 text-center">
                            <p class="text-xs font-bold uppercase tracking-widest text-primary">Total Harga</p>
                            <p class="mt-2 font-headline text-3xl font-extrabold text-primary">Rp <span x-text="formatNumber(totalPrice)"></span></p>
                        </div>
                    </div>

                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Catatan
                        <textarea name="notes" rows="2" class="input-field mt-2"></textarea>
                    </label>

                    <button :disabled="!canSubmit" class="w-full rounded-xl bg-primary py-4 font-headline font-bold text-on-primary shadow-lg shadow-primary/20 disabled:cursor-not-allowed disabled:opacity-50">
                        Simpan dan Cetak Nota
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div x-show="pinModalOpen" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-black/80 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="font-headline text-xl font-extrabold text-primary">Verifikasi PIN</h3>
                    <p class="text-sm text-on-surface-variant">Saldo tabungan santri akan dipotong untuk transaksi ini.</p>
                </div>
                <button type="button" @click="pinModalOpen = false; pin = []" class="rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-low">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="mb-5 flex justify-center gap-2 rounded-xl bg-surface-container-low p-3">
                <template x-for="i in 6" :key="i">
                    <div class="flex h-12 w-10 items-center justify-center rounded-lg border-2 bg-white" :class="pin[i-1] ? 'border-primary' : 'border-surface-container-highest'">
                        <span class="h-3 w-3 rounded-full" :class="pin[i-1] ? 'bg-primary' : 'bg-outline/20'"></span>
                    </div>
                </template>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <template x-for="num in 9" :key="num">
                    <button type="button" @click="addPin(num)" class="h-14 rounded-xl bg-surface-container-low font-headline text-xl font-bold hover:bg-primary hover:text-on-primary" x-text="num"></button>
                </template>
                <button type="button" class="h-14 rounded-xl bg-white"></button>
                <button type="button" @click="addPin(0)" class="h-14 rounded-xl bg-surface-container-low font-headline text-xl font-bold hover:bg-primary hover:text-on-primary">0</button>
                <button type="button" @click="pin.pop()" @dblclick="pin = []" class="flex h-14 items-center justify-center rounded-xl bg-error/10 text-error">
                    <span class="material-symbols-outlined">backspace</span>
                </button>
            </div>
            <button type="button" @click="submitWithPin" :disabled="pin.length !== 6" class="mt-5 w-full rounded-xl bg-primary py-4 font-headline font-bold text-on-primary disabled:opacity-50">Verifikasi dan Cetak Nota</button>
        </div>
    </div>
</div>

<script>
function laundryForm() {
    return {
        paymentType: 'tunai',
        paymentMethod: 'cash',
        rfidInput: '',
        searchQuery: '',
        searchOpen: false,
        searchLoading: false,
        searchResults: [],
        loading: false,
        errorMessage: '',
        santriData: null,
        laundryDate: @js($today->toDateString()),
        pricePerKg: {{ $defaultPricePerKg }},
        weightKg: 0,
        pin: [],
        pinModalOpen: false,
        clothSearch: '',
        clothPage: 1,
        clothPerPage: 8,
        clothItems: @js(collect($clothes)->map(fn ($item, $key) => ['key' => $key, 'label' => $item['label'], 'icon' => $item['icon']])->values()),
        clothes: {},
        init() {
            this.clothItems.forEach((item) => this.clothes[item.key] = 0);
        },
        setPaymentType(type) {
            this.paymentType = type;
            if (type === 'bulanan') this.paymentMethod = 'quota_bulanan';
            if (type === 'tunai' && this.paymentMethod === 'quota_bulanan') this.paymentMethod = 'cash';
            this.santriData = null;
            this.searchQuery = '';
            this.searchResults = [];
            this.pin = [];
        },
        get filteredClothes() {
            const query = this.clothSearch.toLowerCase().trim();
            const items = query ? this.clothItems.filter((item) => item.label.toLowerCase().includes(query)) : this.clothItems;
            this.clothPage = Math.min(this.clothPage, Math.max(1, Math.ceil(items.length / this.clothPerPage)));
            return items;
        },
        get paginatedClothes() {
            const start = (this.clothPage - 1) * this.clothPerPage;
            return this.filteredClothes.slice(start, start + this.clothPerPage);
        },
        get clothTotalPages() {
            return Math.max(1, Math.ceil(this.filteredClothes.length / this.clothPerPage));
        },
        get totalClothes() {
            return Object.values(this.clothes).reduce((total, value) => total + Number(value || 0), 0);
        },
        get totalPrice() {
            return Math.round(Number(this.weightKg || 0) * Number(this.pricePerKg || 0));
        },
        get quotaRemainingAfter() {
            const remaining = Number(this.santriData?.subscription?.remaining_kg ?? 0);
            return remaining - Number(this.weightKg || 0);
        },
        get quotaPercent() {
            const quota = Number(this.santriData?.subscription?.quota_kg ?? {{ $defaultQuotaKg }});
            const used = Number(this.santriData?.subscription?.used_kg ?? 0);
            return Math.min(100, Math.round((used / Math.max(quota, 1)) * 100));
        },
        get quotaAfterPercent() {
            const quota = Number(this.santriData?.subscription?.quota_kg ?? {{ $defaultQuotaKg }});
            const usedAfter = Number(this.santriData?.subscription?.used_kg ?? 0) + Number(this.weightKg || 0);
            return Math.min(100, Math.round((usedAfter / Math.max(quota, 1)) * 100));
        },
        get paymentLabel() {
            if (this.paymentType === 'bulanan') return 'Kuota Bulanan';
            return this.paymentMethod === 'cash' ? 'Cash' : 'Saldo Tabungan';
        },
        get canSubmit() {
            const hasMonthlyQuota = this.paymentType === 'tunai' || (this.santriData?.subscription && this.quotaRemainingAfter >= 0);
            return this.santriData && this.weightKg > 0 && this.totalClothes > 0 && hasMonthlyQuota;
        },
        async searchSantri() {
            this.searchLoading = true;
            this.searchOpen = true;
            const params = new URLSearchParams({ query: this.searchQuery, payment_type: this.paymentType, date: this.laundryDate });
            try {
                const response = await fetch(`${@js(route('petugas.laundry.search'))}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
                const data = await response.json();
                this.searchResults = data.data || [];
            } finally {
                this.searchLoading = false;
            }
        },
        async selectSantri(item) {
            this.rfidInput = item.rfid_code || '';
            this.searchQuery = item.nama;
            this.searchOpen = false;
            await this.scanSantri();
        },
        async refreshSelectedSantri() {
            if (this.santriData?.rfid_code) {
                this.rfidInput = this.santriData.rfid_code;
                await this.scanSantri();
            }
        },
        async scanSantri() {
            if (!this.rfidInput.trim() && !this.santriData) return;
            this.loading = true;
            this.errorMessage = '';
            try {
                const response = await fetch(@js(route('petugas.laundry.scan')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ rfid_code: this.rfidInput || this.santriData?.rfid_code, date: this.laundryDate })
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    this.errorMessage = data.message || 'Santri tidak ditemukan.';
                    return;
                }
                this.santriData = data.data;
                this.searchQuery = data.data.nama;
                this.rfidInput = '';
            } catch (error) {
                this.errorMessage = 'Gagal memverifikasi santri.';
            } finally {
                this.loading = false;
            }
        },
        handleSubmit() {
            if (this.paymentType === 'tunai' && this.paymentMethod === 'saldo_tabungan') {
                this.pin = [];
                this.pinModalOpen = true;
                return;
            }
            this.$refs.form.submit();
        },
        submitWithPin() {
            if (this.pin.length === 6) this.$refs.form.submit();
        },
        incrementCloth(key) {
            this.clothes[key] = Number(this.clothes[key] || 0) + 1;
        },
        decrementCloth(key) {
            this.clothes[key] = Math.max(0, Number(this.clothes[key] || 0) - 1);
        },
        addPin(num) {
            if (this.pin.length < 6) this.pin.push(num.toString());
        },
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        },
        formatKg(num) {
            return `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(num || 0)} Kg`;
        }
    };
}
</script>

<style>
[x-cloak] { display: none !important; }
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
