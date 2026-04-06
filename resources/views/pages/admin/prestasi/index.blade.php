@extends('layouts.app')

@section('header-title', 'Prestasi Santri')
@php $activeRole = 'admin'; @endphp

@section('content')
<div x-data="prestasiApp()">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Prestasi Santri</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola prestasi hafalan kitab para santri.</p>
        </div>
        <a href="{{ route('admin.prestasi.create') }}" class="bg-primary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">add</span>
            <span>Tambah Prestasi</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Prestasi</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $prestasiList->total() }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-green-600 text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <p class="text-xs text-on-surface-variant font-medium">Telah Dihafalkan</p>
            </div>
            <p class="text-3xl font-bold text-green-600">{{ $prestasiList->getCollection()->where('status', 'telah_dihafalkan')->count() }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-tertiary text-sm" style="font-variation-settings: 'FILL' 1;">autorenew</span>
                <p class="text-xs text-on-surface-variant font-medium">Sedang Dihafal</p>
            </div>
            <p class="text-3xl font-bold text-tertiary">{{ $prestasiList->getCollection()->where('status', 'sedang_dihafal')->count() }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">stars</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Poin</p>
            </div>
            <p class="text-3xl font-bold text-primary">{{ number_format($prestasiList->getCollection()->sum('poin'), 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Prestasi Table -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Daftar Prestasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">Nama Kitab</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Nilai</th>
                        <th class="px-6 py-4 text-right">Poin</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($prestasiList as $prestasi)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden flex-shrink-0">
                                        @if($prestasi->santri->foto)
                                            <img src="{{ Storage::url($prestasi->santri->foto) }}" alt="{{ $prestasi->santri->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($prestasi->santri->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-on-surface truncate">{{ $prestasi->santri->name }}</div>
                                        <div class="text-xs text-on-surface-variant truncate">{{ $prestasi->santri->nis ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-on-surface">{{ $prestasi->nama_kitab }}</div>
                                @if($prestasi->tanggal_selesai)
                                    <div class="text-xs text-on-surface-variant">{{ $prestasi->tanggal_selesai->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-on-surface-variant px-2 py-1 bg-surface-container-low rounded">
                                    {{ $prestasi->kategori ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($prestasi->status === 'telah_dihafalkan')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 px-2 py-1 rounded-full">
                                        <span class="w-2 h-2 rounded-full bg-green-600"></span>
                                        {{ $prestasi->status_text }}
                                    </span>
                                @elseif($prestasi->status === 'sedang_dihafal')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-tertiary bg-tertiary/10 px-2 py-1 rounded-full">
                                        <span class="w-2 h-2 rounded-full bg-tertiary animate-pulse"></span>
                                        {{ $prestasi->status_text }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-on-surface-variant bg-surface-container px-2 py-1 rounded-full">
                                        <span class="w-2 h-2 rounded-full bg-outline-variant"></span>
                                        {{ $prestasi->status_text }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($prestasi->nilai)
                                    <span class="font-bold text-primary">{{ $prestasi->nilai }}</span>
                                    @if($prestasi->skor)
                                        <div class="text-xs text-on-surface-variant">Score: {{ $prestasi->skor }}/100</div>
                                    @endif
                                @else
                                    <span class="text-on-surface-variant">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-headline font-bold text-primary">{{ number_format($prestasi->poin, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openDetailModal({{ $prestasi->id }})" 
                                            class="text-primary hover:text-primary-container transition-colors p-1 rounded-lg hover:bg-primary/5"
                                            title="Lihat Detail">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </button>
                                    <a href="{{ route('admin.prestasi.edit', $prestasi) }}" 
                                       class="text-tertiary hover:text-tertiary-container transition-colors p-1 rounded-lg hover:bg-tertiary/5"
                                       title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <button @click="confirmDelete({{ $prestasi->id }}, '{{ $prestasi->nama_kitab }}')" 
                                            class="text-error hover:text-error-container transition-colors p-1 rounded-lg hover:bg-error/5"
                                            title="Hapus">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">military_tech</span>
                                <p class="text-on-surface-variant font-medium">Belum ada data prestasi</p>
                                <p class="text-xs text-on-surface-variant opacity-60 mt-1">Mulai tambahkan prestasi santri untuk melihat data di sini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($prestasiList->hasPages())
            <div class="p-6 border-t border-surface-container">
                {{ $prestasiList->links() }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div x-show="detailModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.outside="detailModalOpen = false" x-transition.scale class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-surface-container-lowest border-b border-surface-container p-6 flex items-center justify-between z-10">
                <h3 class="font-headline font-bold text-2xl text-primary">Detail Prestasi</h3>
                <button @click="detailModalOpen = false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <template x-if="selectedPrestasi">
                <div class="p-6 space-y-6">
                    <!-- Santri Info -->
                    <div class="flex items-center gap-4 p-4 bg-surface-container-low rounded-xl">
                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-2xl overflow-hidden">
                            <template x-if="selectedSantri?.foto">
                                <img :src="`/storage/${selectedSantri.foto}`" class="w-full h-full object-cover">
                            </template>
                            <span x-show="!selectedSantri?.foto" x-text="selectedSantri?.name?.substring(0, 2)"></span>
                        </div>
                        <div>
                            <h4 class="font-bold text-on-surface text-lg" x-text="selectedSantri?.name"></h4>
                            <p class="text-sm text-on-surface-variant" x-text="`NIS: ${selectedSantri?.nis || '-'}`"></p>
                        </div>
                    </div>

                    <!-- Kitab Info -->
                    <div>
                        <h5 class="text-xs font-bold text-primary uppercase tracking-widest mb-2">Informasi Kitab</h5>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-surface-container-low p-4 rounded-lg">
                                <p class="text-xs text-on-surface-variant mb-1">Nama Kitab</p>
                                <p class="font-bold text-on-surface" x-text="selectedPrestasi?.nama_kitab"></p>
                            </div>
                            <div class="bg-surface-container-low p-4 rounded-lg">
                                <p class="text-xs text-on-surface-variant mb-1">Kategori</p>
                                <p class="font-bold text-on-surface" x-text="selectedPrestasi?.kategori || '-'"></p>
                            </div>
                            <div class="bg-surface-container-low p-4 rounded-lg">
                                <p class="text-xs text-on-surface-variant mb-1">Status</p>
                                <p class="font-bold" :class="{
                                    'text-green-600': selectedPrestasi?.status === 'telah_dihafalkan',
                                    'text-tertiary': selectedPrestasi?.status === 'sedang_dihafal',
                                    'text-on-surface-variant': selectedPrestasi?.status === 'belum_dihafal'
                                }" x-text="selectedPrestasi?.status_text"></p>
                            </div>
                            <div class="bg-surface-container-low p-4 rounded-lg">
                                <p class="text-xs text-on-surface-variant mb-1">Poin</p>
                                <p class="font-bold text-primary" x-text="selectedPrestasi?.poin"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Nilai & Tanggal -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-surface-container-low p-4 rounded-lg">
                            <p class="text-xs text-on-surface-variant mb-1">Nilai</p>
                            <p class="font-bold text-on-surface text-lg" x-text="selectedPrestasi?.nilai || '-'"></p>
                        </div>
                        <div class="bg-surface-container-low p-4 rounded-lg">
                            <p class="text-xs text-on-surface-variant mb-1">Skor</p>
                            <p class="font-bold text-on-surface text-lg" x-text="selectedPrestasi?.skor || '-'"></p>
                        </div>
                        <div class="bg-surface-container-low p-4 rounded-lg">
                            <p class="text-xs text-on-surface-variant mb-1">Tanggal Selesai</p>
                            <p class="font-bold text-on-surface text-sm" x-text="selectedPrestasi?.tanggal_selesai ? new Date(selectedPrestasi.tanggal_selesai).toLocaleDateString('id-ID') : '-'"></p>
                        </div>
                    </div>

                    <!-- Ustadz & Catatan -->
                    <div x-show="selectedPrestasi?.ustadz_pembimbing || selectedPrestasi?.catatan_ustadz" class="space-y-4">
                        <div x-show="selectedPrestasi?.ustadz_pembimbing" class="bg-surface-container-low p-4 rounded-lg">
                            <p class="text-xs text-on-surface-variant mb-1">Ustadz Pembimbing</p>
                            <p class="font-bold text-on-surface" x-text="selectedPrestasi?.ustadz_pembimbing"></p>
                        </div>
                        <div x-show="selectedPrestasi?.catatan_ustadz" class="bg-primary/5 p-4 rounded-lg border border-primary/10">
                            <p class="text-xs text-primary mb-1">Catatan Ustadz</p>
                            <p class="text-on-surface italic" x-text="selectedPrestasi?.catatan_ustadz"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.outside="deleteModalOpen = false" x-transition.scale class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <span class="material-symbols-outlined text-6xl text-error mb-4">warning</span>
                <h3 class="font-headline font-bold text-2xl text-on-surface mb-2">Hapus Prestasi?</h3>
                <p class="text-on-surface-variant text-sm">Apakah Anda yakin ingin menghapus prestasi <span class="font-bold" x-text="deleteItemName"></span>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form :action="`/admin/prestasi/${deleteItemId}`" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" @click="deleteModalOpen = false" 
                            class="flex-1 px-4 py-3 bg-surface-container-high text-on-surface rounded-xl font-bold hover:bg-surface-container transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 bg-error text-on-error rounded-xl font-bold hover:bg-error-container transition-colors">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prestasiApp() {
    return {
        detailModalOpen: false,
        deleteModalOpen: false,
        selectedPrestasi: null,
        selectedSantri: null,
        deleteItemId: null,
        deleteItemName: '',

        async openDetailModal(id) {
            try {
                const response = await fetch(`/admin/prestasi/${id}/modal-data`);
                const data = await response.json();
                this.selectedPrestasi = data.prestasi;
                this.selectedSantri = data.santri;
                this.detailModalOpen = true;
            } catch (error) {
                console.error('Error loading modal data:', error);
            }
        },

        confirmDelete(id, name) {
            this.deleteItemId = id;
            this.deleteItemName = name;
            this.deleteModalOpen = true;
        }
    }
}
</script>

@endsection
