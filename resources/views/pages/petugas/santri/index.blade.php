@extends('layouts.app')

@section('header-title', 'Data Santri')
@php
    $activeRole = 'petugas';
    $routePrefix = 'petugas.santri';
    $currentStatus = request('status', 'aktif');
@endphp

@section('content')
<div x-data="petugasSantriView()" class="space-y-5">
    <!-- Minimalist Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-surface-container-lowest p-5 sm:p-6 rounded-2xl border border-outline-variant/10 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="font-headline text-xl sm:text-2xl font-bold text-primary tracking-tight">Data Santri</h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary border border-primary/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                    Lihat Data
                </span>
            </div>
            <p class="text-on-surface-variant text-xs mt-1">Daftar informasi santri terdaftar dan detail profil.</p>
        </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-2xl shadow-sm border border-outline-variant/10">
        <form method="GET" action="{{ route($routePrefix.'.index') }}" class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
            <input type="hidden" name="status" value="{{ $currentStatus }}">

            <!-- Status Tabs -->
            <div class="flex items-center bg-surface-container-high/60 p-1 rounded-xl">
                <a href="{{ route($routePrefix.'.index', array_merge(request()->except('status'), ['status' => 'aktif'])) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $currentStatus === 'aktif' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                    Aktif
                </a>
                <a href="{{ route($routePrefix.'.index', array_merge(request()->except('status'), ['status' => 'alumni'])) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $currentStatus === 'alumni' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                    Alumni
                </a>
                <a href="{{ route($routePrefix.'.index', array_merge(request()->except('status'), ['status' => 'semua'])) }}" 
                   class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $currentStatus === 'semua' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface' }}">
                    Semua
                </a>
            </div>

            <!-- Search and Kamar Filters -->
            <div class="flex flex-wrap items-center gap-2 flex-1 lg:justify-end">
                <!-- Kamar Filter -->
                @if(!empty($kamarList))
                    <select name="kamar" onchange="this.form.submit()" 
                            class="bg-surface-container-high border-none rounded-xl px-3 py-2 text-xs font-semibold text-on-surface focus:ring-2 focus:ring-primary">
                        <option value="">-- Semua Kamar --</option>
                        @foreach($kamarList as $kamarItem)
                            <option value="{{ $kamarItem }}" {{ request('kamar') === $kamarItem ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $kamarItem)) }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <!-- Search Input -->
                <div class="relative flex-1 sm:w-64 min-w-[200px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama atau NIS..." 
                           class="w-full bg-surface-container-high border-none rounded-xl pl-9 pr-8 py-2 text-xs font-medium focus:ring-2 focus:ring-primary focus:outline-none">
                    @if(request('search'))
                        <a href="{{ route($routePrefix.'.index', ['status' => $currentStatus]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface">
                            <span class="material-symbols-outlined text-xs">close</span>
                        </a>
                    @endif
                </div>

                <button type="submit" class="bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">search</span>
                    <span>Cari</span>
                </button>

                @if(request()->hasAny(['search', 'kamar']))
                    <a href="{{ route($routePrefix.'.index', ['status' => $currentStatus]) }}" class="bg-surface-container-high text-on-surface-variant px-3 py-2 rounded-xl text-xs font-bold hover:bg-surface-container transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- MINIMALIST TABLE VIEW -->
    <div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-outline-variant/10">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest border-b border-surface-container">
                    <tr>
                        <th class="px-6 py-4 text-center">Foto</th>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">NIS</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4">Kamar</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($santriList as $santri)
                        @php
                            $fotoUrl = $santri->foto ? Storage::url($santri->foto) : null;
                        @endphp
                        <tr class="hover:bg-surface transition-colors">
                            <!-- Clickable Foto Thumbnail -->
                            <td class="px-6 py-3 text-center">
                                <button type="button" 
                                        @click="openPhotoModal('{{ $santri->name }}', '{{ $fotoUrl }}')"
                                        class="relative group/photo inline-block focus:outline-none"
                                        title="Klik untuk melihat foto besar">
                                    <div class="w-11 h-11 rounded-xl bg-primary/10 ring-2 ring-primary/20 group-hover/photo:ring-primary group-hover/photo:scale-105 transition-all flex items-center justify-center text-primary font-bold overflow-hidden shadow-sm">
                                        @if($fotoUrl)
                                            <img src="{{ $fotoUrl }}" alt="{{ $santri->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-xs font-bold">{{ strtoupper(substr($santri->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div class="absolute inset-0 rounded-xl bg-black/40 opacity-0 group-hover/photo:opacity-100 transition-opacity flex items-center justify-center text-white">
                                        <span class="material-symbols-outlined text-sm">zoom_in</span>
                                    </div>
                                </button>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="min-w-0">
                                    <div class="font-bold text-on-surface text-xs sm:text-sm truncate">{{ $santri->name }}</div>
                                    <div class="text-[11px] text-on-surface-variant truncate">{{ $santri->email }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-on-surface-variant font-mono font-medium">{{ $santri->nis ?? '-' }}</td>
                            <td class="px-6 py-3.5">
                                <span class="text-xs font-semibold text-on-surface-variant px-2.5 py-1 bg-surface-container-low rounded-lg border border-outline-variant/10">
                                    {{ $santri->kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-on-surface-variant font-medium">
                                {{ $santri->kamarSantri?->kamar ? ucfirst(str_replace('_', ' ', $santri->kamarSantri->kamar)) : '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $santri->isAlumni() ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' }}">
                                    {{ $santri->isAlumni() ? 'Alumni' : 'Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <button @click="openDetailModal({{ $santri->id }})" 
                                        class="px-3 py-1.5 bg-primary/10 text-primary hover:bg-primary hover:text-on-primary rounded-xl transition-all inline-flex items-center gap-1.5 text-xs font-bold shadow-sm" title="Lihat Detail">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                    <span>Detail</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-2">group_off</span>
                                <p class="text-sm font-semibold text-on-surface-variant">Belum ada data santri</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($santriList->hasPages())
        <div class="bg-surface-container-lowest p-4 rounded-2xl shadow-sm border border-outline-variant/10">
            {{ $santriList->links() }}
        </div>
    @endif

    <!-- LARGE PHOTO PREVIEW MODAL -->
    <div x-show="showPhotoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 z-0 bg-black/80 backdrop-blur-md transition-opacity" @click="showPhotoModal = false"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="bg-surface rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-scale-in flex flex-col" @click.stop>
                <!-- Modal Header -->
                <div class="p-4 bg-surface-container-high border-b border-surface-container flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">image</span>
                        <h4 class="font-headline font-bold text-sm text-on-surface truncate" x-text="photoTitle"></h4>
                    </div>
                    <button @click="showPhotoModal = false" class="p-1.5 text-on-surface-variant hover:text-on-surface rounded-lg hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body (Large Image) -->
                <div class="p-6 flex items-center justify-center bg-black/40 min-h-[300px]">
                    <template x-if="photoUrl">
                        <img :src="photoUrl" :alt="photoTitle" class="max-h-[70vh] w-auto max-w-full object-contain rounded-xl shadow-2xl ring-1 ring-white/10">
                    </template>
                    <template x-if="!photoUrl">
                        <div class="flex flex-col items-center justify-center text-on-surface-variant py-12">
                            <span class="material-symbols-outlined text-6xl text-outline mb-2">account_circle</span>
                            <p class="text-xs font-semibold">Tidak Ada Foto Profil</p>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-surface-container-lowest border-t border-surface-container flex justify-between items-center">
                    <span class="text-xs text-on-surface-variant font-medium" x-text="photoTitle"></span>
                    <button @click="showPhotoModal = false" class="px-5 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DETAIL MODAL -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 z-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showDetailModal = false"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="bg-surface rounded-2xl shadow-2xl max-w-2xl w-full animate-scale-in max-h-[92vh] overflow-hidden flex flex-col" @click.stop>
                <div x-show="loading" class="flex items-center justify-center py-24">
                    <span class="material-symbols-outlined text-primary text-5xl animate-spin">progress_activity</span>
                </div>

                <div x-show="!loading && selectedSantri" class="flex min-h-0 flex-col">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-primary via-primary to-primary-container text-on-primary p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-4">
                                <!-- Clickable Large Avatar Preview in Modal -->
                                <button type="button" 
                                        @click="openPhotoModal(selectedSantri?.name, selectedSantri?.foto_url)"
                                        class="group/modalPhoto relative focus:outline-none flex-shrink-0"
                                        title="Klik untuk memperbesar foto">
                                    <div class="h-20 w-20 rounded-2xl bg-white/15 ring-2 ring-white/30 group-hover/modalPhoto:ring-white flex items-center justify-center text-2xl font-bold shadow-inner overflow-hidden transition-all">
                                        <template x-if="selectedSantri && selectedSantri.foto_url">
                                            <img :src="selectedSantri.foto_url" class="h-full w-full object-cover">
                                        </template>
                                        <template x-if="selectedSantri && !selectedSantri.foto_url">
                                            <span x-text="selectedSantri && selectedSantri.name ? selectedSantri.name.substring(0, 2).toUpperCase() : ''"></span>
                                        </template>
                                    </div>
                                    <div class="absolute inset-0 rounded-2xl bg-black/40 opacity-0 group-hover/modalPhoto:opacity-100 transition-opacity flex items-center justify-center text-white">
                                        <span class="material-symbols-outlined text-base">zoom_in</span>
                                    </div>
                                </button>
                                <div class="min-w-0">
                                    <h3 class="font-headline text-2xl font-bold tracking-tight truncate" x-text="selectedSantri?.name"></h3>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-white/90">
                                        <span class="rounded-lg bg-white/15 px-3 py-1 font-semibold">NIS <span x-text="selectedSantri?.nis || '-'"></span></span>
                                        <span class="rounded-lg bg-white/15 px-3 py-1 font-semibold" x-text="selectedSantri?.kamar_text || '-'"></span>
                                        <span class="rounded-lg bg-white/15 px-3 py-1 font-semibold" x-text="selectedSantri?.santri_status === 'alumni' ? 'Alumni' : 'Aktif'"></span>
                                    </div>
                                </div>
                            </div>
                            <button @click="showDetailModal = false" class="rounded-xl p-2 transition-colors hover:bg-white/10 text-white">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-5">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <!-- Section: Data Pribadi -->
                            <section class="rounded-2xl bg-surface-container-lowest p-5 border border-outline-variant/10 shadow-sm space-y-3">
                                <h4 class="flex items-center gap-2 font-headline font-bold text-primary text-sm">
                                    <span class="material-symbols-outlined text-base">person</span>
                                    Data Pribadi
                                </h4>
                                <div class="space-y-2.5">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Email</p>
                                        <p class="break-words text-xs font-semibold text-on-surface mt-0.5" x-text="selectedSantri?.email || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Tempat, Tanggal Lahir</p>
                                        <p class="text-xs font-semibold text-on-surface mt-0.5" x-text="formatBirthPlaceDate(selectedSantri?.tempat_lahir, selectedSantri?.tanggal_lahir)"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Alamat</p>
                                        <p class="whitespace-pre-line text-xs font-medium leading-relaxed text-on-surface mt-0.5" x-text="selectedSantri?.alamat || '-'"></p>
                                    </div>
                                </div>
                            </section>

                            <!-- Section: Akademik & Wali -->
                            <section class="rounded-2xl bg-surface-container-lowest p-5 border border-outline-variant/10 shadow-sm space-y-3">
                                <h4 class="flex items-center gap-2 font-headline font-bold text-primary text-sm">
                                    <span class="material-symbols-outlined text-base">school</span>
                                    Akademik & Wali
                                </h4>
                                <div class="space-y-2.5">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Kelas Formal</p>
                                        <p class="text-xs font-semibold text-on-surface mt-0.5" x-text="selectedSantri?.asal_sekolah || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">RFID Tag Code</p>
                                        <p class="text-xs font-semibold text-on-surface mt-0.5 font-mono" x-text="selectedSantri?.rfid_code || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Nama Wali</p>
                                        <p class="text-xs font-semibold text-on-surface mt-0.5" x-text="selectedSantri?.nama_wali || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">No HP Wali</p>
                                        <p class="text-xs font-semibold text-on-surface mt-0.5" x-text="selectedSantri?.no_hp_wali || '-'"></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 bg-surface-container-low border-t border-surface-container flex justify-end">
                        <button @click="showDetailModal = false" class="rounded-xl bg-surface-container-high px-6 py-2.5 text-xs font-bold text-on-surface hover:bg-surface-container transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function petugasSantriView() {
    return {
        showDetailModal: false,
        showPhotoModal: false,
        photoTitle: '',
        photoUrl: null,
        loading: false,
        selectedSantri: null,

        openPhotoModal(title, url) {
            this.photoTitle = title || 'Foto Santri';
            this.photoUrl = url || null;
            this.showPhotoModal = true;
        },

        async openDetailModal(santriId) {
            this.showDetailModal = true;
            this.loading = true;
            try {
                const response = await fetch(`{{ url('petugas/santri') }}/${santriId}/modal-data`);
                const data = await response.json();
                this.selectedSantri = data.santri;
                this.selectedSantri.foto_url = data.foto_url;
                this.selectedSantri.kamar_text = data.kamar_text;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        formatBirthPlaceDate(place, dateStr) {
            if (!place && !dateStr) return '-';
            let formattedDate = dateStr;
            if (dateStr) {
                try {
                    const d = new Date(dateStr);
                    formattedDate = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                } catch (e) {}
            }
            if (place && formattedDate) return `${place}, ${formattedDate}`;
            return place || formattedDate || '-';
        }
    };
}
</script>
@endsection
