@extends('layouts.app')

@section('title', $permission ? 'Edit Perizinan' : 'Buat Perizinan')
@section('header-title', $permission ? 'Edit Perizinan' : 'Buat Perizinan')

@section('content')
<div class="mx-auto max-w-3xl" x-data="permissionSantriSearch()" x-init="init()">
    <div class="mb-6"><p class="text-sm font-bold text-primary">Tanpa proses persetujuan</p><h1 class="font-headline text-3xl font-black">{{ $permission ? 'Edit Perizinan Santri' : 'Buat Perizinan Santri' }}</h1><p class="text-sm text-on-surface-variant">Setelah disimpan, status absensi santri otomatis dianggap izin selama periode berikut.</p></div>
    <form method="POST" action="{{ $permission ? route($routePrefix.'.permissions.update', $permission) : route($routePrefix.'.permissions.store') }}" class="space-y-5 rounded-2xl bg-surface-container-lowest p-6 shadow-sm">
        @csrf
        @if($permission) @method('PUT') @endif
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
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-bold block mb-2">Tanggal Izin</label>
                <div class="grid grid-cols-3 gap-2">
                    <input type="date" name="start_date" required value="{{ old('start_date', $permission?->start_date?->toDateString() ?? today()->toDateString()) }}" class="input-field col-span-2">
                    <input type="time" name="start_time" value="{{ old('start_time', $permission?->start_date?->format('H:i') ?? '') }}" class="input-field">
                </div>
            </div>
            <div>
                <label class="text-sm font-bold block mb-2">Batas Akhir Izin</label>
                <div class="grid grid-cols-3 gap-2">
                    <input type="date" name="end_date" required value="{{ old('end_date', $permission?->end_date?->toDateString() ?? today()->toDateString()) }}" class="input-field col-span-2">
                    <input type="time" name="end_time" value="{{ old('end_time', $permission?->end_date?->format('H:i') ?? '') }}" class="input-field">
                </div>
            </div>
        </div>
        <label class="block text-sm font-bold">Alasan<textarea name="reason" required rows="4" class="input-field mt-2" placeholder="Tuliskan alasan izin secara lengkap">{{ old('reason', $permission?->reason) }}</textarea></label>
        <label class="block text-sm font-bold">Yang Mengizinkan
            <select name="approved_by" required class="input-field mt-2">
                <option value="">Pilih yang mengizinkan</option>
                @foreach($approvers as $approver)
                    <option value="{{ $approver }}" @selected(old('approved_by', $permission?->approved_by) === $approver)>{{ $approver }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm font-bold">Catatan Tambahan<textarea name="notes" rows="3" class="input-field mt-2">{{ old('notes', $permission?->notes) }}</textarea></label>
        @if($errors->any())<div class="rounded-xl bg-error-container p-4 text-sm text-on-error-container">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <div class="flex justify-end gap-3"><a href="{{ route($routePrefix.'.permissions.index') }}" class="btn-secondary">Batal</a><button class="btn-primary"><span class="material-symbols-outlined">save</span> {{ $permission ? 'Simpan Perubahan' : 'Simpan & Cetak Izin' }}</button></div>
    </form>
</div>

<script>
function permissionSantriSearch() {
    return {
        santriList: @js($santriList->map(fn ($santri) => [
            'id' => $santri->id,
            'name' => $santri->name,
            'nis' => $santri->nis,
            'kamar' => $santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $santri->kamarSantri->kamar)) : 'Tanpa kamar',
            'meta' => ($santri->nis ?? '-') . ' - ' . ($santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $santri->kamarSantri->kamar)) : 'Tanpa kamar'),
        ])->values()),
        selectedSantriId: @js((string) old('santri_id', $permission?->santri_id ?? '')),
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
