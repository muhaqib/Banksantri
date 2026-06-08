<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Izin {{ $permission->permission_number }}</title>
    <style>
        body{font-family:Arial,sans-serif;background:#eef2f1;margin:0;padding:32px;color:#17201f}.toolbar{max-width:850px;margin:0 auto 16px;display:flex;gap:8px;justify-content:flex-end}.toolbar a,.toolbar button{border:0;border-radius:8px;padding:10px 16px;font-weight:700;cursor:pointer;text-decoration:none;background:#004d4c;color:#fff}.card{max-width:850px;margin:auto;background:#fff;border:2px solid #004d4c;border-radius:16px;overflow:hidden}.header{background:#004d4c;color:#fff;padding:24px 30px;display:flex;justify-content:space-between}.body{padding:30px}.grid{display:grid;grid-template-columns:180px 1fr;gap:12px 20px}.label{font-size:12px;text-transform:uppercase;color:#65706f;font-weight:bold}.value{font-size:15px;font-weight:bold;border-bottom:1px solid #d8dfde;padding-bottom:8px}.reason{margin-top:24px;padding:18px;background:#f1f5f4;border-radius:10px}.signature{margin-top:54px;margin-left:auto;width:250px;text-align:center}.signature-space{height:75px}.footer{padding:14px 30px;background:#f1f5f4;font-size:11px;color:#65706f}@media print{body{background:#fff;padding:0}.toolbar{display:none}.card{border-radius:0;max-width:none}}
    </style>
</head>
<body>
    <div class="toolbar"><a href="{{ route($routePrefix.'.permissions.index') }}">Kembali</a><button onclick="window.print()">Cetak Kartu Izin</button></div>
    <div class="card">
        <div class="header"><div><strong style="font-size:22px">KARTU IZIN SANTRI</strong><div style="margin-top:5px">Pondok Pesantren Mambaul Hikmah</div></div><strong>{{ $permission->permission_number }}</strong></div>
        <div class="body">
            <div class="grid">
                <div class="label">Nama Santri</div><div class="value">{{ $permission->santri->name }}</div>
                <div class="label">NIS</div><div class="value">{{ $permission->santri->nis ?? '-' }}</div>
                <div class="label">Kamar</div><div class="value">{{ ucwords(str_replace('_', ' ', $permission->kamar)) }}</div>
                <div class="label">Asal</div><div class="value">{{ $permission->santri->asal_sekolah ?? '-' }}</div>
                <div class="label">Nomor HP</div><div class="value">{{ $permission->santri->no_hp ?? $permission->santri->no_hp_wali ?? '-' }}</div>
                <div class="label">Tanggal Izin</div><div class="value">{{ $permission->start_date->translatedFormat('d F Y') }}</div>
                <div class="label">Batas Akhir Izin</div><div class="value">{{ $permission->end_date->translatedFormat('d F Y') }}</div>
                <div class="label">Yang Mengizinkan</div><div class="value">{{ $permission->creator?->name ?? '-' }} ({{ ucfirst($permission->creator?->role ?? '-') }})</div>
            </div>
            <div class="reason"><div class="label">Alasan Izin</div><p><strong>{{ $permission->reason }}</strong></p>@if($permission->notes)<div class="label">Catatan</div><p>{{ $permission->notes }}</p>@endif</div>
            <div class="signature"><div>{{ $permission->created_at->translatedFormat('d F Y') }}</div><div class="signature-space"></div><strong>{{ $permission->creator?->name ?? 'Petugas/Admin' }}</strong><div>Yang Mengizinkan</div></div>
        </div>
        <div class="footer">Kartu ini diterbitkan oleh Mawa Smart pada {{ $permission->created_at->format('d/m/Y H:i') }} WIB. Setelah batas izin berakhir, ketidakhadiran santri akan dihitung sebagai ghoib.</div>
    </div>
    @if(session('success'))<script>window.addEventListener('load',()=>window.print())</script>@endif
</body>
</html>
