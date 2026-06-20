@extends('layouts.app')

@section('title', 'Keamanan Santri')
@section('header-title', 'Keamanan Santri')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-bold text-primary">Pelanggaran & Poin</p>
            <h1 class="font-headline text-3xl font-black">Keamanan Santri</h1>
            <p class="text-sm text-on-surface-variant">Catat pelanggaran santri dan pengurangan poin prestasi secara otomatis.</p>
        </div>
        <a href="{{ route('petugas.security.create') }}" class="btn-primary">
            <span class="material-symbols-outlined">add</span> Input Pelanggaran
        </a>
    </div>

    <form method="GET" class="grid gap-3 rounded-xl bg-surface-container-lowest p-4 shadow-sm md:grid-cols-4">
        <input name="search" value="{{ request('search') }}" placeholder="Nama atau NIS" class="input-field md:col-span-2">
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
                        <th class="px-5 py-4">Jenis Pelanggaran</th>
                        <th class="px-5 py-4">Waktu</th>
                        <th class="px-5 py-4">Pengurangan</th>
                        <th class="px-5 py-4">Petugas</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse($violations as $violation)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $violation->santri->name }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $violation->santri->nis ?? '-' }} · {{ $violation->santri->kamarSantri?->kamar ? ucwords(str_replace('_', ' ', $violation->santri->kamarSantri->kamar)) : 'Tanpa kamar' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $violation->jenis_pelanggaran }}</p>
                                <p class="max-w-xs text-xs text-on-surface-variant">{{ $violation->keterangan ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4">{{ $violation->waktu->translatedFormat('d F Y H:i') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-red-50 px-2 py-1 text-xs font-bold text-red-700">-{{ $violation->pengurangan_point }} poin</span>
                            </td>
                            <td class="px-5 py-4">{{ $violation->creator?->name ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('petugas.security.edit', $violation) }}" class="text-amber-600" title="Edit"><span class="material-symbols-outlined">edit</span></a>
                                    <form method="POST" action="{{ route('petugas.security.destroy', $violation) }}" onsubmit="return confirm('Hapus pelanggaran ini? Poin santri akan dihitung ulang.')">
                                        @csrf @method('DELETE')
                                        <button class="text-error" title="Hapus"><span class="material-symbols-outlined">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-on-surface-variant">Belum ada data pelanggaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $violations->links() }}</div>
    </div>
</div>
@endsection
