@extends('layouts.app')

@section('header-title', 'Data Petugas')
@php $activeRole = 'admin'; @endphp

@section('content')
<div x-data="petugasApp()">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Data Petugas</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola data petugas bank pesantren.</p>
        </div>
        <a href="{{ route('admin.petugas.create') }}" class="btn-primary">
            <span class="material-symbols-filled">add</span>
            <span>Tambah Petugas</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-filled text-primary">group</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Petugas</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $petugasList->total() }}</p>
        </div>
        
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-filled text-primary">verified_user</span>
                <p class="text-xs text-on-surface-variant font-medium">Petugas Aktif</p>
            </div>
            <p class="text-3xl font-bold text-primary">{{ $petugasList->total() }}</p>
        </div>
        
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-filled text-primary">work</span>
                <p class="text-xs text-on-surface-variant font-medium">Jabatan</p>
            </div>
            <p class="text-lg font-bold text-on-surface">{{ $petugasList->getCollection()->pluck('jabatan')->unique()->count() }} Tipe</p>
        </div>
    </div>

    <!-- Petugas List Table -->
    <div class="card !p-0 overflow-hidden">
        <div class="p-6 border-b border-outline-variant/10 flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Daftar Petugas</h3>
            <div class="flex gap-2">
                <input type="text" placeholder="Cari petugas..." 
                       class="input-field !py-2 !text-sm" style="width: 250px;">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Petugas</th>
                        <th class="px-6 py-4">NIP</th>
                        <th class="px-6 py-4">Jabatan</th>
                        <th class="px-6 py-4">No HP</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($petugasList as $petugas)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden flex-shrink-0">
                                        @if($petugas->foto)
                                            <img src="{{ Storage::url($petugas->foto) }}" alt="{{ $petugas->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($petugas->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-on-surface truncate">{{ $petugas->name }}</div>
                                        <div class="text-xs text-on-surface-variant truncate">{{ $petugas->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-on-surface-variant">{{ $petugas->nip ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-on-surface-variant px-2 py-1 bg-surface-container-low rounded">
                                    {{ $petugas->jabatan ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $petugas->no_hp ?? '-' }}</td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $petugas->email }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openDetailModal({{ $petugas->id }})"
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </button>
                                    <button @click="openEditModal({{ $petugas->id }})"
                                       class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <form action="{{ route('admin.petugas.destroy', $petugas) }}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data petugas ini?')">
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">group_off</span>
                                <p class="text-sm text-on-surface-variant">Belum ada data petugas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($petugasList->hasPages())
            <div class="p-6 border-t border-outline-variant/10">
                {{ $petugasList->links() }}
            </div>
        @endif
    </div>

<!-- Detail Modal -->
<div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showDetailModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-surface rounded-3xl shadow-2xl max-w-2xl w-full animate-scale-in" @click.stop>
            <div x-show="loading" class="flex items-center justify-center py-24">
                <span class="material-symbols-outlined text-primary text-5xl animate-spin">progress_activity</span>
            </div>
            
            <div x-show="!loading && selectedPetugas" class="space-y-0">
                <!-- Profile Header with Gradient -->
                <div class="relative overflow-hidden bg-gradient-to-br from-primary to-primary-container rounded-t-3xl p-8 pb-12">
                    <!-- Abstract Texture Overlay -->
                    <div class="absolute inset-0 opacity-10 pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-5">
                                <div class="w-24 h-24 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white font-bold text-3xl overflow-hidden ring-4 ring-white/30">
                                    <template x-if="selectedPetugas.foto_url">
                                        <img :src="selectedPetugas.foto_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!selectedPetugas.foto_url">
                                        <span x-text="selectedPetugas.name ? selectedPetugas.name.substring(0, 2).toUpperCase() : ''"></span>
                                    </template>
                                </div>
                                <div class="text-white">
                                    <h3 class="font-headline font-bold text-2xl tracking-tight mb-1" x-text="selectedPetugas.name"></h3>
                                    <p class="text-sm text-white/80 mb-2" x-text="selectedPetugas.jabatan || '-'"></p>
                                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-2 rounded-xl">
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">badge</span>
                                        <span class="font-medium text-sm" x-text="selectedPetugas.nip ? 'NIP: ' + selectedPetugas.nip : 'NIP: -'"></span>
                                    </div>
                                </div>
                            </div>
                            <button @click="showDetailModal = false" class="p-2 hover:bg-white/10 rounded-xl transition-colors">
                                <span class="material-symbols-outlined text-white">close</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Content Section -->
                <div class="px-8 -mt-6 pb-8 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-surface-container-lowest rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">person</span>
                            </div>
                            <h4 class="font-headline font-bold text-on-surface">Informasi Personal</h4>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Email</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedPetugas.email || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">No HP</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedPetugas.no_hp || '-'"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Alamat</p>
                                <p class="font-medium text-on-surface text-sm leading-relaxed" x-text="selectedPetugas.alamat || '-'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Professional Information -->
                    <div class="bg-surface-container-lowest rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">work</span>
                            </div>
                            <h4 class="font-headline font-bold text-on-surface">Informasi Jabatan</h4>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Jabatan</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedPetugas.jabatan || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">NIP</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedPetugas.nip || '-'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button @click="showDetailModal = false; openEditModal(selectedPetugas.id)" class="flex-1 bg-primary text-on-primary font-bold py-4 px-6 rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">edit</span>
                            <span>Edit Data</span>
                        </button>
                        <button @click="showDetailModal = false" class="flex-1 bg-surface-container-high text-on-surface font-bold py-4 px-6 rounded-xl hover:bg-surface-container transition-all">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Edit Modal -->
<div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-surface rounded-2xl shadow-2xl max-w-2xl w-full p-6 animate-scale-in" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-headline font-bold text-xl text-on-surface">Edit Data Petugas</h3>
                <button @click="showEditModal = false" class="p-2 hover:bg-surface-container-high rounded-full transition-colors">
                    <span class="material-symbols-outlined text-on-surface-variant">close</span>
                </button>
            </div>
            
            <div x-show="loading" class="flex items-center justify-center py-12">
                <span class="material-symbols-outlined text-primary animate-spin">progress_activity</span>
            </div>
            
            <form x-show="!loading && selectedPetugas" :action="`/admin/petugas/${selectedPetugas.id}`" method="POST" enctype="multipart/form-data" class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                @csrf
                @method('PUT')
                
                <div class="flex items-center gap-4 p-4 bg-surface-container-lowest rounded-xl">
                    <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-2xl overflow-hidden flex-shrink-0">
                        <template x-if="editData.foto_preview">
                            <img :src="editData.foto_preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!editData.foto_preview && selectedPetugas.foto_url">
                            <img :src="selectedPetugas.foto_url" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!editData.foto_preview && !selectedPetugas.foto_url">
                            <span x-text="selectedPetugas.name ? selectedPetugas.name.substring(0, 2).toUpperCase() : ''"></span>
                        </template>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Foto Profil</label>
                        <input type="file" name="foto" accept="image/*" @change="handleFotoUpload" class="text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Lengkap <span class="text-error">*</span></label>
                        <input type="text" name="name" :value="selectedPetugas.name" required class="input-field w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Jabatan <span class="text-error">*</span></label>
                        <input type="text" name="jabatan" :value="selectedPetugas.jabatan" required class="input-field w-full">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Email <span class="text-error">*</span></label>
                    <input type="email" name="email" :value="selectedPetugas.email" required class="input-field w-full">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">NIP</label>
                        <input type="text" name="nip" :value="selectedPetugas.nip" class="input-field w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">No HP</label>
                        <input type="text" name="no_hp" :value="selectedPetugas.no_hp" class="input-field w-full">
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Alamat</label>
                    <textarea name="alamat" rows="3" class="input-field w-full" x-text="selectedPetugas.alamat"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Password Baru (Opsional)</label>
                    <input type="password" name="password" class="input-field w-full" placeholder="Kosongkan jika tidak ingin mengubah">
                </div>
                
                <div class="flex gap-3 pt-4 sticky bottom-0 bg-surface pb-2">
                    <button type="button" @click="showEditModal = false" class="flex-1 bg-surface-container-high text-on-surface font-bold py-3 px-4 rounded-xl hover:bg-surface-container transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-primary text-on-primary font-bold py-3 px-4 rounded-xl hover:shadow-lg transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
.material-symbols-filled {
    font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>

<script>
function petugasApp() {
    return {
        showDetailModal: false,
        showEditModal: false,
        loading: false,
        selectedPetugas: null,
        editData: {
            foto_preview: null
        },
        
        async openDetailModal(id) {
            console.log('Opening detail modal for petugas ID:', id);
            this.loading = true;
            this.showDetailModal = true;
            try {
                const response = await fetch(`/admin/petugas/${id}/modal-data`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                console.log('Data received:', data);
                this.selectedPetugas = data.petugas;
                this.selectedPetugas.foto_url = data.foto_url;
            } catch (error) {
                console.error('Error loading petugas data:', error);
                alert('Gagal memuat data petugas: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async openEditModal(id) {
            console.log('Opening edit modal for petugas ID:', id);
            this.loading = true;
            this.showEditModal = true;
            this.showDetailModal = false;
            this.editData.foto_preview = null;
            try {
                const response = await fetch(`/admin/petugas/${id}/modal-data`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                this.selectedPetugas = data.petugas;
                this.selectedPetugas.foto_url = data.foto_url;
            } catch (error) {
                console.error('Error loading petugas data:', error);
                alert('Gagal memuat data petugas: ' + error.message);
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
        }
    }
}
</script>
</div>
@endsection
