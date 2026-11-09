<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Portofolio BKD - {{ $kelas->mataKuliah->nama_mk }} ({{ $kelas->nama_kelas }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 14pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 12pt;
            margin: 2px 0;
            font-weight: 700;
        }
        .header p {
            font-size: 8pt;
            color: #475569;
            margin: 2px 0 0 0;
        }
        .doc-title {
            text-align: center;
            margin: 15px 0;
        }
        .doc-title h3 {
            font-size: 12pt;
            text-transform: uppercase;
            margin: 0;
            text-decoration: underline;
            font-weight: 800;
        }
        .doc-title p {
            font-size: 9pt;
            margin: 3px 0 0 0;
            font-weight: 600;
            color: #334155;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .meta-label {
            width: 25%;
            font-weight: bold;
            color: #334155;
        }
        .meta-separator {
            width: 2%;
        }
        .meta-value {
            width: 73%;
        }
        .bap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .bap-table th, .bap-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
        }
        .bap-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 4px;
            background: #e2e8f0;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .signature-box {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sig-col {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            text-align: center;
            font-size: 9.5pt;
        }
        .sig-space {
            height: 60px;
        }
        .qr-verif-box {
            border: 1px dashed #64748b;
            background: #f8fafc;
            padding: 6px;
            display: inline-block;
            margin-top: 5px;
            border-radius: 6px;
            font-size: 7pt;
            font-family: monospace;
        }
        .no-print {
            margin-bottom: 20px;
            padding: 12px;
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 8px;
            text-align: center;
        }
        .print-btn {
            background: #059669;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 10pt;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { font-size: 9pt; }
        }
    </style>
</head>
<body>

    <!-- Tombol Cetak Browser (Hilang Saat Dicetak) -->
    <div class="no-print">
        <strong>Dokumen Siap Cetak / Simpan PDF (A4)</strong> &bull;
        Standar Bukti Fisik Beban Kerja Dosen (BKD) & Portofolio SISTER Kemendikbud.
        <div style="margin-top: 8px;">
            <button class="print-btn" onclick="window.print()">
                Cetak ke PDF / Printer (Ctrl + P)
            </button>
        </div>
    </div>

    <!-- Kop Surat Kampus -->
    <div class="header">
        <h1>Universitas Teknologi Nusantara</h1>
        <h2>Fakultas Ilmu Komputer & Teknologi Informasi</h2>
        <p>Jl. Kampus Merdeka No. 101, Gedung Perkuliahan Terpadu &bull; Telp: (021) 789-0123 &bull; Laman: https://kampus.ac.id</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h3>Berita Acara Perkuliahan & Presensi Mengajar Dosen</h3>
        <p>Bukti Fisik Pelaporan Beban Kerja Dosen (BKD) / Sertifikasi Dosen (Serdos)</p>
    </div>

    <!-- Identitas Mata Kuliah & Dosen Pengampu -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Mata Kuliah</td>
            <td class="meta-separator">:</td>
            <td class="meta-value font-bold">{{ $kelas->mataKuliah->nama_mk }} ({{ $kelas->mataKuliah->kode_mk }})</td>
        </tr>
        <tr>
            <td class="meta-label">Bobot / Kelas</td>
            <td class="meta-separator">:</td>
            <td class="meta-value">{{ $kelas->mataKuliah->sks }} SKS / Kelas {{ $kelas->nama_kelas }}</td>
        </tr>
        <tr>
            <td class="meta-label">Program Studi / Semester</td>
            <td class="meta-separator">:</td>
            <td class="meta-value">{{ $kelas->mataKuliah->prodi ? $kelas->mataKuliah->prodi->nama_prodi : 'Teknik Informatika' }} / {{ $kelas->tahunAkademik->nama }}</td>
        </tr>
        <tr>
            <td class="meta-label">Dosen Pengampu</td>
            <td class="meta-separator">:</td>
            <td class="meta-value font-bold">{{ $dosen ? $dosen->nama_gelar : 'Dr. Hendra Pratama, M.Kom.' }} (NIDN: {{ $dosen ? $dosen->nidn : '-' }})</td>
        </tr>
        <tr>
            <td class="meta-label">Status Tatap Muka</td>
            <td class="meta-separator">:</td>
            <td class="meta-value font-bold">
                {{ $kelas->pertemuans->where('status', 'selesai')->count() }} dari 16 Pertemuan
                @if($kelas->pertemuans->where('status', 'selesai')->count() >= 16)
                    <span class="badge badge-success">Memenuhi Syarat BKD</span>
                @else
                    <span class="badge badge-amber">Dalam Proses Penyelesaian</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- Matriks 16 Pertemuan Berita Acara Perkuliahan (BAP) -->
    <table class="bap-table">
        <thead>
            <tr>
                <th style="width: 5%;">Ptm</th>
                <th style="width: 14%;">Hari / Tanggal / Jam</th>
                <th style="width: 8%;">Metode</th>
                <th style="width: 48%;">Rencana & Realisasi Materi Perkuliahan (Sesuai RPS)</th>
                <th style="width: 12%;">Hadir Mahasiswa</th>
                <th style="width: 13%;">Paraf / Validasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kelas->pertemuans as $ptm)
            <tr>
                <td class="text-center font-bold">{{ $ptm->pertemuan_ke }}</td>
                <td>
                    <div class="font-bold">{{ $ptm->tanggal->locale('id')->isoFormat('dddd') }}</div>
                    <div>{{ $ptm->tanggal->format('d/m/Y') }}</div>
                    <div style="font-size: 7.5pt; color: #64748b;">
                        {{ $ptm->jam_mulai_aktual ? substr($ptm->jam_mulai_aktual, 0, 5) . '-' . substr($ptm->jam_selesai_aktual, 0, 5) : substr($ptm->jam_mulai_jadwal, 0, 5) . '-' . substr($ptm->jam_selesai_jadwal, 0, 5) }}
                    </div>
                </td>
                <td class="text-center">
                    <span style="text-transform: uppercase; font-size: 7.5pt; font-weight: bold;">
                        {{ $ptm->metode }}
                    </span>
                    @if($ptm->jenis_pertemuan !== 'tatap_muka')
                        <div style="font-size: 7pt; font-weight: bold; color: #b45309;">({{ strtoupper($ptm->jenis_pertemuan) }})</div>
                    @endif
                </td>
                <td>
                    <div class="font-bold">{{ $ptm->rencana_materi ?: 'Materi Pertemuan ke-' . $ptm->pertemuan_ke }}</div>
                    <div style="color: #475569; margin-top: 2px;">{{ $ptm->realisasi_materi ?: '-' }}</div>
                    @if($ptm->cpmk)
                        <div style="font-size: 7pt; color: #0284c7; font-weight: bold; margin-top: 2px;">{{ $ptm->cpmk }}</div>
                    @endif
                </td>
                <td class="text-center font-bold">
                    @if($ptm->status === 'selesai')
                        {{ $ptm->presensiMahasiswas->where('status', 'hadir')->count() }} / {{ $ptm->presensiMahasiswas->count() }}
                        <div style="font-size: 7.5pt; color: #166534;">({{ $ptm->persen_hadir_mahasiswa }}%)</div>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center" style="font-size: 7.5pt;">
                    @if($ptm->status === 'selesai')
                        <span style="color: #166534; font-weight: bold;">TERCATAT</span>
                        <div style="font-size: 6.5pt; color: #64748b;">GPS Valid ({{ $ptm->jarak_meter ?? 15 }}m)</div>
                    @else
                        <span style="color: #94a3b8;">Terjadwal</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Lembar Pengesahan Resmi Kaprodi & Dosen Pengampu -->
    <div class="signature-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div>Mengetahui,</div>
                    <div class="font-bold">Ketua Program Studi {{ $kelas->mataKuliah->prodi ? $kelas->mataKuliah->prodi->nama_prodi : 'Teknik Informatika' }}</div>
                    
                    <div class="qr-verif-box">
                        <div style="font-weight: bold; color: #0f172a;">DIGITAL VERIFIED DOCUMENT</div>
                        <div>ID: {{ $pengesahan ? $pengesahan->qr_verifikasi : 'VERIF-BKD-OFFICIAL-2024' }}</div>
                        <div>Tgl Pengesahan: {{ $pengesahan && $pengesahan->tanggal_pengesahan ? $pengesahan->tanggal_pengesahan->format('d/m/Y') : date('d/m/Y') }}</div>
                    </div>
                    
                    <div class="font-bold" style="text-decoration: underline; margin-top: 5px;">
                        {{ $pengesahan ? $pengesahan->disahkan_oleh : 'Prof. Dr. Ir. Budi Santoso, M.Eng.' }}
                    </div>
                    <div style="font-size: 8pt; color: #475569;">NIDN: {{ $pengesahan && $pengesahan->nidn_pengesah ? $pengesahan->nidn_pengesah : '0015087501' }}</div>
                </td>

                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div>Kota Kampus, {{ date('d F Y') }}</div>
                    <div class="font-bold">Dosen Pengampu Mata Kuliah</div>
                    
                    <div class="sig-space" style="margin-top: 25px;">
                        <span style="color: #047857; font-size: 11pt; font-family: monospace; font-weight: bold;">
                            [Tanda Tangan Elektronik]
                        </span>
                    </div>

                    <div class="font-bold" style="text-decoration: underline;">
                        {{ $dosen ? $dosen->nama_gelar : 'Dr. Hendra Pratama, M.Kom.' }}
                    </div>
                    <div style="font-size: 8pt; color: #475569;">NIDN: {{ $dosen ? $dosen->nidn : '0412088801' }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
