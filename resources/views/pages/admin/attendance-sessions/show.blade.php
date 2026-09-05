@extends('layouts.app')

@section('title', 'Hasil Sesi Absensi: ' . $session->title)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.attendance-sessions.index') }}" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-surface-container-low text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold font-headline text-on-surface">{{ $session->title }}</h2>
                <p class="text-sm font-medium text-on-surface-variant mt-1">
                    {{ $session->start_time->format('d M Y') }} &bull; {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
                    @if ($session->isCompleted())
                        &bull; <span class="font-bold text-primary">Selesai pada {{ $session->completed_at->format('H:i') }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.attendance-sessions.export', $session) }}" class="btn-secondary">
                <span class="material-symbols-outlined text-[18px] text-primary">download</span>
                Export Excel
            </a>
            
            @if (!$session->isCompleted())
                <form action="{{ route('admin.attendance-sessions.finish', $session) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan sesi ini? Pemindaian akan ditutup.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-error">
                        <span class="material-symbols-outlined text-[18px]">stop_circle</span>
                        Selesai Pengabsenan
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm rounded-lg bg-primary-fixed text-on-primary-fixed-variant">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="p-4 mb-4 text-sm rounded-lg bg-error-container text-on-error-container">
            {{ session('error') }}
        </div>
    @endif

    <div class="card p-0 sm:p-0 overflow-hidden">
        <div class="p-6 bg-surface-container-low border-b border-outline-variant/10">
            <form method="GET" class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau UID RFID..." class="input-field">
                </div>
                <div class="w-full md:w-48">
                    <label class="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Status</label>
                    <select name="status" class="input-field">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                </div>
                <div class="w-full md:w-48">
                    <label class="block text-xs font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Kamar</label>
                    <select name="kamar" class="input-field">
                        <option value="">Semua Kamar</option>
                        @foreach ($kamarList as $k)
                            <option value="{{ $k }}" {{ request('kamar') === $k ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $k)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1 md:flex-none">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'kamar', 'kelas']))
                        <a href="{{ route('admin.attendance-sessions.show', $session) }}" class="btn-secondary flex-1 md:flex-none">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-on-surface-variant">
                <thead class="text-xs text-on-surface uppercase bg-surface-container-low border-b border-outline-variant/10">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">Kamar / Kelas</th>
                        <th class="px-6 py-4">Waktu Kedatangan</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $index => $record)
                        <tr class="bg-surface even:bg-surface-container-low hover:bg-surface-container-high transition-colors border-b border-outline-variant/5 last:border-0">
                            <td class="px-6 py-4 font-medium">{{ $records->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <img class="w-10 h-10 rounded-full object-cover border border-outline-variant/20" src="{{ $record->santri->foto ? \Illuminate\Support\Facades\Storage::url($record->santri->foto) : '/images/default-avatar.png' }}" alt="">
                                    <div>
                                        <div class="font-bold text-on-surface">{{ $record->santri->name }}</div>
                                        <div class="text-xs text-on-surface-variant">UID: <span class="font-mono">{{ $record->santri->rfid_code ?? '-' }}</span></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-on-surface">{{ ucwords(str_replace('_', ' ', $record->santri->kamarSantri?->kamar ?? $record->santri->kamar_terakhir ?? '-')) }}</div>
                                <div class="text-xs text-on-surface-variant">{{ $record->santri->kelas ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-on-surface">
                                {{ $record->scanned_at->format('H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($record->status === 'hadir')
                                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">Hadir</span>
                                @else
                                    <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">Terlambat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[48px] text-outline-variant/50 mb-2 block">history</span>
                                Tidak ada data absensi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($records->hasPages())
            <div class="p-4 bg-surface border-t border-outline-variant/10">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
