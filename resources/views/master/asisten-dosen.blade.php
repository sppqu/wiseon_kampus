@extends('layouts.app')

@section('title', 'Data Master - Asisten Dosen')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Asisten Dosen</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Daftar Asisten Dosen & Tutor Lab</h1>
            <p class="text-xs text-slate-500">Asisten akademik yang mendampingi praktikum dan presensi mahasiswa</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Tombol Unduh Template -->
            <a href="{{ route('master.asisten-dosen.template') }}" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-sm" title="Unduh format berkas CSV">
                <i class="fa-solid fa-file-arrow-down text-emerald-600"></i> Unduh Template
            </a>

            <!-- Tombol Impor Data Asdos -->
            <button onclick="openModal('modalImporAsdos')" class="px-3.5 py-2.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-import text-emerald-600"></i> Impor Asdos
            </button>

            <!-- Tombol Tambah Asdos Baru -->
            <button onclick="openModal('modalTambahAsdos')" class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Tambah Asisten Baru
            </button>
        </div>
    </div>

    <!-- Grid Kartu Asisten Dosen -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @forelse($asdosList as $asdos)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-[#154e85] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold text-[#154e85] bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                        {{ $asdos->nim }}
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $asdos->status === 'Aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $asdos->status }}
                    </span>
                </div>
                <h2 class="text-base font-bold text-slate-900 mt-2.5 leading-snug">{{ $asdos->nama }}</h2>
                <div class="text-xs text-slate-600 mt-3 space-y-1.5">
                    <div>
                        <i class="fa-solid fa-book-open text-slate-400 mr-1.5 w-4 text-center"></i>
                        MK: <strong>{{ $asdos->mataKuliah ? $asdos->mataKuliah->nama_mk : 'Pemrograman Web' }}</strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-chalkboard-user text-slate-400 mr-1.5 w-4 text-center"></i>
                        Dosen: {{ $asdos->dosen ? $asdos->dosen->nama_gelar : 'Dosen Pengampu' }}
                    </div>
                    <div>
                        <i class="fa-solid fa-users text-slate-400 mr-1.5 w-4 text-center"></i>
                        Kelas: <strong>{{ $asdos->nama_kelas ?: 'TI-1A' }}</strong>
                    </div>
                    @if($asdos->email)
                    <div class="text-slate-500 font-mono text-[11px] truncate">
                        <i class="fa-solid fa-envelope text-slate-400 mr-1.5 w-4 text-center"></i>
                        {{ $asdos->email }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tombol Aksi Edit & Hapus -->
            <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400 text-[11px]">{{ $asdos->no_hp ?: '-' }}</span>

                <div class="flex items-center gap-1.5">
                    <!-- Tombol Edit -->
                    <button onclick="editAsdos({{ $asdos->id }}, '{{ addslashes($asdos->nim) }}', '{{ addslashes($asdos->nama) }}', '{{ addslashes($asdos->email) }}', '{{ addslashes($asdos->no_hp) }}', '{{ $asdos->mata_kuliah_id }}', '{{ $asdos->dosen_id }}', '{{ addslashes($asdos->nama_kelas) }}', '{{ $asdos->status }}')"
                        class="p-1.5 rounded-lg text-slate-600 hover:text-[#154e85] hover:bg-blue-50 transition" title="Edit Data Asdos">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                    <!-- Tombol Hapus -->
                    <form action="{{ route('master.asisten-dosen.destroy', $asdos->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus asisten dosen {{ $asdos->nama }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Asdos">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 bg-white rounded-2xl border border-slate-200">
            <i class="fa-solid fa-users text-4xl text-slate-300 mb-2"></i>
            <p class="text-sm font-bold text-slate-700">Belum Ada Data Asisten Dosen</p>
            <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Asisten Baru" atau "Impor Asdos".</p>
        </div>
        @endforelse
    </div>
</div>

<!-- MODAL TAMBAH ASISTEN DOSEN -->
<div id="modalTambahAsdos" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-amber-300"></i>
                <h3 class="font-bold text-sm">Tambah Asisten Dosen Baru</h3>
            </div>
            <button onclick="closeModal('modalTambahAsdos')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.asisten-dosen.store') }}" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIM Mahasiswa *</label>
                    <input type="text" name="nim" required placeholder="Contoh: 202255201001"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama" required placeholder="Contoh: Ahmad Fauzi"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Mata Kuliah Praktikum *</label>
                    <select name="mata_kuliah_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($mataKuliahs as $mk)
                            <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Dosen Pembimbing *</label>
                    <select name="dosen_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="">-- Pilih Dosen --</option>
                        @foreach($dosens as $ds)
                            <option value="{{ $ds->id }}">{{ $ds->nama_gelar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kelas Praktikum *</label>
                    <input type="text" name="nama_kelas" required placeholder="Contoh: TI-5A"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Keaktifan *</label>
                    <select name="status" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="Aktif">Aktif</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Email Mahasiswa</label>
                    <input type="email" name="email" placeholder="student@kampus.ac.id"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">No. HP / WA</label>
                    <input type="text" name="no_hp" placeholder="0812xxxxxxxx"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahAsdos')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-check"></i> Simpan Asisten Dosen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT ASISTEN DOSEN -->
<div id="modalEditAsdos" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i>
                <h3 class="font-bold text-sm">Edit Data Asisten Dosen</h3>
            </div>
            <button onclick="closeModal('modalEditAsdos')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditAsdos" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIM Mahasiswa *</label>
                    <input type="text" name="nim" id="editAsdosNim" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama" id="editAsdosNama" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Mata Kuliah Praktikum *</label>
                    <select name="mata_kuliah_id" id="editAsdosMk" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        @foreach($mataKuliahs as $mk)
                            <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Dosen Pembimbing *</label>
                    <select name="dosen_id" id="editAsdosDosen" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        @foreach($dosens as $ds)
                            <option value="{{ $ds->id }}">{{ $ds->nama_gelar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kelas Praktikum *</label>
                    <input type="text" name="nama_kelas" id="editAsdosKelas" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Keaktifan *</label>
                    <select name="status" id="editAsdosStatus" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="Aktif">Aktif</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Email Mahasiswa</label>
                    <input type="email" name="email" id="editAsdosEmail"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">No. HP / WA</label>
                    <input type="text" name="no_hp" id="editAsdosHp"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditAsdos')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPOR ASISTEN DOSEN VIA CSV -->
<div id="modalImporAsdos" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 bg-emerald-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-import text-emerald-300"></i>
                <h3 class="font-bold text-sm">Impor Asisten Dosen Sekaligus (CSV)</h3>
            </div>
            <button onclick="closeModal('modalImporAsdos')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.asisten-dosen.import') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4 text-xs">
            @csrf
            
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-950 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5 text-xs text-emerald-900">
                    <i class="fa-solid fa-circle-question text-emerald-600"></i> Format Berkas CSV Asisten Dosen:
                </div>
                <p class="text-[11px] leading-relaxed text-emerald-800">
                    Unggah berkas <strong>.CSV</strong>. Baris pertama berisi nama kolom. Anda dapat mengunduh contoh berkas yang sudah siap pakai:
                </p>
                <div class="pt-1">
                    <a href="{{ route('master.asisten-dosen.template') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-white px-2.5 py-1 rounded border border-emerald-300 hover:bg-emerald-100 transition">
                        <i class="fa-solid fa-download"></i> Unduh Contoh Template CSV Asdos
                    </a>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Berkas CSV *</label>
                <input type="file" name="file_asdos" accept=".csv,text/csv,text/plain" required
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 space-y-1">
                <div class="font-bold text-slate-800">Urutan Kolom Berkas CSV:</div>
                <div class="font-mono text-[10px] text-slate-500">nim, nama, email, no_hp, kode_mk, nidn_dosen, nama_kelas, status</div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalImporAsdos')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
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

    function editAsdos(id, nim, nama, email, hp, mkId, dosenId, kelas, status) {
        document.getElementById('formEditAsdos').action = '/master/asisten-dosen/' + id;
        document.getElementById('editAsdosNim').value = nim;
        document.getElementById('editAsdosNama').value = nama;
        document.getElementById('editAsdosEmail').value = email;
        document.getElementById('editAsdosHp').value = hp;
        document.getElementById('editAsdosMk').value = mkId;
        document.getElementById('editAsdosDosen').value = dosenId;
        document.getElementById('editAsdosKelas').value = kelas;
        document.getElementById('editAsdosStatus').value = status;
        openModal('modalEditAsdos');
    }
</script>
@endsection
