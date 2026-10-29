<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Penanganan Laporan Gangguan IT</title>
    <style>
        @page {
            margin: 18mm 12mm 18mm 12mm;
            size: A4 landscape;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            margin: 0;
            line-height: 1.35;
        }

        .document {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 8px;
        }

        .instansi {
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #111827;
            text-transform: uppercase;
        }

        .instansi-line {
            display: block;
            font-size: 12pt;
            margin: 2px 0;
        }

        h1 {
            margin: 10px 0 4px;
            text-align: center;
            font-size: 16pt;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #111827;
        }

        .meta {
            text-align: center;
            font-size: 9pt;
            color: #374151;
            margin-bottom: 10px;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #bfc9d4;
            padding: 6px 5px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        table.report-table th {
            background: #f3f4f6;
            font-size: 8pt;
            font-weight: 700;
            text-align: center;
            color: #111827;
        }

        .no-data {
            text-align: center;
            padding: 22px 10px;
            font-style: italic;
            color: #4b5563;
        }

        .page-number {
            text-align: right;
            font-size: 8pt;
            color: #4b5563;
            margin-top: 10px;
        }

        .footer {
            margin-top: 12px;
            font-size: 8.5pt;
            color: #374151;
            border-top: 1px solid #d1d5db;
            padding-top: 6px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .muted {
            color: #4b5563;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 9px;
            font-size: 7.5pt;
            line-height: 1.2;
            font-weight: 700;
            background: #e2e8f0;
            color: #111827;
        }

        .status-success { background: #dcfce7; color: #166534; }
        .status-warning { background: #fef3c7; color: #92400e; }
        .status-info { background: #dbeafe; color: #1d4ed8; }
        .status-danger { background: #fee2e2; color: #991b1b; }
        .status-secondary { background: #e5e7eb; color: #374151; }
        .status-dark { background: #111827; color: #f8fafc; }
    </style>
</head>
<body>
    <div class="document">
        <div class="header">
            <div class="instansi">
                <span class="instansi-line">KEPOLISIAN NEGARA REPUBLIK INDONESIA</span>
                <span class="instansi-line">DAERAH KALIMANTAN SELATAN</span>
                <span class="instansi-line">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</span>
            </div>
        </div>

        <h1>REKAPITULASI PENANGANAN LAPORAN GANGGUAN IT</h1>
        <div class="meta">LayanIT &nbsp;•&nbsp; Periode: {{ $reportStart->translatedFormat('d') }}–{{ $reportEnd->translatedFormat('d F Y') }} &nbsp;•&nbsp; Dibuat: {{ $createdAt->translatedFormat('d F Y, H:i') }} WIB</div>

        @if($isEmpty)
            <div class="no-data">Tidak ada laporan untuk periode ini.</div>
        @else
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 9%;">No. Tiket</th>
                        <th style="width: 15%;">Pelapor & Satker</th>
                        <th style="width: 18%;">Kategori & Judul</th>
                        <th style="width: 10%;">Lokasi</th>
                        <th style="width: 9%;">Padal</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 8%;">Tgl Lapor</th>
                        <th style="width: 8%;">Tgl Selesai</th>
                        <th style="width: 7%;">Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $index => $ticket)
                        @php
                            $statusClass = match($ticket->status) {
                                'menunggu_verifikasi' => 'status-warning',
                                'dalam_penanganan' => 'status-info',
                                'selesai' => 'status-success',
                                'selesai_dinilai' => 'status-dark',
                                'ditolak_admin' => 'status-danger',
                                'ditolak_padal' => 'status-secondary',
                                default => 'status-secondary',
                            };
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $index + 1 }}</td>
                            <td>{{ $ticket->ticket_number }}</td>
                            <td>
                                {{ $ticket->user?->name ?? '-' }}<br>
                                <span class="muted">{{ $ticket->user?->satuan_kerja ?? '-' }}</span>
                            </td>
                            <td>
                                {{ $ticket->title ?? '-' }}<br>
                                <span class="muted">{{ $ticket->category?->name ?? '-' }}</span>
                            </td>
                            <td>{{ $ticket->location ?? '-' }}</td>
                            <td>{{ $ticket->padal?->name ?? '-' }}</td>
                            <td style="text-align:center;"><span class="badge {{ $statusClass }}">{{ $ticket->status_label }}</span></td>
                            <td>{{ $ticket->created_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $ticket->completed_at?->format('d/m/Y') ?? '-' }}</td>
                            <td style="text-align:center;">{{ $ticket->rating?->rating ? $ticket->rating->rating : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            <div>Dokumen ini merupakan hasil rekapitulasi dari sistem LayanIT.</div>
            <div>Halaman {{ isset($pageNumber) ? $pageNumber : 1 }}</div>
        </div>
    </div>
</body>
</html>
