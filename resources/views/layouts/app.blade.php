<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WiseOn Presensi Kampus') - Presensi Dosen & Portofolio BKD</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        kampus: {
                            800: '#154e85',
                            900: '#123e6b',
                            950: '#0c2d4e',
                        },
                        gold: {
                            300: '#fde047',
                            400: '#facc15',
                            500: '#eab308',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- QRCode JS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Custom scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col antialiased">

    @if(auth()->check())
    <!-- Wrapper Utama dengan Sidebar Samping -->
    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR NAVIGASI SAMPING (Warna & Gaya Sesuai Lampiran) -->
        <aside id="sidebar" class="w-64 bg-[#154e85] text-white flex-shrink-0 flex flex-col shadow-xl z-30 transition-transform duration-300 md:translate-x-0 -translate-x-full fixed md:static inset-y-0 left-0">
            
            <!-- Logo & Identitas Kampus -->
            @php $ptSidebar = \App\Models\PerguruanTinggi::first(); @endphp
            <div class="p-4 bg-[#113f6c] border-b border-[#1b5b99] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($ptSidebar && $ptSidebar->logo)
                        <div class="w-9 h-9 rounded-xl bg-white p-1 flex items-center justify-center shadow-md overflow-hidden shrink-0">
                            <img src="{{ asset('storage/' . $ptSidebar->logo) }}" alt="Logo Kampus" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-200 text-[#154e85] font-black text-xl flex items-center justify-center shadow-md shrink-0">
                            W
                        </div>
                    @endif
                    <div>
                        <div class="font-extrabold text-base tracking-tight text-white leading-tight">
                            WiseOn <span class="text-amber-300 text-xs font-semibold px-1.5 py-0.5 bg-amber-400/20 rounded">Kampus</span>
                        </div>
                        <div class="text-[10px] text-blue-200 truncate max-w-[140px]">{{ $ptSidebar->singkatan ?? 'Presensi & BKD SISTER' }}</div>
                    </div>
                </div>
                <!-- Tombol tutup mobile -->
                <button onclick="toggleSidebar()" class="md:hidden text-white/70 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Konten Menu Sidebar -->
            <div class="flex-1 overflow-y-auto sidebar-scroll py-3 px-3 space-y-4">

                <!-- 1. GRUP MENU: DASHBOARD (MENU UTAMA) -->
                <div>
                    <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-300/80 mb-1">
                        Dashboard
                    </div>
                    <div class="space-y-0.5">
                        @if(auth()->user()->role === 'dosen')
                            <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dosen.dashboard*') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                                <i class="fa-solid fa-gauge-high w-4 text-center text-amber-300"></i>
                                <span>Dashboard Dosen</span>
                            </a>
                        @elseif(auth()->user()->role === 'kaprodi')
                            <a href="{{ route('kaprodi.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('kaprodi.dashboard*') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                                <i class="fa-solid fa-user-check w-4 text-center text-amber-300"></i>
                                <span>Dashboard Kaprodi</span>
                            </a>
                        @elseif(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.dashboard*') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                                <i class="fa-solid fa-chart-pie w-4 text-center text-amber-300"></i>
                                <span>Dashboard Admin</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 2. GRUP MENU: DATA MASTER (SESUAI GAMBAR LAMPIRAN USER - DEFAULT MINIMIZE) -->
                <div class="pt-1">
                    <!-- Header Accordion DATA MASTER -->
                    <button type="button" onclick="toggleDropdown('dataMasterMenu', 'chevronMaster')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-amber-300 font-extrabold text-xs tracking-wider uppercase hover:bg-[#1b5b99] transition">
                        <div class="flex items-center gap-2.5">
                            <!-- Database disc icon persis seperti di gambar -->
                            <i class="fa-solid fa-database text-amber-300 text-sm"></i>
                            <span class="tracking-wider">DATA MASTER</span>
                        </div>
                        <i id="chevronMaster" class="fa-solid fa-chevron-down text-amber-300 text-xs transition-transform duration-200 {{ request()->routeIs('master.fakultas*', 'master.program-studi*', 'master.dosen*', 'master.asisten-dosen*', 'master.mata-kuliah*') ? 'rotate-180' : '' }}"></i>
                    </button>

                    <!-- Submenu Data Master dengan Bullet Kuning/Emas (Default Minimize: Hidden) -->
                    <div id="dataMasterMenu" class="mt-1 space-y-0.5 pl-3 {{ request()->routeIs('master.fakultas*', 'master.program-studi*', 'master.dosen*', 'master.asisten-dosen*', 'master.mata-kuliah*') ? '' : 'hidden' }}">
                        
                        <!-- 1. Perguruan Tinggi -->
                        <a href="{{ route('master.perguruan-tinggi') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-xs transition {{ request()->routeIs('master.perguruan-tinggi*') ? 'bg-[#1e61a3] text-white font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <span class="text-amber-400 text-base leading-none">&bull;</span>
                            <span>Perguruan Tinggi</span>
                        </a>

                        <!-- 2. Fakultas -->
                        <a href="{{ route('master.fakultas') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-xs transition {{ request()->routeIs('master.fakultas*') ? 'bg-[#1e61a3] text-white font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <span class="text-amber-400 text-base leading-none">&bull;</span>
                            <span>Fakultas</span>
                        </a>

                        <!-- 3. Program Studi -->
                        <a href="{{ route('master.program-studi') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-xs transition {{ request()->routeIs('master.program-studi*') ? 'bg-[#1e61a3] text-white font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <span class="text-amber-400 text-base leading-none">&bull;</span>
                            <span>Program Studi</span>
                        </a>

                        <!-- 4. Dosen -->
                        <a href="{{ route('master.dosen') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-xs transition {{ request()->routeIs('master.dosen*') ? 'bg-[#1e61a3] text-white font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <span class="text-amber-400 text-base leading-none">&bull;</span>
                            <span>Dosen</span>
                        </a>

                        <!-- 5. Asisten Dosen -->
                        <a href="{{ route('master.asisten-dosen') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-xs transition {{ request()->routeIs('master.asisten-dosen*') ? 'bg-[#1e61a3] text-white font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <span class="text-amber-400 text-base leading-none">&bull;</span>
                            <span>Asisten Dosen</span>
                        </a>

                        <!-- 6. Mata Kuliah -->
                        <a href="{{ route('master.mata-kuliah') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-xs transition {{ request()->routeIs('master.mata-kuliah*') ? 'bg-[#1e61a3] text-white font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <span class="text-amber-400 text-base leading-none">&bull;</span>
                            <span>Mata Kuliah</span>
                        </a>

                        <!-- 7. Ruang -->
                        <a href="{{ route('master.ruang') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-md text-xs transition {{ request()->routeIs('master.ruang*') ? 'bg-[#1e61a3] text-white font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <span class="text-amber-400 text-base leading-none">&bull;</span>
                            <span>Ruang</span>
                        </a>
                    </div>
                </div>

                <!-- 3. GRUP MENU: PERKULIAHAN -->
                <div class="pt-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-300/80 mb-1">
                        Perkuliahan & Kehadiran
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ route('dosen.presensi-harian') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dosen.presensi-harian') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-business-time w-4 text-center text-amber-300"></i>
                                <span>Presensi Kantor (In/Out)</span>
                            </div>
                        </a>

                        <a href="{{ route('dosen.presensi-mobile') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dosen.presensi-mobile') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-mobile-screen-button w-4 text-center text-amber-300"></i>
                                <span>Presensi Versi Mobile</span>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-amber-400 text-slate-900 rounded-full uppercase tracking-tighter">App</span>
                        </a>

                        <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dosen.buka-kelas*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-location-crosshairs w-4 text-center text-amber-300"></i>
                            <span>Presensi & BAP Kelas (GPS)</span>
                        </a>

                        @php
                            $firstKelas = \App\Models\KelasKuliah::first();
                        @endphp
                        @if($firstKelas)
                        <a href="{{ route('dosen.portofolio-bkd', $firstKelas->id) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dosen.portofolio-bkd*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-certificate w-4 text-center text-amber-300"></i>
                            <span>Portofolio BKD (16 Sesi)</span>
                        </a>
                        @endif

                        <a href="{{ route('kaprodi.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('kaprodi.*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-stamp w-4 text-center text-amber-300"></i>
                            <span>Pengesahan Kaprodi</span>
                        </a>

                        <a href="{{ route('admin.laporan-kehadiran') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.laporan-kehadiran*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-file-invoice text-amber-300 w-4 text-center"></i>
                            <span>Laporan BKD Perkuliahan</span>
                        </a>
                    </div>
                </div>

                <!-- 4. GRUP MENU: PENGATURAN PRESENSI -->
                <div class="pt-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-300/80 mb-1">
                        Pengaturan Presensi
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.pengaturan-presensi') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.pengaturan-presensi*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-sliders w-4 text-center text-amber-300"></i>
                            <span>Kebijakan Presensi & BKD</span>
                        </a>

                        <a href="{{ route('admin.jadwal-kerja-dosen') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.jadwal-kerja-dosen*') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-user-clock w-4 text-center text-amber-300"></i>
                            <span>Jadwal Kerja per Dosen</span>
                        </a>

                        <a href="{{ route('admin.presensi-harian-dosen') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.presensi-harian-dosen*') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-clipboard-user w-4 text-center text-amber-300"></i>
                            <span>Presensi Kantor Harian</span>
                        </a>

                        <a href="{{ route('admin.rekap-kehadiran-dosen') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.rekap-kehadiran-dosen*') ? 'bg-[#1e61a3] text-amber-300 font-bold' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-chart-pie w-4 text-center text-amber-300"></i>
                            <span>Rekap Kehadiran Dosen</span>
                        </a>

                        <a href="{{ route('master.ruang') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('master.ruang*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-map-location-dot w-4 text-center text-amber-300"></i>
                            <span>Lokasi & Geofencing GPS</span>
                        </a>
                    </div>
                </div>

                <!-- 5. GRUP MENU: MENU PENGATURAN -->
                <div class="pt-1">
                    <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-blue-300/80 mb-1">
                        Menu Pengaturan
                    </div>
                    <div class="space-y-0.5">
                        <a href="{{ route('master.perguruan-tinggi') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('master.perguruan-tinggi*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-school w-4 text-center text-amber-300"></i>
                            <span>Profil Institusi Kampus</span>
                        </a>

                        <a href="{{ route('admin.feeder') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.feeder*') ? 'bg-[#1e61a3] text-amber-300' : 'text-slate-100 hover:bg-[#1b5b99]' }}">
                            <i class="fa-solid fa-network-wired w-4 text-center text-amber-300"></i>
                            <span>Neo Feeder PDDikti</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Footer User Sidebar -->
            <div class="p-3 bg-[#113f6c] border-t border-[#1b5b99] flex items-center justify-between text-xs">
                <div class="truncate">
                    <div class="font-bold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-amber-300 font-semibold uppercase">{{ auth()->user()->role }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-blue-200 hover:text-rose-300 hover:bg-white/10 rounded-lg transition" title="Keluar">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- AREA KONTEN KANAN -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Topbar Atas -->
            <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 shadow-sm z-20">
                <div class="flex items-center gap-3">
                    <!-- Hamburger button for mobile -->
                    <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <!-- Kampus Info Pill -->
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            T.A. 2024/2025 Ganjil
                        </span>
                        <span class="text-xs text-slate-400">|</span>
                        <span class="text-xs text-slate-600 font-medium">Universitas Teknologi Nusantara</span>
                    </div>
                </div>

                <!-- Beralih Peran Cepat (Quick Switch) & User Status -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('dosen.presensi-mobile') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition shadow-sm" title="Buka Tampilan Presensi Versi Mobile Touch / Smartphone">
                        <i class="fa-solid fa-mobile-screen-button text-amber-600"></i>
                        <span class="hidden sm:inline">UI Mobile Presensi</span>
                    </a>
                    <div class="hidden lg:flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs">
                        <span class="px-2 text-slate-500 font-semibold">Demo Role:</span>
                        <a href="{{ route('quick.login', ['role' => 'dosen']) }}" class="px-2.5 py-1 rounded-lg {{ auth()->user()->email == 'dosen@kampus.ac.id' ? 'bg-[#154e85] text-white font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-200' }}">Dosen</a>
                        <a href="{{ route('quick.login', ['role' => 'dosen2']) }}" class="px-2.5 py-1 rounded-lg {{ auth()->user()->email == 'dosen2@kampus.ac.id' ? 'bg-[#154e85] text-white font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-200' }}">Team Teaching</a>
                        <a href="{{ route('quick.login', ['role' => 'kaprodi']) }}" class="px-2.5 py-1 rounded-lg {{ auth()->user()->role == 'kaprodi' ? 'bg-[#154e85] text-white font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-200' }}">Kaprodi</a>
                        <a href="{{ route('quick.login', ['role' => 'admin']) }}" class="px-2.5 py-1 rounded-lg {{ auth()->user()->role == 'admin' ? 'bg-[#154e85] text-white font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-200' }}">Admin</a>
                    </div>

                    <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-[#154e85] text-amber-300 font-bold text-xs flex items-center justify-center">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="hidden md:block text-right">
                            <div class="text-xs font-bold text-slate-800 leading-none">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-500 capitalize mt-0.5 font-medium">{{ auth()->user()->role }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Konten Halaman Scrollable -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <!-- Alerts / Flash Messages -->
                @if(session('success'))
                    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                        <i class="fa-solid fa-circle-check text-emerald-600 mt-1"></i>
                        <div>
                            <div class="font-bold text-sm">Berhasil</div>
                            <div class="text-sm">{{ session('success') }}</div>
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-5 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-1"></i>
                        <div>
                            <div class="font-bold text-sm">Pemberitahuan</div>
                            <div class="text-sm">{{ session('warning') }}</div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 mt-1"></i>
                        <div>
                            <div class="font-bold text-sm">Terjadi Kesalahan</div>
                            <ul class="text-sm list-disc list-inside mt-1">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @else
    <!-- Tampilan Khusus Login (Tanpa Sidebar) -->
    <main class="flex-1 flex items-center justify-center p-4">
        @yield('content')
    </main>
    @endif

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        function toggleDropdown(menuId, chevronId) {
            const menu = document.getElementById(menuId);
            const chevron = document.getElementById(chevronId);
            menu.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }
    </script>

    @stack('scripts')
</body>
</html>
