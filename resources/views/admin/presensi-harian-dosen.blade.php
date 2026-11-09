@extends('layouts.app')

@section('title', 'Presensi Harian Kantor Dosen')

@section('content')
<div class="space-y-6">

    <!-- Header Admin -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-0.5 rounded-full font-bold uppercase">
                    Kepegawaian & SDM Kampus
                </span>
                <span class="text-xs text-slate-300">| Presensi Masuk / Pulang Kantor</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white mt-1">Presensi Masuk & Pulang Kantor Dosen (In / Out)</h1>
            <p class="text-xs text-slate-300 mt-1">
                Pantau kehadiran harian dosen di lingkungan kampus, jam kerja kantor (In / Out), status keterlambatan, dan validasi Geofencing GPS.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Navigasi ke Rekapitulasi Kehadiran Bulanan -->
            <a href="{{ route('admin.rekap-kehadiran-dosen') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-500 text-slate-900 font-extrabold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-chart-pie"></i> Laporan Rekap Kehadiran
            </a>

            <!-- Tombol Ekspor CSV -->
            <a href="{{ route('admin.presensi-harian-dosen.export', request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-file-excel"></i> Ekspor CSV Harian
            </a>

            <!-- Tombol Pengaturan Jam Kerja -->
            <a href="{{ route('admin.pengaturan-presensi') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-sliders"></i> Pengaturan Jam Kerja
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Dosen</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $summary['total_dosen'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">Terdaftar di institusi</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Hadir Tepat Waktu</div>
            <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $summary['hadir_tepat_waktu'] }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1"><i class="fa-solid fa-circle-check"></i> Sebelum batas toleransi</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Terlambat Masuk</div>
            <div class="text-3xl font-extrabold text-amber-500 mt-1">{{ $summary['terlambat'] }}</div>
            <div class="text-[11px] text-amber-600 font-semibold mt-1"><i class="fa-solid fa-clock"></i> Melewati batas jam masuk</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Belum Presensi</div>
            <div class="text-3xl font-extrabold text-rose-500 mt-1">{{ $summary['belum_absen'] }}</div>
            <div class="text-[11px] text-rose-600 font-medium mt-1">Belum melakukan clock in</div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form action="{{ route('admin.presensi-harian-dosen') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Filter Tanggal -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tanggal Presensi</label>
                <input type="date" name="tanggal" value="{{ $tanggalPilih }}"
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Filter Program Studi -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Program Studi</label>
                <select name="program_studi_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Semua Program Studi</option>
                    @foreach($semuaProdi as $p)
                        <option value="{{ $p->id }}" {{ $prodiPilih == $p->id ? 'selected' : '' }}>
                            {{ $p->jenjang }} {{ $p->nama_prodi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Status Kehadiran</label>
                <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="semua" {{ $statusPilih === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="hadir" {{ $statusPilih === 'hadir' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                    <option value="terlambat" {{ $statusPilih === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="belum_absen" {{ $statusPilih === 'belum_absen' ? 'selected' : '' }}>Belum Absen</option>
                </select>
            </div>

            <!-- Pencarian Bebas -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Cari Nama Dosen / NIDN</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama dosen..."
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.presensi-harian-dosen') }}" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Monitoring Presensi Kantor Dosen -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-user text-indigo-600"></i> Rekapitulasi Presensi Kantor (In / Out)
                </h3>
                <p class="text-xs text-slate-500">Tanggal: <strong>{{ \Carbon\Carbon::parse($tanggalPilih)->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong></p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                Total: {{ $dataPresensi->count() }} Dosen
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 text-center w-12">No</th>
                        <th class="py-3 px-4">Dosen & NIDN</th>
                        <th class="py-3 px-4">Program Studi</th>
                        <th class="py-3 px-4 text-center">Jam Masuk (In)</th>
                        <th class="py-3 px-4 text-center">Jam Pulang (Out)</th>
                        <th class="py-3 px-4 text-center">Durasi Kerja</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Geofencing GPS</th>
                        <th class="py-3 px-4 text-center">Foto Selfie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dataPresensi as $item)
                        @php
                            $dosen = $item->dosen;
                        @endphp
                        <tr class="hover:bg-indigo-50/30 transition">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-mono">{{ $loop->iteration }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-xs">{{ $dosen->nama_lengkap }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">NIDN: {{ $dosen->nidn ?: '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700 font-semibold">{{ $dosen->prodi->nama_prodi ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $dosen->prodi->jenjang ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-700">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-amber-700">
                                {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-medium text-slate-600">
                                {{ $item->durasi }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->status_kehadiran === 'hadir')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[10px]"></i> Tepat Waktu
                                    </span>
                                @elseif($item->status_kehadiran === 'terlambat')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Terlambat
                                    </span>
                                @elseif($item->status_kehadiran === 'belum_absen')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-100 text-rose-800">
                                        Belum Absen
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                        {{ ucfirst($item->status_kehadiran) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->validasi_lokasi === 'valid')
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        <i class="fa-solid fa-location-dot"></i> Dalam Radius ({{ $item->jarak_meter ?? 15 }}m)
                                    </span>
                                @elseif($item->validasi_lokasi === 'luar_radius')
                                    <span class="text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                        Luar Radius
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($item->foto_masuk)
                                        <a href="{{ asset('storage/' . $item->foto_masuk) }}" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100" title="Foto Masuk">
                                            <i class="fa-solid fa-image"></i> In
                                        </a>
                                    @endif
                                    @if($item->foto_pulang)
                                        <a href="{{ asset('storage/' . $item->foto_pulang) }}" target="_blank" class="p-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100" title="Foto Pulang">
                                            <i class="fa-solid fa-camera"></i> Out
                                        </a>
                                    @endif
                                    @if(!$item->foto_masuk && !$item->foto_pulang)
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                Tidak ada data dosen yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
