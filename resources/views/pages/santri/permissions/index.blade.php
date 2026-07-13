@extends('layouts.santri')

@section('title', 'Riwayat Perizinan')

@section('content')
<div x-data="permissionHistory()" class="pb-24">
    <header class="w-full pt-12 pb-6 px-5 sticky top-0 z-40 bg-surface/80 backdrop-blur-md">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('santri.home') }}" class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center transition-colors shrink-0">
                    <span class="material-symbols-outlined text-primary">arrow_back</span>
                </a>
                <div class="min-w-0">
                    <h1 class="font-headline font-bold text-xl text-primary truncate">Riwayat Perizinan</h1>
                    <p class="text-xs text-on-surface-variant">{{ \Carbon\Carbon::create($selectedYear, $selectedMonth)->translatedFormat('F Y') }}</p>
                </div>
            </div>
            <button type="button" @click="showFilter = !showFilter" class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-primary">filter_list</span>
            </button>
        </div>
    </header>

    <main class="px-5 space-y-5">
        <form x-show="showFilter" x-cloak method="GET" action="{{ route('santri.permissions.index') }}" class="bg-surface-container-lowest rounded-xl p-4 shadow-sm">
            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant">Filter Bulan</label>
            <div class="mt-3 flex gap-2">
                <input type="month" name="month_picker" value="{{ $monthPicker }}" class="input-field flex-1">
                <button class="w-12 h-12 rounded-xl bg-primary text-on-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </div>
        </form>

        <section class="bg-surface-container-low rounded-[1.75rem] p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary">Total Izin</p>
                    <p class="mt-1 font-headline text-2xl font-bold text-on-surface">{{ $permissions->total() }}</p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-surface-container-lowest flex items-center justify-center text-primary shadow-sm">
                    <span class="material-symbols-outlined text-3xl">handshake</span>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            @forelse($permissions as $permission)
                @php
                    $permissionDetail = [
                        'number' => $permission->permission_number,
                        'title' => \Illuminate\Support\Str::limit($permission->reason, 36),
                        'reason' => $permission->reason,
                        'notes' => $permission->notes ?: '-',
                        'date' => $permission->start_date->format('d-m-Y').' - '.$permission->end_date->format('d-m-Y'),
                        'approved_by' => $permission->approved_by ?: '-',
                        'created_by' => $permission->creator?->name ?: '-',
                        'status' => $permission->is_active ? 'Aktif' : ($permission->end_date->isPast() ? 'Selesai' : 'Akan Datang'),
                    ];
                @endphp

                <div class="bg-surface-container-lowest rounded-[1.75rem] p-5 shadow-sm flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-surface-container-high flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined text-3xl">event_available</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="font-headline font-extrabold text-lg leading-tight text-on-surface truncate">{{ \Illuminate\Support\Str::limit($permission->reason, 32) }}</h2>
                        <p class="mt-1 text-sm font-semibold text-on-surface-variant">{{ $permission->start_date->format('d-m-Y') }} - {{ $permission->end_date->format('d-m-Y') }}</p>
                        <p class="mt-1 text-[11px] font-bold text-primary">{{ $permissionDetail['status'] }}</p>
                    </div>
                    <button type="button" @click="openPermission(@js($permissionDetail))" class="shrink-0 rounded-full bg-primary-fixed px-3 py-2 text-xs font-bold text-primary">
                        Detail
                    </button>
                </div>
            @empty
                <div class="bg-surface-container-lowest rounded-[1.75rem] p-10 text-center">
                    <span class="material-symbols-outlined text-6xl text-outline">event_busy</span>
                    <p class="mt-3 text-sm font-bold text-on-surface">Belum ada perizinan</p>
                    <p class="mt-1 text-xs text-on-surface-variant">Tidak ada izin pada bulan yang dipilih.</p>
                </div>
            @endforelse
        </section>

        @if($permissions->hasPages())
            <div class="pt-1">
                {{ $permissions->links() }}
            </div>
        @endif
    </main>

    <x-santri.bottom-nav />

    <div x-show="showPermissionDetail" x-cloak class="fixed inset-0 z-50 flex items-end bg-black/40 px-4 pb-4" @click.self="showPermissionDetail = false">
        <div x-show="showPermissionDetail" x-transition class="w-full rounded-[1.75rem] bg-surface-container-lowest p-5 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary" x-text="selectedPermission.number"></p>
                    <h3 class="mt-1 font-headline text-xl font-extrabold text-on-surface" x-text="selectedPermission.title"></h3>
                </div>
                <button type="button" @click="showPermissionDetail = false" class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-surface-container pb-3">
                    <span class="font-semibold text-on-surface-variant">Tanggal</span>
                    <span class="text-right font-bold text-on-surface" x-text="selectedPermission.date"></span>
                </div>
                <div class="flex justify-between gap-4 border-b border-surface-container pb-3">
                    <span class="font-semibold text-on-surface-variant">Status</span>
                    <span class="text-right font-bold text-primary" x-text="selectedPermission.status"></span>
                </div>
                <div class="flex justify-between gap-4 border-b border-surface-container pb-3">
                    <span class="font-semibold text-on-surface-variant">Mengizinkan</span>
                    <span class="text-right font-bold text-on-surface" x-text="selectedPermission.approved_by"></span>
                </div>
                <div class="flex justify-between gap-4 border-b border-surface-container pb-3">
                    <span class="font-semibold text-on-surface-variant">Petugas</span>
                    <span class="text-right font-bold text-on-surface" x-text="selectedPermission.created_by"></span>
                </div>
                <div>
                    <span class="font-semibold text-on-surface-variant">Alasan</span>
                    <p class="mt-1 font-bold text-on-surface" x-text="selectedPermission.reason"></p>
                </div>
                <div>
                    <span class="font-semibold text-on-surface-variant">Catatan</span>
                    <p class="mt-1 text-on-surface" x-text="selectedPermission.notes"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function permissionHistory() {
    return {
        showFilter: false,
        showPermissionDetail: false,
        selectedPermission: {},
        openPermission(permission) {
            this.selectedPermission = permission;
            this.showPermissionDetail = true;
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection
