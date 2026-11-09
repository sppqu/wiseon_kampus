<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Kehadiran Dosen - {{ $namaBulan }} {{ $tahunPilih }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                background: white;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 landscape;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8">

    <!-- Floating Print Control Bar (Hanya Tampil di Layar) -->
    <div class="no-print max-w-5xl mx-auto mb-6 px-4 flex items-center justify-between bg-white p-4 rounded-2xl shadow-md border border-slate-200">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-700">Dokumen Rekapitulasi Presensi Siap Dicetak</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-[#154e85] hover:bg-[#124270] text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Dokumen Sekarang</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>

    <!-- Container Lembar Cetak A4 Landscape -->
    <div class="print-container max-w-5xl mx-auto bg-white p-10 rounded-2xl shadow-xl border border-slate-200">
        
        <!-- KOP RESMI PERGURUAN TINGGI -->
        <div class="flex items-center gap-5 pb-4 border-b-4 border-double border-slate-800">
            <div class="w-20 h-20 rounded-2xl bg-[#154e85] text-white flex items-center justify-center font-black text-2xl tracking-tighter shrink-0 shadow-md">
                {{ $pt->singkatan ?? 'UTN' }}
            </div>
            <div class="flex-1">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight mt-0.5">
                    {{ $pt->nama_pt ?? 'UNIVERSITAS TEKNOLOGI NUSANTARA' }}
                </h1>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    {{ $pt->alamat ?? 'Jl. Kampus Merdeka No. 101, Gedung Perkuliahan Terpadu' }} &bull; Telp: {{ $pt->telepon ?? '(021) 789-0123' }}
                </p>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    Website: {{ $pt->website ?? 'https://kampus.ac.id' }} &bull; Email: {{ $pt->email ?? 'info@kampus.ac.id' }} &bull; Akreditasi: {{ $pt->akreditasi ?? 'Unggul' }}
                </div>
            </div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="text-center my-6">
            <h2 class="text-base font-extrabold uppercase tracking-wide text-slate-900">
                LAPORAN REKAPITULASI KEHADIRAN DOSEN & KARYAWAN
            </h2>
            <div class="text-xs font-bold text-slate-600 mt-1">
                PERIODE BULAN: <span class="uppercase text-[#154e85]">{{ $namaBulan }} {{ $tahunPilih }}</span>
            </div>
        </div>

        <!-- TABEL REKAPITULASI -->
        <table class="w-full text-left border-collapse text-xs border border-slate-300">
            <thead>
                <tr class="bg-slate-100 font-extrabold text-slate-800 text-[11px] uppercase border-b border-slate-300">
                    <th class="py-2.5 px-3 border border-slate-300 text-center w-10">No</th>
                    <th class="py-2.5 px-3 border border-slate-300">NIDN / NIP</th>
                    <th class="py-2.5 px-3 border border-slate-300">Nama Lengkap Dosen / Karyawan</th>
                    <th class="py-2.5 px-3 border border-slate-300">Program Studi</th>
                    <th class="py-2.5 px-2.5 border border-slate-300 text-center">Hari Efektif</th>
                    <th class="py-2.5 px-2.5 border border-slate-300 text-center">Hadir Tepat</th>
                    <th class="py-2.5 px-2.5 border border-slate-300 text-center">Terlambat</th>
                    <th class="py-2.5 px-2.5 border border-slate-300 text-center">Izin/Sakit</th>
                    <th class="py-2.5 px-2.5 border border-slate-300 text-center">Alpa</th>
                    <th class="py-2.5 px-2.5 border border-slate-300 text-center">Total Masuk</th>
                    <th class="py-2.5 px-3 border border-slate-300 text-center">Persentase (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapDosen as $index => $row)
                <tr class="{{ $index % 2 == 1 ? 'bg-slate-50' : 'bg-white' }} border-b border-slate-200">
                    <td class="py-2 px-3 border border-slate-300 text-center font-semibold">{{ $index + 1 }}</td>
                    <td class="py-2 px-3 border border-slate-300 font-mono text-[11px]">{{ $row->dosen->nidn ?: ($row->dosen->nip ?: '-') }}</td>
                    <td class="py-2 px-3 border border-slate-300 font-bold text-slate-900">{{ $row->dosen->nama_lengkap }}</td>
                    <td class="py-2 px-3 border border-slate-300">{{ $row->dosen->prodi->nama_prodi ?? '-' }}</td>
                    <td class="py-2 px-2.5 border border-slate-300 text-center font-semibold">{{ $row->hari_efektif }}</td>
                    <td class="py-2 px-2.5 border border-slate-300 text-center font-bold text-emerald-700">{{ $row->hadir_tepat }}</td>
                    <td class="py-2 px-2.5 border border-slate-300 text-center font-bold text-amber-700">{{ $row->terlambat }}</td>
                    <td class="py-2 px-2.5 border border-slate-300 text-center font-semibold">{{ $row->izin_sakit }}</td>
                    <td class="py-2 px-2.5 border border-slate-300 text-center font-bold {{ $row->alpa > 0 ? 'text-rose-700 font-extrabold' : 'text-slate-400' }}">{{ $row->alpa }}</td>
                    <td class="py-2 px-2.5 border border-slate-300 text-center font-bold">{{ $row->total_masuk }}</td>
                    <td class="py-2 px-3 border border-slate-300 text-center font-bold font-mono {{ $row->persentase >= 85 ? 'text-emerald-700' : ($row->persentase >= 75 ? 'text-indigo-700' : 'text-rose-700') }}">
                        {{ $row->persentase }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TANDA TANGAN PEJABAT RESMI KAMPUS -->
        <div class="mt-12 grid grid-cols-2 gap-8 text-xs">
            <div class="text-center">
                <div class="text-slate-500 font-medium">Mengetahui,</div>
                <div class="font-bold text-slate-800 mt-0.5">Kepala Bagian Kepegawaian / BAAK</div>
                <div class="h-20"></div>
                <div class="font-bold text-slate-900 underline">Drs. H. Mulyadi, M.M.</div>
                <div class="text-slate-500 text-[11px]">NIP. 19780512 200501 1 003</div>
            </div>

            <div class="text-center">
                <div class="text-slate-500 font-medium">Ditetapkan di Jakarta, {{ now()->locale('id')->isoFormat('D MMMM Y') }}</div>
                <div class="font-bold text-slate-800 mt-0.5">Rektor {{ $pt->nama_pt ?? 'Universitas Teknologi Nusantara' }}</div>
                <div class="h-20"></div>
                <div class="font-bold text-slate-900 underline">{{ $pt->nama_rektor ?? 'Prof. Dr. H. Faisal Anwar, M.Eng.' }}</div>
                <div class="text-slate-500 text-[11px]">NIDN. 0312086501</div>
            </div>
        </div>

        <div class="mt-8 pt-3 border-t border-slate-200 text-[10px] text-slate-400 flex items-center justify-between">
            <span>Dicetak secara otomatis oleh Sistem Manajemen Presensi Kampus (WiseOn)</span>
            <span>Waktu Cetak: {{ now()->format('d/m/Y H:i:s') }} WIB</span>
        </div>

    </div>

</body>
</html>
