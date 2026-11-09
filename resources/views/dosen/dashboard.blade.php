@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
<div class="space-y-6">

    <!-- Header Profil Dosen & Status Semester -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-2xl font-bold text-emerald-400">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight">{{ $dosen->nama_gelar }}</h1>
                    <span class="text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2.5 py-0.5 rounded-full font-medium">Dosen Aktif</span>
                </div>
                <div class="text-xs text-slate-300 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span><i class="fa-solid fa-id-card text-emerald-400 mr-1"></i> NIDN: <strong>{{ $dosen->nidn }}</strong></span>
                    <span><i class="fa-solid fa-building-columns text-emerald-400 mr-1"></i> Prodi: <strong>{{ $dosen->prodi ? $dosen->prodi->nama_prodi : 'Teknik Informatika' }}</strong></span>
                    <span><i class="fa-regular fa-calendar-check text-emerald-400 mr-1"></i> Semester: <strong>{{ $taAktif ? $taAktif->nama : '2024/2025 Ganjil' }}</strong></span>
                </div>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 text-right">
            <div class="text-[11px] text-slate-300 uppercase tracking-wider font-semibold">Beban Mengajar Semester Ini</div>
            <div class="text-2xl font-extrabold text-emerald-400">
                {{ number_format($totalSks, 1) }} <span class="text-sm font-medium text-white">SKS</span>
            </div>
            <div class="text-[10px] text-emerald-300">
                @if($totalSks >= 12 && $totalSks <= 16)
                    <i class="fa-solid fa-circle-check"></i> Memenuhi rentang BKD (12-16 SKS)
                @else
                    <i class="fa-solid fa-circle-info"></i> Sesuai penugasan institusi
                @endif
            </div>
        </div>
    </div>

    <!-- Banner Presensi Harian Kantor Dosen (In / Out) -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/30 border border-indigo-400/40 text-amber-300 flex items-center justify-center text-xl shadow-inner">
                <i class="fa-solid fa-business-time"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 px-2 py-0.5 rounded font-bold uppercase tracking-wider">
                        Presensi Harian Kantor Dosen
                    </span>
                    @if($presensiHariIni && $presensiHariIni->jam_masuk)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $presensiHariIni->status === 'hadir' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                            <i class="fa-solid fa-check"></i> Sudah Clock-In ({{ substr($presensiHariIni->jam_masuk, 0, 5) }} WIB)
                        </span>
                    @else
                        <span class="text-[10px] bg-rose-500/20 text-rose-300 border border-rose-500/30 px-2 py-0.5 rounded font-bold">
                            <i class="fa-solid fa-clock"></i> Belum Masuk Kantor
                        </span>
                    @endif
                </div>
                <h3 class="font-bold text-white text-base mt-1">
                    Kehadiran Kerja Harian di Kampus (In / Out)
                </h3>
                <p class="text-xs text-slate-300 mt-0.5">
                    Standar Jam Kantor: <strong>{{ $settings['jam_masuk_kantor'] ?? '08:00' }} - {{ $settings['jam_pulang_kantor'] ?? '16:00' }} WIB</strong> &bull; Validasi Geofencing GPS & Foto Selfie Wajah
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('dosen.presensi-harian') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-fingerprint"></i> Buka Presensi Kantor (In / Out) &rarr;
            </a>
        </div>
    </div>

    <!-- Sesi Perkuliahan Berlangsung (Jika Ada) -->
    @if($pertemuanBerlangsung)
    <div class="bg-amber-50 border-2 border-amber-400 rounded-2xl p-5 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4 animate-pulse">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-md">
                <i class="fa-solid fa-broadcast-tower"></i>
            </div>
            <div>
                <span class="text-xs bg-amber-200 text-amber-900 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Perkuliahan Sedang Berlangsung</span>
                <h3 class="font-bold text-slate-900 text-base mt-0.5">
                    {{ $pertemuanBerlangsung->kelasKuliah->mataKuliah->nama_mk }} ({{ $pertemuanBerlangsung->kelasKuliah->nama_kelas }})
                </h3>
                <p class="text-xs text-slate-600">
                    Pertemuan ke-{{ $pertemuanBerlangsung->pertemuan_ke }} &bull; Dimulai sejak {{ $pertemuanBerlangsung->jam_mulai_aktual }} WIB &bull; {{ $pertemuanBerlangsung->ruangan ? $pertemuanBerlangsung->ruangan->nama_ruangan : 'Lab' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dosen.buka-kelas', $pertemuanBerlangsung->id) }}" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Masuk Ruang Kelas / BAP
            </a>
        </div>
    </div>
    @endif

    <!-- Jadwal & Pertemuan Hari Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-emerald-600"></i> Agenda Mengajar & Presensi Hari Ini
                </h2>
                <p class="text-xs text-slate-500">Mencatat presensi GPS & berita acara perkuliahan (BAP) real-time</p>
            </div>
            <span class="text-xs bg-slate-100 text-slate-700 px-3 py-1 rounded-full font-semibold">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </span>
        </div>

        @if($pertemuanHariIni->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($pertemuanHariIni as $ptm)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-emerald-300 transition shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">
                                    Pertemuan ke-{{ $ptm->pertemuan_ke }} ({{ strtoupper($ptm->jenis_pertemuan) }})
                                </span>
                                <span class="text-slate-500 font-mono">
                                    <i class="fa-regular fa-clock mr-1"></i> {{ substr($ptm->jam_mulai_jadwal, 0, 5) }} - {{ substr($ptm->jam_selesai_jadwal, 0, 5) }} WIB
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base">
                                {{ $ptm->kelasKuliah->mataKuliah->nama_mk }}
                            </h3>
                            <div class="text-xs text-slate-600 mt-1 flex items-center gap-3">
                                <span><i class="fa-solid fa-users-viewfinder text-slate-400 mr-1"></i> Kelas: <strong>{{ $ptm->kelasKuliah->nama_kelas }}</strong></span>
                                <span><i class="fa-solid fa-door-open text-slate-400 mr-1"></i> {{ $ptm->ruangan ? $ptm->ruangan->nama_ruangan : 'Laboratorium' }}</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-2 line-clamp-1 italic">
                                Rencana Materi: {{ $ptm->rencana_materi ?: 'Sesuai silabus/RPS' }}
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-xs font-semibold {{ $ptm->status === 'selesai' ? 'text-emerald-600' : ($ptm->status === 'berlangsung' ? 'text-amber-600 font-bold' : 'text-slate-500') }}">
                                <i class="fa-solid fa-circle text-[8px] mr-1"></i> Status: {{ ucfirst($ptm->status) }}
                            </span>
                            <a href="{{ route('dosen.buka-kelas', $ptm->id) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                                <i class="fa-solid fa-location-crosshairs"></i> Buka Kelas & Presensi
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-slate-400">
                <i class="fa-regular fa-calendar-xmark text-4xl mb-2"></i>
                <p class="text-sm">Tidak ada jadwal mengajar pada hari ini.</p>
                <p class="text-xs text-slate-400">Silakan periksa daftar kelas kuliah di bawah untuk melihat rekapitulasi portofolio BKD.</p>
            </div>
        @endif
    </div>

    <!-- Daftar Kelas Kuliah & Portofolio BKD (Indikator 16 Pertemuan) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-folder-tree text-emerald-600"></i> Kelas Kuliah & Pemenuhan Syarat BKD (16 Pertemuan)
                </h2>
                <p class="text-xs text-slate-500">Pelaporan portofolio mengajar terstandar untuk Sertifikasi Dosen (Serdos) & SISTER Kemendikbud</p>
            </div>
            <div class="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl font-semibold">
                Target Standar: 16 Pertemuan (Termasuk UTS & UAS)
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($kelasDosens as $kd)
                @php
                    $kelas = $kd->kelasKuliah;
                    $totalSelesai = $kelas->pertemuans->where('status', 'selesai')->count();
                    $persen = round(($totalSelesai / 16) * 100);
                    $isMemenuhi = $totalSelesai >= 16;
                @endphp
                <div class="border border-slate-200 rounded-2xl p-5 hover:border-emerald-400 transition bg-white shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[11px] font-mono font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded">
                                    {{ $kelas->mataKuliah->kode_mk }}
                                </span>
                                <h3 class="font-bold text-slate-900 text-lg mt-1">
                                    {{ $kelas->mataKuliah->nama_mk }}
                                </h3>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Kelas: <strong class="text-slate-800">{{ $kelas->nama_kelas }}</strong> &bull;
                                    SKS: <strong class="text-slate-800">{{ $kelas->mataKuliah->sks }} SKS</strong> &bull;
                                    Porsi Dosen: <strong class="text-emerald-700">{{ $kd->sks_substansi }} SKS</strong>
                                    @if(!$kd->is_koordinator)
                                        <span class="ml-1 text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">Team Teaching</span>
                                    @endif
                                </div>
                            </div>

                            @if($isMemenuhi)
                                <span class="bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold px-2.5 py-1 rounded-xl flex items-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-certificate text-emerald-600"></i> Siap BKD
                                </span>
                            @else
                                <span class="bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold px-2.5 py-1 rounded-xl flex items-center gap-1">
                                    <i class="fa-solid fa-hourglass-half text-amber-600"></i> {{ $totalSelesai }}/16 Sesi
                                </span>
                            @endif
                        </div>

                        <!-- Progress Bar 16 Pertemuan BKD -->
                        <div class="mt-5">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="font-semibold text-slate-700">Progres Pemenuhan Tatap Muka:</span>
                                <span class="font-mono font-bold text-slate-800">{{ $totalSelesai }} / 16 Pertemuan ({{ $persen }}%)</span>
                            </div>
                            <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                <div class="h-full rounded-full transition-all duration-500 {{ $isMemenuhi ? 'bg-emerald-500' : 'bg-gradient-to-r from-amber-400 to-emerald-500' }}" style="width: {{ min($persen, 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Matrix Preview 16 Boxes -->
                        <div class="grid grid-cols-8 gap-1.5 mt-3">
                            @for($i = 1; $i <= 16; $i++)
                                @php
                                    $ptmBox = $kelas->pertemuans->firstWhere('pertemuan_ke', $i);
                                    $statusBox = $ptmBox ? $ptmBox->status : 'none';
                                @endphp
                                <div class="text-center py-1 text-[10px] font-bold rounded {{ $statusBox === 'selesai' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($statusBox === 'berlangsung' ? 'bg-amber-100 text-amber-800 border border-amber-300 animate-pulse' : 'bg-slate-100 text-slate-400 border border-slate-200') }}" title="Pertemuan ke-{{ $i }}: {{ ucfirst($statusBox) }}">
                                    {{ $i }}
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                        <span class="text-xs text-slate-500">
                            <i class="fa-solid fa-network-wired text-slate-400 mr-1"></i> Sync Feeder:
                            <strong class="text-emerald-700">{{ $kelas->pertemuans->where('status_sync_feeder', 'synced')->count() }}/{{ $totalSelesai }}</strong>
                        </span>
                        <a href="{{ route('dosen.portofolio-bkd', $kelas->id) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-file-shield"></i> Buka Portofolio BKD
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
