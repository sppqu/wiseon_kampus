@extends('layouts.app')

@section('title', 'Pengaturan Kebijakan Presensi')

@section('content')
<div class="space-y-6">

    <!-- Header Pengaturan Presensi -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-amber-400/20 text-amber-300 border border-amber-400/30 px-2.5 py-0.5 rounded-full font-bold uppercase">
                    Pengaturan Presensi
                </span>
                <span class="text-xs text-slate-300">| Kebijakan & Geofencing Kampus</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white mt-1">Pengaturan & Kebijakan Presensi Perkuliahan</h1>
            <p class="text-xs text-slate-300 mt-1">
                Konfigurasi radius toleransi geofencing GPS, standar target 16 sesi BKD Serdos, batas kehadiran mahasiswa, dan validasi dokumentasi kelas.
            </p>
        </div>

        <div>
            <a href="{{ route('master.ruang') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-door-open"></i> Kelola Titik GPS Ruang ({{ $totalRuangan }})
            </a>
        </div>
    </div>

    <!-- Form Konfigurasi Presensi -->
    <form action="{{ route('admin.pengaturan-presensi.update') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Card 1: Pengaturan Jam Masuk & Pulang Kantor Dosen -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-business-time"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Presensi Kantor Dosen (In / Out)</h3>
                        <p class="text-xs text-slate-500">Jam kerja harian masuk kantor kampus / fakultas</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Masuk Kantor</label>
                        <input type="time" name="jam_masuk_kantor" value="{{ old('jam_masuk_kantor', $settings['jam_masuk_kantor'] ?? '08:00') }}" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Standar jam kehadiran kerja</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Pulang Kantor</label>
                        <input type="time" name="jam_pulang_kantor" value="{{ old('jam_pulang_kantor', $settings['jam_pulang_kantor'] ?? '16:00') }}" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Batas awal jam kepulangan</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Toleransi Keterlambatan</label>
                        <div class="relative">
                            <input type="number" name="toleransi_menit_kantor" value="{{ old('toleransi_menit_kantor', $settings['toleransi_menit_kantor'] ?? 15) }}" required min="0" max="120"
                                class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-bold">Menit</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Radius Geofencing Kantor</label>
                        <div class="relative">
                            <input type="number" name="radius_kantor_meter" value="{{ old('radius_kantor_meter', $settings['radius_kantor_meter'] ?? 200) }}" required min="10" max="2000"
                                class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-bold">Meter</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Geofencing GPS Kampus -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Geofencing GPS Kampus</h3>
                        <p class="text-xs text-slate-500">Radius dan koordinat toleransi kehadiran fisik</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Radius Ruang Perkuliahan (Meter)</label>
                    <input type="number" name="radius_meter" value="{{ old('radius_meter', $settings['radius_meter']) }}" required min="10" max="2000"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Jarak maksimum toleransi antara posisi GPS dosen dengan titik ruangan kampus (disarankan: 100 - 300 meter).</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Latitude Kampus</label>
                        <input type="text" name="kampus_lat" value="{{ old('kampus_lat', $settings['kampus_lat']) }}" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Longitude Kampus</label>
                        <input type="text" name="kampus_lon" value="{{ old('kampus_lon', $settings['kampus_lon']) }}" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mode Penegakan Geofencing</label>
                    <select name="mode_geofencing" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="strict" {{ $settings['mode_geofencing'] === 'strict' ? 'selected' : '' }}>
                            Ketat (Strict) - Wajib dalam radius GPS untuk presensi tatap muka
                        </option>
                        <option value="warning" {{ $settings['mode_geofencing'] === 'warning' ? 'selected' : '' }}>
                            Fleksibel (Peringatan) - Tetap catat presensi dengan penandaan 'Luar Radius'
                        </option>
                    </select>
                </div>
            </div>

            <!-- Card 2: Kebijakan BKD & Sertifikasi Dosen -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Standar BKD & Serdos SISTER</h3>
                        <p class="text-xs text-slate-500">Ketentuan pemenuhan portofolio beban kerja dosen</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Sesi Pertemuan per Semester</label>
                    <input type="number" name="target_pertemuan_bkd" value="{{ old('target_pertemuan_bkd', $settings['target_pertemuan_bkd']) }}" required min="1" max="32"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Standar BKD nasional adalah <strong>16 pertemuan</strong> (termasuk UTS dan UAS).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Toleransi Keterlambatan Dosen (Menit)</label>
                    <input type="number" name="toleransi_menit" value="{{ old('toleransi_menit', $settings['toleransi_menit']) }}" required min="0" max="120"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Batas waktu sebelum presensi perkuliahan ditandai sebagai terlambat.</p>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                        <input type="checkbox" name="wajib_foto_bukti" value="1" {{ $settings['wajib_foto_bukti'] ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Wajibkan Foto Bukti Perkuliahan</span>
                            <p class="text-[11px] text-slate-400">Dosen wajib melampirkan foto suasana kelas/papan tulis saat mengisi BAP untuk audit BKD.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Card 3: Kebijakan Kehadiran Mahasiswa -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Ketentuan Kehadiran Mahasiswa</h3>
                        <p class="text-xs text-slate-500">Ambang batas presensi untuk kelayakan ujian</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Minimal Kehadiran Ujian (%)</label>
                    <div class="relative">
                        <input type="number" name="minimal_hadir_persen" value="{{ old('minimal_hadir_persen', $settings['minimal_hadir_persen']) }}" required min="1" max="100"
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <span class="absolute right-4 top-2.5 text-xs font-bold text-slate-400">%</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Standar akademik kampus: Mahasiswa dengan kehadiran di bawah persentase ini akan ditandai tidak berhak mengikuti UAS.</p>
                </div>
            </div>

            <!-- Card 4: Ringkasan Pengaturan & Bantuan -->
            <div class="bg-gradient-to-tr from-slate-900 to-blue-950 text-white rounded-2xl p-6 shadow-sm space-y-3 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 text-amber-300 text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-shield-halved"></i> Audit & Integritas Presensi
                    </div>
                    <h3 class="text-base font-bold text-white mt-2">Sinkronisasi Otomatis dengan SIAKAD & SISTER</h3>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Data presensi yang tersimpan akan otomatis digunakan oleh Kaprodi dalam penerbitan Surat Pernyataan Pengesahan BKD bertanda tangan elektronik (QR Code) serta disiapkan untuk sinkronisasi Neo Feeder PDDikti.
                    </p>
                </div>

                <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                    <span>Terakhir diperbarui: Sistem Aktif</span>
                    <span class="text-emerald-400 font-bold"><i class="fa-solid fa-circle-check"></i> Siap Pakai</span>
                </div>
            </div>

        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Kebijakan Presensi
            </button>
        </div>
    </form>

</div>
@endsection
