@extends('layouts.app')

@section('title', $permission ? 'Edit Perizinan' : 'Buat Perizinan')
@section('header-title', $permission ? 'Edit Perizinan' : 'Buat Perizinan')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6"><p class="text-sm font-bold text-primary">Tanpa proses persetujuan</p><h1 class="font-headline text-3xl font-black">{{ $permission ? 'Edit Perizinan Santri' : 'Buat Perizinan Santri' }}</h1><p class="text-sm text-on-surface-variant">Setelah disimpan, status absensi santri otomatis dianggap izin selama periode berikut.</p></div>
    <form method="POST" action="{{ $permission ? route($routePrefix.'.permissions.update', $permission) : route($routePrefix.'.permissions.store') }}" class="space-y-5 rounded-2xl bg-surface-container-lowest p-6 shadow-sm">
        @csrf
        @if($permission) @method('PUT') @endif
        <label class="block text-sm font-bold">Santri
            <select name="santri_id" required class="input-field mt-2">
                <option value="">Pilih santri</option>
                @foreach($santriList as $santri)<option value="{{ $santri->id }}" @selected(old('santri_id', $permission?->santri_id) == $santri->id)>{{ $santri->name }} · {{ $santri->nis }} · {{ ucwords(str_replace('_', ' ', $santri->kamarSantri->kamar)) }}</option>@endforeach
            </select>
        </label>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-bold">Tanggal Izin<input type="date" name="start_date" required value="{{ old('start_date', $permission?->start_date?->toDateString() ?? today()->toDateString()) }}" class="input-field mt-2"></label>
            <label class="text-sm font-bold">Batas Akhir Izin<input type="date" name="end_date" required value="{{ old('end_date', $permission?->end_date?->toDateString() ?? today()->toDateString()) }}" class="input-field mt-2"></label>
        </div>
        <label class="block text-sm font-bold">Alasan<textarea name="reason" required rows="4" class="input-field mt-2" placeholder="Tuliskan alasan izin secara lengkap">{{ old('reason', $permission?->reason) }}</textarea></label>
        <label class="block text-sm font-bold">Catatan Tambahan<textarea name="notes" rows="3" class="input-field mt-2">{{ old('notes', $permission?->notes) }}</textarea></label>
        @if($errors->any())<div class="rounded-xl bg-error-container p-4 text-sm text-on-error-container">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <div class="flex justify-end gap-3"><a href="{{ route($routePrefix.'.permissions.index') }}" class="btn-secondary">Batal</a><button class="btn-primary"><span class="material-symbols-outlined">save</span> {{ $permission ? 'Simpan Perubahan' : 'Simpan & Cetak Izin' }}</button></div>
    </form>
</div>
@endsection
