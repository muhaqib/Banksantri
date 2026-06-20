<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Izin {{ $permission->permission_number }}</title>
    @php
        $logoPath = public_path('images/logo.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : asset('images/logo.png');
        $phone = $permission->santri->no_hp ?? $permission->santri->no_hp_wali ?? '-';
        $approver = $permission->approved_by ?? "Mudirul Ma'had";
    @endphp
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f1;
            color: #202020;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.25;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            width: 210mm;
            margin: 18px auto 12px;
        }

        .toolbar a,
        .toolbar button {
            border: 0;
            border-radius: 10px;
            padding: 10px 16px;
            background: #004d4c;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .permit-card {
            width: 210mm;
            min-height: 148mm;
            margin: 0 auto 24px;
            padding: 10mm 11mm 8mm;
            background: #fff;
            border: 1px solid #d5dddc;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
        }

        .letterhead {
            display: grid;
            grid-template-columns: 25mm 1fr auto;
            align-items: center;
            gap: 7mm;
            padding-bottom: 5mm;
            border-bottom: 1.1mm solid #111;
        }

        .logo {
            width: 24mm;
            height: 24mm;
            object-fit: contain;
        }

        .title {
            font-size: 17pt;
            font-weight: 900;
            line-height: 1.05;
            text-transform: uppercase;
        }

        .school {
            margin-top: 1.5mm;
            font-size: 12pt;
            font-weight: 900;
            text-transform: uppercase;
        }

        .address {
            margin-top: 1mm;
            font-size: 9.5pt;
            font-style: italic;
        }

        .permit-number {
            min-width: 43mm;
            padding: 3mm 4mm;
            border: .45mm solid #111;
            text-align: center;
        }

        .permit-number span {
            display: block;
            font-size: 7.5pt;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .permit-number strong {
            display: block;
            margin-top: 1mm;
            font-size: 10pt;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10mm;
            padding: 7mm 0 5mm;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .info-table th,
        .info-table td {
            padding: 2.2mm 0;
            vertical-align: top;
            border-bottom: .25mm solid #d7d7d7;
        }

        .info-table th {
            width: 33mm;
            color: #4f5655;
            font-size: 8pt;
            font-weight: 900;
            letter-spacing: .05em;
            text-align: left;
            text-transform: uppercase;
        }

        .info-table td {
            font-weight: 800;
        }

        .info-table td::before {
            content: ": ";
            font-weight: 400;
        }

        .reason-box {
            display: grid;
            grid-template-columns: 33mm 1fr;
            gap: 4mm;
            min-height: 23mm;
            padding: 4mm;
            border: .5mm solid #111;
            background: #f8faf9;
            font-size: 10pt;
        }

        .reason-label {
            color: #4f5655;
            font-size: 8pt;
            font-weight: 900;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .reason-content {
            font-weight: 800;
        }

        .notes {
            margin-top: 2mm;
            color: #555;
            font-size: 9pt;
            font-weight: 600;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22mm;
            margin-top: 9mm;
            text-align: center;
            font-size: 10pt;
        }

        .signature-space {
            height: 17mm;
        }

        .signature-name {
            font-weight: 900;
            text-decoration: underline;
        }

        .footer {
            margin-top: 7mm;
            padding-top: 2mm;
            border-top: .35mm dashed #bdbdbd;
            color: #666;
            font-size: 8pt;
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 0;
            }

            html,
            body {
                width: 210mm;
                min-height: 148mm;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .toolbar {
                display: none !important;
            }

            .permit-card {
                width: 210mm !important;
                min-height: 148mm !important;
                margin: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route($routePrefix.'.permissions.index') }}">Kembali</a>
        <button type="button" onclick="window.print()">Cetak Kartu Izin</button>
    </div>

    <main class="permit-card">
        <header class="letterhead">
            <img src="{{ $logoSrc }}" alt="Logo Pondok Pesantren Mambaul Hikmah" class="logo">
            <div>
                <div class="title">Kartu Izin Santri</div>
                <div class="school">Pondok Pesantren Mambaul Hikmah</div>
                <div class="address">Alamat: Jl Kh Muhammad Barmawi, Tegalwangi - Talang - Tegal. www.mambaulhikmah.com</div>
            </div>
            <div class="permit-number">
                <span>Nomor Izin</span>
                <strong>{{ $permission->permission_number }}</strong>
            </div>
        </header>

        <section class="meta-grid">
            <table class="info-table">
                <tr>
                    <th>Nama Santri</th>
                    <td>{{ $permission->santri->name }}</td>
                </tr>
                <tr>
                    <th>NIS</th>
                    <td>{{ $permission->santri->nis ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Kamar</th>
                    <td>{{ ucwords(str_replace('_', ' ', $permission->kamar)) }}</td>
                </tr>
                <tr>
                    <th>Asal</th>
                    <td>{{ $permission->santri->asal_sekolah ?? '-' }}</td>
                </tr>
            </table>

            <table class="info-table">
                <tr>
                    <th>Nomor HP</th>
                    <td>{{ $phone }}</td>
                </tr>
                <tr>
                    <th>Tanggal Izin</th>
                    <td>{{ $permission->start_date->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <th>Batas Akhir</th>
                    <td>{{ $permission->end_date->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <th>Mengizinkan</th>
                    <td>{{ $approver }}</td>
                </tr>
            </table>
        </section>

        <section class="reason-box">
            <div class="reason-label">Alasan Izin</div>
            <div class="reason-content">
                {{ $permission->reason }}
                @if($permission->notes)
                    <div class="notes">Catatan: {{ $permission->notes }}</div>
                @endif
            </div>
        </section>

        <section class="signatures">
            <div>
                <div>Yang Mengizinkan,</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $approver }}</div>
            </div>
            <div>
                <div>Tegal, {{ $permission->created_at->translatedFormat('d F Y') }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $permission->creator?->name ?? 'Petugas/Admin' }}</div>
                <div>Petugas</div>
            </div>
        </section>

        <footer class="footer">
            Kartu ini diterbitkan oleh Mawa Smart pada {{ $permission->created_at->format('d/m/Y H:i') }} WIB. Setelah batas izin berakhir, ketidakhadiran santri akan dihitung sebagai ghoib.
        </footer>
    </main>

    @if(session('success'))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</body>
</html>
