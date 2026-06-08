@extends('layouts.app')

@section('title', 'Perizinan Santri')
@section('header-title', 'Perizinan Santri')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div><p class="text-sm font-bold text-primary">Izin Langsung Aktif</p><h1 class="font-headline text-3xl font-black">Perizinan Santri</h1><p class="text-sm text-on-surface-variant">Perizinan otomatis mengubah ketidakhadiran menjadi izin selama periode aktif.</p></div>
        <a href="{{ route($routePrefix.'.permissions.create') }}" class="btn-primary"><span class="material-symbols-outlined">add</span> Buat Perizinan</a>
    </div>
    <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-4">
        <input name="search" value="{{ request('search') }}" placeholder="Nama atau NIS" class="input-field">
        <select name="kamar" class="input-field"><option value="">Semua kamar</option>@foreach($kamarList as $room)<option value="{{ $room }}" @selected(request('kamar') === $room)>{{ ucwords(str_replace('_', ' ', $room)) }}</option>@endforeach</select>
        <input type="month" name="month_picker" value="{{ request('year', now()->year).'-'.str_pad(request('month', now()->month), 2, '0', STR_PAD_LEFT) }}" class="input-field" onchange="this.form.month.value=this.value.split('-')[1];this.form.year.value=this.value.split('-')[0]">
        <input type="hidden" name="month" value="{{ request('month') }}"><input type="hidden" name="year" value="{{ request('year') }}">
        <button class="btn-primary"><span class="material-symbols-outlined">search</span> Cari</button>
    </form>
    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant"><tr><th class="px-5 py-4">Nomor / Santri</th><th class="px-5 py-4">Periode</th><th class="px-5 py-4">Alasan</th><th class="px-5 py-4">Yang Mengizinkan</th><th class="px-5 py-4">Aksi</th></tr></thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($permissions as $permission)
                        <tr>
                            <td class="px-5 py-4"><p class="text-xs font-bold text-primary">{{ $permission->permission_number }}</p><p class="font-bold">{{ $permission->santri->name }}</p><p class="text-xs text-on-surface-variant">{{ ucwords(str_replace('_', ' ', $permission->kamar)) }}</p></td>
                            <td class="px-5 py-4"><p class="font-bold">{{ $permission->start_date->format('d/m/Y') }} - {{ $permission->end_date->format('d/m/Y') }}</p><span class="inline-flex rounded-full px-2 py-1 text-xs font-bold {{ $permission->is_active ? 'bg-green-50 text-green-700' : ($permission->end_date->isPast() ? 'bg-surface-container text-on-surface-variant' : 'bg-blue-50 text-blue-700') }}">{{ $permission->is_active ? 'Aktif' : ($permission->end_date->isPast() ? 'Selesai' : 'Akan Datang') }}</span></td>
                            <td class="max-w-xs px-5 py-4">{{ $permission->reason }}</td>
                            <td class="px-5 py-4">{{ $permission->creator?->name ?? '-' }}</td>
                            <td class="px-5 py-4"><div class="flex gap-2"><a href="{{ route($routePrefix.'.permissions.print', $permission) }}" class="text-primary" title="Cetak"><span class="material-symbols-outlined">print</span></a><a href="{{ route($routePrefix.'.permissions.edit', $permission) }}" class="text-amber-600" title="Edit"><span class="material-symbols-outlined">edit</span></a><form method="POST" action="{{ route($routePrefix.'.permissions.destroy', $permission) }}" onsubmit="return confirm('Hapus izin dan hitung ulang absensi terkait?')">@csrf @method('DELETE')<button class="text-error"><span class="material-symbols-outlined">delete</span></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center text-on-surface-variant">Belum ada data perizinan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $permissions->links() }}</div>
    </div>
</div>
@endsection
