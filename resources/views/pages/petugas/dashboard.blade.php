@extends('layouts.app')

@section('header-title', 'Dashboard Petugas')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div
    x-data="{
        showNews: false,
        selectedNews: {},
        openNews(news) {
            this.selectedNews = news;
            this.showNews = true;
            document.body.classList.add('overflow-hidden');
        },
        closeNews() {
            this.showNews = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
    @keydown.escape.window="closeNews()"
    class="space-y-6"
>
    <section class="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary via-primary to-primary-container text-white p-4 sm:p-5 md:p-9 shadow-xl shadow-primary/15">
        <div class="relative z-10 max-w-3xl">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary-fixed-dim">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h2 class="mt-3 font-headline text-lg md:text-2xl font-bold tracking-tight">Selamat berkhidmah,
                <div> {{ auth()->user()->name }}.</h2> </div>
            <p class="mt-3 text-sm md:text-base text-primary-fixed/80 leading-relaxed">"Berkhidmah dengan Ikhlas, Menjemput berkah tanpa batas."</p>
        </div>
        <div class="absolute -right-20 -top-24 w-72 h-72 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute right-8 bottom-6 hidden md:block opacity-20">
            <span class="material-symbols-outlined text-[120px]">mosque</span>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
        <section class=" py-3 xl:col-span-7 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-primary">Informasi Utama</p>
                    <h3 class="font-headline text-xl font-bold text-on-surface">Pengumuman</h3>
                </div>
                <span class="px-3 py-1.5 rounded-full bg-primary/10 text-primary text-xs font-bold">{{ $announcements->count() }} aktif</span>
            </div>

            <div class="space-y-3">
                @forelse($announcements as $announcement)
                    @php
                        $announcementTone = match($announcement->priority) {
                            'urgent' => 'border-error/30 bg-error/5',
                            'important' => 'border-tertiary/30 bg-tertiary/5',
                            default => 'border-primary/20 bg-surface-container-lowest',
                        };
                    @endphp
                    <article class="rounded-xl border {{ $announcementTone }} p-5 md:p-4 sm:p-5 shadow-sm">
                        <div class="flex gap-4">
                            <div class="w-11 h-11 flex-shrink-0 rounded-xl {{ $announcement->priority === 'urgent' ? 'bg-error text-white' : 'bg-primary/10 text-primary' }} flex items-center justify-center">
                                <span class="material-symbols-outlined">campaign</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h4 class="font-headline font-bold text-lg text-on-surface">{{ $announcement->title }}</h4>
                                    @if($announcement->priority !== 'normal')
                                        <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $announcement->priority === 'urgent' ? 'bg-error/10 text-error' : 'bg-tertiary/10 text-tertiary' }}">{{ $announcement->priority }}</span>
                                    @endif
                                </div>
                                <p class="text-sm leading-relaxed text-on-surface-variant whitespace-pre-line">{{ $announcement->content }}</p>
                                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-on-surface-variant">
                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">person</span>{{ $announcement->creator->name }}</span>
                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span>{{ $announcement->published_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-surface-container-lowest p-10 text-center shadow-sm">
                        <span class="material-symbols-outlined text-5xl text-primary/30">notifications_off</span>
                        <p class="mt-3 font-bold text-on-surface">Belum ada pengumuman</p>
                        <p class="text-sm text-on-surface-variant">Informasi terbaru dari admin akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="xl:col-span-5">
            <div class="rounded-xl bg-surface-container-lowest p-5 md:p-4 sm:p-5 shadow-sm xl:sticky xl:top-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-primary">Agenda Bersama</p>
                        <h3 class="font-headline text-xl font-bold text-on-surface">To Do List</h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined">checklist</span>
                    </div>
                </div>
                <div class="space-y-3">
                    @forelse($todos as $todo)
                        @php $task = $todo->dashboardContent; @endphp
                        <div class="group rounded-xl bg-surface-container-low p-4 border border-transparent hover:border-primary/20 transition-colors">
                            <div class="flex items-start gap-3">
                                <form action="{{ route('petugas.dashboard.todo.complete', $todo) }}" method="POST" onsubmit="return confirm('Tandai tugas ini selesai?')">
                                    @csrf @method('PATCH')
                                    <button class="mt-0.5 w-6 h-6 rounded-md border-2 {{ $task->priority === 'urgent' ? 'border-error text-error' : 'border-primary text-primary' }} flex-shrink-0 hover:bg-primary hover:text-white transition-colors" title="Tandai selesai"><span class="material-symbols-outlined text-sm">check</span></button>
                                </form>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-sm text-on-surface">{{ $task->title }}</p>
                                    <p class="mt-1 text-xs text-on-surface-variant leading-relaxed">{{ $task->summary ?: Str::limit($task->content, 120) }}</p>
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-[10px] font-bold text-primary">Lihat instruksi</summary>
                                        <p class="mt-2 text-xs leading-relaxed text-on-surface-variant whitespace-pre-line">{{ $task->content }}</p>
                                    </details>
                                    @if($task->due_date)
                                        <span class="mt-3 inline-flex items-center gap-1 text-[10px] font-bold {{ $task->due_date->isPast() ? 'text-error' : 'text-primary' }}">
                                            <span class="material-symbols-outlined text-sm">event</span>
                                            Tenggat {{ $task->due_date->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center">
                            <span class="material-symbols-outlined text-5xl text-primary/30">task_alt</span>
                            <p class="mt-3 text-sm text-on-surface-variant">Belum ada agenda bersama.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>

    <section class="space-y-5">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-primary">Cerita & Kegiatan</p>
            <h3 class="font-headline text-xl font-bold text-on-surface">Blog Pondok</h3>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 xl:gap-4">
            @forelse($news->take(3) as $item)
                <article class="flex min-w-0 flex-col overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex aspect-[16/9] min-h-36 items-start justify-between bg-linear-to-br from-primary/15 to-tertiary/20 bg-cover bg-center p-4 sm:p-5" @if($item->thumbnail_url) style="background-image: linear-gradient(rgba(0,77,76,.2),rgba(0,77,76,.5)), url('{{ $item->thumbnail_url }}')" @endif>
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white/85 text-primary shadow-sm sm:h-11 sm:w-11"><span class="material-symbols-outlined">newspaper</span></div>
                        @if($item->category)
                            <span class="max-w-[65%] truncate rounded-full bg-white/85 px-3 py-1.5 text-[10px] font-black text-primary">{{ $item->category }}</span>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-4 sm:p-5">
                        <h4 class="font-headline text-base font-bold leading-snug text-on-surface sm:text-lg">{{ $item->title }}</h4>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-on-surface-variant">{{ Str::limit($item->excerpt ?: strip_tags($item->content), 130) }}</p>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-primary">{{ $item->published_at?->translatedFormat('d M Y') }}</p>
                            <a
                                href="{{ route('petugas.blog.read', $item) }}"
                                class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-3 py-2 text-xs font-bold text-primary transition-colors hover:bg-primary/15"
                            >
                                <span>Baca lengkap</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="w-full rounded-xl bg-surface-container-lowest p-10 text-center shadow-sm">
                    <span class="material-symbols-outlined text-5xl text-primary/30">newspaper</span>
                    <p class="mt-3 text-sm text-on-surface-variant">Belum ada berita pondok terbaru.</p>
                </div>
            @endforelse
        </div>
    </section>

    <div x-show="showNews" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeNews()"></div>
        <div class="relative flex min-h-screen items-end justify-center p-3 sm:items-center sm:p-4">
            <article class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-surface p-5 shadow-2xl sm:rounded-xl md:p-5 sm:p-6" @click.stop>
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-widest text-primary">Blog Pondok</p>
                        <h3 class="mt-2 font-headline text-xl font-extrabold leading-tight text-on-surface md:text-3xl" x-text="selectedNews.title"></h3>
                    </div>
                    <button type="button" @click="closeNews()" class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl hover:bg-surface-container"><span class="material-symbols-outlined">close</span></button>
                </div>
                <p class="mt-3 text-xs text-on-surface-variant"><span x-text="selectedNews.author"></span> · <span x-text="selectedNews.date"></span></p>
                <template x-if="selectedNews.thumbnail_url"><img :src="selectedNews.thumbnail_url" class="mt-5 aspect-video w-full rounded-xl object-cover" alt=""></template>
                <div class="mt-6 text-sm leading-7 text-on-surface-variant md:text-base [&_*]:max-w-full [&_img]:rounded-xl [&_img]:my-4 [&_a]:font-bold [&_a]:text-primary" x-html="selectedNews.content || 'Konten blog belum tersedia.'"></div>
            </article>
        </div>
    </div>
</div>
@endsection
