@extends('layouts.app')

@section('title', 'Data Master - Fakultas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Fakultas</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Daftar Fakultas</h1>
            <p class="text-xs text-slate-500">Struktur unit fakultas pengelola program studi di lingkungan kampus</p>
        </div>
        <button onclick="openModal('modalTambahFakultas')" class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Fakultas Baru
        </button>
    </div>

    <!-- Grid Kartu Fakultas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @forelse($fakultasList as $fak)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-[#154e85] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold bg-blue-50 text-[#154e85] px-2.5 py-1 rounded-lg border border-blue-200">
                        {{ $fak->kode_fakultas }}
                    </span>
                    <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2.5 py-0.5 rounded-full">
                        {{ $fak->program_studis_count }} Program Studi
                    </span>
                </div>
                <h2 class="text-base font-bold text-slate-900 mt-2.5 leading-snug">{{ $fak->nama_fakultas }}</h2>
                <div class="text-xs text-slate-600 mt-3 space-y-1.5">
                    <div>
                        <i class="fa-solid fa-user-tie text-slate-400 mr-1.5 w-4 text-center"></i>
                        Dekan: <strong>{{ $fak->dekan ?: 'Belum ditentukan' }}</strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-building text-slate-400 mr-1.5 w-4 text-center"></i>
                        Lokasi: {{ $fak->gedung ?: 'Gedung Perkuliahan' }}
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Edit & Hapus -->
            <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('master.program-studi') }}" class="text-[#154e85] hover:underline font-bold text-[11px]">
                    Lihat Prodi &rarr;
                </a>

                <div class="flex items-center gap-1.5">
                    <!-- Tombol Edit -->
                    <button onclick="editFakultas({{ $fak->id }}, '{{ addslashes($fak->kode_fakultas) }}', '{{ addslashes($fak->nama_fakultas) }}', '{{ addslashes($fak->dekan) }}', '{{ addslashes($fak->gedung) }}')"
                        class="p-1.5 rounded-lg text-slate-600 hover:text-[#154e85] hover:bg-blue-50 transition" title="Edit Fakultas">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                    <!-- Tombol Hapus -->
                    <form action="{{ route('master.fakultas.destroy', $fak->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus fakultas {{ $fak->nama_fakultas }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Fakultas">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 bg-white rounded-2xl border border-slate-200">
            <i class="fa-solid fa-building-columns text-4xl text-slate-300 mb-2"></i>
            <p class="text-sm font-bold text-slate-700">Belum Ada Data Fakultas</p>
            <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Fakultas Baru" untuk menambahkan data.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- MODAL TAMBAH FAKULTAS -->
<div id="modalTambahFakultas" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-building-columns text-amber-300"></i>
                <h3 class="font-bold text-sm">Tambah Fakultas Baru</h3>
            </div>
            <button onclick="closeModal('modalTambahFakultas')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.fakultas.store') }}" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Kode Fakultas *</label>
                <input type="text" name="kode_fakultas" required placeholder="Contoh: FTI, FEB, FKIP"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Fakultas *</label>
                <input type="text" name="nama_fakultas" required placeholder="Contoh: Fakultas Teknologi Informasi"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Dekan Lengkap & Gelar</label>
                <input type="text" name="dekan" placeholder="Contoh: Dr. Ir. Ahmad Dahlan, M.Sc."
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Gedung / Lokasi Kantor</label>
                <input type="text" name="gedung" placeholder="Contoh: Gedung Rektorat Baru Lt. 2-3"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahFakultas')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-check"></i> Simpan Fakultas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT FAKULTAS -->
<div id="modalEditFakultas" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i>
                <h3 class="font-bold text-sm">Edit Data Fakultas</h3>
            </div>
            <button onclick="closeModal('modalEditFakultas')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditFakultas" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Kode Fakultas *</label>
                <input type="text" name="kode_fakultas" id="editKodeFakultas" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Fakultas *</label>
                <input type="text" name="nama_fakultas" id="editNamaFakultas" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Dekan Lengkap & Gelar</label>
                <input type="text" name="dekan" id="editDekan"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Gedung / Lokasi Kantor</label>
                <input type="text" name="gedung" id="editGedung"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditFakultas')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
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

    function editFakultas(id, kode, nama, dekan, gedung) {
        document.getElementById('formEditFakultas').action = '/master/fakultas/' + id;
        document.getElementById('editKodeFakultas').value = kode;
        document.getElementById('editNamaFakultas').value = nama;
        document.getElementById('editDekan').value = dekan;
        document.getElementById('editGedung').value = gedung;
        openModal('modalEditFakultas');
    }
</script>
@endsection
