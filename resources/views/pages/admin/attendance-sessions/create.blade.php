@extends('layouts.app')

@section('title', 'Buat Sesi Absensi')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.attendance-sessions.index') }}" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-surface-container-low text-on-surface-variant transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="text-2xl font-bold text-on-surface font-headline">Buat Sesi Absensi Baru</h2>
    </div>

    <div class="card">
        <form action="{{ route('admin.attendance-sessions.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="title" class="block mb-2 text-sm font-semibold text-on-surface">Judul Absensi</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" class="input-field" placeholder="Contoh: Apel Pagi / Pengajian Malam" required>
                @error('title')
                    <p class="mt-2 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="start_time" class="block mb-2 text-sm font-semibold text-on-surface">Waktu Mulai</label>
                    <input type="datetime-local" id="start_time" name="start_time" value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}" class="input-field" required>
                    @error('start_time')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block mb-2 text-sm font-semibold text-on-surface">Waktu Selesai (Batas Hadir)</label>
                    <input type="datetime-local" id="end_time" name="end_time" value="{{ old('end_time', now()->addHour()->format('Y-m-d\TH:i')) }}" class="input-field" required>
                    @error('end_time')
                        <p class="mt-2 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t border-outline-variant/10">
                <button type="submit" class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Sesi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
