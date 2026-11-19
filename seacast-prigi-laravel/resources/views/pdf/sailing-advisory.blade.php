<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Rencana Operasional Melaut — {{ $trip->reference_no }}</title>
    <style>
        @page {
            margin: 1.0cm 1.2cm;
            size: A4 portrait;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #1E293B;
            background: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .mono {
            font-family: 'DejaVu Sans Mono', monospace;
        }

        /* Kop Surat Resmi */
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            height: 82px;
            width: auto;
        }
        .kop-fallback {
            font-size: 16pt;
            font-weight: bold;
            color: #0B2545;
            letter-spacing: 0.5px;
        }
        .kop-sub {
            font-size: 7pt;
            color: #475569;
            margin-top: 2px;
        }
        .doc-meta {
            text-align: right;
        }
        .doc-kicker {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
        }
        .doc-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #0B2545;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 1px;
        }
        .doc-ref {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9.5pt;
            font-weight: bold;
            color: #0B2545;
            margin-top: 2px;
        }
        .doc-date {
            font-size: 7pt;
            color: #64748B;
            margin-top: 2px;
        }
        .kop-rule-thick {
            border-top: 2px solid #0B2545;
            margin-top: 7px;
        }
        .kop-rule-thin {
            border-top: 0.5px solid #94A3B8;
            margin-top: 2px;
            margin-bottom: 9px;
        }

        /* Screening Status Strip */
        .status-strip {
            border: 1px solid #CBD5E1;
            border-left: 4px solid #0B2545;
            background-color: #F8FAFC;
            padding: 6px 9px;
            margin-bottom: 4px;
        }
        .status-strip.ok {
            border-color: #94A3B8;
            border-left-color: #0F766E;
        }
        .status-strip.warn {
            border-color: #D97706;
            border-left-color: #B45309;
            background-color: #FFFDF7;
        }
        .status-strip.danger {
            border-color: #DC2626;
            border-left-color: #B91C1C;
            background-color: #FEF9F9;
        }
        .status-heading {
            font-size: 8pt;
            font-weight: bold;
            color: #0F172A;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-note {
            font-size: 7pt;
            color: #475569;
            margin-bottom: 8px;
        }

        /* Section Headings */
        .sec-head {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #0B2545;
            border-bottom: 1.5px solid #0B2545;
            padding-bottom: 2px;
            margin-top: 7px;
            margin-bottom: 4px;
        }

        /* Layout 2 Kolom */
        .col-wrap {
            width: 100%;
            margin-bottom: 4px;
        }
        .col-wrap > tbody > tr > td {
            width: 50%;
            vertical-align: top;
        }
        .col-left {
            padding-right: 9px;
        }
        .col-right {
            padding-left: 9px;
        }

        /* Tabel Data */
        .kv-table td {
            padding: 3.5px 4px;
            border-bottom: 0.5px solid #E2E8F0;
            font-size: 8pt;
            vertical-align: top;
        }
        .kv-table td.k {
            width: 38%;
            color: #475569;
        }
        .kv-table td.v {
            width: 62%;
            color: #0F172A;
            font-weight: bold;
        }

        /* Badge Ringkas */
        .pill {
            display: inline-block;
            padding: 1px 5px;
            border: 0.75px solid #94A3B8;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7.5pt;
            font-weight: bold;
            color: #0B2545;
            background-color: #F8FAFC;
        }
        .pill-ok {
            border-color: #0F766E;
            color: #0F766E;
        }
        .pill-warn {
            border-color: #B45309;
            color: #B45309;
        }
        .pill-danger {
            border-color: #B91C1C;
            color: #B91C1C;
        }

        /* Tabel Ringkasan Ekonomi */
        .econ-table {
            border: 1px solid #94A3B8;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        .econ-table th {
            background-color: #F1F5F9;
            color: #334155;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 4px 6px;
            border-bottom: 1px solid #94A3B8;
            border-right: 0.5px solid #CBD5E1;
            text-align: center;
        }
        .econ-table th:last-child,
        .econ-table td:last-child {
            border-right: none;
        }
        .econ-table td {
            padding: 6px 6px;
            text-align: center;
            border-right: 0.5px solid #CBD5E1;
            vertical-align: middle;
        }
        .econ-val {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0B2545;
        }
        .econ-sub {
            font-size: 6.8pt;
            color: #64748B;
            margin-top: 1px;
        }

        .sub-note {
            font-size: 7pt;
            color: #475569;
            margin-top: 2px;
            line-height: 1.3;
        }
        .notes-box {
            margin-top: 5px;
            padding: 4px 7px;
            border: 0.75px solid #CBD5E1;
            background-color: #F8FAFC;
            font-size: 7.5pt;
            color: #334155;
        }

        /* Tanda Tangan & Token */
        .sign-table {
            margin-top: 10px;
            width: 100%;
        }
        .sign-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
            color: #1E293B;
        }
        .sign-space {
            height: 44px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .sign-sub {
            font-size: 7pt;
            color: #64748B;
            margin-top: 1px;
        }

        .token-row {
            margin-top: 10px;
            text-align: center;
        }
        .token-box {
            display: inline-block;
            border: 0.75px solid #94A3B8;
            background-color: #F8FAFC;
            padding: 3px 8px;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7pt;
            color: #334155;
        }

        .footer-note {
            margin-top: 8px;
            border-top: 0.75px solid #CBD5E1;
            padding-top: 5px;
            font-size: 6.8pt;
            color: #475569;
            text-align: justify;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/brand/logo-full.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath))
            : null;

        $savedFuelPrice = $trip->total_fuel_liters > 0
            ? (int) round($trip->total_fuel_cost_rp / $trip->total_fuel_liters)
            : null;
        $savedOperationalCost = $trip->gross_revenue_rp - $trip->net_profit_rp;
        $savedOtherCosts = $savedOperationalCost - $trip->total_fuel_cost_rp;
        $savedTariffLabel = match (true) {
            $savedFuelPrice === 6800 && $trip->gt_class !== '> 30 GT' => 'Subsidi (asumsi, belum verifikasi izin)',
            $savedFuelPrice === 15000 && $trip->gt_class === '> 30 GT' => 'Non subsidi (asumsi harga khusus 2026)',
            default => 'Tarif rencana tersimpan',
        };

        $screeningClass = 'danger';
        $screeningText = 'SCREENING AWAL: POTENSI KONFLIK JALUR DAN TONASE; PERLU VERIFIKASI';
        if (in_array($trip->permen_kp_status, ['COMPLIANT', 'COMPLIANT_WITHIN_LEGAL_BOUNDS'], true)) {
            $screeningClass = 'ok';
            $screeningText = 'SCREENING AWAL: TIDAK ADA KONFLIK TONASE / JARAK YANG TERDETEKSI';
        } elseif ($trip->permen_kp_status === 'WARNING_OUT_OF_LEGAL_RANGE') {
            $screeningClass = 'warn';
            $screeningText = 'SCREENING AWAL: PERLU TINJAUAN JALUR DAN TONASE';
        }

        $waveClass = match ($trip->wave_status) {
            'AMAN', 'SAFE' => 'pill-ok',
            'WASPADA', 'CAUTION' => 'pill-warn',
            default => 'pill-danger',
        };
        $waveLabel = match ($trip->wave_status) {
            'AMAN', 'SAFE' => 'AMAN',
            'WASPADA', 'CAUTION' => 'WASPADA',
            'DANGER' => 'BAHAYA',
            default => $trip->wave_status,
        };
    @endphp

    <!-- Kop Surat Resmi -->
    <table class="kop-table">
        <tr>
            <td style="width: 48%;">
                @if ($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="SEACAST" class="kop-logo">
                @else
                    <div class="kop-fallback">SEACAST</div>
                    <div class="kop-sub">Platform Keputusan Melaut &amp; Intelijen Oseanografi WPPNRI 573</div>
                @endif
            </td>
            <td class="doc-meta" style="width: 52%;">
                <div class="doc-kicker">WPPNRI 573 &bull; Samudera Hindia Selatan Jawa</div>
                <div class="doc-title">Slip Rencana Operasional Melaut</div>
                <div class="doc-ref">{{ $trip->reference_no }}</div>
                <div class="doc-date">Dicetak: {{ now()->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>
    <div class="kop-rule-thick"></div>
    <div class="kop-rule-thin"></div>

    <!-- Screening Status -->
    <div class="status-strip {{ $screeningClass }}">
        <div class="status-heading">{{ $screeningText }}</div>
    </div>
    <div class="status-note">
        Screening dihitung dari jarak pelabuhan asal. Jalur resmi tetap wajib diverifikasi dari garis pantai; dokumen ini bukan persetujuan berlayar (SPB).
    </div>

    <!-- Bagian I & II -->
    <table class="col-wrap">
        <tr>
            <td class="col-left">
                <div class="sec-head">I. Profil Armada &amp; Nakhoda</div>
                <table class="kv-table">
                    <tr>
                        <td class="k">Nama Kapal</td>
                        <td class="v">{{ $trip->vessel_name }}</td>
                    </tr>
                    <tr>
                        <td class="k">Nama Nakhoda</td>
                        <td class="v">{{ $trip->skipper_name }}</td>
                    </tr>
                    <tr>
                        <td class="k">Pelabuhan Pangkalan</td>
                        <td class="v">{{ $trip->port_name }} ({{ $trip->port_code }})</td>
                    </tr>
                    <tr>
                        <td class="k">Kelas Tonase</td>
                        <td class="v"><span class="pill">{{ $trip->gt_class }}</span></td>
                    </tr>
                    <tr>
                        <td class="k">Alat Tangkap</td>
                        <td class="v">{{ $trip->gear_name }} ({{ $trip->gear_code }})</td>
                    </tr>
                    <tr>
                        <td class="k">Tanggal Berangkat</td>
                        <td class="v">{{ $trip->departure_date->format('d F Y') }}</td>
                    </tr>
                </table>
            </td>
            <td class="col-right">
                <div class="sec-head">II. Koordinat Sasaran &amp; Navigasi</div>
                <table class="kv-table">
                    <tr>
                        <td class="k">Komoditas Sasaran</td>
                        <td class="v">{{ $trip->target_commodity }}</td>
                    </tr>
                    <tr>
                        <td class="k">Koordinat (DMS)</td>
                        <td class="v mono">{{ $trip->target_dms }}</td>
                    </tr>
                    <tr>
                        <td class="k">Koordinat Desimal</td>
                        <td class="v mono">{{ number_format($trip->target_lat, 4) }}°, {{ number_format($trip->target_lon, 4) }}°</td>
                    </tr>
                    <tr>
                        <td class="k">Jarak dari Pangkalan</td>
                        <td class="v">{{ $trip->distance_nm }} NM (~{{ round($trip->distance_nm * 1.852, 1) }} km)</td>
                    </tr>
                    <tr>
                        <td class="k">Haluan Kompas</td>
                        <td class="v">{{ $trip->bearing_degrees }}° ({{ $trip->compass_direction }})</td>
                    </tr>
                    <tr>
                        <td class="k">Wilayah Pengelolaan</td>
                        <td class="v">WPPNRI 573 (Selatan Jawa)</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Bagian III & IV -->
    <table class="col-wrap" style="margin-top: 4px;">
        <tr>
            <td class="col-left">
                <div class="sec-head">III. Logistik Bahan Bakar (+15% Cadangan)</div>
                <table class="kv-table">
                    <tr>
                        <td class="k">Konsumsi PP Basis</td>
                        <td class="v">{{ number_format($trip->base_fuel_liters, 1) }} Liter</td>
                    </tr>
                    <tr>
                        <td class="k">Cadangan Wajib (+15%)</td>
                        <td class="v">+{{ number_format($trip->safety_reserve_liters, 1) }} Liter</td>
                    </tr>
                    <tr>
                        <td class="k">Total Solar PP</td>
                        <td class="v">{{ number_format($trip->total_fuel_liters, 1) }} Liter</td>
                    </tr>
                    <tr>
                        <td class="k">Biaya Solar PP</td>
                        <td class="v">Rp {{ number_format($trip->total_fuel_cost_rp, 0, ',', '.') }} (@ Rp {{ $savedFuelPrice === null ? '-' : number_format($savedFuelPrice, 0, ',', '.') }}/L)</td>
                    </tr>
                    <tr>
                        <td class="k">Kategori Tarif</td>
                        <td class="v">{{ $savedTariffLabel }}</td>
                    </tr>
                </table>
                <div class="sub-note">
                    Tarif berasal dari biaya &amp; liter tersimpan. Tarif subsidi memerlukan rekomendasi resmi; tarif non-subsidi dikonfirmasi di pelabuhan.
                </div>
            </td>
            <td class="col-right">
                <div class="sec-head">IV. Keselamatan Oseanografi &amp; Lunar</div>
                <table class="kv-table">
                    <tr>
                        <td class="k">Status Gelombang</td>
                        <td class="v">
                            <span class="pill {{ $waveClass }}">{{ $waveLabel }} ({{ $trip->wave_height_m }} m)</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="k">Fase Bulan</td>
                        <td class="v">{{ $trip->moon_phase_name }}</td>
                    </tr>
                    <tr>
                        <td class="k">Penilaian Gelombang</td>
                        <td class="v">
                            @if ($trip->wave_height_m > 2.5)
                                Melewati ambang skenario; tunda &amp; cek peringatan resmi BMKG.
                            @elseif ($trip->wave_height_m > 1.5)
                                Waspada gelombang; periksa prakiraan terbaru sebelum bertolak.
                            @else
                                Di bawah ambang skenario; tetap pantau prakiraan maritim resmi.
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="k">Faktor Lunar</td>
                        <td class="v">Parameter skenario; bukan jaminan hasil tangkapan.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Bagian V -->
    <div class="sec-head" style="margin-top: 8px;">V. Proyeksi Hasil Tangkapan &amp; Analisis Keekonomian</div>
    <table class="econ-table">
        <thead>
            <tr>
                <th style="width: 24%;">Proyeksi Tangkapan</th>
                <th style="width: 25%;">Estimasi Penerimaan</th>
                <th style="width: 25%;">Biaya Bahan Bakar</th>
                <th style="width: 26%;">Margin Operasional Skenario</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="econ-val">{{ number_format($trip->projected_catch_kg, 0, ',', '.') }} kg</div>
                    <div class="econ-sub">{{ $trip->target_commodity }}</div>
                </td>
                <td>
                    <div class="econ-val">Rp {{ number_format($trip->gross_revenue_rp, 0, ',', '.') }}</div>
                    <div class="econ-sub">Harga acuan skenario</div>
                </td>
                <td>
                    <div class="econ-val">Rp {{ number_format($trip->total_fuel_cost_rp, 0, ',', '.') }}</div>
                    <div class="econ-sub">Termasuk +15% cadangan</div>
                </td>
                <td style="background-color: #F8FAFC;">
                    <div class="econ-val">Rp {{ number_format($trip->net_profit_rp, 0, ',', '.') }}</div>
                    <div class="econ-sub">Penerimaan dikurangi biaya tersimpan</div>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="sub-note">
        Total biaya operasional tersimpan: <strong>Rp {{ number_format($savedOperationalCost, 0, ',', '.') }}</strong>
        (solar PP Rp {{ number_format($trip->total_fuel_cost_rp, 0, ',', '.') }}; biaya operasi &amp; logistik gabungan Rp {{ number_format($savedOtherCosts, 0, ',', '.') }}).
        Biaya awak, perawatan kapal, dan retribusi pelabuhan belum diperhitungkan.
    </div>

    @if ($trip->notes)
        <div class="notes-box">
            <strong>Catatan Operasional:</strong> {{ $trip->notes }}
        </div>
    @endif

    <!-- Blok Tanda Tangan -->
    <table class="sign-table">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div style="font-weight: bold;">Nakhoda Kapal Penangkap Ikan</div>
                <div class="sign-space"></div>
                <div class="sign-name">( {{ $trip->skipper_name }} )</div>
                <div class="sign-sub">{{ $trip->vessel_name }}</div>
            </td>
            <td>
                <div>Untuk pemeriksaan petugas,</div>
                <div style="font-weight: bold;">Petugas Syahbandar Pelabuhan</div>
                <div class="sign-space"></div>
                <div class="sign-name">( ............................................ )</div>
                <div class="sign-sub">{{ $trip->port_name }} &bull; Kesyahbandaran</div>
            </td>
        </tr>
    </table>

    <!-- Verifikasi -->
    <div class="token-row">
        <div class="token-box">
            KODE DOKUMEN SEACAST: <strong>{{ strtoupper(hash('sha256', $trip->reference_no . $trip->created_at)) }}</strong>
        </div>
    </div>

    <!-- Catatan Hukum & Simulasi -->
    <div class="footer-note">
        <strong>Catatan Simulasi:</strong> Dokumen ini dicetak otomatis oleh sistem SEACAST. Proyeksi tangkapan dan keekonomian menggunakan skenario formula (<span class="mono">FORMULA_SCENARIO</span>) serta skor habitat proxy (<span class="mono">PROXY_HSI</span>), dan bukan merupakan jaminan hasil tangkapan. Status gelombang mencerminkan penilaian saat rencana disimpan. Verifikasi ulang prakiraan BMKG terbaru, jarak jalur resmi dari garis pantai, rekomendasi BBM, serta Surat Persetujuan Berlayar (SPB) sebelum berlayar.
    </div>
</body>
</html>
