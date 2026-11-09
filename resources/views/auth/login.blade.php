@extends('layouts.app')

@section('title', 'Masuk Portal Presensi Dosen')

@section('content')
<div class="max-w-md mx-auto my-8">
    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200 overflow-hidden">
        <!-- Header -->
        <div class="p-6 bg-gradient-to-tr from-slate-900 to-slate-800 text-white text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-400 text-2xl mb-3 shadow-inner">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h1 class="text-xl font-bold tracking-tight">Portal Presensi & BKD Dosen</h1>
            <p class="text-xs text-slate-300 mt-1">Sistem Terintegrasi Neo Feeder PDDikti & SISTER Kemendikbud</p>
        </div>

        <!-- Form Login -->
        <div class="p-6">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email / Akun Kampus</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email', 'dosen@kampus.ac.id') }}" required
                            class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" value="password" required
                            class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Sistem
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 text-center">Akun Uji Coba Cepat (1-Klik)</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('quick.login', ['role' => 'dosen']) }}" class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/70 text-emerald-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-chalkboard-user text-emerald-600"></i> Dosen Pengampu</span>
                        <span class="text-[10px] text-emerald-700 mt-0.5">Dr. Hendra Pratama (Pengusul BKD)</span>
                    </a>

                    <a href="{{ route('quick.login', ['role' => 'dosen2']) }}" class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/70 text-emerald-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-users-rectangle text-emerald-600"></i> Team Teaching</span>
                        <span class="text-[10px] text-emerald-700 mt-0.5">Siti Rahmawati, M.T.</span>
                    </a>

                    <a href="{{ route('quick.login', ['role' => 'kaprodi']) }}" class="p-2.5 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100/70 text-amber-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-signature text-amber-600"></i> Kaprodi</span>
                        <span class="text-[10px] text-amber-700 mt-0.5">Verifikasi & Pengesahan BKD</span>
                    </a>

                    <a href="{{ route('quick.login', ['role' => 'admin']) }}" class="p-2.5 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100/70 text-blue-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-network-wired text-blue-600"></i> Admin BAAK</span>
                        <span class="text-[10px] text-blue-700 mt-0.5">Feeder PDDikti Sinkronisasi</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
