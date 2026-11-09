@extends('layouts.app')

@section('title', 'Jadwal Kerja per Dosen')

@section('content')
<div class="space-y-6">

    <!-- Header Admin -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-0.5 rounded-full font-bold uppercase">
                    Pengaturan Presensi
                </span>
                <span class="text-xs text-slate-300">| Pengaturan Jam & Hari Kerja Individual</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-white mt-1">Jadwal Kerja & Hari Kerja per Dosen / Karyawan</h1>
            <p class="text-xs text-slate-300 mt-1">
                Atur jam masuk, jam pulang, dan hari kerja khusus untuk setiap dosen secara fleksibel agar tidak harus sama antar satu dosen dengan yang lain.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Tombol Terapkan Massal -->
            <button onclick="openMassalModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-users-gear"></i> Terapkan Jadwal Massal
            </button>

            <!-- Tombol Kembali ke Pengaturan Global -->
            <a href="{{ route('admin.pengaturan-presensi') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs transition flex items-center gap-2">
                <i class="fa-solid fa-sliders"></i> Standar Global Kampus
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Dosen</div>
            <div class="text-3xl font-extrabold text-slate-900 mt-1">{{ $summary['total_dosen'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">Dosen terdaftar di sistem</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Jadwal Khusus (Custom)</div>
            <div class="text-3xl font-extrabold text-indigo-600 mt-1">{{ $summary['jadwal_khusus'] }}</div>
            <div class="text-[11px] text-indigo-600 font-semibold mt-1"><i class="fa-solid fa-user-check"></i> Memiliki jam / hari kerja khusus</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Jadwal Standar Kampus</div>
            <div class="text-3xl font-extrabold text-slate-700 mt-1">{{ $summary['jadwal_standar'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1">
                {{ $defaultSettings['jam_masuk_kantor'] ?? '08:00' }} - {{ $defaultSettings['jam_pulang_kantor'] ?? '16:00' }} (Senin-Jumat)
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form action="{{ route('admin.jadwal-kerja-dosen') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <!-- Filter Program Studi -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Program Studi</label>
                <select name="program_studi_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Semua Program Studi</option>
                    @foreach($semuaProdi as $p)
                        <option value="{{ $p->id }}" {{ $prodiPilih == $p->id ? 'selected' : '' }}>
                            {{ $p->jenjang }} {{ $p->nama_prodi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pencarian Bebas -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Cari Nama Dosen / NIDN</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama dosen..."
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Tombol Filter -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter
                </button>
                <a href="{{ route('admin.jadwal-kerja-dosen') }}" class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Pengaturan Jam Kerja per Dosen -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-clock text-indigo-600"></i> Daftar Jadwal Kerja Individual Dosen
                </h3>
                <p class="text-xs text-slate-500">Klik "Atur Jadwal" pada baris dosen untuk mengubah hari kerja dan jam operasionalnya</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                Total: {{ $allDosen->count() }} Dosen
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 text-center w-12">No</th>
                        <th class="py-3 px-4">Dosen & NIDN</th>
                        <th class="py-3 px-4">Program Studi</th>
                        <th class="py-3 px-4">Hari Kerja Aktif</th>
                        <th class="py-3 px-4 text-center">Jam Kerja (In - Out)</th>
                        <th class="py-3 px-4 text-center">Toleransi</th>
                        <th class="py-3 px-4 text-center">Status Jadwal</th>
                        <th class="py-3 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($allDosen as $dsn)
                        @php
                            $jk = $dsn->jadwalKerja;
                            $isCustom = $jk && $jk->is_custom;
                            $jamMasuk = $isCustom ? substr($jk->jam_masuk, 0, 5) : ($defaultSettings['jam_masuk_kantor'] ?? '08:00');
                            $jamPulang = $isCustom ? substr($jk->jam_pulang, 0, 5) : ($defaultSettings['jam_pulang_kantor'] ?? '16:00');
                            $toleransi = $isCustom ? $jk->toleransi_menit : ($defaultSettings['toleransi_menit_kantor'] ?? 15);
                            $hariKerja = $isCustom && !empty($jk->hari_kerja) ? $jk->hari_kerja : ($defaultSettings['hari_kerja'] ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
                        @endphp
                        <tr class="hover:bg-indigo-50/30 transition">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-mono">{{ $loop->iteration }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 text-xs">{{ $dsn->nama_lengkap }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">NIDN: {{ $dsn->nidn ?: '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700 font-semibold">{{ $dsn->prodi->nama_prodi ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $dsn->prodi->jenjang ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                                        @if(in_array($h, $hariKerja))
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                {{ substr($h, 0, 3) }}
                                            </span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400">
                                                {{ substr($h, 0, 3) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                                @if($isCustom && $jk->keterangan)
                                    <div class="text-[10px] text-slate-400 mt-1 italic"><i class="fa-solid fa-tag"></i> {{ $jk->keterangan }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="font-bold text-emerald-700">{{ $jamMasuk }}</span>
                                <span class="text-slate-400">-</span>
                                <span class="font-bold text-amber-700">{{ $jamPulang }}</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">WIB</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="text-slate-600 font-semibold">+{{ $toleransi }} Mnt</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($isCustom)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-300">
                                        <i class="fa-solid fa-user-pen"></i> Jadwal Khusus
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                        Standar Kampus
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Edit Modal -->
                                    <button type="button" onclick="openEditModal({{ $dsn->id }}, '{{ addslashes($dsn->nama_lengkap) }}', '{{ $jamMasuk }}', '{{ $jamPulang }}', {{ $toleransi }}, {{ json_encode($hariKerja) }}, '{{ addslashes($jk->keterangan ?? '') }}')"
                                        class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg border border-indigo-200 transition flex items-center gap-1 text-[11px]" title="Atur Jadwal Dosen Ini">
                                        <i class="fa-solid fa-pen-to-square"></i> Atur
                                    </button>

                                    @if($isCustom)
                                    <!-- Tombol Reset ke Standar -->
                                    <form action="{{ route('admin.jadwal-kerja-dosen.reset', $dsn->id) }}" method="POST" class="inline" onsubmit="return confirm('Kembalikan jadwal dosen {{ $dsn->nama_lengkap }} ke standar kampus?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200 transition text-[11px]" title="Reset ke Standar">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Tidak ada data dosen yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal 1: Edit Jadwal Kerja Individual Per Dosen -->
<div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-clock text-indigo-600"></i> Atur Jadwal Kerja Dosen
                </h3>
                <p class="text-xs text-slate-500 mt-0.5" id="modalDosenNama">Nama Dosen</p>
            </div>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditJadwal" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Pilihan Hari Kerja -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Hari Kerja Aktif</label>
                    <div class="flex gap-2">
                        <button type="button" onclick="setHariQuick(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])" class="text-[10px] text-indigo-600 hover:underline font-bold">Senin-Jumat</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="setHariQuick(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])" class="text-[10px] text-indigo-600 hover:underline font-bold">Senin-Sabtu</button>
                    </div>
                </div>
                <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                        <label class="cursor-pointer text-center">
                            <input type="checkbox" name="hari_kerja[]" value="{{ $hari }}" id="chk_{{ $hari }}" class="sr-only peer hari-checkbox">
                            <div class="py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition">
                                {{ substr($hari, 0, 3) }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Masuk (Clock In)</label>
                    <input type="time" name="jam_masuk" id="inputJamMasuk" required
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Pulang (Clock Out)</label>
                    <input type="time" name="jam_pulang" id="inputJamPulang" required
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <!-- Toleransi Keterlambatan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Toleransi Keterlambatan</label>
                <div class="relative">
                    <input type="number" name="toleransi_menit" id="inputToleransi" required min="0" max="120"
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <span class="absolute right-3 top-2 text-xs text-slate-400 font-bold">Menit</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Presensi di atas (Jam Masuk + Toleransi) otomatis berstatus Terlambat.</p>
            </div>

            <!-- Keterangan / Tag -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Label Jadwal (Opsional)</label>
                <input type="text" name="keterangan" id="inputKeterangan" placeholder="Contoh: Dosen Tetap, Pejabat Dekan, Shift Khusus"
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal Dosen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Terapkan Jadwal Massal -->
<div id="massalModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-indigo-600"></i> Terapkan Jadwal Massal ke Dosen
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Terapkan konfigurasi jam & hari kerja ke seluruh dosen sekaligus</p>
            </div>
            <button onclick="closeMassalModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.jadwal-kerja-dosen.massal') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Target Prodi -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Dosen</label>
                <select name="target_prodi_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="semua">Semua Dosen di Seluruh Kampus</option>
                    @foreach($semuaProdi as $p)
                        <option value="{{ $p->id }}">Khusus Dosen Prodi: {{ $p->nama_prodi }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Hari Kerja -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Hari Kerja Aktif</label>
                <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                        <label class="cursor-pointer text-center">
                            <input type="checkbox" name="hari_kerja[]" value="{{ $hari }}" {{ in_array($hari, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']) ? 'checked' : '' }} class="sr-only peer">
                            <div class="py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition">
                                {{ substr($hari, 0, 3) }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Jam Masuk & Pulang -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Masuk</label>
                    <input type="time" name="jam_masuk" value="08:00" required
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Pulang</label>
                    <input type="time" name="jam_pulang" value="16:00" required
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Toleransi Keterlambatan</label>
                <div class="relative">
                    <input type="number" name="toleransi_menit" value="15" required min="0" max="120"
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <span class="absolute right-3 top-2 text-xs text-slate-400 font-bold">Menit</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan Label</label>
                <input type="text" name="keterangan" value="Shift Reguler Kampus"
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeMassalModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5" onclick="return confirm('Apakah Anda yakin ingin menerapkan pengaturan jadwal ini ke seluruh dosen yang dipilih?')">
                    <i class="fa-solid fa-bolt"></i> Terapkan Massal Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditModal(dosenId, nama, jamMasuk, jamPulang, toleransi, hariArr, keterangan) {
        document.getElementById('modalDosenNama').innerText = nama;
        document.getElementById('formEditJadwal').action = '/admin/jadwal-kerja-dosen/' + dosenId;
        document.getElementById('inputJamMasuk').value = jamMasuk;
        document.getElementById('inputJamPulang').value = jamPulang;
        document.getElementById('inputToleransi').value = toleransi;
        document.getElementById('inputKeterangan').value = keterangan || '';

        // Reset semua checkbox hari
        document.querySelectorAll('.hari-checkbox').forEach(cb => {
            cb.checked = hariArr.includes(cb.value);
        });

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function setHariQuick(hariList) {
        document.querySelectorAll('.hari-checkbox').forEach(cb => {
            cb.checked = hariList.includes(cb.value);
        });
    }

    function openMassalModal() {
        document.getElementById('massalModal').classList.remove('hidden');
    }

    function closeMassalModal() {
        document.getElementById('massalModal').classList.add('hidden');
    }
</script>
@endpush
