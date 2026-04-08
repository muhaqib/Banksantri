@extends('layouts.app')

@section('header-title', 'Detail Blog')
@php $activeRole = 'admin'; @endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ route('admin.blog.index') }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Blog</span>
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">{{ $blog->title }}</h2>
                <p class="text-on-surface-variant text-sm mt-1">
                    @if($blog->category)
                        <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-bold">{{ $blog->category }}</span>
                    @endif
                    @if($blog->author)
                        <span class="ml-2">oleh {{ $blog->author }}</span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.blog.edit', $blog) }}" 
                   class="bg-primary text-on-primary font-bold py-2 px-4 rounded-xl shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    <span>Edit</span>
                </a>
                <form action="{{ route('admin.blog.toggle-publish', $blog) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="{{ $blog->is_published ? 'bg-yellow-600' : 'bg-green-600' }} text-white font-bold py-2 px-4 rounded-xl shadow-lg transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">{{ $blog->is_published ? 'unpublished' : 'publish' }}</span>
                        <span>{{ $blog->is_published ? 'Unpublish' : 'Publish' }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Blog Content -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        @if($blog->thumbnail)
            <div class="w-full h-96 overflow-hidden">
                <img src="{{ Storage::url($blog->thumbnail) }}" alt="{{ $blog->title }}" class="w-full h-full object-cover">
            </div>
        @endif
        
        <div class="p-8">
            <!-- Meta Info -->
            <div class="flex items-center gap-4 mb-6 text-sm text-on-surface-variant">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">calendar_today</span>
                    <span>{{ $blog->created_at->format('d F Y') }}</span>
                </div>
                @if($blog->author)
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">person</span>
                        <span>{{ $blog->author }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-1">
                    @if($blog->is_published)
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                            Published
                        </span>
                    @else
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">
                            Draft
                        </span>
                    @endif
                </div>
            </div>

            <!-- Excerpt -->
            <div class="bg-surface-container-high p-6 rounded-xl mb-6">
                <h3 class="font-bold text-lg text-primary mb-2">Ringkasan</h3>
                <p class="text-on-surface">{{ $blog->excerpt }}</p>
            </div>

            <!-- Content -->
            <div class="prose prose-lg max-w-none">
                <h3 class="font-bold text-lg text-primary mb-4">Konten Lengkap</h3>
                <div class="text-on-surface leading-relaxed">
                    {!! nl2br(e($blog->content)) !!}
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 pt-6 border-t border-surface-container flex gap-3">
                <a href="{{ route('admin.blog.index') }}"
                   class="px-6 py-3 bg-surface-container-high text-on-surface rounded-xl font-bold hover:bg-surface-container transition-colors">
                    Kembali ke Daftar
                </a>
                <a href="{{ route('admin.blog.edit', $blog) }}"
                   class="px-6 py-3 bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-colors">
                    Edit Blog Ini
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
