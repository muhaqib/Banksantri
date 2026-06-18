@extends('layouts.app')

@section('header-title', 'Laundry')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div x-data="laundryForm()" x-init="init()" class="space-y-6">
    <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="font-headline text-3xl font-extrabold tracking-tight text-primary">Transaksi Laundry</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Pembayaran laundry tunai dan bulanan terpisah dari saldo santri.</p>
        </div>
        <div class="flex w-fit rounded-xl bg-surface-container-high p-1">
            <button type="button" @click="paymentType = 'tunai'" :class="paymentType === 'tunai' ? 'bg-primary text-on-primary' : 'text-on-surface-variant'" class="rounded-lg px-6 py-2 text-sm font-bold transition">Tunai</button>
            <button type="button" @click="paymentType = 'bulanan'" :class="paymentType === 'bulanan' ? 'bg-primary text-on-primary' : 'text-on-surface-variant'" class="rounded-lg px-6 py-2 text-sm font-bold transition">Bulanan</button>
        </div>
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
        <section class="xl:col-span-4 space-y-6">
            <div class="rounded-xl border-l-4 border-primary bg-surface-container-lowest p-6 shadow-sm">
                <h2 class="font-headline text-sm font-bold uppercase tracking-widest text-primary">Identifikasi Santri</h2>
                <form @submit.prevent="scanSantri" class="mt-5 flex gap-2">
                    <input x-model="rfidInput" autofocus placeholder="Tap RFID atau masukkan kode" class="input-field flex-1">
                    <button :disabled="loading || !rfidInput" class="rounded-xl bg-primary px-5 font-bold text-on-primary disabled:opacity-50">
                        <span class="material-symbols-outlined" :class="loading && 'animate-spin'" x-text="loading ? 'progress_activity' : 'search'"></span>
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

                <div class="mt-6 rounded-xl bg-surface-container-low p-4 text-left">
                    <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Status Laundry Bulanan</p>
                    <template x-if="santriData?.subscription">
                        <div class="mt-3 space-y-2">
                            <div class="flex justify-between text-sm"><span>Kuota</span><strong x-text="formatKg(santriData.subscription.quota_kg)"></strong></div>
                            <div class="flex justify-between text-sm"><span>Terpakai</span><strong x-text="formatKg(santriData.subscription.used_kg)"></strong></div>
                            <div class="flex justify-between text-sm text-primary"><span>Sisa</span><strong x-text="formatKg(santriData.subscription.remaining_kg)"></strong></div>
                        </div>
                    </template>
                    <p x-show="!santriData?.subscription" class="mt-3 text-sm font-semibold text-on-surface-variant">Belum terdaftar bulan ini.</p>
                </div>
            </div>
        </section>

        <section class="xl:col-span-8">
            <form action="{{ route('petugas.laundry.store') }}" method="POST" class="rounded-xl bg-surface-container-lowest p-6 shadow-sm" @submit="syncHiddenInputs">
                @csrf
                <input type="hidden" name="santri_id" :value="santriData?.id">
                <input type="hidden" name="payment_type" :value="paymentType">
                <input type="hidden" name="total_price" :value="totalPrice">
                <input type="hidden" name="pin" :value="pin.join('')">

                <div x-show="!santriData" class="py-20 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined mb-3 text-5xl text-outline">local_laundry_service</span>
                    <p class="font-semibold">Verifikasi santri terlebih dahulu untuk memulai laundry.</p>
                </div>

                <div x-show="santriData" x-cloak class="space-y-7">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Hari dan Tanggal
                            <input type="date" name="laundry_date" x-model="laundryDate" @change="scanSantri" required class="input-field mt-2">
                        </label>
                        <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Harga per Kg
                            <input type="number" name="price_per_kg" x-model.number="pricePerKg" :readonly="paymentType === 'bulanan'" required min="0" class="input-field mt-2">
                        </label>
                        <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Berat Laundry
                            <input type="number" name="weight_kg" x-model.number="weightKg" required min="0.1" step="0.1" class="input-field mt-2">
                        </label>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="font-headline text-sm font-bold uppercase tracking-widest text-primary">Rincian Baju</h2>
                            <p class="text-xs font-bold text-on-surface-variant">Total: <span x-text="totalClothes"></span> pcs</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            @foreach($clothes as $key => $item)
                                <div class="rounded-xl border border-outline-variant/20 bg-surface-container-low p-3 text-center">
                                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-primary/5 text-primary">
                                        <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                                    </div>
                                    <p class="mt-2 text-xs font-bold">{{ $item['label'] }}</p>
                                    <div class="mt-3 flex items-center justify-center gap-3">
                                        <button type="button" @click="decrementCloth('{{ $key }}')" class="flex h-7 w-7 items-center justify-center rounded-full bg-surface-container-high">
                                            <span class="material-symbols-outlined text-xs">remove</span>
                                        </button>
                                        <span class="w-6 text-center text-sm font-bold" x-text="clothes.{{ $key }}"></span>
                                        <button type="button" @click="incrementCloth('{{ $key }}')" class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-on-primary">
                                            <span class="material-symbols-outlined text-xs">add</span>
                                        </button>
                                    </div>
                                    <input type="hidden" name="clothes[{{ $key }}]" :value="clothes.{{ $key }}">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-primary/5 p-5 text-center">
                            <p class="text-xs font-bold uppercase tracking-widest text-primary">Total Harga</p>
                            <p class="mt-2 font-headline text-3xl font-extrabold text-primary">Rp <span x-text="formatNumber(totalPrice)"></span></p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-5 text-center">
                            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Total Baju</p>
                            <p class="mt-2 font-headline text-3xl font-extrabold text-on-surface"><span x-text="totalClothes"></span> pcs</p>
                        </div>
                        <div class="rounded-2xl bg-surface-container-low p-5 text-center">
                            <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Sisa Kuota</p>
                            <p class="mt-2 font-headline text-3xl font-extrabold" :class="quotaRemainingAfter < 0 ? 'text-error' : 'text-on-surface'" x-text="paymentType === 'bulanan' ? formatKg(quotaRemainingAfter) : '-'"></p>
                        </div>
                    </div>

                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Catatan
                        <textarea name="notes" rows="2" class="input-field mt-2"></textarea>
                    </label>

                    <div class="rounded-xl bg-surface-container-low p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-headline text-sm font-bold uppercase tracking-widest text-primary">Verifikasi PIN Santri</h3>
                            <button type="button" @click="showPin = !showPin" class="text-xs font-bold text-primary" x-text="showPin ? 'Sembunyikan' : 'Lihat PIN'"></button>
                        </div>
                        <div class="mb-4 flex justify-center gap-2 rounded-xl bg-white p-3">
                            <template x-for="i in 6" :key="i">
                                <div class="flex h-11 w-9 items-center justify-center rounded-lg border-2 bg-surface-container-high" :class="pin[i-1] ? 'border-primary' : 'border-surface-container-highest'">
                                    <span class="font-headline text-xl font-bold" :class="showPin && pin[i-1] ? 'text-primary' : 'text-transparent'" x-text="pin[i-1] || '0'"></span>
                                </div>
                            </template>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="num in 9" :key="num">
                                <button type="button" @click="addPin(num)" class="h-12 rounded-xl bg-white font-headline text-lg font-bold"> <span x-text="num"></span> </button>
                            </template>
                            <button type="button" class="h-12 rounded-xl bg-surface-container-low"></button>
                            <button type="button" @click="addPin(0)" class="h-12 rounded-xl bg-white font-headline text-lg font-bold">0</button>
                            <button type="button" @click="pin.pop()" @dblclick="pin = []" class="flex h-12 items-center justify-center rounded-xl bg-error/10 text-error">
                                <span class="material-symbols-outlined">backspace</span>
                            </button>
                        </div>
                    </div>

                    <button :disabled="!canSubmit" class="w-full rounded-xl bg-primary py-4 font-headline font-bold text-on-primary shadow-lg shadow-primary/20 disabled:cursor-not-allowed disabled:opacity-50">
                        Simpan Transaksi Laundry
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
function laundryForm() {
    return {
        paymentType: 'tunai',
        rfidInput: '',
        loading: false,
        errorMessage: '',
        santriData: null,
        laundryDate: @js($today->toDateString()),
        pricePerKg: {{ $defaultPricePerKg }},
        weightKg: 0,
        pin: [],
        showPin: false,
        clothes: {
            @foreach($clothes as $key => $item)
                {{ $key }}: 0,
            @endforeach
        },
        init() {},
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
        get canSubmit() {
            const hasMonthlyQuota = this.paymentType === 'tunai' || (this.santriData?.subscription && this.quotaRemainingAfter >= 0);
            return this.santriData && this.pin.length === 6 && this.weightKg > 0 && this.totalClothes > 0 && hasMonthlyQuota;
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
                this.rfidInput = '';
            } catch (error) {
                this.errorMessage = 'Gagal memverifikasi santri.';
            } finally {
                this.loading = false;
            }
        },
        incrementCloth(key) {
            this.clothes[key]++;
        },
        decrementCloth(key) {
            this.clothes[key] = Math.max(0, this.clothes[key] - 1);
        },
        addPin(num) {
            if (this.pin.length < 6) this.pin.push(num.toString());
        },
        syncHiddenInputs() {},
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
