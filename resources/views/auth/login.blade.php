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
            <!-- Banner Pintasan Presensi Mobile Khusus Smartphone -->
            <a href="{{ route('presensi.login') }}" class="mb-5 block p-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white shadow-md hover:shadow-lg hover:from-emerald-700 hover:to-teal-800 transition active:scale-[0.99] group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-lg text-emerald-100">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <div>
                            <div class="text-[10px] text-emerald-200 uppercase font-extrabold tracking-wider">Khusus Presensi Smartphone</div>
                            <div class="text-sm font-bold leading-tight">Masuk Presensi Mobile Dosen</div>
                            <div class="text-[11px] text-emerald-100 mt-0.5">Login hanya dengan Nomor HP & PIN 6 Digit</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-emerald-200 group-hover:translate-x-1 transition text-sm"></i>
                </div>
            </a>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 text-sm"></i>
                    <div>
                        <div class="font-bold">Gagal Masuk</div>
                        <div>{{ $errors->first() }}</div>
                    </div>
                </div>
            @endif

            <!-- Info Petunjuk Login No HP -->
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-2.5">
                <i class="fa-solid fa-circle-info text-emerald-600 mt-0.5 text-sm"></i>
                <div class="leading-relaxed">
                    <span class="font-bold">Dosen & Karyawan:</span> Cukup masukkan <strong>Nomor HP</strong> yang terdaftar. Kata sandi default adalah <strong>6 digit terakhir</strong> dari No. HP Anda.
                </div>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nomor HP Terdaftar / Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </span>
                        <input type="text" id="login" name="login" value="{{ old('login', old('email', '081234567890')) }}" required autofocus
                            placeholder="Contoh: 081234567890 atau email@kampus.ac.id"
                            class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Gunakan nomor WhatsApp / HP aktif yang tercatat di BAAK.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                        <span class="text-[10px] text-emerald-700 bg-emerald-100/70 font-semibold px-2 py-0.5 rounded">Default: 6 Digit Belakang No HP</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" value="567890" placeholder="6 digit belakang no HP / kata sandi Anda"
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
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Presensi
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 text-center">Akun Uji Coba Cepat (1-Klik)</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('quick.login', ['role' => 'dosen']) }}" class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/70 text-emerald-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-chalkboard-user text-emerald-600"></i> Dosen Pengampu</span>
                        <span class="text-[10px] text-emerald-700 mt-0.5">Dr. Hendra Pratama</span>
                        <span class="text-[9px] font-mono text-slate-500 bg-white/80 px-1.5 py-0.5 rounded border border-emerald-200 mt-1">HP: 081234567890 (PIN: 567890)</span>
                    </a>

                    <a href="{{ route('quick.login', ['role' => 'dosen2']) }}" class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/70 text-emerald-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-users-rectangle text-emerald-600"></i> Team Teaching</span>
                        <span class="text-[10px] text-emerald-700 mt-0.5">Siti Rahmawati, M.T.</span>
                        <span class="text-[9px] font-mono text-slate-500 bg-white/80 px-1.5 py-0.5 rounded border border-emerald-200 mt-1">HP: 081298765432 (PIN: 765432)</span>
                    </a>

                    <a href="{{ route('quick.login', ['role' => 'kaprodi']) }}" class="p-2.5 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100/70 text-amber-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-signature text-amber-600"></i> Kaprodi</span>
                        <span class="text-[10px] text-amber-700 mt-0.5">Verifikasi & Pengesahan BKD</span>
                        <span class="text-[9px] font-mono text-slate-500 bg-white/80 px-1.5 py-0.5 rounded border border-amber-200 mt-1">kaprodi@kampus.ac.id</span>
                    </a>

                    <a href="{{ route('quick.login', ['role' => 'admin']) }}" class="p-2.5 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100/70 text-blue-900 transition flex flex-col items-center text-center">
                        <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-network-wired text-blue-600"></i> Admin BAAK</span>
                        <span class="text-[10px] text-blue-700 mt-0.5">Feeder PDDikti Sinkronisasi</span>
                        <span class="text-[9px] font-mono text-slate-500 bg-white/80 px-1.5 py-0.5 rounded border border-blue-200 mt-1">admin@kampus.ac.id</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
