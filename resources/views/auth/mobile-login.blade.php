<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#154e85">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Masuk Presensi Mobile - Dosen & Karyawan</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 1.5rem);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-800 antialiased min-h-screen flex justify-center">

    <!-- Container Smartphone Frame -->
    <div class="w-full max-w-md bg-white min-h-screen flex flex-col justify-between shadow-2xl relative">

        <!-- Top Header Mobile App -->
        @php
            $ptMobile = \App\Models\PerguruanTinggi::first();
            $tzMap = ['Asia/Jakarta' => 'WIB', 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT', 'UTC' => 'UTC'];
            $tzLabel = $tzMap[config('app.timezone')] ?? config('app.timezone');
        @endphp
        <div>
            <header class="bg-gradient-to-r from-[#113f6c] via-[#154e85] to-[#1e61a3] text-white p-5 pt-7 pb-6 rounded-b-[2rem] shadow-lg relative">
                <div class="flex items-center gap-3.5">
                    @if($ptMobile && $ptMobile->logo)
                        <div class="w-12 h-12 rounded-2xl bg-white p-1.5 flex items-center justify-center shadow-md overflow-hidden shrink-0">
                            <img src="{{ asset('storage/' . $ptMobile->logo) }}" alt="Logo Kampus" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-300 font-extrabold text-xl shadow-inner shrink-0">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-amber-300 font-extrabold uppercase tracking-wider">Aplikasi Presensi Mobile</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        </div>
                        <h1 class="text-base font-extrabold text-white leading-tight">Presensi Dosen & Karyawan</h1>
                        <div class="text-[11px] text-blue-200">{{ $ptMobile->nama_pt ?? 'Universitas Teknologi Nusantara' }}</div>
                    </div>
                </div>

                <!-- Jam Digital & Tanggal Realtime -->
                <div class="mt-4 bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-3 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-slate-200 uppercase font-semibold">
                            {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </div>
                        <div class="text-xl font-black font-mono text-amber-300 tracking-tight" id="liveClock">
                            {{ now()->format('H:i:s') }} {{ $tzLabel }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-2 py-0.5 rounded-full font-bold">
                            <i class="fa-solid fa-location-dot mr-1"></i> Geofencing GPS
                        </span>
                        <div class="text-[10px] text-slate-300 mt-1">Radius Kantor Kampus</div>
                    </div>
                </div>
            </header>

            <!-- Form Content -->
            <main class="p-6">
                <!-- Status Pesan Error / Berhasil -->
                @if (session('success'))
                    <div class="mb-4 p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5 text-base"></i>
                        <div>
                            <div class="font-bold">Gagal Masuk Presensi</div>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    </div>
                @endif

                <!-- Card Form Login -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </div>
                        <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Masuk dengan Nomor HP</h2>
                    </div>

                    <form action="{{ route('presensi.login.post') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="no_hp" class="block text-xs font-semibold text-slate-700 mb-1">Nomor HP Terdaftar</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-phone"></i>
                                </span>
                                <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp', '081234567890') }}" required autofocus
                                    placeholder="0812xxxxxxxx"
                                    oninput="updateDefaultPinHint(this.value)"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium">
                            </div>
                            <div class="flex items-center justify-between mt-1 text-[11px]">
                                <span class="text-slate-400">Nomor WhatsApp aktif di BAAK</span>
                                <span id="pinHintBadge" class="text-emerald-700 font-mono font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                    PIN: 567890
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="password" class="block text-xs font-semibold text-slate-700">PIN / Kata Sandi</label>
                                <span class="text-[10px] text-slate-500">Default: 6 Digit Belakang No HP</span>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <input type="password" id="password" name="password" value="567890" placeholder="Kosongkan jika pakai PIN default"
                                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium">
                                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                                    <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Bila belum diubah, PIN adalah 6 digit terakhir nomor HP Anda.</p>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                                <input type="checkbox" name="remember" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span>Ingat saya di HP ini</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-fingerprint text-lg"></i>
                            <span>Masuk Presensi Mobile</span>
                        </button>
                    </form>
                </div>

                <!-- 1-Tap Quick Select Presets for Dosen Mobile -->
                <div class="mt-5">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center justify-between">
                        <span>Pilihan Cepat Uji Coba:</span>
                        <span class="text-[10px] text-emerald-600 font-semibold"><i class="fa-solid fa-bolt mr-1"></i>1-Ketuk</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="quickFillDosen('081234567890', '567890')"
                            class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/70 active:scale-95 text-left transition flex flex-col">
                            <span class="font-bold text-emerald-950 flex items-center gap-1">
                                <i class="fa-solid fa-user-tie text-emerald-600"></i> Dr. Hendra P.
                            </span>
                            <span class="text-[10px] text-emerald-700 font-mono mt-0.5">081234567890</span>
                            <span class="text-[9px] text-slate-500 mt-1">PIN: 567890</span>
                        </button>

                        <button type="button" onclick="quickFillDosen('081298765432', '765432')"
                            class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100/70 active:scale-95 text-left transition flex flex-col">
                            <span class="font-bold text-emerald-950 flex items-center gap-1">
                                <i class="fa-solid fa-user-check text-emerald-600"></i> Siti Rahmawati
                            </span>
                            <span class="text-[10px] text-emerald-700 font-mono mt-0.5">081298765432</span>
                            <span class="text-[9px] text-slate-500 mt-1">PIN: 765432</span>
                        </button>
                    </div>
                </div>
            </main>
        </div>

        <!-- Footer Mobile Nav Switch -->
        <footer class="p-4 pt-2 border-t border-slate-100 bg-slate-50 text-center pb-safe">
            <div class="text-[11px] text-slate-500 mb-2">
                Ingin masuk ke Dashboard Admin BAAK / Kaprodi?
            </div>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-200 shadow-sm transition">
                <i class="fa-solid fa-desktop text-slate-500"></i>
                <span>Buka Portal Web Admin / BAAK</span>
            </a>
            <div class="text-[10px] text-slate-400 mt-3 font-medium">
                &copy; {{ date('Y') }} WiseOn Presensi Kampus &bull; SISTER & PDDikti
            </div>
        </footer>

    </div>

    <!-- Script Jam Realtime & Helper No HP -->
    <script>
        function updateLiveClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const clockEl = document.getElementById('liveClock');
            if (clockEl) {
                clockEl.textContent = `${h}:${m}:${s} {{ $tzLabel }}`;
            }
        }
        setInterval(updateLiveClock, 1000);

        function updateDefaultPinHint(val) {
            const cleanDigits = val.replace(/\D/g, '');
            const badge = document.getElementById('pinHintBadge');
            const pwdInput = document.getElementById('password');

            if (cleanDigits.length >= 6) {
                const sixDigits = cleanDigits.slice(-6);
                if (badge) {
                    badge.textContent = `PIN: ${sixDigits}`;
                    badge.classList.remove('hidden');
                }
                // Auto isi ke password jika password belum diedit secara manual
                if (pwdInput && (pwdInput.value === '' || pwdInput.dataset.autofilled === 'true')) {
                    pwdInput.value = sixDigits;
                    pwdInput.dataset.autofilled = 'true';
                }
            } else {
                if (badge) {
                    badge.textContent = 'PIN: 6 digit blkg No HP';
                }
            }
        }

        function quickFillDosen(phone, pin) {
            const phoneInput = document.getElementById('no_hp');
            const pwdInput = document.getElementById('password');
            if (phoneInput) {
                phoneInput.value = phone;
                updateDefaultPinHint(phone);
            }
            if (pwdInput) {
                pwdInput.value = pin;
                pwdInput.dataset.autofilled = 'false';
            }
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Jalankan hint saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const phoneInput = document.getElementById('no_hp');
            if (phoneInput && phoneInput.value) {
                updateDefaultPinHint(phoneInput.value);
            }
        });
    </script>
</body>
</html>
