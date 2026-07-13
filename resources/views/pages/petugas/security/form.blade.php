@extends('layouts.app')

@section('title', $violation ? 'Edit Pelanggaran' : 'Input Pelanggaran')
@section('header-title', $violation ? 'Edit Pelanggaran' : 'Input Pelanggaran')

@section('content')
<div class="mx-auto max-w-3xl" x-data="securitySantriSearch()" x-init="init()">
    <div class="mb-6">
        <p class="text-sm font-bold text-primary">Keamanan Santri</p>
        <h1 class="font-headline text-2xl font-bold">{{ $violation ? 'Edit Pelanggaran Santri' : 'Input Pelanggaran Santri' }}</h1>
        <p class="text-sm text-on-surface-variant">Pengurangan poin akan memengaruhi total poin prestasi santri.</p>
    </div>

    <form method="POST" action="{{ $violation ? route('petugas.security.update', $violation) : route('petugas.security.store') }}" class="space-y-5 rounded-xl bg-surface-container-lowest p-4 sm:p-5 shadow-sm">
        @csrf
        @if($violation) @method('PUT') @endif

        <div>
            <label class="block text-sm font-bold">Santri</label>
            <div class="relative mt-2" @click.outside="dropdownOpen = false">
                <input type="hidden" name="santri_id" :value="selectedSantriId">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">search</span>
                <input type="text"
                       x-model="search"
                       @focus="dropdownOpen = true"
                       @input="selectedSantriId = ''; dropdownOpen = true"
                       @keydown.escape.prevent="dropdownOpen = false"
                       :required="!selectedSantriId"
                       placeholder="Cari nama, NIS, atau kamar santri..."
                       class="input-field w-full pl-12 pr-12"
                       autocomplete="off">
                <button type="button" @click="dropdownOpen = !dropdownOpen" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-on-surface-variant hover:text-primary">
                    <span class="material-symbols-outlined transition-transform" :class="{ 'rotate-180': dropdownOpen }">expand_more</span>
                </button>
                <div x-show="dropdownOpen" x-cloak x-transition class="absolute z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl bg-surface-container-lowest shadow-xl ring-1 ring-outline-variant/30">
                    <div class="max-h-72 overflow-y-auto py-2">
                        <template x-for="santri in filteredSantri" :key="santri.id">
                            <button type="button" @click="selectSantri(santri)" class="w-full px-4 py-3 text-left hover:bg-primary/10 focus:bg-primary/10 focus:outline-none">
                                <span class="block font-bold text-on-surface" x-text="santri.name"></span>
                                <span class="block text-xs text-on-surface-variant" x-text="santri.meta"></span>
                            </button>
                        </template>
                        <div x-show="filteredSantri.length === 0" class="px-4 py-5 text-sm text-on-surface-variant">Santri tidak ditemukan.</div>
                    </div>
                </div>
            </div>
            @error('santri_id') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <label class="block text-sm font-bold">Jenis Pelanggaran
            <input name="jenis_pelanggaran" required value="{{ old('jenis_pelanggaran', $violation?->jenis_pelanggaran) }}" class="input-field mt-2" placeholder="Contoh: Terlambat kembali ke pondok">
        </label>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-bold">Waktu
                <input type="datetime-local" name="waktu" required value="{{ old('waktu', $violation?->waktu?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" class="input-field mt-2">
            </label>
            <label class="text-sm font-bold">Pengurangan Point
                <input type="number" name="pengurangan_point" required min="1" max="10" value="{{ old('pengurangan_point', $violation?->pengurangan_point ?? 1) }}" class="input-field mt-2">
            </label>
        </div>

        <label class="block text-sm font-bold">Keterangan
            <textarea name="keterangan" rows="4" class="input-field mt-2" placeholder="Tuliskan kronologi atau catatan tambahan">{{ old('keterangan', $violation?->keterangan) }}</textarea>
        </label>

        @if($errors->any())
            <div class="rounded-xl bg-error-container p-4 text-sm text-on-error-container">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <a href="{{ route('petugas.security.index') }}" class="btn-secondary">Batal</a>
            <button class="btn-primary"><span class="material-symbols-outlined">save</span> {{ $violation ? 'Simpan Perubahan' : 'Simpan Pelanggaran' }}</button>
        </div>
    </form>
</div>

<script>
function securitySantriSearch() {
    return {
        santriList: @js($santriList->map(fn ($santri) => [
            'id' => $santri->id,
            'name' => $santri->name,
            'nis' => $santri->nis,
            'kamar' => $santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $santri->kamarSantri->kamar)) : 'Tanpa kamar',
            'meta' => ($santri->nis ?? '-') . ' - ' . ($santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $santri->kamarSantri->kamar)) : 'Tanpa kamar'),
        ])->values()),
        selectedSantriId: @js((string) old('santri_id', $violation?->santri_id ?? '')),
        search: '',
        dropdownOpen: false,

        init() {
            this.syncSelectedLabel();
        },

        get filteredSantri() {
            const term = this.normalize(this.search);
            if (!term) return this.santriList.slice(0, 30);

            return this.santriList
                .filter((santri) => this.normalize(`${santri.name} ${santri.nis || ''} ${santri.kamar || ''}`).includes(term))
                .slice(0, 30);
        },

        normalize(value) {
            return String(value || '').toLowerCase().trim();
        },

        label(santri) {
            return santri ? `${santri.name} - ${santri.meta}` : '';
        },

        syncSelectedLabel() {
            const selected = this.santriList.find((santri) => String(santri.id) === String(this.selectedSantriId));
            this.search = this.label(selected);
        },

        selectSantri(santri) {
            this.selectedSantriId = String(santri.id);
            this.search = this.label(santri);
            this.dropdownOpen = false;
        },
    }
}
</script>
@endsection
