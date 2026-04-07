@extends('layouts.app')

@section('header-title', 'Tambah Santri Baru')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('admin.santri.index') }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Data Santri</span>
        </a>
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Tambah Santri Baru</h2>
        <p class="text-on-surface-variant text-sm mt-1">Lengkapi data santri dengan benar.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.santri.store') }}" method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest p-8 rounded-xl shadow-sm space-y-6">
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
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">NIS <span class="text-error">*</span></label>
                    <input type="text" name="nis" required value="{{ old('nis') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('nis')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Email <span class="text-error">*</span></label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('tempat_lahir')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('tanggal_lahir')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">RFID Code</label>
                    <input type="text" name="rfid_code" value="{{ old('rfid_code') }}"
                           placeholder="Tap kartu RFID pada reader"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('rfid_code')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Alamat Lengkap</label>
                    <textarea name="alamat" rows="3"
                              class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all resize-none">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Data Orang Tua/Wali -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">family_restroom</span>
                <span>Data Orang Tua/Wali</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Wali</label>
                    <input type="text" name="nama_wali" value="{{ old('nama_wali') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('nama_wali')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">No HP Wali</label>
                    <input type="text" name="no_hp_wali" value="{{ old('no_hp_wali') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('no_hp_wali')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Data Sekolah -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">school</span>
                <span>Data Sekolah</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Asal Sekolah</label>
                    <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('asal_sekolah')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kelas</label>
                    <input type="text" name="kelas" value="{{ old('kelas') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('kelas')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Keamanan & Saldo -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">lock</span>
                <span>Keamanan & Saldo</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Password <span class="text-error">*</span></label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('password')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">PIN (6 digit) <span class="text-error">*</span></label>
                    <input type="text" name="pin" required maxlength="6" minlength="6" pattern="[0-9]{6}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="123456">
                    @error('pin')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Saldo Awal</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold">Rp</span>
                        <input type="number" name="saldo" value="{{ old('saldo', 0) }}" min="0"
                               class="w-full bg-surface-container-high border-none rounded-xl py-3 pl-12 pr-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    </div>
                    @error('saldo')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Foto Profil</label>
                    <input type="file" name="foto" accept="image/*"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('foto')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-surface-container">
            <a href="{{ route('admin.santri.index') }}" 
               class="px-6 py-3 bg-surface-container-high text-on-surface font-semibold rounded-xl hover:bg-surface-container-highest transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="bg-primary text-on-primary font-bold py-3 px-8 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">save</span>
                <span>Simpan Data Santri</span>
            </button>
        </div>
    </form>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
