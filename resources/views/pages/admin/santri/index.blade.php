@extends('layouts.app')

@section('header-title', 'Data Santri')
@php
    $activeRole = $activeRole ?? 'admin';
    $routePrefix = $routePrefix ?? $activeRole.'.santri';
    $currentStatus = $currentStatus ?? request('status', 'aktif');
@endphp

@section('content')
<div x-data="santriApp()">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Data Santri</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola data santri dan saldo mereka.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route($routePrefix.'.export', ['status' => $currentStatus]) }}" class="bg-primary/10 text-primary font-bold py-3 px-4 rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined">download</span><span>Export Excel</span>
            </a>
            <a href="{{ route($routePrefix.'.create') }}" class="bg-primary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">add</span>
                <span>Tambah / Import</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">group</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Santri</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $activeCount }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Saldo Santri</p>
            </div>
            <p class="text-3xl font-bold text-primary">Rp {{ number_format($santriList->getCollection()->sum('saldo'), 0, ',', '.') }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">add_card</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Alumni</p>
            </div>
            <p class="text-3xl font-bold text-primary">{{ $alumniCount }}</p>
        </div>
    </div>

    <!-- Santri List Table -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Daftar Santri</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route($routePrefix.'.index', ['status' => 'aktif']) }}" class="px-3 py-2 rounded-lg text-sm font-bold {{ $currentStatus === 'aktif' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface' }}">Aktif</a>
                <a href="{{ route($routePrefix.'.index', ['status' => 'alumni']) }}" class="px-3 py-2 rounded-lg text-sm font-bold {{ $currentStatus === 'alumni' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface' }}">Alumni</a>
                <form method="GET" action="{{ route($routePrefix.'.index') }}" class="flex gap-2" x-data="{ hasSearch: {{ request('search') ? 'true' : 'false' }} }" x-init="if(hasSearch) { setTimeout(() => $el.querySelector('input[name=\"search\"]').focus(), 100); }" @keydown.enter.prevent="$event.target.closest('form').submit()">
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari nama atau NIS santri..."
                           class="bg-surface-container-high border-none rounded-lg px-4 py-2 text-sm focus:ring-0 focus:outline-none">
                    <button type="submit"
                            class="bg-primary text-on-primary px-4 py-2 rounded-lg text-sm font-semibold hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-sm">search</span>
                    </button>
                    @if(request('search'))
                        <a href="{{ route($routePrefix.'.index', ['status' => $currentStatus]) }}"
                           class="bg-surface-container-high text-on-surface-variant px-4 py-2 rounded-lg text-sm font-semibold hover:bg-surface-container transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </a>
                    @endif
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">NIS</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4">Kamar</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Saldo</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($santriList as $santri)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden flex-shrink-0">
                                        @if($santri->foto)
                                            <img src="{{ Storage::url($santri->foto) }}" alt="{{ $santri->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($santri->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-on-surface truncate">{{ $santri->name }}</div>
                                        <div class="text-xs text-on-surface-variant truncate">{{ $santri->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $santri->nis ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-on-surface-variant px-2 py-1 bg-surface-container-low rounded">
                                    {{ $santri->kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $santri->kamarSantri?->kamar ? ucfirst(str_replace('_', ' ', $santri->kamarSantri->kamar)) : '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold px-2 py-1 rounded-full {{ $santri->isAlumni() ? 'bg-surface-container-high text-on-surface-variant' : 'bg-primary/10 text-primary' }}">
                                    {{ $santri->isAlumni() ? 'Alumni' : 'Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-headline font-bold text-primary">Rp {{ number_format($santri->saldo, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openDetailModal({{ $santri->id }})"
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </button>
                                    <button @click="openEditModal({{ $santri->id }})"
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    @if($santri->isAlumni())
                                        <form action="{{ route($routePrefix.'.activate', $santri) }}" method="POST" onsubmit="return confirm('Aktifkan kembali santri ini?')">
                                            @csrf @method('PATCH')
                                            <button class="p-2 text-primary hover:bg-primary/10 rounded-lg" title="Aktifkan kembali"><span class="material-symbols-outlined text-sm">person_check</span></button>
                                        </form>
                                    @else
                                        <form action="{{ route($routePrefix.'.graduate', $santri) }}" method="POST" onsubmit="return confirm('Jadikan santri ini alumni? Akun akan menjadi read-only dan kamar aktif dilepas.')">
                                            @csrf @method('PATCH')
                                            <button class="p-2 text-on-surface-variant hover:bg-surface-container rounded-lg" title="Jadikan alumni"><span class="material-symbols-outlined text-sm">school</span></button>
                                        </form>
                                    @endif
                                    <form action="{{ route($routePrefix.'.destroy', $santri) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data santri ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-error hover:bg-error/10 rounded-lg transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">group_off</span>
                                <p class="text-sm text-on-surface-variant">Belum ada data santri</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($santriList->hasPages())
            <div class="p-6 border-t border-surface-container">
                {{ $santriList->links() }}
            </div>
        @endif
    </div>

<!-- Detail Modal -->
<div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 z-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showDetailModal = false"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div class="bg-surface rounded-2xl shadow-2xl max-w-3xl w-full animate-scale-in max-h-[92vh] overflow-hidden flex flex-col" @click.stop>
            <div x-show="loading" class="flex items-center justify-center py-24">
                <span class="material-symbols-outlined text-primary text-5xl animate-spin">progress_activity</span>
            </div>

            <div x-show="!loading && selectedSantri" class="flex min-h-0 flex-col">
                <div class="bg-primary text-on-primary p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-2xl bg-white/15 ring-2 ring-white/30 flex items-center justify-center text-2xl font-bold">
                                <template x-if="selectedSantri.foto_url">
                                    <img :src="selectedSantri.foto_url" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!selectedSantri.foto_url">
                                    <span x-text="selectedSantri.name ? selectedSantri.name.substring(0, 2).toUpperCase() : ''"></span>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-headline text-2xl font-extrabold tracking-tight truncate" x-text="selectedSantri.name"></h3>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-white/85">
                                    <span class="rounded-lg bg-white/15 px-3 py-1">NIS <span x-text="selectedSantri.nis || '-'"></span></span>
                                    <span class="rounded-lg bg-white/15 px-3 py-1" x-text="selectedSantri.kamar_text || '-'"></span>
                                </div>
                            </div>
                        </div>
                        <button @click="showDetailModal = false" class="rounded-xl p-2 transition-colors hover:bg-white/10">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-white/15 px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-white/75">Saldo</p>
                            <p class="font-headline text-lg font-bold" x-text="'Rp ' + formatNumber(selectedSantri.saldo)"></p>
                        </div>
                        <div class="rounded-xl bg-white/15 px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-white/75">Kelas</p>
                            <p class="text-sm font-bold" x-text="selectedSantri.kelas || '-'"></p>
                        </div>
                        <div class="rounded-xl bg-white/15 px-4 py-3">
                            <p class="text-xs font-semibold uppercase text-white/75">Status</p>
                            <p class="text-sm font-bold" x-text="selectedSantri.santri_status === 'alumni' ? 'Alumni' : 'Aktif'"></p>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <section class="rounded-2xl bg-surface-container-lowest p-5">
                            <h4 class="mb-4 flex items-center gap-2 font-headline font-bold text-primary">
                                <span class="material-symbols-outlined text-lg">person</span>
                                Data Pribadi
                            </h4>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase text-on-surface-variant">Email</p>
                                    <p class="break-words text-sm font-medium text-on-surface" x-text="selectedSantri.email || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-on-surface-variant">Tempat, Tanggal Lahir</p>
                                    <p class="text-sm font-medium text-on-surface" x-text="formatBirthPlaceDate(selectedSantri.tempat_lahir, selectedSantri.tanggal_lahir)"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-on-surface-variant">Alamat</p>
                                    <p class="whitespace-pre-line text-sm font-medium leading-relaxed text-on-surface" x-text="selectedSantri.alamat || '-'"></p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl bg-surface-container-lowest p-5">
                            <h4 class="mb-4 flex items-center gap-2 font-headline font-bold text-primary">
                                <span class="material-symbols-outlined text-lg">school</span>
                                Akademik & Wali
                            </h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs font-semibold uppercase text-on-surface-variant">Kelas Formal</p>
                                    <p class="text-sm font-medium text-on-surface" x-text="selectedSantri.asal_sekolah || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-on-surface-variant">RFID</p>
                                    <p class="text-sm font-medium text-on-surface" x-text="selectedSantri.rfid_code || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-on-surface-variant">Nama Wali</p>
                                    <p class="text-sm font-medium text-on-surface" x-text="selectedSantri.nama_wali || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-on-surface-variant">No HP Wali</p>
                                    <p class="text-sm font-medium text-on-surface" x-text="selectedSantri.no_hp_wali || '-'"></p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button @click="showDetailModal = false" class="rounded-xl bg-surface-container-high px-6 py-3 font-bold text-on-surface transition-colors hover:bg-surface-container">
                            Tutup
                        </button>
                        <button @click="showDetailModal = false; openEditModal(selectedSantri.id)" class="rounded-xl bg-primary px-6 py-3 font-bold text-on-primary shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">edit</span>
                            <span>Edit Data</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 z-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div class="bg-surface rounded-3xl shadow-2xl max-w-2xl w-full animate-scale-in max-h-[90vh] flex flex-col" @click.stop>
            <div x-show="loading" class="flex items-center justify-center py-24">
                <span class="material-symbols-outlined text-primary text-5xl animate-spin">progress_activity</span>
            </div>

            <div x-show="!loading && selectedSantri" class="flex flex-col h-full">
                <!-- Header -->
                <div class="relative overflow-hidden bg-gradient-to-br from-primary to-primary-container rounded-t-3xl p-6 pb-4 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-headline font-bold text-2xl text-white tracking-tight">Edit Data Santri</h3>
                            <p class="text-sm text-white/80 mt-1">Ubah informasi santri</p>
                        </div>
                        <button @click="showEditModal = false" class="p-2 hover:bg-white/10 rounded-xl transition-colors">
                            <span class="material-symbols-outlined text-white">close</span>
                        </button>
                    </div>
                </div>

                <!-- Form Section (Scrollable) -->
                <div class="flex-1 overflow-y-auto px-6 pb-6 z-50">
                    <form :id="`edit-santri-form-${selectedSantri.id}`" :action="`${santriBasePath}/${selectedSantri.id}`" method="POST" enctype="multipart/form-data" class="space-y-4 pt-4">
                        @csrf
                        @method('PUT')

                        <!-- Photo Upload -->
                        <div class="bg-surface-container-lowest rounded-2xl p-5">
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-bold text-2xl overflow-hidden ring-2 ring-primary/20">
                                    <template x-if="editData.foto_preview">
                                        <img :src="editData.foto_preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!editData.foto_preview && selectedSantri.foto_url">
                                        <img :src="selectedSantri.foto_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!editData.foto_preview && !selectedSantri.foto_url">
                                        <span x-text="selectedSantri.name ? selectedSantri.name.substring(0, 2).toUpperCase() : ''"></span>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Foto Profil</label>
                                    <input type="file" name="foto" accept="image/*" @change="handleFotoUpload" class="text-sm text-on-surface-variant file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                </div>
                            </div>
                        </div>

                        <!-- Personal Info -->
                        <div class="bg-surface-container-lowest rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">person</span>
                                </div>
                                <h4 class="font-headline font-bold text-on-surface">Data Pribadi</h4>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Lengkap <span class="text-error">*</span></label>
                                    <input type="text" name="name" x-model="editData.name" required class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">NIS <span class="text-error">*</span></label>
                                    <input type="text" name="nis" x-model="editData.nis" required class="input-field w-full">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Email <span class="text-error">*</span></label>
                                <input type="email" name="email" x-model="editData.email" required class="input-field w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Tempat, Tanggal Lahir</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <input type="text" name="tempat_lahir" x-model="editData.tempat_lahir" class="input-field w-full">
                                    <input type="date" name="tanggal_lahir" x-model="editData.tanggal_lahir" class="input-field w-full">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">RFID Code</label>
                                <input type="text" name="rfid_code" x-model="editData.rfid_code" class="input-field w-full" placeholder="Tap kartu RFID pada reader">
                            </div>
                        </div>

                        <!-- Guardian Info -->
                        <div class="bg-surface-container-lowest rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">family_home</span>
                                </div>
                                <h4 class="font-headline font-bold text-on-surface">Data Wali</h4>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Wali</label>
                                    <input type="text" name="nama_wali" x-model="editData.nama_wali" class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">No HP Wali</label>
                                    <input type="text" name="no_hp_wali" x-model="editData.no_hp_wali" class="input-field w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Academic Info -->
                        <div class="bg-surface-container-lowest rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">school</span>
                                </div>
                                <h4 class="font-headline font-bold text-on-surface">Data Akademik</h4>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kelas</label>
                                    <input type="text" name="kelas" x-model="editData.kelas" list="pondok-class-options" class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kelas Formal</label>
                                    <input type="text" name="asal_sekolah" x-model="editData.asal_sekolah" list="formal-class-options" class="input-field w-full">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Alamat</label>
                                <textarea name="alamat" rows="3" x-model="editData.alamat" class="input-field w-full"></textarea>
                            </div>
                        </div>

                        <!-- Account Settings -->
                        <div class="bg-surface-container-lowest rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">settings</span>
                                </div>
                                <h4 class="font-headline font-bold text-on-surface">Pengaturan Akun</h4>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Saldo (Rp)</label>
                                    <input type="number" name="saldo" x-model="editData.saldo" step="0.01" class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">PIN Baru (Opsional)</label>
                                    <input type="password" name="pin" maxlength="6" pattern="[0-9]{6}" class="input-field w-full" placeholder="6 digit PIN">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Password Baru (Opsional)</label>
                                <input type="password" name="password" class="input-field w-full" placeholder="Kosongkan jika tidak ingin mengubah">
                            </div>
                        </div>
                    </form>
                    <div class="flex gap-4 p-5">
                        <button type="button" @click="showEditModal = false" class="flex-1 bg-surface-container-high text-on-surface font-bold py-4 px-6 rounded-xl hover:bg-surface-container transition-all">
                            Batal
                        </button>
                        <button type="submit" :form="`edit-santri-form-${selectedSantri.id}`" class="flex-1 bg-primary text-on-primary font-bold py-4 px-6 rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<datalist id="pondok-class-options">
    @foreach(\App\Support\TarbiyahClass::levels() as $classLevel)
        <option value="{{ $classLevel }}">
    @endforeach
</datalist>
<datalist id="formal-class-options">
    @foreach(\App\Models\FormalClass::active()->orderBy('sort_order')->orderBy('name')->get() as $formalClass)
        <option value="{{ $formalClass->name }}">
    @endforeach
</datalist>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>

<script>
function santriApp() {
    return {
        santriBasePath: @json(url($activeRole.'/santri')),
        showDetailModal: false,
        showEditModal: false,
        loading: false,
        selectedSantri: null,
        editData: {
            foto_preview: null,
            name: '',
            email: '',
            nis: '',
            tempat_lahir: '',
            tanggal_lahir: '',
            rfid_code: '',
            nama_wali: '',
            no_hp_wali: '',
            asal_sekolah: '',
            kelas: '',
            alamat: '',
            saldo: 0
        },

        async openDetailModal(id) {
            console.log('Opening detail modal for santri ID:', id);
            this.loading = true;
            this.showDetailModal = true;
            try {
                const response = await fetch(`${this.santriBasePath}/${id}/modal-data`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                console.log('Data received:', data);
                this.selectedSantri = data.santri;
                this.selectedSantri.foto_url = data.foto_url;
                this.selectedSantri.kamar_text = data.kamar_text;
            } catch (error) {
                console.error('Error loading santri data:', error);
                alert('Gagal memuat data santri: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async openEditModal(id) {
            console.log('Opening edit modal for santri ID:', id);
            this.loading = true;
            this.showEditModal = true;
            this.showDetailModal = false;
            this.editData.foto_preview = null;
            try {
                const response = await fetch(`${this.santriBasePath}/${id}/modal-data`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                this.selectedSantri = data.santri;
                this.selectedSantri.foto_url = data.foto_url;
                this.selectedSantri.kamar_text = data.kamar_text;
                
                // Initialize editData with current values
                this.editData.name = data.santri.name || '';
                this.editData.email = data.santri.email || '';
                this.editData.nis = data.santri.nis || '';
                this.editData.tempat_lahir = data.santri.tempat_lahir || '';
                this.editData.tanggal_lahir = data.santri.tanggal_lahir || '';
                this.editData.rfid_code = data.santri.rfid_code || '';
                this.editData.nama_wali = data.santri.nama_wali || '';
                this.editData.no_hp_wali = data.santri.no_hp_wali || '';
                this.editData.asal_sekolah = data.santri.asal_sekolah || '';
                this.editData.kelas = data.santri.kelas || '';
                this.editData.alamat = data.santri.alamat || '';
                this.editData.saldo = data.santri.saldo || 0;
            } catch (error) {
                console.error('Error loading santri data:', error);
                alert('Gagal memuat data santri: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        handleFotoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.editData.foto_preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        formatBirthPlaceDate(place, dateStr) {
            const formattedDate = this.formatDate(dateStr);

            if (place && formattedDate !== '-') {
                return `${place}, ${formattedDate}`;
            }

            return place || formattedDate;
        }
    }
}
</script>
</div>
@endsection
