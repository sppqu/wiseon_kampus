@extends('layouts.app')

@section('title', 'Dashboard Administrator BAAK')

@section('content')
<div class="space-y-6">

    <!-- Header Admin -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-blue-500/20 text-blue-300 border border-blue-500/30 px-2.5 py-0.5 rounded-full font-bold uppercase">
                    Biro Administrasi Akademik & Kemahasiswaan (BAAK)
                </span>
                <span class="text-xs text-slate-300">| Panel Administrator Pusat</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white mt-1">{{ auth()->user()->name }}</h1>
            <p class="text-xs text-slate-300 mt-1">
                Kelola integrasi PDDikti Neo Feeder, pemantauan data presensi institusi, dan sinkronisasi berkas mengajar SISTER.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.laporan-kehadiran') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-file-invoice"></i> Rekapitulasi Kehadiran
            </a>
            <a href="{{ route('admin.feeder') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-network-wired"></i> Kelola Feeder PDDikti
            </a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Dosen</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $totalDosen }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1"><i class="fa-solid fa-check"></i> Terdaftar di Kampus</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Kelas Kuliah</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $totalKelas }}</div>
            <div class="text-[11px] text-blue-600 font-semibold mt-1"><i class="fa-solid fa-layer-group"></i> Semester Berjalan</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Kesiapan BKD (&ge; 16 Sesi)</div>
            <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $kelasBkdMemenuhi }} <span class="text-xs text-slate-400 font-normal">/ {{ $totalKelas }} Kelas</span></div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1"><i class="fa-solid fa-shield-check"></i> Memenuhi Syarat SISTER</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Pertemuan Selesai</div>
            <div class="text-3xl font-extrabold text-blue-600 mt-1">{{ $totalPertemuan }}</div>
            <div class="text-[11px] text-blue-600 font-semibold mt-1"><i class="fa-solid fa-file-shield"></i> BAP Terverifikasi</div>
        </div>
    </div>

    <!-- Live Monitoring: Sesi Perkuliahan Hari Ini & Geofencing GPS -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i class="fa-solid fa-satellite-dish text-blue-600"></i> Monitoring Perkuliahan & Geofencing GPS Dosen
                </h3>
                <p class="text-xs text-slate-500">Pemantauan real-time status kehadiran fisik dosen di ruangan kampus dan pengisian Berita Acara (BAP)</p>
            </div>
            <a href="{{ route('admin.laporan-kehadiran') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                Lihat Seluruh Laporan &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Mata Kuliah & Kelas</th>
                        <th class="py-3 px-3">Dosen Pengampu</th>
                        <th class="py-3 px-3">Ruangan & Jadwal</th>
                        <th class="py-3 px-3 text-center">Validasi GPS Dosen</th>
                        <th class="py-3 px-3 text-center">Kehadiran Mahasiswa</th>
                        <th class="py-3 px-3 text-center">BAP & Feeder</th>
                        <th class="py-3 px-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kelasHariIni as $ptm)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($ptm->status === 'berlangsung')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 animate-pulse text-[11px]">
                                        <i class="fa-solid fa-circle text-[8px] text-amber-600"></i> Berlangsung
                                    </span>
                                @elseif($ptm->status === 'selesai')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 text-[11px]">
                                        <i class="fa-solid fa-check text-[10px]"></i> Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold bg-slate-100 text-slate-700 text-[11px]">
                                        <i class="fa-regular fa-clock text-[10px]"></i> Terjadwal
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-800">{{ $ptm->kelasKuliah->mataKuliah->nama_mk ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-2">
                                    <span class="bg-blue-100 text-blue-800 font-bold px-1.5 py-0.2 rounded text-[10px]">{{ $ptm->kelasKuliah->nama_kelas }}</span>
                                    <span>Sesi {{ $ptm->pertemuan_ke }} ({{ strtoupper($ptm->jenis_pertemuan) }})</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-semibold text-slate-800">{{ $ptm->dosen->nama_lengkap ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">NIDN: {{ $ptm->dosen->nidn ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-semibold text-slate-700">{{ $ptm->ruangan ? $ptm->ruangan->nama_ruangan : 'Laboratorium' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ substr($ptm->jam_mulai_jadwal, 0, 5) }} - {{ substr($ptm->jam_selesai_jadwal, 0, 5) }} WIB
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if($ptm->validasi_lokasi === 'valid')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-location-dot text-emerald-600"></i> Dalam Kampus ({{ $ptm->jarak_meter ?? 18 }}m)
                                    </span>
                                @elseif($ptm->validasi_lokasi === 'luar_radius')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Luar Radius ({{ $ptm->jarak_meter }}m)
                                    </span>
                                @elseif($ptm->metode === 'daring')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-video text-blue-600"></i> Kuliah Daring
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                        <i class="fa-solid fa-map-pin text-slate-400"></i> Menunggu GPS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="bg-blue-50 text-blue-800 font-bold px-2 py-0.5 rounded border border-blue-200 text-[11px]">
                                    {{ $ptm->presensiMahasiswas->where('status', 'hadir')->count() }} / {{ $ptm->presensiMahasiswas->count() }} Hadir
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $ptm->persen_hadir_mahasiswa }}%</div>
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                @if($ptm->status_sync_feeder === 'synced')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-cloud-check"></i> Feeder Synced
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-hourglass-start"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('dosen.portofolio-bkd', $ptm->kelas_kuliah_id) }}" class="p-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg border border-blue-200 transition" title="Lihat Portofolio BKD">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($ptm->status === 'selesai' && $ptm->status_sync_feeder !== 'synced')
                                        <form action="{{ route('admin.feeder.push-pertemuan', $ptm->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition" title="Push ke Feeder PDDikti">
                                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Tidak ada aktivitas perkuliahan aktif saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Web Service Feeder & Recent Sync Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Feeder Status Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-server text-blue-600"></i> Status Neo Feeder
                </h3>
                <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">
                    {{ strtoupper($feederConfig->mode ?? 'SANDBOX') }}
                </span>
            </div>

            <div class="space-y-2 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200 font-mono">
                <div>URL: <span class="text-slate-700">{{ $feederConfig->url ?? 'http://localhost:8100/ws/live2.php' }}</span></div>
                <div>User: <span class="text-slate-700">{{ $feederConfig->username ?? 'feeder_admin' }}</span></div>
                <div>PT: <span class="text-slate-700">{{ $feederConfig->nama_pt ?? 'Universitas Teknologi Nusantara' }}</span></div>
            </div>

            <div class="pt-2 flex items-center gap-2">
                <form action="{{ route('admin.feeder.test') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-plug-circle-check"></i> Uji Koneksi Feeder
                    </button>
                </form>
                <a href="{{ route('admin.feeder') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    Setting &rarr;
                </a>
            </div>
        </div>

        <!-- Recent Pertemuan Real-time -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Perkuliahan Terbaru Terverifikasi
                </h3>
                <span class="text-xs text-slate-500">Real-time attendance feed</span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @foreach($pertemuanTerbaru as $ptm)
                <div class="py-3 flex items-center justify-between gap-3">
                    <div>
                        <div class="font-bold text-slate-800">{{ $ptm->kelasKuliah->mataKuliah->nama_mk }} ({{ $ptm->kelasKuliah->nama_kelas }})</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            Pertemuan ke-{{ $ptm->pertemuan_ke }} &bull; Dosen: <strong>{{ $ptm->dosen->nama_gelar }}</strong> &bull; {{ $ptm->tanggal->format('d M Y') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded">
                            {{ $ptm->persen_hadir_mahasiswa }}% Hadir
                        </span>
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $ptm->jam_mulai_aktual }} - {{ $ptm->jam_selesai_aktual }} WIB</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
