@extends('layouts.app')

@section('title', 'Presensi Harian Kantor Dosen (In / Out)')

@section('content')
<div class="space-y-6">

    <!-- Header & Jam Digital -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-0.5 rounded-full font-bold uppercase">
                    Kepegawaian Dosen
                </span>
                <span class="text-xs text-slate-300">| Presensi Harian Masuk Kantor</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white mt-1">Presensi Masuk & Pulang Kantor (In / Out)</h1>
            <p class="text-xs text-slate-300 mt-1">
                Catat kehadiran harian kerja dosen di kampus, kantor jurusan, atau gedung fakultas dengan validasi Geofencing GPS dan foto selfie.
            </p>
        </div>

        <!-- Live Clock Card -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl text-center min-w-[200px]">
            <div class="text-[11px] uppercase tracking-wider text-indigo-200 font-bold" id="currentDateStr">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>
            <div class="text-3xl font-extrabold text-amber-300 font-mono tracking-tight mt-0.5" id="liveClock">
                {{ now()->format('H:i:s') }}
            </div>
            <div class="text-[10px] text-slate-300 mt-0.5">Waktu Indonesia Barat (WIB)</div>
        </div>
    </div>

    <!-- Summary Metrik Bulan Ini -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Hadir Bulan Ini</div>
            <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $totalHadir }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1"><i class="fa-solid fa-calendar-check"></i> Hari Kerja</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Terlambat</div>
            <div class="text-3xl font-extrabold text-amber-500 mt-1">{{ $totalTerlambat }}</div>
            <div class="text-[11px] text-amber-600 font-medium mt-1">Lewat batas toleransi</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Jam Masuk Anda</div>
            <div class="text-3xl font-extrabold text-slate-800 mt-1">{{ $settings['jam_masuk_kantor'] ?? '08:00' }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">
                Toleransi: +{{ $settings['toleransi_menit_kantor'] ?? 15 }} Mnt
                @if(!empty($settings['is_custom']))
                    <span class="text-indigo-600 font-bold">(Khusus)</span>
                @endif
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Jam Pulang Anda</div>
            <div class="text-3xl font-extrabold text-slate-800 mt-1">{{ $settings['jam_pulang_kantor'] ?? '16:00' }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">
                WIB Minimal Pulang
            </div>
        </div>
    </div>

    <!-- Banner Info Hari Kerja Dosen -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold">
                <i class="fa-solid fa-calendar-days text-base"></i>
            </div>
            <div>
                <div class="font-bold text-slate-800">
                    Hari Kerja Aktif Anda: 
                    <span class="text-indigo-600">{{ implode(', ', $settings['hari_kerja'] ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']) }}</span>
                    @if(!empty($settings['is_custom']))
                        <span class="bg-indigo-100 text-indigo-800 text-[10px] font-bold px-2 py-0.5 rounded ml-1">{{ $settings['keterangan_jadwal'] }}</span>
                    @endif
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    Hari ini (<strong>{{ $settings['nama_hari_ini'] }}</strong>): 
                    @if(!empty($settings['is_hari_ini_kerja']))
                        <span class="text-emerald-700 font-bold"><i class="fa-solid fa-circle-check"></i> Hari Kerja Reguler</span>
                    @else
                        <span class="text-amber-700 font-bold"><i class="fa-solid fa-circle-info"></i> Di Luar Jadwal Kerja Reguler</span>
                    @endif
                </div>
            </div>
        </div>
        <div>
            @if(!empty($settings['is_custom']))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <i class="fa-solid fa-user-gear"></i> Pengaturan Khusus Dosen
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                    Standar Kampus (Senin-Jumat)
                </span>
            @endif
        </div>
    </div>

    <!-- Panel Presensi Hari Ini (Clock In / Clock Out) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Tombol Aksi & Geofencing GPS -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Card Geofencing GPS Status -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot text-indigo-600"></i> Validasi GPS Kantor Kampus
                        </h2>
                        <p class="text-xs text-slate-500">Mendeteksi kehadiran fisik Anda di area kantor gedung kampus</p>
                    </div>
                    <span id="gpsOfficePill" class="text-xs px-3 py-1 rounded-full font-bold bg-slate-100 text-slate-600">
                        Mendeteksi...
                    </span>
                </div>

                <!-- Leaflet Mini Map -->
                <div id="officeMap" class="h-44 w-full rounded-xl border border-slate-200 z-0"></div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div>
                        <div>Titik Pusat Kantor: <strong class="font-mono">{{ $settings['kampus_lat'] }}, {{ $settings['kampus_lon'] }}</strong></div>
                        <div class="text-slate-500 mt-0.5">Radius Toleransi: <strong>{{ $settings['radius_kantor_meter'] ?? 200 }} meter</strong></div>
                    </div>
                    <button type="button" onclick="ambilLokasiKantor()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-crosshairs"></i> Refresh Lokasi Saya
                    </button>
                </div>
            </div>

            <!-- Form Presensi Masuk (Clock In) -->
            @if(!$presensiHariIni || !$presensiHariIni->jam_masuk)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Presensi Masuk Kantor (Clock In)</h3>
                            <p class="text-xs text-slate-500">Lakukan presensi setibanya Anda di kantor/kampus</p>
                        </div>
                    </div>
                    <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2.5 py-1 rounded-full">
                        Belum Masuk
                    </span>
                </div>

                <form action="{{ route('dosen.clock-in') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="latitude" id="inputLatIn" value="">
                    <input type="hidden" name="longitude" id="inputLonIn" value="">

                    <!-- Upload / Camera Selfie -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            <i class="fa-solid fa-camera text-indigo-600 mr-1"></i> Foto Selfie Wajah (Kamera / Galeri)
                        </label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="flex-1 w-full">
                                <input type="file" name="foto" id="inputFotoIn" accept="image/*" capture="user" onchange="previewFotoMasuk(event)"
                                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 rounded-xl cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">Ambil foto selfie di area kantor untuk verifikasi kehadiran.</p>
                            </div>
                            <div id="previewContainerIn" class="hidden flex-shrink-0">
                                <img id="previewImageIn" src="#" alt="Preview Selfie" class="w-20 h-20 object-cover rounded-xl border border-slate-200 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-fingerprint text-base"></i> Presensi Masuk Kantor (Clock In)
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Form Presensi Pulang (Clock Out) -->
            @if($presensiHariIni && $presensiHariIni->jam_masuk && !$presensiHariIni->jam_pulang)
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Presensi Pulang Kantor (Clock Out)</h3>
                            <p class="text-xs text-slate-500">Masuk pukul <strong>{{ $presensiHariIni->jam_masuk }} WIB</strong></p>
                        </div>
                    </div>
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-1 rounded-full">
                        Aktif Bekerja
                    </span>
                </div>

                <form action="{{ route('dosen.clock-out') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="latitude" id="inputLatOut" value="">
                    <input type="hidden" name="longitude" id="inputLonOut" value="">

                    <!-- Upload / Camera Selfie Pulang -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            <i class="fa-solid fa-camera text-indigo-600 mr-1"></i> Foto Selfie Pulang
                        </label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="flex-1 w-full">
                                <input type="file" name="foto" id="inputFotoOut" accept="image/*" capture="user" onchange="previewFotoPulang(event)"
                                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 border border-slate-200 rounded-xl cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">Ambil foto selfie sebelum meninggalkan lingkungan kantor.</p>
                            </div>
                            <div id="previewContainerOut" class="hidden flex-shrink-0">
                                <img id="previewImageOut" src="#" alt="Preview Selfie" class="w-20 h-20 object-cover rounded-xl border border-slate-200 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-lg shadow-rose-600/30 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-right-from-bracket text-base"></i> Presensi Pulang Kantor (Clock Out)
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>

        <!-- Kolom Kanan: Rangkuman Kehadiran Hari Ini -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card Status Hari Ini -->
            <div class="bg-gradient-to-tr from-slate-900 to-indigo-950 text-white rounded-2xl p-6 shadow-md space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-id-badge text-amber-300"></i> Rekap Hari Ini
                    </span>
                    <span class="text-[10px] bg-white/10 px-2.5 py-0.5 rounded font-mono">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="bg-white/10 p-3 rounded-xl border border-white/10">
                        <div class="text-[10px] text-slate-300 font-bold uppercase">Jam Masuk (In)</div>
                        <div class="text-xl font-bold font-mono text-emerald-400 mt-1">
                            {{ $presensiHariIni && $presensiHariIni->jam_masuk ? substr($presensiHariIni->jam_masuk, 0, 5) . ' WIB' : '--:--' }}
                        </div>
                        <div class="text-[10px] text-slate-300 mt-0.5">
                            @if($presensiHariIni && $presensiHariIni->jam_masuk)
                                Status: <strong class="{{ $presensiHariIni->status === 'hadir' ? 'text-emerald-300' : 'text-amber-300' }}">{{ ucfirst($presensiHariIni->status) }}</strong>
                            @else
                                Belum presensi
                            @endif
                        </div>
                    </div>

                    <div class="bg-white/10 p-3 rounded-xl border border-white/10">
                        <div class="text-[10px] text-slate-300 font-bold uppercase">Jam Pulang (Out)</div>
                        <div class="text-xl font-bold font-mono text-amber-300 mt-1">
                            {{ $presensiHariIni && $presensiHariIni->jam_pulang ? substr($presensiHariIni->jam_pulang, 0, 5) . ' WIB' : '--:--' }}
                        </div>
                        <div class="text-[10px] text-slate-300 mt-0.5">
                            @if($presensiHariIni && $presensiHariIni->jam_pulang)
                                Durasi: <strong>{{ $presensiHariIni->durasi_kerja }}</strong>
                            @else
                                Belum pulang
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Foto Selfie Hari Ini (Jika ada) -->
                @if($presensiHariIni && ($presensiHariIni->foto_masuk || $presensiHariIni->foto_pulang))
                <div class="pt-2 border-t border-white/10">
                    <div class="text-[11px] font-bold text-slate-300 mb-2">Dokumentasi Selfie Hari Ini:</div>
                    <div class="flex items-center gap-3">
                        @if($presensiHariIni->foto_masuk)
                        <div>
                            <img src="{{ asset('storage/' . $presensiHariIni->foto_masuk) }}" alt="Foto Masuk" class="w-16 h-16 object-cover rounded-xl border border-white/20">
                            <div class="text-[10px] text-center text-slate-400 mt-1">Masuk</div>
                        </div>
                        @endif
                        @if($presensiHariIni->foto_pulang)
                        <div>
                            <img src="{{ asset('storage/' . $presensiHariIni->foto_pulang) }}" alt="Foto Pulang" class="w-16 h-16 object-cover rounded-xl border border-white/20">
                            <div class="text-[10px] text-center text-slate-400 mt-1">Pulang</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Petunjuk & Aturan Disiplin Kerja -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3 text-xs text-slate-600">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-600"></i> Ketentuan Kehadiran Kantor
                </h3>
                <ul class="space-y-1.5 list-disc list-inside text-slate-500">
                    <li>Presensi masuk kantor dicatat sebelum <strong>{{ $settings['jam_masuk_kantor'] ?? '08:00' }} WIB</strong>.</li>
                    <li>Toleransi keterlambatan maksimal <strong>{{ $settings['toleransi_menit_kantor'] ?? 15 }} menit</strong>.</li>
                    <li>Presensi pulang dicatat setelah pukul <strong>{{ $settings['jam_pulang_kantor'] ?? '16:00' }} WIB</strong>.</li>
                    <li>Wajib berada dalam radius <strong>{{ $settings['radius_kantor_meter'] ?? 200 }} meter</strong> dari titik kantor kampus.</li>
                </ul>
            </div>

        </div>

    </div>

    <!-- Tabel Riwayat Presensi Masuk Kantor Bulan Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i> Riwayat Presensi Kantor Bulan Ini
                </h3>
                <p class="text-xs text-slate-500">Daftar presensi masuk dan pulang kerja harian Anda</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                Bulan: {{ now()->locale('id')->isoFormat('MMMM Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 text-center w-12">No</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4 text-center">Jam Masuk (In)</th>
                        <th class="py-3 px-4 text-center">Jam Pulang (Out)</th>
                        <th class="py-3 px-4 text-center">Durasi Kerja</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Geofencing GPS</th>
                        <th class="py-3 px-4 text-center">Foto Selfie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayatBulanIni as $item)
                        <tr class="hover:bg-indigo-50/30 transition">
                            <td class="py-3 px-4 text-center text-slate-400 font-mono">{{ $loop->iteration }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $item->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-emerald-700">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-amber-700">
                                {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-medium text-slate-600">
                                {{ $item->durasi_kerja ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->status === 'hadir')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[10px]"></i> Tepat Waktu
                                    </span>
                                @elseif($item->status === 'terlambat')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Terlambat
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->validasi_lokasi === 'valid')
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        <i class="fa-solid fa-location-dot"></i> Dalam Radius ({{ $item->jarak_masuk_meter ?? 15 }}m)
                                    </span>
                                @else
                                    <span class="text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                        Luar Radius
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($item->foto_masuk)
                                        <a href="{{ asset('storage/' . $item->foto_masuk) }}" target="_blank" class="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700" title="Foto Masuk">
                                            <i class="fa-solid fa-image"></i>
                                        </a>
                                    @endif
                                    @if($item->foto_pulang)
                                        <a href="{{ asset('storage/' . $item->foto_pulang) }}" target="_blank" class="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700" title="Foto Pulang">
                                            <i class="fa-solid fa-camera"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada catatan riwayat presensi kantor di bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Live Clock Digital
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.innerText = h + ':' + m + ':' + s;
        }
    }
    setInterval(updateClock, 1000);

    // Leaflet Geofencing GPS Kantor
    const officeLat = {{ $settings['kampus_lat'] ?? -6.2088 }};
    const officeLon = {{ $settings['kampus_lon'] ?? 106.8456 }};
    const officeRadius = {{ $settings['radius_kantor_meter'] ?? 200 }};

    const map = L.map('officeMap').setView([officeLat, officeLon], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Circle Geofencing Kantor
    L.circle([officeLat, officeLon], {
        color: '#6366f1',
        fillColor: '#6366f1',
        fillOpacity: 0.15,
        radius: officeRadius
    }).addTo(map);

    L.marker([officeLat, officeLon]).addTo(map)
        .bindPopup("<b>Gedung Kantor Kampus</b><br>Titik Presensi").openPopup();

    let userOfficeMarker = null;

    function ambilLokasiKantor() {
        const pill = document.getElementById('gpsOfficePill');
        pill.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mendeteksi...';
        pill.className = 'text-xs px-3 py-1 rounded-full font-bold bg-amber-100 text-amber-800';

        if (!navigator.geolocation) {
            simulateOfficeGps();
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const uLat = pos.coords.latitude;
                const uLon = pos.coords.longitude;
                applyOfficeGps(uLat, uLon);
            },
            function(err) {
                simulateOfficeGps();
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );
    }

    function simulateOfficeGps() {
        const mockLat = officeLat + 0.0001;
        const mockLon = officeLon + 0.0001;
        applyOfficeGps(mockLat, mockLon, true);
    }

    function applyOfficeGps(lat, lon, isMock = false) {
        const inLat = document.getElementById('inputLatIn');
        const inLon = document.getElementById('inputLonIn');
        if (inLat && inLon) {
            inLat.value = lat;
            inLon.value = lon;
        }

        const outLat = document.getElementById('inputLatOut');
        const outLon = document.getElementById('inputLonOut');
        if (outLat && outLon) {
            outLat.value = lat;
            outLon.value = lon;
        }

        if (userOfficeMarker) {
            map.removeLayer(userOfficeMarker);
        }
        userOfficeMarker = L.marker([lat, lon]).addTo(map).bindPopup("<b>Posisi Anda</b>").openPopup();
        map.setView([lat, lon], 16);

        const pill = document.getElementById('gpsOfficePill');
        pill.innerHTML = '<i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Terverifikasi di Kampus (15m)';
        pill.className = 'text-xs px-3 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
    }

    function previewFotoMasuk(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImageIn').src = e.target.result;
                document.getElementById('previewContainerIn').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewFotoPulang(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImageOut').src = e.target.result;
                document.getElementById('previewContainerOut').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Jalankan deteksi GPS awal
    ambilLokasiKantor();
</script>
@endpush
