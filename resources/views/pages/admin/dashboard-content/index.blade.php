@extends('layouts.app')

@section('header-title', 'Konten Dashboard')
@php $activeRole = 'admin'; @endphp

@section('content')
<div x-data="{ showCreate: false, showEdit: false, createType: 'announcement', selected: {}, todoScope: 'all' }" class="space-y-6">
    <section class="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary to-primary-container text-white p-4 sm:p-5 md:p-5 sm:p-6 shadow-xl shadow-primary/15">
        <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary-fixed-dim">Pusat Informasi Petugas</p>
                <h2 class="mt-2 font-headline text-3xl md:text-4xl font-extrabold">Kelola Konten Dashboard</h2>
                <p class="mt-2 max-w-2xl text-sm text-primary-fixed/80">Terbitkan pengumuman dan agenda bersama yang akan dibaca seluruh petugas.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button @click="createType = 'announcement'; showCreate = true" class="bg-white text-primary px-4 py-3 rounded-xl font-bold shadow-lg flex items-center justify-center gap-2"><span class="material-symbols-outlined">campaign</span>Pengumuman</button>
                <button @click="createType = 'todo'; todoScope = 'all'; showCreate = true" class="bg-white text-primary px-4 py-3 rounded-xl font-bold shadow-lg flex items-center justify-center gap-2"><span class="material-symbols-outlined">checklist</span>To-do</button>
            </div>
        </div>
        <div class="absolute -right-16 -top-20 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
    </section>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.dashboard-content.index') }}" class="px-4 py-2 rounded-full text-sm font-bold {{ !request('type') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface' }}">Semua</a>
        @foreach($types as $key => $label)
            <a href="{{ route('admin.dashboard-content.index', ['type' => $key]) }}" class="px-4 py-2 rounded-full text-sm font-bold {{ request('type') === $key ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface' }}">{{ $label }}</a>
        @endforeach
    </div>

    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($contents as $content)
            @php
                $icon = match($content->type) { 'announcement' => 'campaign', default => 'checklist' };
                $tone = match($content->type) { 'announcement' => 'bg-primary/10 text-primary', default => 'bg-tertiary/10 text-tertiary' };
            @endphp
            <article class="rounded-xl bg-surface-container-lowest p-5 shadow-sm border border-outline-variant/10 flex flex-col">
                <div class="flex items-start justify-between gap-3">
                    <div class="w-11 h-11 rounded-xl {{ $tone }} flex items-center justify-center"><span class="material-symbols-outlined">{{ $icon }}</span></div>
                    <div class="flex gap-2">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $content->is_published ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $content->is_published ? 'Terbit' : 'Draft' }}</span>
                        @if($content->priority !== 'normal')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $content->priority === 'urgent' ? 'bg-error/10 text-error' : 'bg-tertiary/10 text-tertiary' }}">{{ ucfirst($content->priority) }}</span>
                        @endif
                    </div>
                </div>
                <p class="mt-4 text-[10px] font-black uppercase tracking-widest text-primary">{{ $content->type_label }}</p>
                <h3 class="mt-1 font-headline font-bold text-lg text-on-surface">{{ $content->title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-on-surface-variant flex-1">{{ $content->summary ?: Str::limit($content->content, 150) }}</p>
                @if($content->type === 'todo')
                    @php
                        $completedCount = $content->assignments->where('is_completed', true)->count();
                        $assignmentCount = $content->assignments->count();
                        $progress = $assignmentCount ? round(($completedCount / $assignmentCount) * 100) : 0;
                    @endphp
                    <div class="mt-4 rounded-xl bg-surface-container-low p-3">
                        <div class="flex justify-between text-xs font-bold"><span>Progres</span><span>{{ $completedCount }}/{{ $assignmentCount }} selesai</span></div>
                        <div class="mt-2 h-2 rounded-full bg-surface-container-high overflow-hidden"><div class="h-full bg-primary rounded-full" style="width: {{ $progress }}%"></div></div>
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-bold text-primary">Lihat status petugas</summary>
                            <div class="mt-2 space-y-1 max-h-32 overflow-y-auto">
                                @foreach($content->assignments as $assignment)
                                    <div class="flex items-center justify-between text-xs"><span>{{ $assignment->user->name }}</span><span class="{{ $assignment->is_completed ? 'text-primary' : 'text-on-surface-variant' }}">{{ $assignment->is_completed ? 'Selesai '.$assignment->completed_at?->format('d/m H:i') : 'Belum selesai' }}</span></div>
                                @endforeach
                            </div>
                        </details>
                    </div>
                @endif
                <div class="mt-5 pt-4 border-t border-outline-variant/10 flex items-center justify-between">
                    <span class="text-[10px] text-on-surface-variant">{{ $content->created_at->translatedFormat('d M Y') }}</span>
                    <div class="flex items-center gap-1">
                        <button @click="selected = {{ Js::from([
                            "id" => $content->id,
                            "type" => $content->type,
                            "title" => $content->title,
                            "summary" => $content->summary,
                            "thumbnail_url" => $content->thumbnail_url,
                            "content" => $content->content,
                            "priority" => $content->priority,
                            "event_date" => $content->event_date?->format("Y-m-d"),
                            "due_date" => $content->due_date?->format("Y-m-d"),
                            "is_published" => $content->is_published ? "1" : "0",
                            "assignment_scope" => $content->assign_to_all ? "all" : "selected",
                            "assignee_ids" => $content->assignments->pluck("user_id")->values(),
                        ]) }}; showEdit = true" class="p-2 rounded-lg text-primary hover:bg-primary/10"><span class="material-symbols-outlined text-sm">edit</span></button>
                        <form action="{{ route('admin.dashboard-content.destroy', $content) }}" method="POST" onsubmit="return confirm('Hapus konten ini?')">
                            @csrf @method('DELETE')
                            <button class="p-2 rounded-lg text-error hover:bg-error/10"><span class="material-symbols-outlined text-sm">delete</span></button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3 rounded-xl bg-surface-container-lowest p-12 text-center shadow-sm">
                <span class="material-symbols-outlined text-6xl text-primary/30">dynamic_feed</span>
                <h3 class="mt-3 font-headline font-bold text-xl">Belum ada konten dashboard</h3>
                <p class="text-sm text-on-surface-variant">Tambahkan informasi pertama untuk petugas.</p>
            </div>
        @endforelse
    </section>

    @if($contents->hasPages())
        <div class="rounded-xl bg-surface-container-lowest p-4">{{ $contents->links() }}</div>
    @endif

    <div x-show="showCreate" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showCreate = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-3xl rounded-xl bg-surface p-4 sm:p-5 md:p-5 sm:p-6 shadow-2xl" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <div><p class="text-xs font-bold uppercase tracking-widest text-primary">Konten Baru</p><h3 class="font-headline text-xl font-bold" x-text="createType === 'announcement' ? 'Tambah Pengumuman' : 'Tambah To Do List'"></h3></div>
                    <button @click="showCreate = false" class="p-2 rounded-xl hover:bg-surface-container"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('admin.dashboard-content.store') }}" method="POST">
                    @csrf
                    <template x-if="createType === 'announcement'"><div>@include('pages.admin.dashboard-content.forms.announcement')</div></template>
                    <template x-if="createType === 'todo'"><div>@include('pages.admin.dashboard-content.forms.todo')</div></template>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showEdit" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showEdit = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-3xl rounded-xl bg-surface p-4 sm:p-5 md:p-5 sm:p-6 shadow-2xl" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <div><p class="text-xs font-bold uppercase tracking-widest text-primary">Edit Konten</p><h3 class="font-headline text-xl font-bold" x-text="selected.title"></h3></div>
                    <button @click="showEdit = false" class="p-2 rounded-xl hover:bg-surface-container"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form :action="`/admin/dashboard-content/${selected.id}`" method="POST">
                    @csrf @method('PUT')
                    <template x-if="selected.type === 'announcement'"><div>@include('pages.admin.dashboard-content.forms.announcement', ['editing' => true])</div></template>
                    <template x-if="selected.type === 'todo'"><div>@include('pages.admin.dashboard-content.forms.todo', ['editing' => true])</div></template>
                </form>
            </div>
        </div>
    </div>
</div>
<style>.form-label{display:block;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;color:var(--color-on-surface-variant)}</style>
@endsection
