@extends('layouts.app')

@section('title', 'Data Master - Ruang')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Ruang</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Daftar Ruang Perkuliahan & Geofencing</h1>
            <p class="text-xs text-slate-500">Master ruangan kelas, laboratorium, dan titik koordinat GPS validasi presensi dosen</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Tombol Unduh Template -->
            <a href="{{ route('master.ruang.template') }}" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-sm" title="Unduh format berkas CSV">
                <i class="fa-solid fa-file-arrow-down text-emerald-600"></i> Unduh Template
            </a>

            <!-- Tombol Impor Ruang -->
            <button onclick="openModal('modalImporRuang')" class="px-3.5 py-2.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-import text-emerald-600"></i> Impor Ruang
            </button>

            <!-- Tombol Tambah Ruang Baru -->
            <button onclick="openModal('modalTambahRuang')" class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Tambah Ruang Baru
            </button>
        </div>
    </div>

    <!-- Grid Kartu Ruangan Kampus -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse($ruangans as $ruang)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-[#154e85] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold bg-blue-50 text-[#154e85] px-2.5 py-1 rounded-lg border border-blue-200">
                        {{ $ruang->kode_ruangan }}
                    </span>
                    <span class="text-xs text-slate-600 font-semibold bg-slate-100 px-2.5 py-0.5 rounded-full">
                        Kapasitas: <strong class="text-slate-900">{{ $ruang->kapasitas }} Mahasiswa</strong>
                    </span>
                </div>
                <h2 class="text-base font-bold text-slate-900 mt-2.5 leading-snug">{{ $ruang->nama_ruangan }}</h2>
                <div class="text-xs text-slate-600 mt-3 space-y-1.5">
                    <div>
                        <i class="fa-solid fa-building text-slate-400 mr-1.5 w-4 text-center"></i>
                        Gedung: <strong>{{ $ruang->gedung }}</strong> (Lantai {{ $ruang->lantai }})
                    </div>
                    <div>
                        <i class="fa-solid fa-location-dot text-emerald-600 mr-1.5 w-4 text-center"></i>
                        Koordinat GPS: <span class="font-mono text-slate-800 font-semibold">{{ $ruang->latitude }}, {{ $ruang->longitude }}</span>
                    </div>
                    <div>
                        <i class="fa-solid fa-circle-notch text-blue-600 mr-1.5 w-4 text-center"></i>
                        Radius Geofencing: <strong>{{ $ruang->radius_meter }} meter</strong>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Edit & Hapus -->
            <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md font-bold flex items-center gap-1 text-[11px]">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Siap Presensi GPS
                </span>

                <div class="flex items-center gap-1.5">
                    <!-- Tombol Edit Ruangan -->
                    <button onclick="editRuang({{ $ruang->id }}, '{{ addslashes($ruang->kode_ruangan) }}', '{{ addslashes($ruang->nama_ruangan) }}', '{{ addslashes($ruang->gedung) }}', {{ $ruang->lantai }}, {{ $ruang->kapasitas }}, {{ $ruang->latitude }}, {{ $ruang->longitude }}, {{ $ruang->radius_meter }})"
                        class="p-1.5 rounded-lg text-slate-600 hover:text-[#154e85] hover:bg-blue-50 transition" title="Edit Ruangan">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                    <!-- Tombol Hapus Ruangan -->
                    <form action="{{ route('master.ruang.destroy', $ruang->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan {{ $ruang->nama_ruangan }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Ruangan">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 text-center py-12 bg-white rounded-2xl border border-slate-200">
            <i class="fa-solid fa-door-open text-4xl text-slate-300 mb-2"></i>
            <p class="text-sm font-bold text-slate-700">Belum Ada Data Ruangan</p>
            <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Ruang Baru" atau "Impor Ruang".</p>
        </div>
        @endforelse
    </div>
</div>

<!-- MODAL TAMBAH RUANG -->
<div id="modalTambahRuang" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-door-open text-amber-300"></i>
                <h3 class="font-bold text-sm">Tambah Ruangan Baru</h3>
            </div>
            <button onclick="closeModal('modalTambahRuang')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.ruang.store') }}" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode Ruangan *</label>
                    <input type="text" name="kode_ruangan" required placeholder="Contoh: LAB-KOMP-01"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kapasitas (Orang) *</label>
                    <input type="number" name="kapasitas" min="1" max="500" value="40" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Ruangan *</label>
                <input type="text" name="nama_ruangan" required placeholder="Contoh: Laboratorium Komputer 1"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Gedung Kampus *</label>
                    <input type="text" name="gedung" required placeholder="Contoh: Gedung Rektorat Baru"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Lantai *</label>
                    <input type="number" name="lantai" min="1" max="20" value="1" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl space-y-2.5">
                <div class="font-bold text-blue-950 flex items-center gap-1.5">
                    <i class="fa-solid fa-location-crosshairs text-blue-700"></i> Parameter Validasi Geofencing Presensi
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px]">
                    <div>
                        <label class="block text-slate-600 font-semibold mb-0.5">Latitude *</label>
                        <input type="number" step="any" name="latitude" value="-6.20880000" required
                            class="w-full py-1.5 px-2 rounded-lg border border-slate-300 font-mono text-[11px] focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold mb-0.5">Longitude *</label>
                        <input type="number" step="any" name="longitude" value="106.84560000" required
                            class="w-full py-1.5 px-2 rounded-lg border border-slate-300 font-mono text-[11px] focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold mb-0.5">Radius (Meter) *</label>
                        <input type="number" name="radius_meter" min="10" max="1000" value="200" required
                            class="w-full py-1.5 px-2 rounded-lg border border-slate-300 font-mono text-[11px] focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahRuang')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-check"></i> Simpan Ruangan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT RUANG -->
