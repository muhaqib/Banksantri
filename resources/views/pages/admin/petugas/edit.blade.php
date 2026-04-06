@extends('layouts.app')

@section('header-title', 'Edit Data Petugas')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('admin.petugas.index') }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Data Petugas</span>
        </a>
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Edit Data Petugas</h2>
        <p class="text-on-surface-variant text-sm mt-1">Update data petugas.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.petugas.update', $petugas) }}" method="POST" enctype="multipart/form-data" class="card space-y-6">
        @csrf
        @method('PUT')

        <!-- Section: Data Pribadi -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">person</span>
                <span>Data Pribadi</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Lengkap <span class="text-error">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $petugas->name) }}"
                           class="input-field">
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $petugas->nip) }}"
                           class="input-field" placeholder="Nomor Induk Pegawai">
                    @error('nip')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Jabatan <span class="text-error">*</span></label>
                    <select name="jabatan" required
                            class="input-field">
                        <option value="">Pilih Jabatan</option>
                        <option value="Kepala Unit" {{ old('jabatan', $petugas->jabatan) == 'Kepala Unit' ? 'selected' : '' }}>Kepala Unit</option>
                        <option value="Petugas Admin" {{ old('jabatan', $petugas->jabatan) == 'Petugas Admin' ? 'selected' : '' }}>Petugas Admin</option>
                        <option value="Petugas Transaksi" {{ old('jabatan', $petugas->jabatan) == 'Petugas Transaksi' ? 'selected' : '' }}>Petugas Transaksi</option>
                        <option value="Petugas Kantin" {{ old('jabatan', $petugas->jabatan) == 'Petugas Kantin' ? 'selected' : '' }}>Petugas Kantin</option>
                        <option value="Petugas Koperasi" {{ old('jabatan', $petugas->jabatan) == 'Petugas Koperasi' ? 'selected' : '' }}>Petugas Koperasi</option>
                        <option value="Supervisor" {{ old('jabatan', $petugas->jabatan) == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                    </select>
                    @error('jabatan')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Email <span class="text-error">*</span></label>
                    <input type="email" name="email" required value="{{ old('email', $petugas->email) }}"
                           class="input-field">
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">No HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $petugas->no_hp) }}"
                           class="input-field" placeholder="08xxxxxxxxxx">
                    @error('no_hp')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Alamat Lengkap</label>
                    <textarea name="alamat" rows="3"
                              class="input-field resize-none">{{ old('alamat', $petugas->alamat) }}</textarea>
                    @error('alamat')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Keamanan & Foto -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-filled">lock</span>
                <span>Keamanan & Foto</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Password Baru</label>
                    <input type="password" name="password" minlength="6"
                           class="input-field" placeholder="Kosongkan jika tidak diubah">
                    <p class="text-xs text-on-surface-variant mt-1">Kosongkan jika tidak ingin mengubah password</p>
                    @error('password')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Foto Profil</label>
                    @if($petugas->foto)
                        <div class="mb-2">
                            <img src="{{ Storage::url($petugas->foto) }}" alt="{{ $petugas->name }}" class="w-20 h-20 object-cover rounded-full border-2 border-outline-variant/10">
                        </div>
                    @endif
                    <input type="file" name="foto" accept="image/*"
                           class="input-field">
                    @error('foto')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-outline-variant/10">
            <a href="{{ route('admin.petugas.index') }}" 
               class="btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn-primary">
                <span class="material-symbols-filled">save</span>
                <span>Update Data Petugas</span>
            </button>
        </div>
    </form>
</div>
@endsection
