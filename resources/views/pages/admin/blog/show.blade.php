@extends('layouts.app')

@section('header-title', 'Detail Blog')
@php $routePrefix = $routePrefix ?? 'admin.blog'; @endphp
@php
    $canManageBlog = $canManageBlog ?? true;
    $backRoute = $backRoute ?? route($routePrefix.'.index');
    $decodedContent = json_decode($blog->content, true);
    $contentBlocks = [];

    if (
        is_array($decodedContent)
        && ($decodedContent['type'] ?? null) === 'blocks'
        && is_array($decodedContent['blocks'] ?? null)
    ) {
        $contentBlocks = collect($decodedContent['blocks'])
            ->map(fn ($block) => [
                'type' => in_array($block['type'] ?? 'p', ['h2', 'h3', 'p', 'quote'], true) ? $block['type'] : 'p',
                'text' => trim((string) ($block['text'] ?? '')),
            ])
            ->filter(fn ($block) => $block['text'] !== '')
            ->values()
            ->all();
    }
@endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <a href="{{ $backRoute }}" class="text-primary hover:underline flex items-center gap-1 mb-4">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Blog</span>
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-headline text-2xl font-bold text-primary tracking-tight">{{ $blog->title }}</h2>
                <p class="text-on-surface-variant text-sm mt-1">
                    @if($blog->category)
                        <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-bold">{{ $blog->category }}</span>
                    @endif
                    @if($blog->author)
                        <span class="ml-2">oleh {{ $blog->author }}</span>
                    @endif
                </p>
            </div>
            @if($canManageBlog)
                <div class="flex gap-2">
                <a href="{{ route($routePrefix.'.edit', $blog) }}" 
                   class="bg-primary text-on-primary font-bold py-2 px-4 rounded-xl shadow-lg hover:shadow-primary/30 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    <span>Edit</span>
                </a>
                <form action="{{ route($routePrefix.'.toggle-publish', $blog) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="{{ $blog->is_published ? 'bg-yellow-600' : 'bg-green-600' }} text-white font-bold py-2 px-4 rounded-xl shadow-lg transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">{{ $blog->is_published ? 'unpublished' : 'publish' }}</span>
                        <span>{{ $blog->is_published ? 'Unpublish' : 'Publish' }}</span>
                    </button>
                </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Blog Content -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        @if($blog->thumbnail)
            <div class="w-full h-96 overflow-hidden">
                <img src="{{ $blog->thumbnail_url }}" alt="{{ $blog->title }}" class="w-full h-full object-cover">
            </div>
        @endif
        
        <div class="p-5 sm:p-6">
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
            <div class="bg-surface-container-high p-4 sm:p-5 rounded-xl mb-6">
                <h3 class="font-bold text-lg text-primary mb-2">Ringkasan</h3>
                <p class="text-on-surface">{{ $blog->excerpt }}</p>
            </div>

            <!-- Content -->
            <div class="prose prose-lg max-w-none">
                <h3 class="font-bold text-lg text-primary mb-4">Konten Lengkap</h3>
                <div class="space-y-5 text-on-surface leading-relaxed">
                    @if(! empty($contentBlocks))
                        @foreach($contentBlocks as $block)
                            @if($block['type'] === 'h2')
                                <h2 class="font-headline text-xl font-bold tracking-tight text-primary">{{ $block['text'] }}</h2>
                            @elseif($block['type'] === 'h3')
                                <h3 class="font-headline text-xl font-bold text-primary">{{ $block['text'] }}</h3>
                            @elseif($block['type'] === 'quote')
                                <blockquote class="border-l-4 border-primary/40 bg-primary/5 px-5 py-4 italic text-on-surface-variant rounded-r-xl">
                                    {{ $block['text'] }}
                                </blockquote>
                            @else
                                <p>{{ $block['text'] }}</p>
                            @endif
                        @endforeach
                    @else
                        {!! nl2br(e($blog->content)) !!}
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 pt-6 border-t border-surface-container flex gap-3">
                <a href="{{ $backRoute }}"
                   class="px-6 py-3 bg-surface-container-high text-on-surface rounded-xl font-bold hover:bg-surface-container transition-colors">
                    Kembali
                </a>
                @if($canManageBlog)
                    <a href="{{ route($routePrefix.'.edit', $blog) }}"
                       class="px-6 py-3 bg-primary text-on-primary rounded-xl font-bold hover:bg-primary-container transition-colors">
                        Edit Blog Ini
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
