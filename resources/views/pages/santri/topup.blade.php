@extends('layouts.santri')

@section('title', 'Top Up Saldo')

@section('content')
<div x-data="topUpSantri()" class="pb-20">
    <!-- Header -->
    <header class="sanctuary-gradient text-white pt-12 pb-20 px-6 rounded-b-3xl shadow-xl shadow-primary/20">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('santri.home') }}" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-white">arrow_back</span>
                </a>
                <h1 class="font-headline font-bold text-xl">Top Up Saldo</h1>
            </div>
        </div>

        <section x-data="copyRekening()" class="space-y-3">
    <h2 class="font-headline font-bold text-primary tracking-tight px-1">
        Tujuan Transfer
    </h2>

    <div class="bg-surface-container-lowest rounded-3xl p-6 shadow-[0_4px_24px_-8px_rgba(0,77,76,0.08)] relative overflow-hidden">

        <!-- Decorative -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16"></div>

        <!-- Header Bank -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-surface-container-low rounded-xl flex items-center justify-center">
                <img class="w-8 h-auto object-contain"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuBMMvjWQUqgCzDFdLv5G5WxpPN-ykKw1RSWZ8NwcJs8RWnr2aOLdfTfOF2C_zAdaMuOAuqIoZLK6vndDBob7Uj_PVRMSPjVUQBwmBWMRSYCZN3ktBAV5cDO_gpJjRn7E8oM__aGYFPxw1th7QI7ijoMCKJwgmnPV74BK9roYlnn0E8V73dRP9m69GDjPSE5Z80Xva-m1ZWQz7Q1758Jihz221CgJHH9vzTdPNqNv1wfNSh7J9kNEzjxuXaigpb6Ysqkw9IdoocQgoTo"
                    alt="BSI Logo">
            </div>

            <div>
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">
                    Bank Penerima
                </p>
                <p class="font-headline font-extrabold text-on-surface">
                    Bank Syariah Indonesia (BSI)
                </p>
            </div>
        </div>

        <!-- Info -->
        <div class="space-y-4">
            <div>
                <p class="text-[10px] uppercase tracking-widest font-bold text-outline">
                    Nama Rekening
                </p>
                <p class="font-headline font-bold text-lg text-on-surface">
                    Bendahara Mambaul Hikmah
                </p>
            </div>

            <!-- Rekening -->
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-widest font-bold text-outline">
                        Nomor Rekening
                    </p>
                    <p class="font-headline font-extrabold text-2xl tracking-wider text-primary"
                       x-text="rekening">
                    </p>
                </div>

                <!-- Button Copy -->
                <button @click="copy()"
                    class="flex items-center gap-2 bg-primary-fixed-dim/30 px-4 py-2 rounded-xl text-primary font-bold text-sm hover:bg-primary-fixed-dim transition-all active:scale-95">

                    <span class="material-symbols-outlined text-sm">
                        content_copy
                    </span>

                    <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                </button>
            </div>
        </div>

        <!-- Toast Notification -->
        <div x-show="showToast"
             x-transition
             class="absolute bottom-4 right-4 bg-primary text-white text-sm px-4 py-2 rounded-xl shadow-lg">
            Nomor berhasil disalin
        </div>

    </div>
