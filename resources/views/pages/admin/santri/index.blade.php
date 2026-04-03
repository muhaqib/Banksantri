@extends('layouts.app')

@section('header-title', 'Data Santri')
@php $activeRole = 'admin'; @endphp

@section('content')
<div x-data="santriApp()">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Data Santri</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola data santri dan saldo mereka.</p>
        </div>
        <a href="{{ route('admin.santri.create') }}" class="bg-primary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">add</span>
            <span>Tambah Santri</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">group</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Santri</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $santriList->total() }}</p>
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
                <p class="text-xs text-on-surface-variant font-medium">Quick Actions</p>
            </div>
            <a href="{{ route('admin.transactions.topup') }}" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                <span class="material-symbols-outlined text-sm">add</span>
                <span>Top Up Saldo</span>
            </a>
        </div>
    </div>

    <!-- Santri List Table -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Daftar Santri</h3>
            <div class="flex gap-2">
                <input type="text" placeholder="Cari santri..." 
                       class="bg-surface-container-high border-none rounded-lg px-4 py-2 text-sm focus:ring-0">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">NIS</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4">No HP</th>
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
                            <td class="px-6 py-4 text-on-surface-variant">{{ $santri->no_hp ?? '-' }}</td>
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
                                    <form action="{{ route('admin.santri.destroy', $santri) }}" method="POST"
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
                            <td colspan="6" class="px-6 py-12 text-center">
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
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showDetailModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-surface rounded-3xl shadow-2xl max-w-2xl w-full animate-scale-in" @click.stop>
            <div x-show="loading" class="flex items-center justify-center py-24">
                <span class="material-symbols-outlined text-primary text-5xl animate-spin">progress_activity</span>
            </div>
            
            <div x-show="!loading && selectedSantri" class="space-y-0">
                <!-- Profile Header with Gradient -->
                <div class="relative overflow-hidden bg-gradient-to-br from-primary to-primary-container rounded-t-3xl p-8 pb-12">
                    <!-- Abstract Texture Overlay -->
                    <div class="absolute inset-0 opacity-10 pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-5">
                                <div class="w-24 h-24 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white font-bold text-3xl overflow-hidden ring-4 ring-white/30">
                                    <template x-if="selectedSantri.foto_url">
                                        <img :src="selectedSantri.foto_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!selectedSantri.foto_url">
                                        <span x-text="selectedSantri.name ? selectedSantri.name.substring(0, 2).toUpperCase() : ''"></span>
                                    </template>
                                </div>
                                <div class="text-white">
                                    <h3 class="font-headline font-bold text-2xl tracking-tight mb-1" x-text="selectedSantri.name"></h3>
                                    <p class="text-sm text-white/80 mb-2">NIS: <span x-text="selectedSantri.nis || '-'"></span></p>
                                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-2 rounded-xl">
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
                                        <span class="font-headline font-bold text-lg" x-text="'Rp ' + formatNumber(selectedSantri.saldo)"></span>
                                    </div>
                                </div>
                            </div>
                            <button @click="showDetailModal = false" class="p-2 hover:bg-white/10 rounded-xl transition-colors">
                                <span class="material-symbols-outlined text-white">close</span>
                            </button>
                        </div>
                    </div>
                
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
                                <p class="font-medium text-on-surface text-sm" x-text="selectedSantri.email || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">No HP</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedSantri.no_hp || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Tempat Lahir</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedSantri.tempat_lahir || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Tanggal Lahir</p>
                                <p class="font-medium text-on-surface text-sm" x-text="formatDate(selectedSantri.tanggal_lahir)"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Alamat</p>
                                <p class="font-medium text-on-surface text-sm leading-relaxed" x-text="selectedSantri.alamat || '-'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Academic Information -->
                    <div class="bg-surface-container-lowest rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">school</span>
                            </div>
                            <h4 class="font-headline font-bold text-on-surface">Informasi Akademik</h4>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Kelas</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedSantri.kelas || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Asal Sekolah</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedSantri.asal_sekolah || '-'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Guardian Information -->
                    <div class="bg-surface-container-lowest rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">family_home</span>
                            </div>
                            <h4 class="font-headline font-bold text-on-surface">Data Wali</h4>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">Nama Wali</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedSantri.nama_wali || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-on-surface-variant mb-1.5 uppercase tracking-wider font-semibold">No HP Wali</p>
                                <p class="font-medium text-on-surface text-sm" x-text="selectedSantri.no_hp_wali || '-'"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button @click="showDetailModal = false; openEditModal(selectedSantri.id)" class="flex-1 bg-primary text-on-primary font-bold py-4 px-6 rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all flex items-center justify-center gap-2">
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
</div>

