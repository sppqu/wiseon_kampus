@extends('layouts.app')

@section('title', 'Monitoring & Pengesahan BKD Kaprodi')

@section('content')
<div class="space-y-6">

    <!-- Header Profil Kaprodi -->
    <div class="bg-gradient-to-r from-slate-900 via-amber-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2.5 py-0.5 rounded-full font-bold uppercase">
                    Ketua Program Studi
                </span>
                <span class="text-xs text-slate-300">| {{ $prodi ? $prodi->nama_prodi : 'Teknik Informatika' }} ({{ $prodi ? $prodi->jenjang : 'S1' }})</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white mt-1">{{ auth()->user()->name }}</h1>
            <p class="text-xs text-slate-300 mt-1">
                Monitoring pelaksanaan perkuliahan, audit pemenuhan 16 pertemuan, dan pengesahan resmi portofolio BKD dosen untuk SISTER.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 text-center min-w-[100px]">
                <div class="text-[10px] text-slate-300 uppercase font-semibold">Total Rombel</div>
                <div class="text-2xl font-black text-amber-400">{{ $totalKelas }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 text-center min-w-[100px]">
                <div class="text-[10px] text-emerald-300 uppercase font-semibold">Siap BKD (16)</div>
                <div class="text-2xl font-black text-emerald-400">{{ $kelasMemenuhiBkd }}</div>
            </div>
        </div>
    </div>

    <!-- Monitoring Seluruh Kelas Kuliah di Prodi -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-amber-600"></i> Status Kelayakan BKD Seluruh Kelas Perkuliahan
                </h2>
                <p class="text-xs text-slate-500">Audit pemenuhan 16 pertemuan dan penandatanganan pengesahan digital Kaprodi</p>
            </div>
            <span class="text-xs bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 rounded-xl font-semibold">
                Semester: {{ $taAktif ? $taAktif->nama : '2024/2025 Ganjil' }}
            </span>
        </div>

        <div class="space-y-4">
            @foreach($kelasList as $kelas)
                @php
                    $totalSelesai = $kelas->pertemuans->where('status', 'selesai')->count();
                    $persen = round(($totalSelesai / 16) * 100);
                    $isMemenuhi = $totalSelesai >= 16;
                    $pengesahan = $kelas->pengesahanBkds->first();
                    $dosenUtama = $kelas->dosens->first();
                @endphp
                <div class="border border-slate-200 rounded-2xl p-5 hover:border-amber-300 transition bg-white shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-1.5 max-w-xl">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded">
                                {{ $kelas->mataKuliah->kode_mk }}
                            </span>
                            <span class="text-xs bg-slate-100 text-slate-800 font-bold px-2 py-0.5 rounded">
                                Kelas {{ $kelas->nama_kelas }}
                            </span>
                            <span class="text-xs text-slate-500">({{ $kelas->mataKuliah->sks }} SKS)</span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg">
                            {{ $kelas->mataKuliah->nama_mk }}
                        </h3>
                        <div class="text-xs text-slate-600 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span><i class="fa-solid fa-user-tie text-slate-400 mr-1"></i> Dosen: <strong>{{ $dosenUtama ? $dosenUtama->nama_gelar : 'Belum ditentukan' }}</strong></span>
                            @if($kelas->dosens->count() > 1)
                                <span class="bg-blue-50 text-blue-800 px-2 py-0.5 rounded font-bold text-[10px]">Team Teaching ({{ $kelas->dosens->count() }} Dosen)</span>
                            @endif
                        </div>
                    </div>

                    <!-- Progress & Status -->
                    <div class="w-full lg:w-72 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-600">Realisasi Tatap Muka:</span>
                            <span class="font-mono font-bold text-slate-900">{{ $totalSelesai }} / 16 ({{ $persen }}%)</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                            <div class="h-full rounded-full {{ $isMemenuhi ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min($persen, 100) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            @if($isMemenuhi)
                                <span class="text-emerald-700 font-bold"><i class="fa-solid fa-circle-check"></i> Memenuhi Syarat BKD</span>
                            @else
                                <span class="text-amber-700 font-bold"><i class="fa-solid fa-clock"></i> Kurang {{ 16 - $totalSelesai }} Sesi Tatap Muka</span>
                            @endif

                            @if($pengesahan && $pengesahan->status_pengesahan === 'disahkan')
                                <span class="text-emerald-700 font-bold"><i class="fa-solid fa-stamp"></i> Telah Disahkan</span>
                            @else
                                <span class="text-slate-400">Belum Disahkan</span>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                        <a href="{{ route('bkd.cetak', $kelas->id) }}" target="_blank" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition flex items-center gap-1">
                            <i class="fa-solid fa-eye"></i> BAP
                        </a>

                        <form action="{{ route('kaprodi.sahkan-bkd', $kelas->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="dosen_id" value="{{ $dosenUtama ? $dosenUtama->id : 1 }}">
                            <button type="submit" class="px-4 py-2 rounded-xl {{ $pengesahan ? 'bg-emerald-700 hover:bg-emerald-800' : 'bg-amber-600 hover:bg-amber-700' }} text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-signature"></i> {{ $pengesahan ? 'Perbarui Pengesahan' : 'Sahkan Portofolio BKD' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
