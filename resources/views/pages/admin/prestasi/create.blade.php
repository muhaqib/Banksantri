@extends('layouts.app')

@section('header-title', 'Tambah Prestasi Santri')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('admin.prestasi.index') }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Prestasi Santri</span>
        </a>
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Tambah Prestasi Santri</h2>
        <p class="text-on-surface-variant text-sm mt-1">Lengkapi data prestasi hafalan kitab santri.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.prestasi.store') }}" method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest p-8 rounded-xl shadow-sm space-y-6">
        @csrf

        <!-- Section: Pilih Santri -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">person</span>
                <span>Pilih Santri</span>
            </h3>
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Santri <span class="text-error">*</span></label>
                <select name="santri_id" required
                        class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    <option value="">-- Pilih Santri --</option>
                    @foreach($santriList as $santri)
                        <option value="{{ $santri->id }}" {{ old('santri_id') == $santri->id ? 'selected' : '' }}>
                            {{ $santri->name }} - {{ $santri->nis }}
                        </option>
                    @endforeach
                </select>
                @error('santri_id')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Section: Informasi Kitab -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">menu_book</span>
                <span>Informasi Kitab</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nama Kitab <span class="text-error">*</span></label>
                    <input type="text" name="nama_kitab" required value="{{ old('nama_kitab') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Contoh: Safinatunaja, Taqrib, Arba'in Nawawi">
                    @error('nama_kitab')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kategori</label>
                    <select name="kategori"
                            class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="hafalan" {{ old('kategori') == 'hafalan' ? 'selected' : '' }}>Hafalan</option>
                        <option value="tajwid" {{ old('kategori') == 'tajwid' ? 'selected' : '' }}>Tajwid</option>
                        <option value="qiraat" {{ old('kategori') == 'qiraat' ? 'selected' : '' }}>Qira'at</option>
                        <option value="hadits" {{ old('kategori') == 'hadits' ? 'selected' : '' }}>Hadits</option>
                        <option value="fiqih" {{ old('kategori') == 'fiqih' ? 'selected' : '' }}>Fiqih</option>
                        <option value="bahasa" {{ old('kategori') == 'bahasa' ? 'selected' : '' }}>Bahasa Arab</option>
                    </select>
                    @error('kategori')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Status <span class="text-error">*</span></label>
                    <select name="status" required
                            class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                        <option value="belum_dihafal" {{ old('status') == 'belum_dihafal' ? 'selected' : '' }}>Belum Dihafal</option>
                        <option value="sedang_dihafal" {{ old('status') == 'sedang_dihafal' ? 'selected' : '' }}>Sedang Dihafal</option>
                        <option value="telah_dihafalkan" {{ old('status') == 'telah_dihafalkan' ? 'selected' : '' }}>Telah Dihafalkan</option>
                    </select>
                    @error('status')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Foto Kitab (Opsional)</label>
                    <input type="file" name="foto_kitab" accept="image/*"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    <p class="text-xs text-on-surface-variant mt-1">Format: JPG, PNG, maksimal 2MB</p>
                    @error('foto_kitab')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Penilaian -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">military_tech</span>
                <span>Penilaian</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Nilai (Huruf)</label>
                    <input type="text" name="nilai" value="{{ old('nilai') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Contoh: Mumtaz, Jayyid, Maqbul">
                    @error('nilai')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Skor (Angka)</label>
                    <input type="number" name="skor" min="0" max="100" value="{{ old('skor') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="0-100">
                    @error('skor')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Poin</label>
                    <input type="number" name="poin" min="0" value="{{ old('poin', 0) }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Poin prestasi">
                    @error('poin')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Tanggal & Pembimbing -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">event</span>
                <span>Tanggal & Pembimbing</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                    @error('tanggal_selesai')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Bulan & Tahun (Hijriah)</label>
                    <input type="text" name="bulan_tahun_selesai" value="{{ old('bulan_tahun_selesai') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Contoh: Safar 1447 H">
                    @error('bulan_tahun_selesai')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Ustadz Pembimbing</label>
                    <input type="text" name="ustadz_pembimbing" value="{{ old('ustadz_pembimbing') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Nama ustadz yang menguji">
                    @error('ustadz_pembimbing')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Catatan & Keterangan -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">note</span>
                <span>Catatan & Keterangan</span>
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Keterangan (Opsional)</label>
                    <textarea name="keterangan" rows="2"
                              class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all resize-none"
                              placeholder="Keterangan tambahan tentang prestasi...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Catatan Ustadz (Opsional)</label>
                    <textarea name="catatan_ustadz" rows="3"
                              class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all resize-none"
                              placeholder="Catatan atau feedback dari ustadz...">{{ old('catatan_ustadz') }}</textarea>
                    @error('catatan_ustadz')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Tags (Opsional)</label>
                    <input type="text" name="tags" value="{{ old('tags') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Contoh: #MakhrajFasih,#TajwidPresisi (pisahkan dengan koma)">
                    <p class="text-xs text-on-surface-variant mt-1">Pisahkan tags dengan tanda koma (,)</p>
                    @error('tags')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4 pt-4 border-t border-surface-container">
            <a href="{{ route('admin.prestasi.index') }}" 
               class="flex-1 px-6 py-3 bg-surface-container-high text-on-surface rounded-xl font-bold hover:bg-surface-container transition-colors text-center">
                Batal
            </a>
            <button type="submit" 
                    class="flex-1 px-6 py-3 bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-colors">
                Simpan Prestasi
            </button>
        </div>
    </form>
</div>
@endsection
