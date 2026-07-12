<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Santri - {{ $permission->permission_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        $logoPath = public_path('images/logo.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : asset('images/logo.png');
        $phone = $permission->santri->no_hp ?? $permission->santri->no_hp_wali ?? '-';
        $approver = $permission->approved_by ?? "Mudirul Ma'had";
        
        $startStr = $permission->start_date->translatedFormat('d F Y') . ' pukul ' . $permission->start_date->format('H:i') . ' WIB';
        $endStr = $permission->end_date->translatedFormat('d F Y') . ' pukul ' . $permission->end_date->format('H:i') . ' WIB';
    @endphp
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e9ecef;
            color: #2b303a;
            font-family: 'Inter', sans-serif;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            width: 297mm;
            margin: 20px auto 10px;
        }

        .toolbar a,
        .toolbar button {
            border: 0;
            border-radius: 12px;
            padding: 12px 24px;
            background: #004d4c;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 77, 76, 0.25);
            transition: all 0.2s ease;
        }

        .toolbar a {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
            box-shadow: none;
        }

        .toolbar a:hover {
            background: #e9ecef;
        }

        .toolbar button:hover {
            background: #003635;
            transform: translateY(-1px);
        }

        .permit-card {
            width: 297mm;
            height: 210mm;
            margin: 0 auto 30px;
            padding: 16mm 18mm;
            background: #fff;
            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .permit-card::before {
            content: "";
            position: absolute;
            top: 5mm;
            left: 5mm;
            right: 5mm;
            bottom: 5mm;
            border: 2.5px solid #004d4c;
            pointer-events: none;
        }

        .permit-card::after {
            content: "";
            position: absolute;
            top: 7.2mm;
            left: 7.2mm;
            right: 7.2mm;
            bottom: 7.2mm;
            border: 1px solid #c5a880;
            pointer-events: none;
        }

        .header-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #004d4c;
            padding-bottom: 5mm;
            position: relative;
            z-index: 10;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 6mm;
        }

        .logo {
            width: 24mm;
            height: 24mm;
            object-fit: contain;
        }

        .header-text {
            display: flex;
            flex-direction: column;
        }

        .school-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 16pt;
            font-weight: 800;
            color: #004d4c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .school-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 12pt;
            font-weight: 600;
            color: #c5a880;
            margin-top: 1mm;
            letter-spacing: 0.5px;
        }

        .school-address {
            font-size: 8.5pt;
            color: #6c757d;
            margin-top: 1.5mm;
            font-style: italic;
        }

        .header-right {
            text-align: right;
            display: flex;
            flex-direction: column;
            gap: 2mm;
        }

        .document-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 18pt;
            font-weight: 900;
            color: #004d4c;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1;
        }

        .permit-no-badge {
            background: #f4f7f6;
            border: 1px solid #004d4c;
            padding: 2mm 4mm;
            border-radius: 4px;
            display: inline-block;
            align-self: flex-end;
        }

        .permit-no-badge span {
            display: block;
            font-size: 7.5pt;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .permit-no-badge strong {
            display: block;
            font-family: monospace;
            font-size: 10pt;
            color: #004d4c;
            margin-top: 0.5mm;
            text-align: center;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 10mm;
            margin-top: 6mm;
            flex-grow: 1;
            position: relative;
            z-index: 10;
        }

        .details-column {
            display: flex;
            flex-direction: column;
            gap: 4mm;
        }

        .section-header {
            font-family: 'Montserrat', sans-serif;
            font-size: 9.5pt;
            font-weight: 800;
            color: #004d4c;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #c5a880;
            padding-bottom: 1.5mm;
            margin-bottom: 2mm;
            display: flex;
            align-items: center;
            gap: 2mm;
        }

        .section-header::after {
            content: "";
            flex-grow: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .card-details {
            background: #fafbfc;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 4mm 5mm;
            height: 100%;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th {
            text-align: left;
            font-size: 8.5pt;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2.2mm 0;
            width: 32mm;
            vertical-align: middle;
        }

        .info-table td {
            font-size: 9.5pt;
            font-weight: 700;
            color: #2b303a;
            padding: 2.2mm 0;
            vertical-align: middle;
        }

        .info-table tr:not(:last-child) th,
        .info-table tr:not(:last-child) td {
            border-bottom: 1px solid #f1f3f5;
        }

        .info-table td::before {
            content: ": ";
            color: #c5a880;
            margin-right: 1.5mm;
            font-weight: 500;
        }

        .reason-textarea {
            font-size: 9.5pt;
            line-height: 1.5;
            color: #2b303a;
            font-weight: 600;
            padding: 3mm;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            min-height: 18mm;
        }

        .notes-box {
            margin-top: 3mm;
            font-size: 8.5pt;
            color: #495057;
            background: #fffdf5;
            border: 1px solid #fff3cd;
            border-radius: 6px;
            padding: 2.5mm 3.5mm;
            font-style: italic;
        }

        .signatures-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30mm;
            text-align: center;
            margin-top: 6mm;
            position: relative;
            z-index: 10;
        }

        .sig-col {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sig-title {
            font-size: 9pt;
            font-weight: 600;
            color: #6c757d;
        }

        .sig-space {
            height: 16mm;
        }

        .sig-line {
            width: 50mm;
            border-bottom: 1.5px solid #004d4c;
            margin-bottom: 1.5mm;
        }

        .sig-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 10pt;
            font-weight: 700;
            color: #004d4c;
        }

        .sig-role {
            font-size: 8pt;
            color: #6c757d;
            margin-top: 0.5mm;
        }

        .footer-section {
            border-top: 1px dashed #ced4da;
            padding-top: 3mm;
            margin-top: 5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 7.5pt;
            color: #6c757d;
            position: relative;
            z-index: 10;
        }

        .footer-left {
            font-weight: 500;
        }

        .footer-right {
            font-style: italic;
            font-weight: 600;
            color: #004d4c;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            html,
            body {
                width: 297mm;
                height: 210mm;
                background: #fff !important;
            }

            .toolbar {
                display: none !important;
            }

            .permit-card {
                width: 297mm !important;
                height: 210mm !important;
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
        <button type="button" onclick="window.print()">Cetak Surat Izin</button>
    </div>

    <main class="permit-card">
        <header class="header-section">
            <div class="header-left">
                <img src="{{ $logoSrc }}" alt="Logo Pondok Pesantren Mambaul Hikmah" class="logo">
                <div class="header-text">
                    <span class="school-title">Pondok Pesantren Mambaul Hikmah</span>
                    <span class="school-subtitle">Pusat Perizinan & Disiplin Santri</span>
                    <span class="school-address">Alamat: Jl. KH. Muhammad Barmawi, Tegalwangi - Talang - Tegal | www.mambaulhikmah.com</span>
                </div>
            </div>
            <div class="header-right">
                <span class="document-title">Surat Izin Santri</span>
                <div class="permit-no-badge">
                    <span>Nomor Surat Izin</span>
                    <strong>{{ $permission->permission_number }}</strong>
                </div>
            </div>
        </header>

        <section class="details-grid">
            <div class="details-column">
                <div class="section-header">Identitas Santri</div>
                <div class="card-details">
                    <table class="info-table">
                        <tr>
                            <th>Nama Lengkap</th>
                            <td>{{ $permission->santri->name }}</td>
                        </tr>
                        <tr>
                            <th>Nomor Induk (NIS)</th>
                            <td>{{ $permission->santri->nis ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kamar/Asrama</th>
                            <td>{{ ucwords(str_replace('_', ' ', $permission->kamar)) }}</td>
                        </tr>
                        <tr>
                            <th>Asal Sekolah</th>
                            <td>{{ $permission->santri->asal_sekolah ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kontak Wali/HP</th>
                            <td>{{ $phone }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="details-column">
                <div class="section-header">Detail Perizinan</div>
                <div class="card-details">
                    <table class="info-table">
                        <tr>
                            <th>Waktu Keluar</th>
                            <td>{{ $startStr }}</td>
                        </tr>
                        <tr>
                            <th>Waktu Kembali</th>
                            <td>{{ $endStr }}</td>
                        </tr>
                        <tr>
                            <th>Wewenang</th>
                            <td>{{ $approver }}</td>
                        </tr>
                    </table>
                    
                    <div style="margin-top: 3mm; display: flex; flex-direction: column;">
                        <span style="font-size: 8.5pt; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 1.5mm;">Alasan / Keperluan Izin</span>
                        <div class="reason-textarea">
                            {{ $permission->reason }}
                        </div>
                    </div>

                    @if($permission->notes)
                        <div class="notes-box">
                            <strong>Catatan Tambahan:</strong> {{ $permission->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="signatures-section">
            <div class="sig-col">
                <span class="sig-title">Mengetahui & Menyetujui,</span>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <span class="sig-name">{{ $approver }}</span>
                <span class="sig-role">Pengurus Pondok</span>
            </div>
            <div class="sig-col">
                <span class="sig-title">Tegal, {{ $permission->created_at->translatedFormat('d F Y') }}</span>
                <div class="sig-space"></div>
                <div class="sig-line"></div>
                <span class="sig-name">{{ $permission->creator?->name ?? 'Petugas/Admin' }}</span>
                <span class="sig-role">Petugas Keamanan/Piket</span>
            </div>
        </section>

        <footer class="footer-section">
            <div class="footer-left">
                Surat izin ini sah diterbitkan melalui sistem digital Mawa Smart pada {{ $permission->created_at->format('d/m/Y H:i') }} WIB.
            </div>
            <div class="footer-right">
                * Santri wajib melapor ketika kembali ke pondok pesantren.
            </div>
        </footer>
    </main>

    @if(session('success'))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</body>
</html>
