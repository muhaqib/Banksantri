@extends('layouts.app')

@section('title', $permission ? 'Edit Perizinan' : 'Buat Perizinan')
@section('header-title', $permission ? 'Edit Perizinan' : 'Buat Perizinan')

@section('content')
<div class="mx-auto max-w-2xl" x-data="permissionSantriSearch()" x-init="init()">
    <div class="mb-5">
        <p class="text-xs font-semibold text-primary uppercase tracking-wider">Tanpa proses persetujuan</p>
        <h1 class="font-headline text-2xl font-bold text-primary mt-0.5">{{ $permission ? 'Edit Perizinan Santri' : 'Buat Perizinan Santri' }}</h1>
        <p class="text-xs text-on-surface-variant mt-0.5">Setelah disimpan, status absensi santri otomatis dianggap izin selama periode berikut.</p>
    </div>
    <form method="POST" action="{{ $permission ? route($routePrefix.'.permissions.update', $permission) : route($routePrefix.'.permissions.store') }}" class="space-y-4 rounded-xl bg-surface-container-lowest p-4 sm:p-5 border border-outline-variant/10 shadow-sm">
        @csrf
        @if($permission) @method('PUT') @endif
        <div>
            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Santri</label>
            <div class="relative" @click.outside="dropdownOpen = false">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                <input type="hidden" name="santri_id" :value="selectedSantriId">
                <input type="text"
                       x-model="search"
                       @focus="dropdownOpen = true"
                       @input="selectedSantriId = ''; dropdownOpen = true"
                       @keydown.escape.prevent="dropdownOpen = false"
                       :required="!selectedSantriId"
                       placeholder="Cari nama, NIS, atau kamar santri..."
                       class="input-field w-full pl-9 pr-9"
                       autocomplete="off">
                <button type="button" @click="dropdownOpen = !dropdownOpen" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-on-surface-variant hover:text-primary">
                    <span class="material-symbols-outlined transition-transform text-lg" :class="{ 'rotate-180': dropdownOpen }">expand_more</span>
                </button>
                <div x-show="dropdownOpen" x-cloak x-transition class="absolute left-0 right-0 z-30 mt-1 max-h-60 overflow-hidden rounded-lg bg-surface-container-lowest border border-outline-variant/20 shadow-lg">
                    <div class="max-h-60 overflow-y-auto py-1">
                        <template x-for="santri in filteredSantri" :key="santri.id">
                            <button type="button" @click="selectSantri(santri)" class="w-full px-3 py-2 text-left hover:bg-surface-container-low transition-colors focus:bg-surface-container-low focus:outline-none">
                                <span class="block text-xs font-bold text-on-surface" x-text="santri.name"></span>
                                <span class="block text-[10px] text-on-surface-variant" x-text="santri.meta"></span>
                            </button>
                        </template>
                        <div x-show="filteredSantri.length === 0" class="px-3 py-3 text-xs text-on-surface-variant">Santri tidak ditemukan.</div>
                    </div>
                </div>
            </div>
            @error('santri_id') <p class="text-error text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block mb-1.5">Tanggal Izin</label>
                <div class="grid grid-cols-3 gap-2">
                    <input type="date" name="start_date" required value="{{ old('start_date', $permission?->start_date?->toDateString() ?? today()->toDateString()) }}" class="input-field col-span-2">
                    <input type="time" name="start_time" value="{{ old('start_time', $permission?->start_date?->format('H:i') ?? '') }}" class="input-field">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider block mb-1.5">Batas Akhir Izin</label>
                <div class="grid grid-cols-3 gap-2">
                    <input type="date" name="end_date" required value="{{ old('end_date', $permission?->end_date?->toDateString() ?? today()->toDateString()) }}" class="input-field col-span-2">
                    <input type="time" name="end_time" value="{{ old('end_time', $permission?->end_date?->format('H:i') ?? '') }}" class="input-field">
                </div>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Alasan</label>
            <textarea name="reason" required rows="3" class="input-field w-full" placeholder="Tuliskan alasan izin secara lengkap">{{ old('reason', $permission?->reason) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Yang Mengizinkan</label>
            <select name="approved_by" required class="input-field">
                <option value="">Pilih yang mengizinkan</option>
                @foreach($approvers as $approver)
                    <option value="{{ $approver }}" @selected(old('approved_by', $permission?->approved_by) === $approver)>{{ $approver }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Catatan Tambahan</label>
            <textarea name="notes" rows="2" class="input-field w-full">{{ old('notes', $permission?->notes) }}</textarea>
        </div>
        @if($errors->any())
            <div class="rounded-lg bg-error-container p-3.5 text-xs text-on-error-container border border-error/20">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <div class="flex justify-end gap-2 border-t border-outline-variant/10 pt-4 mt-2">
            <a href="{{ route($routePrefix.'.permissions.index') }}" class="btn-secondary py-2 px-4 h-[38px] flex items-center justify-center">Batal</a>
            <button class="btn-primary py-2 px-4 h-[38px] flex items-center justify-center"><span class="material-symbols-outlined text-sm">save</span> {{ $permission ? 'Simpan Perubahan' : 'Simpan & Cetak' }}</button>
        </div>
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
