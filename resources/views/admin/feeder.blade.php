@extends('layouts.app')

@section('title', 'Manajemen Integrasi Neo Feeder PDDikti')

@section('content')
<div class="space-y-6">

    <!-- Header Feeder Manager -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}" class="text-xs text-slate-500 hover:text-slate-800 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full">
                    PDDikti Neo Feeder API
                </span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 mt-1">
                Integrasi Web Service Feeder & SISTER
            </h1>
            <p class="text-xs text-slate-500">
                Sinkronisasi dua arah (Pull Master Data & Push Realisasi Pertemuan Mengajar Dosen)
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.feeder.test') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-plug-circle-bolt text-emerald-600"></i> Uji Koneksi Neo Feeder
                </button>
            </form>
            @if($pertemuanPending->count() > 0)
            <form action="{{ route('admin.feeder.push-batch') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-blue-600/30">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Sinkronkan {{ $pertemuanPending->count() }} Data Tertunda
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Antrian Pending Sinkronisasi -->
    @if($pertemuanPending->count() > 0)
    <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shadow-sm">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <h3 class="font-bold text-amber-950 text-sm">Ada {{ $pertemuanPending->count() }} Pertemuan Mengajar Belum Tersinkron ke Feeder</h3>
                <p class="text-xs text-amber-800">
                    Data presensi telah tersimpan aman di database lokal (mode standalone) dan siap didorong ke server Feeder PDDikti kampus.
                </p>
            </div>
        </div>
        <form action="{{ route('admin.feeder.push-batch') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition">
                Dorong ke Feeder Sekarang &rarr;
            </button>
        </form>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Form Konfigurasi Web Service -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-blue-600"></i> Pengaturan Web Service Feeder
            </h2>
            <p class="text-xs text-slate-500">
                Standar Web Service Neo Feeder PDDikti versi 2024/2025 (port default 8100 atau REST live2.php)
            </p>

            <form action="{{ route('admin.feeder.update') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">URL Endpoint Web Service</label>
                    <input type="url" name="url" value="{{ old('url', $config->url) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <span class="text-[10px] text-slate-400">Contoh: http://localhost:8100/ws/live2.php</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Username Feeder</label>
                        <input type="text" name="username" value="{{ old('username', $config->username) }}" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Password Feeder</label>
                        <input type="password" name="password" value="{{ old('password', $config->password) }}" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Kode PT</label>
                        <input type="text" name="kode_pt" value="{{ old('kode_pt', $config->kode_pt) }}"
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Mode Operasional</label>
                        <select name="mode" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="sandbox" {{ $config->mode === 'sandbox' ? 'selected' : '' }}>Sandbox (Simulasi/Uji Coba)</option>
                            <option value="live" {{ $config->mode === 'live' ? 'selected' : '' }}>Live (Produksi Kampus)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Perguruan Tinggi</label>
                    <input type="text" name="nama_pt" value="{{ old('nama_pt', $config->nama_pt) }}"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition flex items-center justify-center gap-1.5 shadow-md shadow-blue-600/20">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Log Aktivitas API Feeder -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-blue-600"></i> Riwayat Transaksi API Neo Feeder
                    </h2>
                    <p class="text-xs text-slate-500">Log pertukaran request/response dengan server Dikti</p>
                </div>
                <span class="text-xs bg-slate-100 text-slate-600 font-bold px-2.5 py-1 rounded-lg">
                    {{ $logs->total() }} Log Transaksi
                </span>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Action</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3">Keterangan / Pesan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 text-slate-500 font-sans">
                                {{ $log->created_at->format('d/m H:i:s') }}
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">
                                {{ $log->endpoint_action }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-sans">
                                @if($log->status === 'success')
                                    <span class="bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded text-[10px]">OK (0)</span>
                                @else
                                    <span class="bg-rose-100 text-rose-800 font-bold px-1.5 py-0.5 rounded text-[10px]">ERR</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 font-sans truncate max-w-xs" title="{{ $log->error_desc }}">
                                {{ $log->error_desc ?: 'Transaksi berhasil' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400 font-sans">
                                Belum ada riwayat transaksi Feeder.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $logs->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
