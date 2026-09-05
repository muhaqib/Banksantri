@extends('layouts.app')

@section('title', 'Sesi Absensi RFID')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-on-surface font-headline">Sesi Absensi RFID</h2>
        <a href="{{ route('admin.attendance-sessions.create') }}" class="btn-primary">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Buat Sesi Baru
        </a>
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
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-on-surface-variant border-collapse">
                <thead class="text-xs text-on-surface uppercase bg-surface-container-low">
                    <tr>
                        <th class="px-6 py-4">Judul Sesi</th>
                        <th class="px-6 py-4">Waktu Mulai</th>
                        <th class="px-6 py-4">Waktu Selesai</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Total Santri</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr class="bg-surface even:bg-surface-container-low hover:bg-surface-container-high transition-colors">
                            <td class="px-6 py-4 font-bold text-on-surface">
                                {{ $session->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $session->start_time->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $session->end_time->format('H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($session->isCompleted())
                                    <span class="bg-surface-container-highest text-on-surface-variant text-xs font-bold px-2.5 py-1 rounded-md">Selesai</span>
                                @elseif (now('Asia/Jakarta')->isBefore($session->start_time))
                                    <span class="bg-tertiary-container/20 text-tertiary text-xs font-bold px-2.5 py-1 rounded-md">Belum Dimulai</span>
                                @else
                                    <span class="bg-primary-fixed text-on-primary-fixed-variant text-xs font-bold px-2.5 py-1 rounded-md">Berlangsung</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-on-surface">
                                {{ $session->records_count }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if (!$session->isCompleted())
                                        <a href="{{ route('admin.attendance-sessions.dashboard', $session) }}" class="bg-primary text-on-primary px-3 py-1.5 text-xs font-semibold rounded-md hover:bg-primary-container transition-colors">
                                            Scan RFID
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.attendance-sessions.show', $session) }}" class="bg-surface-container text-on-surface px-3 py-1.5 text-xs font-semibold rounded-md hover:bg-surface-container-high transition-colors">
                                        Lihat Data
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">
                                Belum ada sesi absensi yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sessions->hasPages())
            <div class="p-4 border-t border-outline-variant/10">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
