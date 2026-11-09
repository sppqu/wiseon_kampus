@extends('layouts.app')

@section('title', 'Portofolio BKD - ' . $kelas->mataKuliah->nama_mk)

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('dosen.dashboard') }}" class="text-xs text-slate-500 hover:text-slate-800 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
            <h1 class="text-xl font-bold text-slate-900 mt-1">
                Portofolio Beban Kerja Dosen (BKD / Serdos)
            </h1>
            <p class="text-xs text-slate-500">
                Dokumen bukti fisik resmi unsur Pendidikan & Pengajaran terstandar SISTER Kemendikbudristek
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('bkd.cetak', $kelas->id) }}" target="_blank" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print"></i> Pratinjau Cetak
            </a>
            <a href="{{ route('bkd.cetak', ['kelas' => $kelas->id, 'download' => 1]) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-emerald-600/30">
                <i class="fa-solid fa-file-pdf"></i> Unduh Berkas PDF (SISTER)
            </a>
        </div>
    </div>

    <!-- Ringkasan Kelayakan BKD Card -->
    <div class="bg-gradient-to-r from-slate-900 to-emerald-950 text-white rounded-2xl p-6 shadow-xl">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
            <div class="md:col-span-2 space-y-2">
                <span class="text-xs font-mono font-bold bg-white/10 text-emerald-300 border border-white/10 px-2.5 py-1 rounded-lg">
                    {{ $kelas->mataKuliah->kode_mk }} &bull; {{ $kelas->nama_kelas }}
                </span>
                <h2 class="text-2xl font-black tracking-tight text-white mt-1">
                    {{ $kelas->mataKuliah->nama_mk }}
                </h2>
                <div class="text-xs text-slate-300 flex flex-wrap gap-x-4 gap-y-1">
                    <span>SKS: <strong>{{ $kelas->mataKuliah->sks }} SKS</strong></span>
                    <span>Prodi: <strong>{{ $kelas->mataKuliah->prodi ? $kelas->mataKuliah->prodi->nama_prodi : 'Teknik Informatika' }}</strong></span>
                    <span>Semester: <strong>{{ $kelas->tahunAkademik->nama }}</strong></span>
                </div>
            </div>

            <!-- Status Kelayakan BKD -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 text-center">
                <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Status Standar BKD</div>
                @if($isMemenuhi)
                    <div class="inline-flex items-center gap-1.5 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-3 py-1 rounded-xl text-sm font-black">
                        <i class="fa-solid fa-circle-check"></i> MEMENUHI SYARAT
                    </div>
                    <div class="text-[11px] text-emerald-200 mt-1">16 Pertemuan Lengkap</div>
                @else
                    <div class="inline-flex items-center gap-1.5 bg-amber-500/20 text-amber-300 border border-amber-400/30 px-3 py-1 rounded-xl text-sm font-black">
                        <i class="fa-solid fa-hourglass-half"></i> MENUJU 16 SESI
                    </div>
                    <div class="text-[11px] text-amber-200 mt-1">Tersisa {{ 16 - $totalPertemuan }} Pertemuan Lagi</div>
                @endif
            </div>

            <!-- Progress Pertemuan -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 text-center">
                <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1">Realisasi Tatap Muka</div>
                <div class="text-3xl font-black text-emerald-400">
                    {{ $totalPertemuan }} <span class="text-base text-slate-300 font-medium">/ 16</span>
                </div>
                <div class="w-full bg-slate-700/60 h-2 rounded-full mt-2 overflow-hidden">
                    <div class="bg-emerald-400 h-full rounded-full" style="width: {{ min($persenBkd, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Pengesahan Kaprodi & QR Code Verifikasi -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-2xl">
                    <i class="fa-solid fa-stamp"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-900 text-base">Pengesahan Resmi Ketua Program Studi</h3>
                        @if($pengesahan && $pengesahan->status_pengesahan === 'disahkan')
                            <span class="bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Disahkan
                            </span>
                        @else
                            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-0.5 rounded-full">
                                Menunggu Pengesahan
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Disahkan oleh: <strong>{{ $pengesahan ? $pengesahan->disahkan_oleh : 'Ketua Program Studi' }}</strong>
                        @if($pengesahan && $pengesahan->tanggal_pengesahan)
                            pada {{ $pengesahan->tanggal_pengesahan->format('d F Y') }}
                        @endif
                    </p>
                    @if($pengesahan && $pengesahan->catatan)
                        <div class="text-xs text-slate-600 italic mt-1 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            "{{ $pengesahan->catatan }}"
                        </div>
                    @endif
                </div>
            </div>

            @if($pengesahan)
            <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="text-right">
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Digital Verification</div>
                    <div class="text-xs font-mono font-bold text-slate-800">{{ substr($pengesahan->qr_verifikasi, 0, 18) }}...</div>
                    <a href="{{ route('bkd.verifikasi', $pengesahan->qr_verifikasi) }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline font-semibold">
                        Uji Link Verifikasi Asesor &rarr;
                    </a>
                </div>
                <div class="w-12 h-12 bg-white p-1 rounded-lg border border-slate-300 flex items-center justify-center">
                    <i class="fa-solid fa-qrcode text-2xl text-slate-800"></i>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Matriks 16 Pertemuan Berita Acara Perkuliahan (BAP) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-emerald-600"></i> Matriks 16 Pertemuan Perkuliahan & BAP
                </h3>
                <p class="text-xs text-slate-500">Rincian aktivitas, jam tatap muka, kehadiran mahasiswa, dan status sinkronisasi Feeder</p>
            </div>
            <span class="text-xs bg-slate-100 text-slate-700 px-3 py-1 rounded-full font-semibold">
                {{ $kelas->pertemuans->count() }} Data Terjadwal
            </span>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3 text-center">Ptm</th>
                        <th class="py-3 px-3">Tanggal & Jam</th>
                        <th class="py-3 px-3">Jenis & Metode</th>
                        <th class="py-3 px-4">Pokok Bahasan / Realisasi Materi</th>
                        <th class="py-3 px-3 text-center">Kehadiran Mhs</th>
                        <th class="py-3 px-3 text-center">Validasi GPS</th>
                        <th class="py-3 px-3 text-center">Sync Feeder</th>
                        <th class="py-3 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kelas->pertemuans as $ptm)
                    <tr class="hover:bg-slate-50/80 transition {{ $ptm->pertemuan_ke === 8 || $ptm->pertemuan_ke === 16 ? 'bg-amber-50/40' : '' }}">
                        <td class="py-3 px-3 text-center font-bold">
                            <span class="inline-block w-6 h-6 rounded-full {{ $ptm->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }} leading-6 text-[11px]">
                                {{ $ptm->pertemuan_ke }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-semibold text-slate-800">{{ $ptm->tanggal->format('d/m/Y') }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                {{ $ptm->jam_mulai_aktual ? substr($ptm->jam_mulai_aktual, 0, 5) . ' - ' . substr($ptm->jam_selesai_aktual, 0, 5) : substr($ptm->jam_mulai_jadwal, 0, 5) . ' - ' . substr($ptm->jam_selesai_jadwal, 0, 5) }} WIB
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-bold uppercase text-[10px] {{ $ptm->jenis_pertemuan === 'uts' || $ptm->jenis_pertemuan === 'uas' ? 'text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded' : 'text-slate-600' }}">
                                {{ $ptm->jenis_pertemuan }}
                            </span>
                            <div class="text-[11px] text-slate-500 capitalize mt-0.5">{{ $ptm->metode }}</div>
                        </td>
                        <td class="py-3 px-4 max-w-xs">
                            <div class="font-bold text-slate-800 line-clamp-1">{{ $ptm->rencana_materi ?: 'Sesuai Silabus' }}</div>
                            <div class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $ptm->realisasi_materi ?: 'Belum diisi' }}</div>
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($ptm->status === 'selesai')
                                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    {{ $ptm->persen_hadir_mahasiswa }}%
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($ptm->validasi_lokasi === 'valid')
                                <span class="text-emerald-600" title="Valid di kampus ({{ $ptm->jarak_meter }}m)"><i class="fa-solid fa-circle-check"></i> Valid</span>
                            @elseif($ptm->validasi_lokasi === 'daring')
                                <span class="text-blue-600" title="Kuliah Daring"><i class="fa-solid fa-video"></i> Daring</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($ptm->status_sync_feeder === 'synced')
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">Synced</span>
                            @elseif($ptm->status === 'selesai')
                                <span class="text-[10px] bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded">Pending</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <a href="{{ route('dosen.buka-kelas', $ptm->id) }}" class="text-emerald-700 hover:text-emerald-900 font-semibold text-[11px]">
                                Detail &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