<div id="modalEditRuang" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i>
                <h3 class="font-bold text-sm">Edit Data Ruangan</h3>
            </div>
            <button onclick="closeModal('modalEditRuang')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditRuang" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode Ruangan *</label>
                    <input type="text" name="kode_ruangan" id="editRuangKode" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kapasitas (Orang) *</label>
                    <input type="number" name="kapasitas" id="editRuangKapasitas" min="1" max="500" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Ruangan *</label>
                <input type="text" name="nama_ruangan" id="editRuangNama" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Gedung Kampus *</label>
                    <input type="text" name="gedung" id="editRuangGedung" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Lantai *</label>
                    <input type="number" name="lantai" id="editRuangLantai" min="1" max="20" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl space-y-2.5">
                <div class="font-bold text-blue-950 flex items-center gap-1.5">
                    <i class="fa-solid fa-location-crosshairs text-blue-700"></i> Parameter Validasi Geofencing Presensi
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px]">
                    <div>
                        <label class="block text-slate-600 font-semibold mb-0.5">Latitude *</label>
                        <input type="number" step="any" name="latitude" id="editRuangLat" required
                            class="w-full py-1.5 px-2 rounded-lg border border-slate-300 font-mono text-[11px] focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold mb-0.5">Longitude *</label>
                        <input type="number" step="any" name="longitude" id="editRuangLon" required
                            class="w-full py-1.5 px-2 rounded-lg border border-slate-300 font-mono text-[11px] focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold mb-0.5">Radius (Meter) *</label>
                        <input type="number" name="radius_meter" id="editRuangRadius" min="10" max="1000" required
                            class="w-full py-1.5 px-2 rounded-lg border border-slate-300 font-mono text-[11px] focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditRuang')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPOR RUANG VIA CSV -->
<div id="modalImporRuang" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 bg-emerald-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-import text-emerald-300"></i>
                <h3 class="font-bold text-sm">Impor Ruangan Sekaligus (CSV)</h3>
            </div>
            <button onclick="closeModal('modalImporRuang')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.ruang.import') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4 text-xs">
            @csrf
            
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-950 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5 text-xs text-emerald-900">
                    <i class="fa-solid fa-circle-question text-emerald-600"></i> Format Berkas CSV Ruangan:
                </div>
                <p class="text-[11px] leading-relaxed text-emerald-800">
                    Unggah berkas <strong>.CSV</strong>. Baris pertama berisi nama kolom. Anda dapat mengunduh contoh berkas template ruangan di bawah:
                </p>
                <div class="pt-1">
                    <a href="{{ route('master.ruang.template') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-white px-2.5 py-1 rounded border border-emerald-300 hover:bg-emerald-100 transition">
                        <i class="fa-solid fa-download"></i> Unduh Contoh Template CSV Ruang
                    </a>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Berkas CSV *</label>
                <input type="file" name="file_ruang" accept=".csv,text/csv,text/plain" required
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 space-y-1">
                <div class="font-bold text-slate-800">Urutan Kolom Berkas CSV:</div>
                <div class="font-mono text-[10px] text-slate-500">kode_ruangan, nama_ruangan, gedung, lantai, kapasitas, latitude, longitude, radius_meter</div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalImporRuang')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Mulai Unggah & Impor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function editRuang(id, kode, nama, gedung, lantai, kapasitas, lat, lon, radius) {
        document.getElementById('formEditRuang').action = '/master/ruang/' + id;
        document.getElementById('editRuangKode').value = kode;
        document.getElementById('editRuangNama').value = nama;
        document.getElementById('editRuangGedung').value = gedung;
        document.getElementById('editRuangLantai').value = lantai;
        document.getElementById('editRuangKapasitas').value = kapasitas;
        document.getElementById('editRuangLat').value = lat;
        document.getElementById('editRuangLon').value = lon;
        document.getElementById('editRuangRadius').value = radius;
        openModal('modalEditRuang');
    }
</script>
@endsection