</section>

    </header>

    <!-- Top Up Content -->
    <div class="px-6 -mt-8 space-y-4">
        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-primary-container/10 border border-primary/20 text-on-surface p-4 rounded-xl flex items-start gap-3">
                <span class="material-symbols-outlined text-primary">check_circle</span>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Top Up Form -->
        <div class="card">
            <h3 class="font-headline font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add_circle</span>
                <span>Form Top Up</span>
            </h3>

            <form action="{{ route('santri.topup.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Nominal Input -->
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nominal Top Up <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm font-semibold">Rp</span>
                        <input type="number"
                               name="nominal"
                               required
                               min="1000"
                               max="10000000"
                               x-model="nominal"
                               @input="formatNominal"
                               class="input-field w-full pl-12 text-lg font-bold"
                               placeholder="0">
                    </div>
                    <p class="text-xs text-on-surface-variant mt-1">Minimal: Rp 1.000 | Maksimal: Rp 10.000.000</p>
                </div>

                <!-- Quick Nominal Buttons -->
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="setNominal(10000)" class="py-2 px-3 bg-surface-container-low text-on-surface text-xs font-bold rounded-lg hover:bg-primary/10 hover:text-primary transition-colors">
                        Rp 10.000
                    </button>
                    <button type="button" @click="setNominal(20000)" class="py-2 px-3 bg-surface-container-low text-on-surface text-xs font-bold rounded-lg hover:bg-primary/10 hover:text-primary transition-colors">
                        Rp 20.000
                    </button>
                    <button type="button" @click="setNominal(50000)" class="py-2 px-3 bg-surface-container-low text-on-surface text-xs font-bold rounded-lg hover:bg-primary/10 hover:text-primary transition-colors">
                        Rp 50.000
                    </button>
                    <button type="button" @click="setNominal(100000)" class="py-2 px-3 bg-surface-container-low text-on-surface text-xs font-bold rounded-lg hover:bg-primary/10 hover:text-primary transition-colors">
                        Rp 100.000
                    </button>
                    <button type="button" @click="setNominal(200000)" class="py-2 px-3 bg-surface-container-low text-on-surface text-xs font-bold rounded-lg hover:bg-primary/10 hover:text-primary transition-colors">
                        Rp 200.000
                    </button>
                    <button type="button" @click="setNominal(500000)" class="py-2 px-3 bg-surface-container-low text-on-surface text-xs font-bold rounded-lg hover:bg-primary/10 hover:text-primary transition-colors">
                        Rp 500.000
                    </button>
                </div>

                <!-- Payment Proof Upload -->
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Upload Bukti Pembayaran <span class="text-error">*</span></label>
                    <div class="border-2 border-dashed border-outline-variant/30 rounded-xl p-6 text-center hover:border-primary/50 transition-colors cursor-pointer"
                         @click="$refs.buktiInput.click()">
                        <template x-if="previewImage">
                            <div class="space-y-3">
                                <img :src="previewImage" class="max-h-48 mx-auto rounded-lg object-contain">
                                <p class="text-xs text-on-surface-variant">Klik untuk mengubah gambar</p>
                            </div>
                        </template>
                        <template x-if="!previewImage">
                            <div class="space-y-3">
                                <span class="material-symbols-outlined text-4xl text-outline">cloud_upload</span>
                                <div>
                                    <p class="text-sm font-medium text-on-surface">Klik untuk upload</p>
                                    <p class="text-xs text-on-surface-variant">PNG, JPG (Max: 2MB)</p>
                                </div>
                            </div>
                        </template>
                        <input type="file"
                               name="bukti_pembayaran"
                               x-ref="buktiInput"
                               accept="image/*"
                               @change="handleFileUpload"
                               class="hidden"
                               required>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-primary text-on-primary font-bold py-4 rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span>Ajukan Top Up</span>
                </button>
            </form>
        </div>

        <!-- Recent Top Up History -->
        <div class="card">
            <h3 class="font-headline font-bold text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">history</span>
                <span>Riwayat Top Up</span>
            </h3>

            <div class="space-y-3">
                @forelse($recentTopUps ?? [] as $topUp)
                    <div class="bg-surface-container-lowest p-4 rounded-xl flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 {{ $topUp->status === 'approved' ? 'bg-primary/10 text-primary' : ($topUp->status === 'rejected' ? 'bg-error/10 text-error' : 'bg-warning/10 text-warning') }} rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined">{{ $topUp->status === 'approved' ? 'check_circle' : ($topUp->status === 'rejected' ? 'cancel' : 'pending') }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-on-surface">Rp {{ number_format($topUp->nominal, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-on-surface-variant">{{ $topUp->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold px-3 py-1 rounded-full {{ $topUp->status === 'approved' ? 'bg-primary/10 text-primary' : ($topUp->status === 'rejected' ? 'bg-error/10 text-error' : 'bg-warning/10 text-warning') }}">
                                {{ $topUp->status_text }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">add_circle</span>
                        <p class="text-sm text-on-surface-variant">Belum ada riwayat top up</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 py-4 bg-surface/80 backdrop-blur-xl shadow-[0_-4px_24px_-4px_rgba(25,28,29,0.06)] rounded-t-[1.5rem]">
        <a href="{{ route('santri.home') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">home</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Beranda</span>
        </a>
        <a href="{{ route('santri.riwayat') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">history</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Riwayat</span>
        </a>
        <a href="{{ route('santri.prestasi') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">military_tech</span>
            <span class="font-manrope text-[10px] font-semibold uppercase tracking-widest mt-1">Achievements</span>
        </a>
        <a href="{{ route('santri.profile') }}" class="flex flex-col items-center justify-center text-on-surface opacity-60 hover:bg-surface-container-low transition-all p-2 rounded-xl">
            <span class="material-symbols-outlined text-lg">person</span>
            <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Profil</span>
        </a>
    </nav>
</div>

<script>

function copyRekening() {
    return {
        rekening: "712 888 2026",
        copied: false,
        showToast: false,

        copy() {
            navigator.clipboard.writeText(this.rekening);

            this.copied = true;
            this.showToast = true;

            setTimeout(() => {
                this.copied = false;
                this.showToast = false;
            }, 2000);
        }
    }
}
function topUpSantri() {
    return {
        nominal: '',
        previewImage: null,

        setNominal(amount) {
            this.nominal = amount;
        },

        formatNominal() {
            this.nominal = this.nominal.replace(/[^0-9]/g, '');
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewImage = e.target.result;
                };
                reader.readAsDataURL(file);
            }
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
