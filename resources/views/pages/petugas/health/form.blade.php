@extends('layouts.app')

@section('title', $record ? 'Edit Kesehatan Santri' : 'Tambah Kesehatan Santri')
@section('header-title', $record ? 'Edit Kesehatan Santri' : 'Tambah Kesehatan Santri')

@section('content')
<div class="mx-auto max-w-4xl" x-data="healthSantriSearch()" x-init="init()">
    <div class="mb-6">
        <p class="text-sm font-bold text-primary">Rekam Medis</p>
        <h1 class="font-headline text-3xl font-black">{{ $record ? 'Edit Data Kesehatan' : 'Tambah Data Kesehatan' }}</h1>
        <p class="text-sm text-on-surface-variant">Catat pemeriksaan terakhir, status, lokasi, dan tindakan kesehatan santri.</p>
    </div>

    <form method="POST" action="{{ $record ? route('petugas.health.update', $record) : route('petugas.health.store') }}" class="space-y-5 rounded-2xl bg-surface-container-lowest p-6 shadow-sm">
        @csrf
        @if($record) @method('PUT') @endif

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

        <div class="grid gap-4 md:grid-cols-3">
            <label class="text-sm font-bold">Tanggal Pemeriksaan
                <input type="date" name="checkup_date" required value="{{ old('checkup_date', $record?->checkup_date?->toDateString() ?? today()->toDateString()) }}" class="input-field mt-2">
            </label>
            <label class="text-sm font-bold">Status
                <select name="status" required class="input-field mt-2">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $record?->status ?? 'sehat') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-bold">Lokasi
                <input name="location" value="{{ old('location', $record?->location ?? 'Klinik Pusat Santri') }}" class="input-field mt-2" placeholder="Klinik Pusat Santri">
            </label>
        </div>

        <label class="block text-sm font-bold">Judul Pemeriksaan
            <input name="title" required value="{{ old('title', $record?->title) }}" class="input-field mt-2" placeholder="Contoh: Pemeriksaan Rutin">
        </label>

        <div class="grid gap-4 md:grid-cols-4">
            <label class="text-sm font-bold">Berat (kg)
                <input type="number" name="weight_kg" min="1" max="300" step="0.1" value="{{ old('weight_kg', $record?->weight_kg) }}" class="input-field mt-2">
            </label>
            <label class="text-sm font-bold">Tinggi (cm)
                <input type="number" name="height_cm" min="30" max="250" step="0.1" value="{{ old('height_cm', $record?->height_cm) }}" class="input-field mt-2">
            </label>
            <label class="text-sm font-bold">Tekanan
                <input name="blood_pressure" value="{{ old('blood_pressure', $record?->blood_pressure) }}" class="input-field mt-2" placeholder="120/80">
            </label>
            <label class="text-sm font-bold">Suhu (°C)
                <input type="number" name="temperature_c" min="30" max="45" step="0.1" value="{{ old('temperature_c', $record?->temperature_c) }}" class="input-field mt-2">
            </label>
        </div>

        <label class="block text-sm font-bold">Keluhan
            <textarea name="complaint" rows="3" class="input-field mt-2" placeholder="Keluhan atau hasil pemeriksaan">{{ old('complaint', $record?->complaint) }}</textarea>
        </label>
        <label class="block text-sm font-bold">Tindakan / Obat
            <textarea name="treatment" rows="3" class="input-field mt-2" placeholder="Tindakan, anjuran istirahat, atau obat">{{ old('treatment', $record?->treatment) }}</textarea>
        </label>
        <label class="block text-sm font-bold">Catatan Tambahan
            <textarea name="notes" rows="3" class="input-field mt-2">{{ old('notes', $record?->notes) }}</textarea>
        </label>

        @if($errors->any())
            <div class="rounded-xl bg-error-container p-4 text-sm text-on-error-container">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <a href="{{ route('petugas.health.index') }}" class="btn-secondary">Batal</a>
            <button class="btn-primary"><span class="material-symbols-outlined">save</span> {{ $record ? 'Simpan Perubahan' : 'Simpan Data' }}</button>
        </div>
    </form>
</div>

<script>
function healthSantriSearch() {
    return {
        santriList: @js($santriList->map(fn ($santri) => [
            'id' => $santri->id,
            'name' => $santri->name,
            'nis' => $santri->nis,
            'kamar' => $santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $santri->kamarSantri->kamar)) : 'Tanpa kamar',
            'meta' => ($santri->nis ?? '-') . ' - ' . ($santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $santri->kamarSantri->kamar)) : 'Tanpa kamar'),
        ])->values()),
        selectedSantriId: @js((string) old('santri_id', $record?->santri_id ?? '')),
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
