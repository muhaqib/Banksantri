@extends('layouts.app')

@section('header-title', 'Tambah Blog Baru')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('admin.blog.index') }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Blog</span>
        </a>
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Tambah Blog Baru</h2>
        <p class="text-on-surface-variant text-sm mt-1">Buat artikel atau berita baru untuk website.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest p-8 rounded-xl shadow-sm space-y-6">
        @csrf

        <!-- Section: Informasi Dasar -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">info</span>
                <span>Informasi Dasar</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Judul Blog <span class="text-error">*</span></label>
                    <input type="text" name="title" required value="{{ old('title') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Masukkan judul artikel yang menarik">
                    @error('title')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Slug (URL Friendly)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="kosongkan untuk auto-generate dari judul">
                    <p class="text-xs text-on-surface-variant mt-1">Akan diisi otomatis dari judul jika dikosongkan</p>
                    @error('slug')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Kategori</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Contoh: Pendidikan, Kegiatan, Berita">
                    @error('category')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Penulis</label>
                    <input type="text" name="author" value="{{ old('author', auth()->user()->name) }}"
                           class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all"
                           placeholder="Nama penulis">
                    @error('author')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Thumbnail -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">image</span>
                <span>Thumbnail Gambar</span>
            </h3>
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Gambar Thumbnail (Opsional)</label>
                <input type="file" name="thumbnail" accept="image/*" id="thumbnail-input"
                       class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all">
                <p class="text-xs text-on-surface-variant mt-1">Format: JPG, PNG, maksimal 2MB. Rasio yang disarankan: 16:9</p>
                @error('thumbnail')
                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
                <div id="thumbnail-preview" class="mt-4 hidden">
                    <img id="preview-image" src="" alt="Preview" class="max-w-full h-auto rounded-xl shadow-lg">
                </div>
            </div>
        </div>

        <!-- Section: Konten -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">article</span>
                <span>Konten Artikel</span>
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Ringkasan (Excerpt) <span class="text-error">*</span></label>
                    <textarea name="excerpt" required rows="3"
                              class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all resize-none"
                              placeholder="Tulis ringkasan singkat artikel (2-3 kalimat)">{{ old('excerpt') }}</textarea>
                    <p class="text-xs text-on-surface-variant mt-1">Akan ditampilkan di halaman listing blog</p>
                    @error('excerpt')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Konten Lengkap <span class="text-error">*</span></label>
                    <textarea name="content" required rows="15"
                              class="w-full bg-surface-container-high border-none rounded-xl py-3 px-4 text-on-surface focus:bg-surface-container-highest focus:ring-0 transition-all font-mono text-sm"
                              placeholder="Tulis konten artikel lengkap di sini. Anda bisa menggunakan tag HTML untuk formatting.">{{ old('content') }}</textarea>
                    <p class="text-xs text-on-surface-variant mt-1">Gunakan tag HTML untuk formatting: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, dll</p>
                    @error('content')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Publikasi -->
        <div>
            <h3 class="font-headline font-bold text-lg text-primary mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">publish</span>
                <span>Publikasi</span>
            </h3>
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}
                           class="w-5 h-5 rounded text-primary focus:ring-primary">
                    <span class="text-sm font-semibold text-on-surface">Langsung Publikasikan</span>
                </label>
                <p class="text-xs text-on-surface-variant mt-2">Jika dicentang, blog akan langsung dipublikasikan. Jika tidak, blog akan disimpan sebagai draft.</p>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4 pt-4 border-t border-surface-container">
            <a href="{{ route('admin.blog.index') }}"
               class="flex-1 px-6 py-3 bg-surface-container-high text-on-surface rounded-xl font-bold hover:bg-surface-container transition-colors text-center">
                Batal
            </a>
            <button type="submit"
                    class="flex-1 px-6 py-3 bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-colors">
                Simpan Blog
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Thumbnail preview
document.getElementById('thumbnail-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('thumbnail-preview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
