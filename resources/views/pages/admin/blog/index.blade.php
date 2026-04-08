@extends('layouts.app')

@section('header-title', 'Blog Management')
@php $activeRole = 'admin'; @endphp

@section('content')
<div>
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Blog Management</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola artikel dan berita untuk website.</p>
        </div>
        <a href="{{ route('admin.blog.create') }}" class="bg-primary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">add</span>
            <span>Tambah Blog</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">article</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Blog</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $blogs->total() }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-green-600 text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <p class="text-xs text-on-surface-variant font-medium">Dipublikasikan</p>
            </div>
            <p class="text-3xl font-bold text-green-600">{{ $blogs->getCollection()->where('is_published', true)->count() }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlook text-tertiary text-sm" style="font-variation-settings: 'FILL' 1;">draft</span>
                <p class="text-xs text-on-surface-variant font-medium">Draft</p>
            </div>
            <p class="text-3xl font-bold text-tertiary">{{ $blogs->getCollection()->where('is_published', false)->count() }}</p>
        </div>
    </div>

    <!-- Blog Table -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Daftar Blog</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Thumbnail</th>
                        <th class="px-6 py-4">Judul</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Penulis</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($blogs as $blog)
                        <tr class="hover:bg-surface transition-colors group">
                            <td class="px-6 py-4">
                                @if($blog->thumbnail)
                                    <img src="{{ Storage::url($blog->thumbnail) }}" alt="{{ $blog->title }}" class="w-16 h-12 object-cover rounded-lg">
                                @else
                                    <div class="w-16 h-12 bg-surface-container rounded-lg flex items-center justify-center">
                                        <span class="material-symbols-outlined text-on-surface-variant/50">image</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-on-surface">{{ $blog->title }}</div>
                                <div class="text-xs text-on-surface-variant mt-1">{{ Str::limit($blog->excerpt, 60) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($blog->category)
                                    <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $blog->category }}
                                    </span>
                                @else
                                    <span class="text-on-surface-variant">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                {{ $blog->author ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($blog->is_published)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                        Published
                                    </span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                {{ $blog->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.blog.show', $blog) }}" 
                                       class="p-2 rounded-lg hover:bg-surface-container-high transition-colors"
                                       title="Lihat">
                                        <span class="material-symbols-outlined text-base text-on-surface-variant">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.blog.edit', $blog) }}" 
                                       class="p-2 rounded-lg hover:bg-surface-container-high transition-colors"
                                       title="Edit">
                                        <span class="material-symbols-outlined text-base text-primary">edit</span>
                                    </a>
                                    <form action="{{ route('admin.blog.toggle-publish', $blog) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="p-2 rounded-lg hover:bg-surface-container-high transition-colors"
                                                title="{{ $blog->is_published ? 'Jadikan Draft' : 'Publikasikan' }}">
                                            <span class="material-symbols-outlined text-base {{ $blog->is_published ? 'text-yellow-600' : 'text-green-600' }}">
                                                {{ $blog->is_published ? 'unpublished' : 'publish' }}
                                            </span>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.blog.destroy', $blog) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus blog ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 rounded-lg hover:bg-red-50 transition-colors"
                                                title="Hapus">
                                            <span class="material-symbols-outlined text-base text-red-600">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-6xl mb-4 opacity-50">article</span>
                                <p class="text-lg font-semibold">Belum ada blog post</p>
                                <p class="text-sm mt-2">Mulai tambahkan artikel pertama Anda</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($blogs->hasPages())
            <div class="p-6 border-t border-surface-container">
                {{ $blogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
