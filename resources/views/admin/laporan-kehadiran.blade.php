@extends('layouts.app')

@section('title', 'Rekapitulasi Kehadiran Dosen & BKD Serdos')

@section('content')
<div class="space-y-6">

    <!-- Header Laporan -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-amber-400/20 text-amber-300 border border-amber-400/30 px-2.5 py-0.5 rounded-full font-bold uppercase">
                    Monitoring BKD & Serdos
                </span>
                <span class="text-xs text-slate-300">| Rekapitulasi Kehadiran Dosen Institusi</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white mt-1">Laporan Kehadiran & Portofolio BKD Dosen</h1>
            <p class="text-xs text-slate-300 mt-1">
                Pantau realisasi 16 pertemuan perkuliahan per kelas, status validasi geofencing GPS dosen, kelayakan Serdos SISTER, dan sinkronisasi Feeder PDDikti.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Tombol Ekspor CSV / Excel -->
            <a href="{{ route('admin.laporan-kehadiran.export', request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-file-excel"></i> Ekspor CSV / Excel
            </a>

            <!-- Tombol Push Massal ke Feeder -->
            <form action="{{ route('admin.feeder.push-batch') }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin melakukan sinkronisasi batch semua pertemuan pending ke Neo Feeder PDDikti?')" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Push Batch Feeder
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Kelas Terpantau</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $summary['total_kelas'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">Rombel aktif semester ini</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Memenuhi Syarat BKD</div>
            <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $summary['total_memenuhi'] }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1"><i class="fa-solid fa-circle-check"></i> Realisasi &ge; 16 Sesi</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Belum Memenuhi BKD</div>
            <div class="text-3xl font-extrabold text-amber-500 mt-1">{{ $summary['total_belum'] }}</div>
            <div class="text-[11px] text-amber-600 font-semibold mt-1"><i class="fa-solid fa-clock"></i> Masih Berproses (&lt; 16 Sesi)</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Tersinkron Feeder</div>
            <div class="text-3xl font-extrabold text-blue-600 mt-1">{{ $summary['total_synced_feeder'] }}</div>
            <div class="text-[11px] text-blue-600 font-medium mt-1">Sesi ter-push ke PDDikti</div>
        </div>
    </div>

    <!-- Form Filter & Pencarian -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form action="{{ route('admin.laporan-kehadiran') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Filter Tahun Akademik -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tahun Akademik</label>
                <select name="tahun_akademik_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Semester</option>
                    @foreach($semuaTa as $ta)
                        <option value="{{ $ta->id }}" {{ $selectedTa == $ta->id ? 'selected' : '' }}>
                            {{ $ta->nama_semester }} {{ $ta->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Program Studi -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Program Studi</label>
                <select name="program_studi_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Program Studi</option>
                    @foreach($semuaProdi as $p)
                        <option value="{{ $p->id }}" {{ $selectedProdi == $p->id ? 'selected' : '' }}>
                            {{ $p->jenjang }} {{ $p->nama_prodi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Kelayakan BKD -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Status Kelayakan BKD</label>
                <select name="status_bkd" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="semua" {{ $statusBkd === 'semua' ? 'selected' : '' }}>Semua Status BKD</option>
                    <option value="memenuhi" {{ $statusBkd === 'memenuhi' ? 'selected' : '' }}>Memenuhi Syarat (&ge; 16 Sesi)</option>
                    <option value="belum" {{ $statusBkd === 'belum' ? 'selected' : '' }}>Belum Memenuhi (&lt; 16 Sesi)</option>
                </select>
            </div>

            <!-- Pencarian Bebas -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Cari Dosen / Matkul / Kelas</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama dosen / MK..."
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>
                <a href="{{ route('admin.laporan-kehadiran') }}" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Data Rekapitulasi Kehadiran Dosen -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-table-list text-blue-600"></i> Rekapitulasi Realisasi Mengajar & BKD
                </h3>
                <p class="text-xs text-slate-500">Daftar kelas perkuliahan, realisasi sesi BAP, kelayakan beban kerja dosen, dan status sinkronisasi feeder</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                Total: {{ $rekapData->count() }} Data Kelas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Dosen Pengampu</th>
                        <th class="py-3 px-4">Mata Kuliah & Kelas</th>
                        <th class="py-3 px-4">Program Studi</th>
                        <th class="py-3 px-4 text-center">Realisasi Sesi (Target 16)</th>
                        <th class="py-3 px-4 text-center">Status BKD Serdos</th>
                        <th class="py-3 px-4 text-center">Feeder PDDikti</th>
                        <th class="py-3 px-4 text-center">Pengesahan Kaprodi</th>
                        <th class="py-3 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekapData as $index => $item)
                        @php
                            $kelas = $item->kelas;
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-mono font-medium">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-3.5 px-4">
                                @forelse($kelas->dosens as $dsn)
                                    <div class="font-bold text-slate-800 text-xs">{{ $dsn->nama_lengkap }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIDN: {{ $dsn->nidn }}</div>
                                @empty
                                    <span class="text-slate-400 italic">Belum ada dosen</span>
                                @endforelse
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $kelas->mataKuliah->nama_mk ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span class="bg-blue-100 text-blue-800 font-bold px-1.5 py-0.2 rounded">{{ $kelas->nama_kelas }}</span>
                                    <span>{{ $kelas->mataKuliah->kode_mk ?? '-' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $kelas->mataKuliah->sks ?? 0 }} SKS</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700 font-semibold">{{ $kelas->mataKuliah->programStudi->nama_prodi ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $kelas->mataKuliah->programStudi->jenjang ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="font-extrabold text-sm {{ $item->is_memenuhi_bkd ? 'text-emerald-600' : 'text-slate-800' }}">
                                    {{ $item->total_selesai }} / 16
                                </div>
                                <div class="w-24 bg-slate-200 h-2 rounded-full mx-auto mt-1 overflow-hidden">
                                    <div class="h-full rounded-full {{ $item->is_memenuhi_bkd ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min(100, $item->persentase) }}%"></div>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $item->persentase }}% Selesai</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->is_memenuhi_bkd)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-circle-check"></i> Memenuhi Syarat
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-clock"></i> Belum Memenuhi
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="font-bold text-xs {{ $item->synced_feeder > 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                                    {{ $item->synced_feeder }} / {{ $item->total_selesai }} Sesi
                                </div>
                                @if($item->pending_feeder > 0)
                                    <div class="text-[10px] text-amber-600 font-semibold mt-0.5">
                                        {{ $item->pending_feeder }} Pending
                                    </div>
                                @else
                                    <div class="text-[10px] text-emerald-600 font-medium mt-0.5">
                                        Sinkron
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->pengesahan && $item->pengesahan->status_pengesahan === 'sah')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800" title="Disahkan oleh: {{ $item->pengesahan->disahkan_oleh ?? 'Kaprodi' }}">
                                        <i class="fa-solid fa-stamp text-blue-600"></i> Sah Kaprodi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                        <i class="fa-solid fa-hourglass-half text-slate-400"></i> Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Portofolio BKD -->
                                    <a href="{{ route('dosen.portofolio-bkd', $kelas->id) }}" class="p-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg border border-blue-200 transition" title="Lihat Portofolio BKD">
                                        <i class="fa-solid fa-certificate"></i>
                                    </a>

                                    <!-- Cetak PDF -->
                                    <a href="{{ route('bkd.cetak', $kelas->id) }}" target="_blank" class="p-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition" title="Cetak Berkas BKD SISTER">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    <!-- Push Feeder Kelas -->
                                    <form action="{{ route('admin.feeder.push-kelas', $kelas->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg border border-amber-200 transition" title="Push Sesi Kelas ke Neo Feeder" onclick="return confirm('Kirimkan seluruh sesi selesai kelas ini ke Feeder PDDikti?')">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-4xl mb-2 text-slate-300"></i>
                                <div class="font-bold text-slate-600">Tidak ada data kelas yang sesuai filter.</div>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kriteria pencarian atau pilih tahun akademik lain.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