<!-- Edit Modal -->
<div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-surface rounded-3xl shadow-2xl max-w-2xl w-full animate-scale-in" @click.stop>
            <div x-show="loading" class="flex items-center justify-center py-24">
                <span class="material-symbols-outlined text-primary text-5xl animate-spin">progress_activity</span>
            </div>
            
            <div x-show="!loading && selectedSantri" class="space-y-0">
                <!-- Header -->
                <div class="relative overflow-hidden bg-gradient-to-br from-primary to-primary-container rounded-t-3xl p-6 pb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-headline font-bold text-2xl text-white tracking-tight">Edit Data Santri</h3>
                            <p class="text-sm text-white/80 mt-1">Ubah informasi santri</p>
                        </div>
                        <button @click="showEditModal = false" class="p-2 hover:bg-white/10 rounded-xl transition-colors">
                            <span class="material-symbols-outlined text-white">close</span>
                        </button>
                        <div class="px-6 -mt-4 pb-6 max-h-[65vh] overflow-y-auto">
                    <form :id="`edit-santri-form-${selectedSantri.id}`" :action="`/admin/santri/${selectedSantri.id}`" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                                <h4 class="font-headline font-bold text-on-surface">Informasi Personal</h4>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Lengkap <span class="text-error">*</span></label>
                                    <input type="text" name="name" :value="selectedSantri.name" required class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">NIS <span class="text-error">*</span></label>
                                    <input type="text" name="nis" :value="selectedSantri.nis" required class="input-field w-full">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Email <span class="text-error">*</span></label>
                                <input type="email" name="email" :value="selectedSantri.email" required class="input-field w-full">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">No HP</label>
                                    <input type="text" name="no_hp" :value="selectedSantri.no_hp" class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kelas</label>
                                    <input type="text" name="kelas" :value="selectedSantri.kelas" class="input-field w-full">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Birth Info -->
                        <div class="bg-surface-container-lowest rounded-2xl p-5 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">cake</span>
                                </div>
                                <h4 class="font-headline font-bold text-on-surface">Tempat & Tanggal Lahir</h4>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" :value="selectedSantri.tempat_lahir" class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" :value="selectedSantri.tanggal_lahir" class="input-field w-full">
                                </div>
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
                                    <input type="text" name="nama_wali" :value="selectedSantri.nama_wali" class="input-field w-full">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">No HP Wali</label>
                                    <input type="text" name="no_hp_wali" :value="selectedSantri.no_hp_wali" class="input-field w-full">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Info -->
                        <div class="bg-surface-container-lowest rounded-2xl p-5 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Asal Sekolah</label>
                                <input type="text" name="asal_sekolah" :value="selectedSantri.asal_sekolah" class="input-field w-full">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Alamat</label>
                                <textarea name="alamat" rows="3" class="input-field w-full" x-text="selectedSantri.alamat"></textarea>
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
                                    <input type="number" name="saldo" :value="selectedSantri.saldo" step="0.01" class="input-field w-full">
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
                </div>
                
                <!-- Fixed Bottom Buttons -->
                <div class="fixed bottom-0 left-0 right-0 bg-surface/95 backdrop-blur-md border-t border-outline-variant/10 p-4 z-10">
                    <div class="max-w-2xl mx-auto flex gap-3">
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

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>

<script>
function santriApp() {
    return {
        showDetailModal: false,
        showEditModal: false,
        loading: false,
        selectedSantri: null,
        editData: {
            foto_preview: null
        },

        async openDetailModal(id) {
            console.log('Opening detail modal for santri ID:', id);
            this.loading = true;
            this.showDetailModal = true;
            try {
                const response = await fetch(`/admin/santri/${id}/modal-data`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                console.log('Data received:', data);
                this.selectedSantri = data.santri;
                this.selectedSantri.foto_url = data.foto_url;
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
                const response = await fetch(`/admin/santri/${id}/modal-data`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                this.selectedSantri = data.santri;
                this.selectedSantri.foto_url = data.foto_url;
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
        }
    }
}
</script>
</div>
@endsection
