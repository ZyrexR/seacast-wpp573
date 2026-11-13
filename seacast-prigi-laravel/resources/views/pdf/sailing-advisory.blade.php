<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sailing Advisory — {{ $trip->reference_no }}</title>
    <style>
        @page {
            margin: 1.2cm 1.4cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.35;
            color: #0F172A;
            background: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-box {
            font-size: 18pt;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #0369A1;
        }
        .logo-sub {
            font-size: 7.5pt;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .doc-title-box {
            text-align: right;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: 800;
            color: #0F172A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-ref {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            font-weight: bold;
            color: #0284C7;
            margin-top: 2px;
        }
        .divider {
            border-bottom: 2px solid #0284C7;
            margin: 8px 0 12px 0;
        }
        .section-title {
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0369A1;
            padding: 4px 8px;
            background-color: #F0F9FF;
            border-left: 3px solid #0284C7;
            margin-bottom: 6px;
            margin-top: 10px;
        }
        .data-table {
            width: 100%;
            margin-bottom: 8px;
        }
        .data-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 8.5pt;
        }
        .data-table td.label {
            width: 28%;
            color: #475569;
            font-weight: 600;
        }
        .data-table td.value {
            width: 72%;
            color: #0F172A;
            font-weight: 700;
        }
        .highlight-box {
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
        }
        .alert-compliant {
            background-color: #ECFDF5;
            border: 1px solid #10B981;
            color: #065F46;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }
        .alert-warning {
            background-color: #FFFBEB;
            border: 1px solid #F59E0B;
            color: #92400E;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }
        .alert-danger {
            background-color: #FEF2F2;
            border: 1px solid #EF4444;
            color: #991B1B;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
        }
        .badge-cyan {
            background-color: #E0F2FE;
            color: #0369A1;
        }
        .grid-2 {
            width: 100%;
        }
        .grid-2 td {
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
        }
        .sign-table {
            margin-top: 25px;
            width: 100%;
        }
        .sign-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
        }
        .sign-space {
            height: 55px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .footer-note {
            margin-top: 15px;
            border-top: 1px dashed #CBD5E1;
            padding-top: 6px;
            font-size: 7pt;
            color: #64748B;
            text-align: justify;
        }
        .token-box {
            border: 1px solid #94A3B8;
            padding: 4px 8px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 7.5pt;
            color: #334155;
            background: #F1F5F9;
            text-align: center;
            display: inline-block;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="logo-box">SEACAST</div>
                <div class="logo-sub">Platform Keputusan Melaut & Intelijen Oseanografi WPPNRI 573</div>
            </td>
            <td class="doc-title-box" style="width: 50%;">
                <div class="doc-title">Rencana Operasional Melaut</div>
                <div class="doc-ref">{{ $trip->reference_no }}</div>
                <div style="font-size: 7.5pt; color: #64748B;">Diterbitkan: {{ date('d F Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- 1. Regulatory Compliance Banner (Permen KP 59/2020) -->
    <div style="margin-bottom: 8px;">
        @if ($trip->permen_kp_status === 'COMPLIANT')
            <div class="alert-compliant">
                STATUS KEPATUHAN: PATUH TERHADAP PERMEN KP NO. 59/2020
                <span style="float: right; font-weight: normal;">Jalur Penangkapan Ikan Sesuai Tonase Kapal</span>
            </div>
        @elseif ($trip->permen_kp_status === 'WARNING_OUT_OF_LEGAL_RANGE')
            <div class="alert-warning">
                PERINGATAN ZONASI: MELEBIHI BATAS LEGAL TONASE (&gt; 12 NM) — PERMEN KP NO. 59/2020
                <span style="float: right; font-weight: normal;">Kapal &lt; 10 GT wajib beroperasi di Jalur I (0–12 NM)</span>
            </div>
        @else
            <div class="alert-danger">
                PELANGGARAN ZONASI: KAPAL INDUSTRI DI WILAYAH PESISIR (&lt;= 12 NM) — PERMEN KP NO. 59/2020
                <span style="float: right; font-weight: normal;">Kapal &gt; 30 GT dilarang menangkap di Jalur I & II</span>
            </div>
        @endif
    </div>

    <!-- 2. Vessel Profile & Navigational Waypoints -->
    <table class="grid-2">
        <tr>
            <!-- Left Column: Vessel Profile -->
            <td>
                <div class="section-title">1. Profil Armada & Nakhoda</div>
                <table class="data-table">
                    <tr>
                        <td class="label">Nama Kapal:</td>
                        <td class="value">{{ $trip->vessel_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nama Nakhoda:</td>
                        <td class="value">{{ $trip->skipper_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pangkalan / Asal:</td>
                        <td class="value">{{ $trip->port_name }} ({{ $trip->port_code }})</td>
                    </tr>
                    <tr>
                        <td class="label">Kelas Tonase:</td>
                        <td class="value"><span class="badge badge-cyan">{{ $trip->gt_class }}</span></td>
                    </tr>
                    <tr>
                        <td class="label">Alat Tangkap API:</td>
                        <td class="value">{{ $trip->gear_name }} ({{ $trip->gear_code }})</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Tolak:</td>
                        <td class="value" style="color: #0284C7;">{{ $trip->departure_date->format('d F Y') }}</td>
                    </tr>
                </table>
            </td>

            <!-- Right Column: Navigational Coordinates -->
            <td>
                <div class="section-title">2. Koordinat Sasaran & Navigasi</div>
                <table class="data-table">
                    <tr>
                        <td class="label">Komoditas Utama:</td>
                        <td class="value">{{ $trip->target_commodity }}</td>
                    </tr>
                    <tr>
                        <td class="label">Titik Koordinat:</td>
                        <td class="value" style="font-family: 'Courier New', Courier, monospace; color: #0284C7;">
                            {{ $trip->target_dms }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Koordinat Desimal:</td>
                        <td class="value" style="font-family: 'Courier New', Courier, monospace;">
                            {{ number_format($trip->target_lat, 4) }}°, {{ number_format($trip->target_lon, 4) }}°
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Jarak Nautika:</td>
                        <td class="value">{{ $trip->distance_nm }} NM (~{{ round($trip->distance_nm * 1.852, 1) }} km)</td>
                    </tr>
                    <tr>
                        <td class="label">Sudut Haluan:</td>
                        <td class="value">{{ $trip->bearing_degrees }}° (Haluan {{ $trip->compass_direction }})</td>
                    </tr>
                    <tr>
                        <td class="label">Wilayah Pengelolaan:</td>
                        <td class="value">WPPNRI 573 (Samudera Hindia Selatan Jawa)</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 3. Fuel Logistics with +15% Safety Reserve & Maritime Safety -->
    <table class="grid-2">
        <tr>
            <!-- Left: Fuel Logistics -->
            <td>
                <div class="section-title">3. Logistik Bahan Bakar (+15% Safety)</div>
                <table class="data-table">
                    <tr>
                        <td class="label">Konsumsi PP Basis:</td>
                        <td class="value">{{ number_format($trip->base_fuel_liters, 1) }} Liter</td>
                    </tr>
                    <tr>
                        <td class="label">Cadangan Wajib (+15%):</td>
                        <td class="value" style="color: #D97706;">+{{ number_format($trip->safety_reserve_liters, 1) }} Liter</td>
                    </tr>
                    <tr>
                        <td class="label">Total Kuota Solar:</td>
                        <td class="value" style="font-size: 10pt; color: #0284C7;">
                            {{ number_format($trip->total_fuel_liters, 1) }} Liter
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Estimasi Biaya Solar:</td>
                        <td class="value">Rp {{ number_format($trip->total_fuel_cost_rp, 0, ',', '.') }} (@ Rp 6.800/L)</td>
                    </tr>
                </table>
            </td>

            <!-- Right: Oceanographic Safety & Lunar Cycle -->
            <td>
                <div class="section-title">4. Keselamatan Oseanografi & Lunar</div>
                <table class="data-table">
                    <tr>
                        <td class="label">Status Gelombang:</td>
                        <td class="value">
                            <span class="badge" style="{{ $trip->wave_status === 'AMAN' ? 'background: #DCFCE7; color: #166534;' : 'background: #FEE2E2; color: #991B1B;' }}">
                                {{ $trip->wave_status }} ({{ $trip->wave_height_m }} m)
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Fase Bulan:</td>
                        <td class="value">{{ $trip->moon_phase_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kondisi Operasional:</td>
                        <td class="value">
                            @if ($trip->wave_height_m > 2.5)
                                Bahaya gelombang tinggi ekstrem; tunda pelayaran.
                            @elseif ($trip->wave_height_m > 1.5)
                                Waspada gelombang sedang; perhatikan manuver jaring/pancing.
                            @else
                                Kondisi laut tenang bersahabat untuk penangkapan optimal.
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Atraktor Cahaya:</td>
                        <td class="value">Optimal untuk penangkapan malam hari pelagis besar.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 4. Catch Projections & Economic Valuation -->
    <div class="section-title">5. Proyeksi Hasil Tangkapan & Analisis Keekonomian</div>
    <table class="data-table">
        <tr>
            <td style="width: 25%; text-align: center; background: #F8FAFC; padding: 6px;">
                <div style="font-size: 7.5pt; color: #64748B;">PROYEKSI TANGKAPAN</div>
                <div style="font-size: 13pt; font-weight: 900; color: #0369A1;">{{ number_format($trip->projected_catch_kg, 0, ',', '.') }} kg</div>
                <div style="font-size: 7pt; color: #475569;">{{ $trip->target_commodity }}</div>
            </td>
            <td style="width: 25%; text-align: center; background: #F8FAFC; padding: 6px;">
                <div style="font-size: 7.5pt; color: #64748B;">ESTIMASI PENERIMAAN</div>
                <div style="font-size: 11pt; font-weight: 800; color: #059669;">Rp {{ number_format($trip->gross_revenue_rp, 0, ',', '.') }}</div>
                <div style="font-size: 7pt; color: #475569;">Nilai Pasar TPI Acuan</div>
            </td>
            <td style="width: 25%; text-align: center; background: #F8FAFC; padding: 6px;">
                <div style="font-size: 7.5pt; color: #64748B;">BIAYA BAHAN BAKAR</div>
                <div style="font-size: 11pt; font-weight: 800; color: #DC2626;">Rp {{ number_format($trip->total_fuel_cost_rp, 0, ',', '.') }}</div>
                <div style="font-size: 7pt; color: #475569;">Termasuk 15% Cadangan</div>
            </td>
            <td style="width: 25%; text-align: center; background: #F0FDF4; padding: 6px; border: 1px solid #BBF7D0;">
                <div style="font-size: 7.5pt; color: #166534; font-weight: bold;">ESTIMASI MARGIN BERSIH</div>
                <div style="font-size: 12pt; font-weight: 900; color: #15803D;">Rp {{ number_format($trip->net_profit_rp, 0, ',', '.') }}</div>
                <div style="font-size: 7pt; color: #166534;">Margin Operasional Bersih</div>
            </td>
        </tr>
    </table>

    @if ($trip->notes)
        <div style="font-size: 7.5pt; color: #475569; margin-top: 4px; padding: 4px 8px; background: #F8FAFC; border-left: 2px solid #94A3B8;">
            <strong>Catatan Operasional:</strong> {{ $trip->notes }}
        </div>
    @endif

    <!-- 5. Signature and Approval Blocks -->
    <table class="sign-table">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div style="font-weight: 600;">Nakhoda Kapal Penangkap Ikan</div>
                <div class="sign-space"></div>
                <div class="sign-name">( {{ $trip->skipper_name }} )</div>
                <div style="font-size: 7pt; color: #64748B;">{{ $trip->vessel_name }}</div>
            </td>
            <td>
                <div>Diverifikasi & Disahkan,</div>
                <div style="font-weight: 600;">Petugas Syahbandar Pelabuhan</div>
                <div class="sign-space"></div>
                <div class="sign-name">( ............................................ )</div>
                <div style="font-size: 7pt; color: #64748B;">{{ $trip->port_name }} • Kesyahbandaran</div>
            </td>
        </tr>
    </table>

    <!-- Security Verification Token Box -->
    <div style="text-align: center; margin-top: 15px;">
        <div class="token-box">
            TOKEN VERIFIKASI SEACAST: <strong>{{ strtoupper(hash('sha256', $trip->reference_no . $trip->created_at)) }}</strong>
        </div>
    </div>

    <!-- Official Legal Footer -->
    <div class="footer-note">
        <strong>Pemberitahuan Resmi:</strong> Dokumen ini dicetak secara otomatis dari Platform SEACAST (Integrated Fisheries Intelligence System WPPNRI 573). Rencana operasional ini mengacu pada ketentuan batas jalur penangkapan ikan Permen KP No. 59/PERMEN-KP/2020 dan rekomendasi keselamatan operasional BMKG/Syahbandar. Nahkoda wajib menjaga kelengkapan SPB (Surat Persetujuan Berlayar) resmi dan alat keselamatan pelayaran standar (life jacket, EPIRB, GPS).
    </div>

</body>
</html>

