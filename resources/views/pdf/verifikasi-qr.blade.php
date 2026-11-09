@extends('layouts.app')

@section('title', 'Verifikasi Keaslian Dokumen BKD')

@section('content')
<div class="max-w-2xl mx-auto my-6">

    @if($pengesahan)
    <div class="bg-white rounded-2xl border-2 border-emerald-500 shadow-xl overflow-hidden">
        <!-- Banner Sukses Verifikasi -->
        <div class="bg-gradient-to-r from-emerald-700 to-teal-800 text-white p-6 text-center">
            <div class="w-16 h-16 rounded-full bg-white text-emerald-700 flex items-center justify-center text-3xl mx-auto shadow-md mb-2">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h1 class="text-xl font-extrabold uppercase tracking-tight">Dokumen Portofolio BKD Sah & Terverifikasi</h1>
            <p class="text-xs text-emerald-100 mt-1">
                Tanda Tangan Elektronik Resmi Universitas Teknologi Nusantara
            </p>
            <div class="mt-3 inline-block bg-emerald-900/50 text-emerald-200 border border-emerald-400/40 px-3 py-1 rounded-full text-xs font-mono font-bold">
                Kode Token: {{ $token }}
            </div>
        </div>

        <!-- Detail Verifikasi -->
        <div class="p-6 space-y-4">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Pengesahan BKD / Serdos</div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <span class="text-slate-500 block">Dosen Pengampu:</span>
                    <strong class="text-slate-900 text-sm">{{ $pengesahan->dosen ? $pengesahan->dosen->nama_gelar : 'Dosen Pengampu' }}</strong>
                    <div class="text-[11px] text-slate-500 font-mono">NIDN: {{ $pengesahan->dosen ? $pengesahan->dosen->nidn : '-' }}</div>
                </div>

                <div>
                    <span class="text-slate-500 block">Mata Kuliah:</span>
                    <strong class="text-slate-900 text-sm">{{ $pengesahan->kelasKuliah->mataKuliah->nama_mk }}</strong>
                    <div class="text-[11px] text-slate-500">Kelas {{ $pengesahan->kelasKuliah->nama_kelas }} ({{ $pengesahan->kelasKuliah->mataKuliah->sks }} SKS)</div>
                </div>

                <div>
                    <span class="text-slate-500 block">Program Studi / Semester:</span>
                    <strong class="text-slate-800">{{ $pengesahan->kelasKuliah->mataKuliah->prodi ? $pengesahan->kelasKuliah->mataKuliah->prodi->nama_prodi : 'Teknik Informatika' }}</strong>
                    <div class="text-[11px] text-slate-500">{{ $pengesahan->tahunAkademik->nama }}</div>
                </div>

                <div>
                    <span class="text-slate-500 block">Realisasi Tatap Muka:</span>
                    <strong class="text-emerald-700 text-sm">{{ $pengesahan->total_pertemuan }} Pertemuan</strong>
                    <span class="ml-1 bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold text-[10px]">
                        {{ strtoupper($pengesahan->status_kelayakan_bkd) }} SYARAT BKD
                    </span>
                </div>
            </div>

            <!-- Pejabat Pengesah -->
            <div class="p-4 rounded-xl border border-slate-200 bg-emerald-50/50 flex items-start gap-3 text-xs">
                <i class="fa-solid fa-stamp text-emerald-600 text-xl mt-0.5"></i>
                <div>
                    <div class="font-bold text-slate-900">Disahkan oleh Ketua Program Studi:</div>
                    <div class="text-sm font-semibold text-emerald-900 mt-0.5">{{ $pengesahan->disahkan_oleh }}</div>
                    <div class="text-slate-500">NIDN: {{ $pengesahan->nidn_pengesah }} &bull; Tanggal: {{ $pengesahan->tanggal_pengesahan ? $pengesahan->tanggal_pengesahan->format('d F Y') : date('d F Y') }}</div>
                    @if($pengesahan->catatan)
                        <div class="mt-2 text-slate-600 italic">"{{ $pengesahan->catatan }}"</div>
                    @endif
                </div>
            </div>

            <div class="text-center pt-2">
                <a href="{{ route('bkd.cetak', $pengesahan->kelas_kuliah_id) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shadow-sm">
                    <i class="fa-solid fa-file-lines"></i> Lihat Dokumen BAP Lengkap (16 Pertemuan)
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl border-2 border-rose-400 p-8 text-center shadow-lg">
        <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mx-auto mb-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h1 class="text-lg font-bold text-slate-900">Kode Verifikasi Dokumen Tidak Ditemukan</h1>
        <p class="text-xs text-slate-500 mt-1">
            Token verifikasi <code class="font-mono bg-slate-100 px-2 py-0.5 rounded text-rose-700">{{ $token }}</code> tidak terdaftar di pangkalan data kampus.
        </p>
    </div>
    @endif

</div>
@endsection
