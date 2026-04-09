@extends('layouts.app')

@section('header-title', 'Tambah Petugas Baru')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('admin.petugas.index') }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Data Petugas</span>
        </a>
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Tambah Petugas Baru</h2>
        <p class="text-on-surface-variant text-sm mt-1">Lengkapi data petugas dengan benar.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.petugas.store') }}" method="POST" enctype="multipart/form-data" class="card space-y-6">
        @csrf

        <!-- Section: Data Pribadi -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">person</span>
                <span>Data Pribadi</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Lengkap <span class="text-error">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="input-field">
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip') }}"
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
                        <option value="Kepala Unit" {{ old('jabatan') == 'Kepala Unit' ? 'selected' : '' }}>Kepala Unit</option>
                        <option value="Staff Pengurus" {{ old('jabatan') == 'Staff Pengurus' ? 'selected' : '' }}>Staff Pengurus</option>
                        <option value="Petugas Laundry" {{ old('jabatan') == 'Petugas Laundry' ? 'selected' : '' }}>Petugas Laundry</option>
                        <option value="Petugas Syirkah" {{ old('jabatan') == 'Petugas Syirkah' ? 'selected' : '' }}>Petugas Syirkah</option>
                        <option value="Koperasi Kitab" {{ old('jabatan') == 'Koperasi Kitab' ? 'selected' : '' }}>Koperasi Kitab</option>
                        <option value="Petugas Mart" {{ old('jabatan') == 'Petugas Mart' ? 'selected' : '' }}>Petugas Mart</option>
                    </select>
                    @error('jabatan')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Email <span class="text-error">*</span></label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="input-field">
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">No HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                           class="input-field" placeholder="08xxxxxxxxxx">
                    @error('no_hp')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Alamat Lengkap</label>
                    <textarea name="alamat" rows="3"
                              class="input-field resize-none">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Keamanan & Foto -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">lock</span>
                <span>Keamanan & Foto</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Password <span class="text-error">*</span></label>
                    <input type="password" name="password" required minlength="6"
                           class="input-field">
                    @error('password')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Foto Profil</label>
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
                <span class="material-symbols-outlined">save</span>
                <span>Simpan Data Petugas</span>
            </button>
        </div>
    </form>
</div>
@endsection
