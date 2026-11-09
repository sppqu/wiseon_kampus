@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600 transition">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <a href="{{ route('admin.presensi-harian-dosen') }}" class="hover:text-indigo-600 transition">Presensi Kantor</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-bold">Laporan Rekapitulasi Kehadiran</span>
            </div>
            <h1 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-chart-pie text-[#154e85]"></i>
                <span>Laporan Rekapitulasi Kehadiran Dosen & Karyawan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Akumulasi kehadiran bulanan, ketepatan waktu, durasi kerja, dan persentase absensi kantor dosen & karyawan.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Navigasi Cepat Tab Harian vs Rekap Bulanan -->
            <a href="{{ route('admin.presensi-harian-dosen') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-slate-200">
                <i class="fa-solid fa-calendar-day"></i>
                <span>Monitoring Harian</span>
            </a>

            <!-- Tombol Cetak Dokumen Resmi -->
            <a href="{{ route('admin.rekap-kehadiran-dosen.cetak', ['bulan' => $bulanPilih, 'tahun' => $tahunPilih, 'program_studi_id' => $prodiPilih]) }}" target="_blank" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-indigo-200 shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Laporan</span>
            </a>

            <!-- Tombol Export CSV / Excel -->
            <a href="{{ route('admin.rekap-kehadiran-dosen.export', ['bulan' => $bulanPilih, 'tahun' => $tahunPilih, 'program_studi_id' => $prodiPilih]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-sm shadow-emerald-600/30">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export CSV / Excel</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik / KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Dosen</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpi['total_dosen'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Pegawai terdata</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Rata-rata Hadir</div>
            <div class="text-2xl font-black text-indigo-700 mt-1">{{ $kpi['avg_kehadiran'] }}%</div>
            <div class="text-[10px] text-indigo-500 mt-0.5">Tingkat kehadiran</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Tepat Waktu</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $kpi['total_hadir_tepat'] }}</div>
            <div class="text-[10px] text-emerald-500 mt-0.5">Sesi kehadiran</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Terlambat</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $kpi['total_terlambat'] }}</div>
            <div class="text-[10px] text-amber-500 mt-0.5">Melewati toleransi</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Izin / Sakit</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $kpi['total_izin_sakit'] }}</div>
            <div class="text-[10px] text-blue-500 mt-0.5">Disertai permohonan</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Tanpa Ket. (Alpa)</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $kpi['total_alpa'] }}</div>
            <div class="text-[10px] text-rose-500 mt-0.5">Belum / Tidak absen</div>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.rekap-kehadiran-dosen') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Bulan Periode</label>
                <select name="bulan" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                    @php
                        $namaBulans = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    @foreach($namaBulans as $num => $nama)
                        <option value="{{ $num }}" {{ $bulanPilih == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun</label>
                <select name="tahun" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                    @for($thn = now()->year + 1; $thn >= now()->year - 4; $thn--)
                        <option value="{{ $thn }}" {{ $tahunPilih == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Program Studi</label>
                <select name="program_studi_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold">
                    <option value="">-- Semua Program Studi --</option>
                    @foreach($semuaProdi as $p)
                        <option value="{{ $p->id }}" {{ $prodiPilih == $p->id ? 'selected' : '' }}>{{ $p->nama_prodi }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pencarian Dosen</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIDN / NIP..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-[#154e85] hover:bg-[#124270] text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="{{ route('admin.rekap-kehadiran-dosen') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition" title="Reset Filter">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Rekapitulasi Kehadiran Dosen -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                    Rekapitulasi Periode: {{ $namaBulans[$bulanPilih] ?? '' }} {{ $tahunPilih }}
                </span>
            </div>
            <span class="text-xs text-slate-500 font-semibold">
                Menampilkan <strong>{{ $rekapDosen->count() }}</strong> Dosen / Karyawan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-extrabold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Dosen / Karyawan</th>
                        <th class="py-3 px-4">Program Studi</th>
                        <th class="py-3 px-3 text-center">Hari Efektif</th>
                        <th class="py-3 px-3 text-center text-emerald-700">Tepat Waktu</th>
                        <th class="py-3 px-3 text-center text-amber-700">Terlambat</th>
                        <th class="py-3 px-3 text-center text-blue-700">Izin / Sakit</th>
                        <th class="py-3 px-3 text-center text-rose-700">Alpa</th>
                        <th class="py-3 px-3 text-center">Total Masuk</th>
                        <th class="py-3 px-4 text-center">Persentase</th>
                        <th class="py-3 px-3 text-center">Rata-rata Durasi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($rekapDosen as $index => $row)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $row->dosen->nama_lengkap }}</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                NIDN: {{ $row->dosen->nidn ?: '-' }} &bull; NIP: {{ $row->dosen->nip ?: '-' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-medium">
                            {{ $row->dosen->prodi->nama_prodi ?? '-' }}
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700">
                            {{ $row->hari_efektif }} hari
                        </td>
                        <td class="py-3 px-3 text-center font-extrabold text-emerald-600">
                            {{ $row->hadir_tepat }}
                        </td>
                        <td class="py-3 px-3 text-center font-extrabold text-amber-600">
                            {{ $row->terlambat }}
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-blue-600">
                            {{ $row->izin_sakit }}
                        </td>
                        <td class="py-3 px-3 text-center font-extrabold {{ $row->alpa > 0 ? 'text-rose-600 bg-rose-50/60 rounded-lg' : 'text-slate-400' }}">
                            {{ $row->alpa }}
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-800">
                            {{ $row->total_masuk }} hari
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="font-extrabold font-mono {{ $row->persentase >= 85 ? 'text-emerald-700' : ($row->persentase >= 75 ? 'text-indigo-700' : 'text-rose-600') }}">
                                    {{ $row->persentase }}%
                                </span>
                            </div>
                            <div class="w-16 mx-auto bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $row->persentase >= 85 ? 'bg-emerald-500' : ($row->persentase >= 75 ? 'bg-indigo-500' : 'bg-rose-500') }}" style="width: {{ $row->persentase }}%"></div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center font-mono text-slate-600">
                            {{ $row->avg_durasi }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button type="button" onclick="bukaModalLogDosen('{{ $row->dosen->id }}', '{{ addslashes($row->dosen->nama_lengkap) }}')" class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] transition border border-indigo-200 flex items-center gap-1 mx-auto">
                                <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                                <span>Rincian</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-clipboard-user text-3xl text-slate-300 mb-2"></i>
                            <div>Tidak ada data dosen yang sesuai dengan kriteria filter.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Rincian Log Presensi Bulanan per Dosen -->
<div id="modalLogDosen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]">
        <div class="p-4 bg-gradient-to-r from-[#113f6c] to-[#154e85] text-white flex items-center justify-between">
            <div>
                <div class="text-[10px] text-amber-300 uppercase font-bold tracking-wider">Log Riwayat Presensi Kantor</div>
                <h3 class="text-sm font-bold text-white mt-0.5" id="modalDosenNama">Nama Dosen</h3>
            </div>
            <button type="button" onclick="tutupModalLogDosen()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-4 flex-1 overflow-y-auto space-y-3" id="modalDosenContent">
            <!-- Konten log per tanggal akan di-render di sini via JS -->
            <div class="text-center py-8 text-slate-400 text-xs">Memuat data...</div>
        </div>

        <div class="p-3 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="button" onclick="tutupModalLogDosen()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Data JSON Log Presensi untuk Modal -->
<script>
    const rekapDataJson = @json($rekapDosen);

    function bukaModalLogDosen(dosenId, dosenNama) {
        document.getElementById('modalDosenNama').innerText = dosenNama;
        const container = document.getElementById('modalDosenContent');
        container.innerHTML = '';

        const item = rekapDataJson.find(r => r.dosen.id == dosenId);

        if (!item || !item.logs || item.logs.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-slate-400 text-xs">
                    <i class="fa-solid fa-calendar-xmark text-2xl text-slate-300 mb-1"></i>
                    <div>Tidak ada catatan presensi pada periode bulan ini.</div>
                </div>
            `;
        } else {
            let html = `
                <div class="grid grid-cols-3 gap-2 mb-3 text-center text-xs">
                    <div class="p-2 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <div class="font-extrabold text-emerald-700 text-base">${item.hadir_tepat}</div>
                        <div class="text-[10px] text-emerald-600 font-semibold">Tepat Waktu</div>
                    </div>
                    <div class="p-2 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="font-extrabold text-amber-700 text-base">${item.terlambat}</div>
                        <div class="text-[10px] text-amber-600 font-semibold">Terlambat</div>
                    </div>
                    <div class="p-2 bg-indigo-50 border border-indigo-200 rounded-xl">
                        <div class="font-extrabold text-indigo-700 text-base">${item.persentase}%</div>
                        <div class="text-[10px] text-indigo-600 font-semibold">Kehadiran</div>
                    </div>
                </div>
                <div class="space-y-2">
            `;

            // Sort log
            const logsArray = Object.values(item.logs);
            logsArray.forEach(log => {
                const badgeClass = log.status === 'hadir' 
                    ? 'bg-emerald-100 text-emerald-800 border-emerald-200' 
                    : (log.status === 'terlambat' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-slate-200 text-slate-700 border-slate-300');

                const statusLabel = log.status === 'hadir' ? 'Tepat Waktu' : (log.status === 'terlambat' ? 'Terlambat' : log.status);

                html += `
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-800">${log.tanggal}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Masuk: <strong>${log.jam_masuk ? log.jam_masuk.substring(0, 5) : '-'}</strong> &bull; Pulang: <strong>${log.jam_pulang ? log.jam_pulang.substring(0, 5) : '-'}</strong> &bull; Durasi: ${log.durasi_kerja || '-'}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badgeClass}">
                                ${statusLabel}
                            </span>
                            <div class="text-[10px] text-slate-400 mt-0.5">Radius: ${log.jarak_masuk_meter ? log.jarak_masuk_meter + 'm' : '-'}</div>
                        </div>
                    </div>
                `;
            });

            html += `</div>`;
            container.innerHTML = html;
        }

        document.getElementById('modalLogDosen').classList.remove('hidden');
    }

    function tutupModalLogDosen() {
        document.getElementById('modalLogDosen').classList.add('hidden');
    }
</script>
@endsection
