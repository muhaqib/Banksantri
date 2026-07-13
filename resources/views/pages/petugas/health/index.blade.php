@extends('layouts.app')

@section('title', 'Kesehatan Santri')
@section('header-title', 'Kesehatan Santri')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Rekam Medis Santri</p>
            <h1 class="font-headline text-2xl font-bold">Kesehatan Santri</h1>
            <p class="text-sm text-on-surface-variant">Kelola pemeriksaan, status kesehatan, dan catatan tindakan santri.</p>
        </div>
        <a href="{{ route('petugas.health.create') }}" class="btn-primary">
            <span class="material-symbols-outlined">add</span> Tambah Data
        </a>
    </div>

    <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-5">
        <input name="search" value="{{ request('search') }}" placeholder="Nama atau NIS" class="input-field md:col-span-2">
        <select name="status" class="input-field">
            <option value="">Semua status</option>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="month" name="month_picker" value="{{ request('year', now()->year).'-'.str_pad(request('month', now()->month), 2, '0', STR_PAD_LEFT) }}" class="input-field" onchange="this.form.month.value=this.value.split('-')[1];this.form.year.value=this.value.split('-')[0]">
        <input type="hidden" name="month" value="{{ request('month') }}">
        <input type="hidden" name="year" value="{{ request('year') }}">
        <button class="btn-primary"><span class="material-symbols-outlined">search</span> Cari</button>
    </form>

    <div class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-surface-container-low text-xs uppercase text-on-surface-variant">
                    <tr>
                        <th class="px-5 py-4">Santri</th>
                        <th class="px-5 py-4">Pemeriksaan</th>
                        <th class="px-5 py-4">Vital</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Petugas</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($records as $record)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $record->santri->name }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $record->santri->nis ?? '-' }} · {{ $record->santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $record->santri->kamarSantri->kamar)) : 'Tanpa kamar' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $record->title }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $record->checkup_date->translatedFormat('d F Y') }} · {{ $record->location ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $record->weight_kg ? rtrim(rtrim($record->weight_kg, '0'), '.') . ' kg' : '-' }} / {{ $record->height_cm ? rtrim(rtrim($record->height_cm, '0'), '.') . ' cm' : '-' }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $record->blood_pressure ?? '-' }}{{ $record->temperature_c ? ' · '.$record->temperature_c.'°C' : '' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-bold {{ $record->status === 'sehat' ? 'bg-green-50 text-green-700' : ($record->status === 'sakit' || $record->status === 'dirawat' ? 'bg-red-50 text-red-700' : 'bg-primary-fixed text-primary') }}">
                                    {{ $record->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">{{ $record->creator?->name ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('petugas.health.edit', $record) }}" class="text-amber-600" title="Edit"><span class="material-symbols-outlined">edit</span></a>
                                    <form method="POST" action="{{ route('petugas.health.destroy', $record) }}" onsubmit="return confirm('Hapus data kesehatan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-error" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-on-surface-variant">Belum ada data kesehatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $records->links() }}</div>
    </div>
</div>
@endsection
