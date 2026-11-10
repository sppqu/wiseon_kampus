<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#154e85">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Presensi Mobile - {{ $dosen->nama_lengkap }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts Inter / Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }
        /* Efek radar pulsa geofencing */
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.18); opacity: 0.25; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .radar-pulse {
            animation: pulse-ring 2.5s infinite ease-in-out;
        }
        /* Scanning line selfie viewfinder */
        @keyframes scan-move {
            0% { top: 0%; opacity: 0; }
            50% { opacity: 1; }
            100% { top: 96%; opacity: 0; }
        }
        .scanner-line {
            animation: scan-move 2.2s infinite ease-in-out;
        }
        /* Mobile safe area support */
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 1.25rem);
        }
        /* Smooth transitions */
        .tab-content {
            transition: opacity 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-800 antialiased min-h-screen flex justify-center">

    <!-- Container Utama Mobile (Khusus Layar Smartphone) -->
    <div class="w-full max-w-md bg-slate-50 min-h-screen flex flex-col relative shadow-2xl overflow-x-hidden">

        <!-- Top Header Mobile App (Hanya tampil pada Beranda / Home) -->
        <header id="mobileMainHeader" class="bg-gradient-to-r from-[#113f6c] via-[#154e85] to-[#1e61a3] text-white p-4 pt-5 pb-5 rounded-b-3xl shadow-lg relative z-20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-300 font-extrabold text-sm shadow-inner">
                        <i class="fa-solid fa-graduation-cap text-base"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-amber-300 font-extrabold uppercase tracking-wider">Aplikasi Presensi Kampus</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        </div>
                        <h1 class="text-sm font-bold text-white leading-tight truncate max-w-[210px]">{{ $dosen->nama_lengkap }}</h1>
                        <div class="text-[10px] text-blue-200">NIDN: {{ $dosen->nidn ?? '-' }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="ambilLokasiMobile(true)" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 active:scale-95 flex items-center justify-center text-white text-xs transition border border-white/20" title="Refresh GPS">
                        <i class="fa-solid fa-arrows-rotate text-sm"></i>
                    </button>
                    <button type="button" onclick="switchMainTab('tabPengaturan')" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 active:scale-95 flex items-center justify-center text-amber-300 text-xs transition border border-white/20" title="Profil & Pengaturan">
                        <i class="fa-solid fa-gear text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Jam Digital Realtime & Indikator Shift Kerja -->
            <div class="mt-3.5 bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-3 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-200 uppercase font-semibold">
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </div>
                    <div class="text-2xl font-black font-mono text-amber-300 tracking-tight" id="mobileClock">
                        {{ now()->format('H:i:s') }}
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-[10px] text-slate-300 font-semibold uppercase">Jadwal Kantor:</div>
                    <div class="text-xs font-extrabold text-white font-mono">
                        {{ $settings['jam_masuk_kantor'] }} - {{ $settings['jam_pulang_kantor'] }}
                    </div>
                    @if(!empty($settings['is_hari_ini_kerja']))
                        <span class="inline-block mt-0.5 text-[9px] font-bold px-2 py-0.5 bg-emerald-500/25 text-emerald-300 border border-emerald-400/30 rounded-full">
                            <i class="fa-solid fa-circle-check text-[8px]"></i> Hari Kerja
                        </span>
                    @else
                        <span class="inline-block mt-0.5 text-[9px] font-bold px-2 py-0.5 bg-amber-500/25 text-amber-300 border border-amber-400/30 rounded-full">
                            Non-Jadwal
                        </span>
                    @endif
                </div>
            </div>
        </header>

        <!-- Flash Message Alerts -->
        <div id="mobileAlertWrapper" class="px-4 -mt-2 z-30">
            @if(session('success'))
                <div class="p-3 bg-emerald-600 text-white rounded-2xl shadow-lg text-xs font-semibold flex items-center gap-2.5 animate-bounce">
                    <i class="fa-solid fa-circle-check text-base"></i>
                    <div class="flex-1">{{ session('success') }}</div>
                </div>
            @endif
            @if(session('warning'))
                <div class="p-3 bg-amber-600 text-white rounded-2xl shadow-lg text-xs font-semibold flex items-center gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-base"></i>
                    <div class="flex-1">{{ session('warning') }}</div>
                </div>
            @endif
            @if($errors->any())
                <div class="p-3 bg-rose-600 text-white rounded-2xl shadow-lg text-xs font-semibold flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-xmark text-base mt-0.5"></i>
                    <div class="flex-1">
                        <div class="font-bold">Terjadi Kesalahan:</div>
                        <ul class="list-disc list-inside mt-0.5 text-[11px]">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: HOME (BERANDA DASHBOARD MOBILE)     -->
        <!-- ========================================== -->
        <div id="tabHome" class="main-tab-content flex-1 p-4 space-y-4">
            <!-- Card Selamat Datang & Status Hari Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Status Kehadiran Hari Ini</div>
                        <h2 class="text-base font-extrabold text-slate-800 mt-0.5">
                            @if(!$presensiHariIni || !$presensiHariIni->jam_masuk)
                                Belum Presensi Masuk
                            @elseif($presensiHariIni && !$presensiHariIni->jam_pulang)
                                Sedang Aktif Bekerja
                            @else
                                Selesai Bertugas (Pulang)
                            @endif
                        </h2>
                    </div>
                    <div class="w-10 h-10 rounded-2xl {{ (!$presensiHariIni || !$presensiHariIni->jam_masuk) ? 'bg-rose-50 text-rose-600 border border-rose-200' : (($presensiHariIni && !$presensiHariIni->jam_pulang) ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-indigo-50 text-indigo-600 border border-indigo-200') }} flex items-center justify-center text-lg">
                        <i class="fa-solid {{ (!$presensiHariIni || !$presensiHariIni->jam_masuk) ? 'fa-clock' : (($presensiHariIni && !$presensiHariIni->jam_pulang) ? 'fa-user-check' : 'fa-flag-checkered') }}"></i>
                    </div>
                </div>

                <!-- Detail Waktu Presensi -->
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Jam Masuk</div>
                        <div class="text-sm font-extrabold text-slate-800 font-mono mt-0.5">
                            {{ $presensiHariIni && $presensiHariIni->jam_masuk ? substr($presensiHariIni->jam_masuk, 0, 5) . ' WIB' : '--:--' }}
                        </div>
                        <div class="text-[10px] mt-0.5 {{ ($presensiHariIni && $presensiHariIni->status === 'terlambat') ? 'text-rose-600 font-bold' : 'text-emerald-600 font-semibold' }}">
                            {{ $presensiHariIni ? ucfirst($presensiHariIni->status) : 'Belum Ada' }}
                        </div>
                    </div>
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Jam Pulang</div>
                        <div class="text-sm font-extrabold text-slate-800 font-mono mt-0.5">
                            {{ $presensiHariIni && $presensiHariIni->jam_pulang ? substr($presensiHariIni->jam_pulang, 0, 5) . ' WIB' : '--:--' }}
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">
                            Durasi: {{ $presensiHariIni && $presensiHariIni->durasi_kerja ? $presensiHariIni->durasi_kerja : '-' }}
                        </div>
                    </div>
                </div>

                <!-- Tombol CTA Cepat ke Tab Presensi -->
                <div class="mt-3">
                    <button type="button" onclick="switchMainTab('tabPresensi')" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 flex items-center justify-center gap-2 active:scale-95 transition">
                        <i class="fa-solid fa-fingerprint text-sm"></i>
                        <span>Buka Presensi GPS & Selfie</span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-75 ml-auto"></i>
                    </button>
                </div>
            </div>

            <!-- Pintasan Menu Cepat (Quick Actions) -->
            <div>
                <div class="text-xs font-bold text-slate-700 mb-2 px-1 flex items-center justify-between">
                    <span>Menu Cepat</span>
                    <span class="text-[10px] text-slate-400 font-normal">Pilih modul</span>
                </div>
                <div class="grid grid-cols-3 gap-2.5">
                    <button type="button" onclick="switchMainTab('tabPresensi')" class="bg-white p-3 rounded-2xl border border-slate-200 text-center shadow-sm active:scale-95 transition hover:border-indigo-300">
                        <div class="w-10 h-10 mx-auto rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg mb-1.5">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="text-xs font-bold text-slate-800">Presensi</div>
                        <div class="text-[9px] text-slate-400 mt-0.5">Kantor GPS</div>
                    </button>

                    <button type="button" onclick="switchMainTab('tabKelas')" class="bg-white p-3 rounded-2xl border border-slate-200 text-center shadow-sm active:scale-95 transition hover:border-indigo-300">
                        <div class="w-10 h-10 mx-auto rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-1.5">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div class="text-xs font-bold text-slate-800">Kelas</div>
                        <div class="text-[9px] text-slate-400 mt-0.5">{{ $pertemuanHariIni->count() }} Jadwal</div>
                    </button>

                    <button type="button" onclick="openRiwayatPresensi()" class="bg-white p-3 rounded-2xl border border-slate-200 text-center shadow-sm active:scale-95 transition hover:border-indigo-300">
                        <div class="w-10 h-10 mx-auto rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-1.5">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div class="text-xs font-bold text-slate-800">Riwayat</div>
                        <div class="text-[9px] text-slate-400 mt-0.5">{{ $totalHadir }} Hadir</div>
                    </button>
                </div>
            </div>

            <!-- Jadwal Mengajar Hari Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar-day text-indigo-600"></i>
                        <span class="text-xs font-extrabold text-slate-800">Jadwal Mengajar Hari Ini</span>
                    </div>
                    <button type="button" onclick="switchMainTab('tabKelas')" class="text-[11px] font-bold text-indigo-600 hover:underline">
                        Lihat Semua
                    </button>
                </div>

                <div class="space-y-2">
                    @forelse($pertemuanHariIni as $pt)
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ $pt->kelasKuliah->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                Kelas {{ $pt->kelasKuliah->nama_kelas ?? '-' }} &bull; Sesi Ke-{{ $pt->pertemuan_ke }} &bull; {{ $pt->ruangan->nama_ruang ?? 'Ruang Kelas' }}
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $pt->status === 'berlangsung' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ ucfirst($pt->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-4 text-slate-400 text-xs">
                        <i class="fa-solid fa-mug-hot text-2xl text-slate-300 mb-1"></i>
                        <div>Tidak ada jadwal mengajar kelas hari ini.</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Ringkasan Kehadiran Bulan Ini -->
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl p-4 shadow-md">
                <div class="text-[11px] text-slate-300 font-bold uppercase tracking-wider">Rekap Presensi Bulan Ini</div>
                <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10">
                        <div class="text-lg font-black text-amber-300">{{ $totalHadir }}</div>
                        <div class="text-[10px] text-slate-300">Total Hadir</div>
                    </div>
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10">
                        <div class="text-lg font-black text-emerald-400">{{ $totalHadir - $totalTerlambat }}</div>
                        <div class="text-[10px] text-slate-300">Tepat Waktu</div>
                    </div>
                    <div class="bg-white/10 rounded-xl p-2 border border-white/10">
                        <div class="text-lg font-black text-rose-400">{{ $totalTerlambat }}</div>
                        <div class="text-[10px] text-slate-300">Terlambat</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: PRESENSI (GPS & SELFIE + RIWAYAT)  -->
        <!-- ========================================== -->
        <div id="tabPresensi" class="main-tab-content hidden flex-1 p-4 space-y-4">
            
            <!-- Segmented Switch Atas: Presensi Mandiri vs Riwayat -->
            <div class="bg-slate-200/80 p-1 rounded-2xl flex items-center text-xs font-bold">
                <button type="button" onclick="switchPresensiSubTab('subPresensiGps')" id="btnSubPresensiGps" class="flex-1 py-2 text-center rounded-xl bg-white text-indigo-700 shadow-sm transition">
                    <i class="fa-solid fa-location-crosshairs mr-1"></i> Presensi GPS & Selfie
                </button>
                <button type="button" onclick="switchPresensiSubTab('subRiwayatPresensi')" id="btnSubRiwayatPresensi" class="flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition">
                    <i class="fa-solid fa-clock-rotate-left mr-1"></i> Riwayat Absensi
                </button>
            </div>

            <!-- SUB-TAB 2A: FORM PRESENSI GPS & SELFIE -->
            <div id="subPresensiGps" class="space-y-4">
                
                <!-- Card 1: Kamera Selfie Live & Viewfinder (POSISI UTAMA / ATAS) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center relative">
                    <div class="text-xs font-bold text-slate-800 mb-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <i class="fa-solid fa-camera text-indigo-600"></i>
                            <span>Live Kamera Depan</span>
                        </div>
                        <button type="button" onclick="restartCamera()" class="text-[11px] text-indigo-600 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Reset Kamera</span>
                        </button>
                    </div>

                    <!-- Live Camera Viewfinder Container (Default Kamera Depan) -->
                    <div class="relative w-52 h-52 mx-auto rounded-full overflow-hidden border-4 border-indigo-500 shadow-xl bg-slate-900 flex items-center justify-center">
                        <!-- Video Live Stream (Kamera Depan / User Facing) -->
                        <video id="webcamVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                        <!-- Captured Snapshot Image Preview -->
                        <img id="capturedSnapshot" class="w-full h-full object-cover hidden" alt="Selfie Result">
                        <!-- Scanner Radar Line -->
                        <div id="scannerLine" class="scanner-line absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-amber-400 to-transparent shadow-lg"></div>
                        <!-- Guidelines Oval Face -->
                        <div class="absolute inset-4 rounded-full border-2 border-dashed border-white/50 pointer-events-none"></div>
                    </div>

                    <!-- Hidden Canvas for Snapshot Capture -->
                    <canvas id="snapshotCanvas" class="hidden"></canvas>

                    <!-- Status Foto Selfie (Otomatis Terambil Saat Klik Masuk / Pulang) -->
                    <div class="mt-3.5">
                        <div id="selfieStatusBadge" class="text-[11px] text-slate-500 font-medium">
                            Arahkan wajah ke dalam lingkaran. Foto otomatis tersimpan saat klik Masuk / Pulang.
                        </div>
                    </div>
                </div>

                <!-- Card 2: Informasi Koordinat GPS & Radius Kantor (Hanya Info Koordinat & Radius) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-location-crosshairs text-indigo-600 text-sm"></i>
                            <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Koordinat dan Radius Lokasi</span>
                        </div>
                        <button type="button" onclick="ambilLokasiMobile(true)" class="text-[11px] text-indigo-600 hover:underline flex items-center gap-1 font-bold">
                            <i class="fa-solid fa-rotate text-xs"></i> Scan GPS
                        </button>
                    </div>

                    <!-- Badge Status Radius Live -->
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500 font-medium">Status Geofencing:</span>
                        <span id="mobileGpsBadge" class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-spinner fa-spin text-xs"></i> Mendeteksi GPS...
                        </span>
                    </div>

                    <!-- Grid Info Koordinat & Radius -->
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <div class="text-[10px] text-slate-400 font-bold uppercase flex items-center gap-1">
                                <i class="fa-solid fa-compass text-indigo-500"></i> Koordinat GPS Anda
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 font-mono mt-1" id="koordinatUserText">
                                Menghubungkan...
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5" id="akurasiGpsText">Akurasi: Memindai...</div>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <div class="text-[10px] text-slate-400 font-bold uppercase flex items-center gap-1">
                                <i class="fa-solid fa-bullseye text-amber-500"></i> Radius & Jarak Kantor
                            </div>
                            <div class="text-xs font-extrabold text-slate-800 font-mono mt-1" id="jarakText">
                                Jarak: Menghitung...
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                Batas Maks: <strong>{{ $settings['radius_kantor_meter'] ?? 200 }} meter</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Status & Tombol Aksi Utama (Clock In / Clock Out) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between text-xs pb-2 border-b border-slate-100">
                        <span class="font-bold text-slate-700">Status Kehadiran Hari Ini:</span>
                        @if(!$presensiHariIni || !$presensiHariIni->jam_masuk)
                            <span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                Belum Masuk Kantor
                            </span>
                        @elseif($presensiHariIni && !$presensiHariIni->jam_pulang)
                            <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                Aktif Bekerja (In: {{ substr($presensiHariIni->jam_masuk, 0, 5) }} WIB)
                            </span>
                        @else
                            <span class="font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                                Sudah Pulang ({{ $presensiHariIni->durasi_kerja }})
                            </span>
                        @endif
                    </div>

                    <!-- Form Clock In Action -->
                    @if(!$presensiHariIni || !$presensiHariIni->jam_masuk)
                    <form action="{{ route('dosen.clock-in') }}" method="POST" id="formClockIn">
                        @csrf
                        <input type="hidden" name="latitude" id="latIn">
                        <input type="hidden" name="longitude" id="lonIn">
                        <input type="hidden" name="foto_base64" id="fotoBase64In">

                        <button type="submit" onclick="return validateBeforeSubmit('in')" class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-arrow-right-to-bracket text-base"></i>
                            <span>CLOCK IN (MASUK KANTOR)</span>
                        </button>
                    </form>
                    @endif

                    <!-- Form Clock Out Action -->
                    @if($presensiHariIni && $presensiHariIni->jam_masuk && !$presensiHariIni->jam_pulang)
                    <form action="{{ route('dosen.clock-out') }}" method="POST" id="formClockOut">
                        @csrf
                        <input type="hidden" name="latitude" id="latOut">
                        <input type="hidden" name="longitude" id="lonOut">
                        <input type="hidden" name="foto_base64" id="fotoBase64Out">

                        <button type="submit" onclick="return validateBeforeSubmit('out')" class="w-full py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-extrabold text-sm shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                            <span>CLOCK OUT (PULANG KANTOR)</span>
                        </button>
                    </form>
                    @endif

                    @if($presensiHariIni && $presensiHariIni->jam_masuk && $presensiHariIni->jam_pulang)
                    <div class="p-3 bg-slate-100 rounded-xl text-center text-xs text-slate-600">
                        <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i>
                        Presensi masuk dan pulang hari ini telah selesai dicatat.
                    </div>
                    @endif
                </div>

            </div>

            <!-- SUB-TAB 2B: RIWAYAT ABSENSI LENGKAP -->
            <div id="subRiwayatPresensi" class="hidden space-y-3">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Log Presensi Bulan Ini</span>
                        <span class="text-[10px] text-slate-400">{{ now()->locale('id')->isoFormat('MMMM Y') }}</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($riwayatBulanIni as $log)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($log->tanggal)->locale('id')->isoFormat('dddd, D MMM Y') }}
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Masuk: <strong>{{ $log->jam_masuk ? substr($log->jam_masuk, 0, 5) : '-' }}</strong> &bull; Pulang: <strong>{{ $log->jam_pulang ? substr($log->jam_pulang, 0, 5) : '-' }}</strong>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $log->status === 'hadir' ? 'bg-emerald-100 text-emerald-800' : ($log->status === 'terlambat' ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-700') }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $log->durasi_kerja ?: '-' }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="py-6 text-center text-slate-400 text-xs">Belum ada riwayat absensi bulan ini.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 3: KELAS (PERKULIAHAN & BAP)           -->
        <!-- ========================================== -->
        <div id="tabKelas" class="main-tab-content hidden flex-1 p-4 space-y-4">
            
            <!-- Header Tab Kelas -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800">Kelas Perkuliahan</h2>
                    <p class="text-[10px] text-slate-500">Jadwal dan pengisian BAP mengajar Anda</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg">
                    Semester Ganjil
                </span>
            </div>

            <!-- Jadwal Hari Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-extrabold text-slate-800 mb-2.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-amber-500"></i>
                    <span>Pertemuan Hari Ini</span>
                </div>

                <div class="space-y-3">
                    @forelse($pertemuanHariIni as $p)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800">
                                    Pertemuan Ke-{{ $p->pertemuan_ke }}
                                </span>
                                <h3 class="text-xs font-bold text-slate-800 mt-1">{{ $p->kelasKuliah->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</h3>
                                <div class="text-[10px] text-slate-500">
                                    Kelas {{ $p->kelasKuliah->nama_kelas ?? '-' }} &bull; {{ $p->ruangan->nama_ruang ?? 'Ruang Belum Ditentukan' }}
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $p->status === 'berlangsung' ? 'bg-amber-100 text-amber-800 animate-pulse' : ($p->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700') }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </div>

                        <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between text-[11px]">
                            <span class="text-slate-500 font-mono">{{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5) : 'Jadwal Hari Ini' }}</span>
                            <a href="{{ route('dosen.buka-kelas', $p->id) }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px] shadow-sm flex items-center gap-1">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span>Buka BAP / Presensi</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="py-5 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-mug-saucer text-2xl text-slate-300 mb-1"></i>
                        <div>Tidak ada jadwal kelas kuliah untuk hari ini.</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Daftar Seluruh Kelas Semester Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <div class="text-xs font-extrabold text-slate-800 mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-book-bookmark text-indigo-600"></i>
                    <span>Daftar Kelas yang Diampu</span>
                </div>

                <div class="space-y-2.5">
                    @forelse($kelasDosens as $kd)
                    @php
                        $kelas = $kd->kelasKuliah;
                        $selesai = $kelas ? $kelas->pertemuans->where('status', 'selesai')->count() : 0;
                        $persen = round(($selesai / 16) * 100);
                    @endphp
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="font-bold text-xs text-slate-800">{{ $kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</div>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-200 rounded-full text-slate-700">
                                {{ $kelas->nama_kelas ?? '-' }}
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-500">
                            {{ $kelas->mataKuliah->sks_total ?? 3 }} SKS &bull; Terlaksana {{ $selesai }} dari 16 Sesi BKD
                        </div>
                        <!-- Progress Bar 16 Pertemuan BKD -->
                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, $persen) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="py-4 text-center text-slate-400 text-xs">Belum ada kelas yang terdaftar.</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 4: PENGATURAN (PROFIL & GANTI PASSWORD)-->
        <!-- ========================================== -->
        <div id="tabPengaturan" class="main-tab-content hidden flex-1 p-4 space-y-4">
            
            <!-- Segmented Switch Tab Pengaturan: Profil vs Ganti Password -->
            <div class="bg-slate-200/80 p-1 rounded-2xl flex items-center text-xs font-bold">
                <button type="button" onclick="switchPengaturanSubTab('subProfil')" id="btnSubProfil" class="flex-1 py-2 text-center rounded-xl bg-white text-indigo-700 shadow-sm transition">
                    <i class="fa-solid fa-user mr-1"></i> Profil Dosen
                </button>
                <button type="button" onclick="switchPengaturanSubTab('subPassword')" id="btnSubPassword" class="flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition">
                    <i class="fa-solid fa-lock mr-1"></i> Ganti Password
                </button>
            </div>

            <!-- SUB-TAB 4A: PROFIL DOSEN -->
            <div id="subProfil" class="space-y-4">
                <!-- Card Header Profil -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-tr from-[#154e85] to-indigo-600 text-amber-300 font-extrabold text-2xl flex items-center justify-center shadow-md border-2 border-white">
                        {{ substr($dosen->nama_lengkap, 0, 1) }}
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-800 mt-2.5 leading-snug">{{ $dosen->nama_lengkap }}</h2>
                    <div class="text-[11px] text-slate-500 mt-0.5">Dosen Tetap &bull; {{ $dosen->prodi->nama_prodi ?? 'Program Studi' }}</div>
                    <div class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Status Akun Aktif
                    </div>
                </div>

                <!-- Detail Informasi Akademik & Kontak -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
                    <div class="text-xs font-extrabold text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card text-indigo-600"></i>
                        <span>Informasi Kepegawaian</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-500">NIDN</span>
                            <span class="font-bold text-slate-800 font-mono">{{ $dosen->nidn ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-t border-slate-100">
                            <span class="text-slate-500">NIP / Kode Dosen</span>
                            <span class="font-bold text-slate-800 font-mono">{{ $dosen->nip ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-t border-slate-100">
                            <span class="text-slate-500">Program Studi</span>
                            <span class="font-bold text-slate-800 text-right truncate max-w-[200px]">{{ $dosen->prodi->nama_prodi ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-t border-slate-100">
                            <span class="text-slate-500">Fakultas</span>
                            <span class="font-bold text-slate-800 text-right truncate max-w-[200px]">{{ $dosen->prodi->fakultas->nama_fakultas ?? 'Teknologi Informasi' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-t border-slate-100">
                            <span class="text-slate-500">Jadwal Jam Kantor</span>
                            <span class="font-bold text-indigo-700">{{ $settings['jam_masuk_kantor'] }} - {{ $settings['jam_pulang_kantor'] }} WIB</span>
                        </div>
                    </div>
                </div>

                <!-- Form Update Kontak (Email & HP) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <div class="text-xs font-extrabold text-slate-800 mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-address-book text-indigo-600"></i>
                        <span>Perbarui Kontak Dosen</span>
                    </div>

                    <form action="{{ route('dosen.update-profil') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Email Dosen / Akun</label>
                            <input type="email" name="email" value="{{ old('email', $dosen->email ?? auth()->user()->email) }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" value="{{ old('no_hp', $dosen->no_hp) }}" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Kontak</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- SUB-TAB 4B: GANTI PASSWORD -->
            <div id="subPassword" class="hidden space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                    <div class="text-xs font-extrabold text-slate-800 mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-key text-amber-500"></i>
                        <span>Keamanan Kata Sandi</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mb-3">Gunakan kombinasi minimal 6 karakter untuk mengamankan akun presensi Anda.</p>

                    <form action="{{ route('dosen.ganti-password') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Password Saat Ini</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="current_password" required class="w-full px-3 py-2 pr-9 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                                <button type="button" onclick="togglePasswordVisibility('current_password')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Password Baru (Min. 6 Karakter)</label>
                            <div class="relative">
                                <input type="password" name="password" id="new_password" required minlength="6" class="w-full px-3 py-2 pr-9 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                                <button type="button" onclick="togglePasswordVisibility('new_password')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Ulangi Konfirmasi Password Baru</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6" class="w-full px-3 py-2 pr-9 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5 mt-2">
                            <i class="fa-solid fa-lock"></i>
                            <span>Simpan Password Baru</span>
                        </button>
                    </form>
                </div>

                <!-- Tombol Logout Akun Mobile Aman -->
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-center">
                    <div class="text-xs font-bold text-rose-800">Keluar dari Sesi Aplikasi</div>
                    <div class="text-[10px] text-rose-600 mt-0.5 mb-3">Pastikan Anda telah melakukan presensi pulang sebelum keluar.</div>
                    <form action="{{ route('logout', ['from' => 'mobile']) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi presensi?')" class="w-full py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm active:scale-95 transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Keluar Akun (Logout)</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Bottom Safe Spacing (Agar konten tidak tertutup fixed bottom bar) -->
        <div class="h-24"></div>

        <!-- ========================================== -->
        <!-- MOBILE BOTTOM NAVIGATION BAR (FIXED)       -->
        <!-- 4 Menu: Home, Presensi, Kelas, Pengaturan  -->
        <!-- ========================================== -->
        <nav class="fixed bottom-0 w-full max-w-md bg-white border-t border-slate-200 px-4 py-2 pb-safe flex items-center justify-around z-40 shadow-2xl">
            <!-- 1. Home -->
            <button type="button" onclick="switchMainTab('tabHome')" id="navHome" class="flex flex-col items-center gap-1 text-indigo-600 transition min-w-[56px]">
                <i class="fa-solid fa-house text-base"></i>
                <span class="text-[9px] font-bold">Home</span>
            </button>

            <!-- 2. Presensi (Center Floating Style) -->
            <button type="button" onclick="switchMainTab('tabPresensi')" id="navPresensi" class="flex flex-col items-center gap-1 text-slate-400 hover:text-indigo-600 transition min-w-[56px]">
                <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center -mt-3 shadow-md shadow-indigo-600/30">
                    <i class="fa-solid fa-fingerprint text-base"></i>
                </div>
                <span class="text-[9px] font-semibold text-slate-700">Presensi</span>
            </button>

            <!-- 3. Kelas -->
            <button type="button" onclick="switchMainTab('tabKelas')" id="navKelas" class="flex flex-col items-center gap-1 text-slate-400 hover:text-indigo-600 transition min-w-[56px]">
                <i class="fa-solid fa-chalkboard-user text-base"></i>
                <span class="text-[9px] font-semibold">Kelas</span>
            </button>

            <!-- 4. Pengaturan -->
            <button type="button" onclick="switchMainTab('tabPengaturan')" id="navPengaturan" class="flex flex-col items-center gap-1 text-slate-400 hover:text-indigo-600 transition min-w-[56px]">
                <i class="fa-solid fa-gear text-base"></i>
                <span class="text-[9px] font-semibold">Pengaturan</span>
            </button>
        </nav>

    </div>

    <!-- Scripts Logika Mobile (Leaflet, Geolocation, MediaDevices Webcam) -->
    <script>
        // 1. Digital Clock Mobile Realtime
        function runMobileClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const clockEl = document.getElementById('mobileClock');
            if (clockEl) clockEl.innerText = h + ':' + m + ':' + s;
        }
        setInterval(runMobileClock, 1000);

        // 2. Main Tab Navigation (Home, Presensi, Kelas, Pengaturan)
        function switchMainTab(tabId) {
            const mainTabs = ['tabHome', 'tabPresensi', 'tabKelas', 'tabPengaturan'];
            mainTabs.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });

            const activeEl = document.getElementById(tabId);
            if (activeEl) activeEl.classList.remove('hidden');

            // Tampilkan header HANYA pada tab Beranda (tabHome)
            const mainHeader = document.getElementById('mobileMainHeader');
            const alertWrapper = document.getElementById('mobileAlertWrapper');
            if (mainHeader) {
                if (tabId === 'tabHome') {
                    mainHeader.classList.remove('hidden');
                    if (alertWrapper) {
                        alertWrapper.classList.add('-mt-2');
                        alertWrapper.classList.remove('mt-3');
                    }
                } else {
                    mainHeader.classList.add('hidden');
                    if (alertWrapper) {
                        alertWrapper.classList.remove('-mt-2');
                        alertWrapper.classList.add('mt-3');
                    }
                }
            }

            // Reset style bottom nav icons
            const navItems = {
                'tabHome': document.getElementById('navHome'),
                'tabPresensi': document.getElementById('navPresensi'),
                'tabKelas': document.getElementById('navKelas'),
                'tabPengaturan': document.getElementById('navPengaturan'),
            };

            for (const [key, btn] of Object.entries(navItems)) {
                if (!btn) continue;
                if (key === tabId) {
                    btn.classList.remove('text-slate-400');
                    btn.classList.add('text-indigo-600');
                    const span = btn.querySelector('span');
                    if (span) {
                        span.classList.add('font-bold', 'text-indigo-700');
                        span.classList.remove('font-semibold');
                    }
                } else {
                    btn.classList.remove('text-indigo-600');
                    btn.classList.add('text-slate-400');
                    const span = btn.querySelector('span');
                    if (span) {
                        span.classList.remove('font-bold', 'text-indigo-700');
                        span.classList.add('font-semibold');
                    }
                }
            }

            // Scroll ke atas saat ganti tab
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Otomatis aktifkan kamera depan & scan GPS jika tab presensi dibuka
            if (tabId === 'tabPresensi') {
                initCamera();
                ambilLokasiMobile();
            }
        }

        // 3. Sub-Tab Presensi: Presensi GPS vs Riwayat
        function switchPresensiSubTab(subId) {
            const gpsEl = document.getElementById('subPresensiGps');
            const riwayatEl = document.getElementById('subRiwayatPresensi');
            const btnGps = document.getElementById('btnSubPresensiGps');
            const btnRiw = document.getElementById('btnSubRiwayatPresensi');

            if (subId === 'subPresensiGps') {
                if (gpsEl) gpsEl.classList.remove('hidden');
                if (riwayatEl) riwayatEl.classList.add('hidden');
                btnGps.className = 'flex-1 py-2 text-center rounded-xl bg-white text-indigo-700 shadow-sm transition';
                btnRiw.className = 'flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition';
                initCamera();
                ambilLokasiMobile();
            } else {
                if (gpsEl) gpsEl.classList.add('hidden');
                if (riwayatEl) riwayatEl.classList.remove('hidden');
                btnRiw.className = 'flex-1 py-2 text-center rounded-xl bg-white text-indigo-700 shadow-sm transition';
                btnGps.className = 'flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition';
            }
        }

        function openRiwayatPresensi() {
            switchMainTab('tabPresensi');
            switchPresensiSubTab('subRiwayatPresensi');
        }

        // 4. Sub-Tab Pengaturan: Profil vs Ganti Password
        function switchPengaturanSubTab(subId) {
            const profEl = document.getElementById('subProfil');
            const passEl = document.getElementById('subPassword');
            const btnProf = document.getElementById('btnSubProfil');
            const btnPass = document.getElementById('btnSubPassword');

            if (subId === 'subProfil') {
                if (profEl) profEl.classList.remove('hidden');
                if (passEl) passEl.classList.add('hidden');
                btnProf.className = 'flex-1 py-2 text-center rounded-xl bg-white text-indigo-700 shadow-sm transition';
                btnPass.className = 'flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition';
            } else {
                if (profEl) profEl.classList.add('hidden');
                if (passEl) passEl.classList.remove('hidden');
                btnPass.className = 'flex-1 py-2 text-center rounded-xl bg-white text-indigo-700 shadow-sm transition';
                btnProf.className = 'flex-1 py-2 text-center rounded-xl text-slate-600 hover:text-slate-900 transition';
            }
        }

        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        // 5. Geolocation Haversine & Info Koordinat / Radius
        const targetLat = {{ $settings['kampus_lat'] ?? -6.2088 }};
        const targetLon = {{ $settings['kampus_lon'] ?? 106.8456 }};
        const targetRadius = {{ $settings['radius_kantor_meter'] ?? 200 }};

        let currentDistance = 0;

        function ambilLokasiMobile(forced = false) {
            const badge = document.getElementById('mobileGpsBadge');
            if (badge) {
                badge.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memindai Satelit GPS...';
                badge.className = 'text-[11px] font-bold px-3 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1.5';
            }

            if (!navigator.geolocation) {
                fallbackMockLocation();
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const uLat = pos.coords.latitude;
                    const uLon = pos.coords.longitude;
                    const accuracy = pos.coords.accuracy || 10;
                    updateUserPosition(uLat, uLon, accuracy);
                },
                function(err) {
                    fallbackMockLocation();
                },
                { enableHighAccuracy: true, timeout: 7000, maximumAge: 0 }
            );
        }

        function fallbackMockLocation() {
            // Simulasi koordinat di area kantor untuk pengujian browser lokal
            const mockLat = targetLat + 0.00012;
            const mockLon = targetLon + 0.00012;
            updateUserPosition(mockLat, mockLon, 15, true);
        }

        function updateUserPosition(lat, lon, accuracy = 15, isMock = false) {
            // Isi hidden input form presensi masuk & pulang
            const latIn = document.getElementById('latIn');
            const lonIn = document.getElementById('lonIn');
            if (latIn) latIn.value = lat;
            if (lonIn) lonIn.value = lon;

            const latOut = document.getElementById('latOut');
            const lonOut = document.getElementById('lonOut');
            if (latOut) latOut.value = lat;
            if (lonOut) lonOut.value = lon;

            // Tampilkan info koordinat teks & akurasi
            const koorEl = document.getElementById('koordinatUserText');
            if (koorEl) {
                koorEl.innerText = lat.toFixed(5) + ', ' + lon.toFixed(5);
            }
            const akurasiEl = document.getElementById('akurasiGpsText');
            if (akurasiEl) {
                akurasiEl.innerText = 'Akurasi GPS: ~' + Math.round(accuracy) + ' m ' + (isMock ? '(Simulasi)' : '');
            }

            // Hitung Jarak Haversine ke Titik Kantor
            currentDistance = Math.round(hitungJarakHaversine(lat, lon, targetLat, targetLon));
            const jarakEl = document.getElementById('jarakText');
            if (jarakEl) jarakEl.innerText = 'Jarak: ' + currentDistance + ' meter';

            const badge = document.getElementById('mobileGpsBadge');
            if (badge) {
                if (currentDistance <= targetRadius) {
                    badge.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600"></i> Dalam Radius (' + currentDistance + 'm)';
                    badge.className = 'text-[11px] font-bold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5';
                } else {
                    badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Luar Radius (' + currentDistance + 'm)';
                    badge.className = 'text-[11px] font-bold px-3 py-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1.5';
                }
            }
        }

        function hitungJarakHaversine(lat1, lon1, lat2, lon2) {
            const R = 6371e3;
            const φ1 = lat1 * Math.PI/180;
            const φ2 = lat2 * Math.PI/180;
            const Δφ = (lat2-lat1) * Math.PI/180;
            const Δλ = (lon2-lon1) * Math.PI/180;
            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ/2) * Math.sin(Δλ/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        // 6. Live Kamera Depan Aktif Default & Snapshot
        let mediaStream = null;
        let capturedBase64 = null;

        async function initCamera() {
            const video = document.getElementById('webcamVideo');
            const statusBadge = document.getElementById('selfieStatusBadge');
            if (!video) return;

            // Jika sudah ada stream aktif, jangan recreate
            if (mediaStream && mediaStream.active) {
                return;
            }

            try {
                // Kamera depan (facingMode: 'user') aktif secara default
                mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'user' },
                        width: { ideal: 720 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });
                video.srcObject = mediaStream;
                await video.play().catch(e => console.log('Autoplay deferred:', e));
                if (statusBadge) {
                    statusBadge.innerHTML = '<span class="text-emerald-600 font-semibold"><i class="fa-solid fa-circle-check"></i> Kamera aktif — foto otomatis tersimpan saat klik Masuk / Pulang</span>';
                }
            } catch (err) {
                console.log('Live camera not permitted or sandbox:', err);
                if (statusBadge) {
                    statusBadge.innerHTML = '<span class="text-amber-600 font-semibold"><i class="fa-solid fa-triangle-exclamation"></i> Izinkan akses kamera browser pada ponsel Anda</span>';
                }
            }
        }

        function captureAutomaticSelfie() {
            const video = document.getElementById('webcamVideo');
            const canvas = document.getElementById('snapshotCanvas');
            const snapshotImg = document.getElementById('capturedSnapshot');
            const scanner = document.getElementById('scannerLine');
            const statusBadge = document.getElementById('selfieStatusBadge');

            if (capturedBase64) {
                return true;
            }

            if (video && (video.videoWidth > 0 || video.readyState >= 2)) {
                canvas.width = video.videoWidth || 480;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                capturedBase64 = canvas.toDataURL('image/jpeg', 0.88);

                if (snapshotImg) {
                    snapshotImg.src = capturedBase64;
                    snapshotImg.classList.remove('hidden');
                }
                video.classList.add('hidden');
                if (scanner) scanner.classList.add('hidden');

                // Isi otomatis ke hidden input form clock-in & clock-out
                const bIn = document.getElementById('fotoBase64In');
                if (bIn) bIn.value = capturedBase64;
                const bOut = document.getElementById('fotoBase64Out');
                if (bOut) bOut.value = capturedBase64;

                if (statusBadge) {
                    statusBadge.innerHTML = '<span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Foto selfie otomatis diambil, memproses presensi...</span>';
                }
                return true;
            }

            return false;
        }

        function restartCamera() {
            const video = document.getElementById('webcamVideo');
            const snapshotImg = document.getElementById('capturedSnapshot');
            const scanner = document.getElementById('scannerLine');
            const statusBadge = document.getElementById('selfieStatusBadge');

            if (video) video.classList.remove('hidden');
            if (snapshotImg) snapshotImg.classList.add('hidden');
            if (scanner) scanner.classList.remove('hidden');

            capturedBase64 = null;
            const bIn = document.getElementById('fotoBase64In');
            if (bIn) bIn.value = '';
            const bOut = document.getElementById('fotoBase64Out');
            if (bOut) bOut.value = '';

            if (statusBadge) {
                statusBadge.innerText = 'Arahkan wajah ke dalam lingkaran. Foto otomatis tersimpan saat klik Masuk / Pulang.';
            }

            initCamera();
        }

        function validateBeforeSubmit(type) {
            if (!captureAutomaticSelfie()) {
                alert('Kamera depan sedang menginisialisasi atau belum diizinkan. Silakan beri izin kamera pada browser terlebih dahulu.');
                initCamera();
                return false;
            }
            return true;
        }

        // Auto buka tab tertentu jika ada session/error tab
        @if(session('active_tab') === 'pengaturan' || $errors->has('current_password') || $errors->has('password'))
            window.addEventListener('DOMContentLoaded', () => {
                switchMainTab('tabPengaturan');
                switchPengaturanSubTab('subPassword');
            });
        @endif

        // Inisialisasi awal saat load
        window.addEventListener('DOMContentLoaded', () => {
            ambilLokasiMobile();
            initCamera();
        });
    </script>
</body>
</html>
