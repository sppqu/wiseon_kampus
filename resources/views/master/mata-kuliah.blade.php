@extends('layouts.app')

@section('title', 'Data Master - Mata Kuliah')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Mata Kuliah</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Daftar Mata Kuliah & Silabus RPS</h1>
            <p class="text-xs text-slate-500">Katalog kurikulum mata kuliah, bobot SKS, dan rencana pembelajaran semester</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Tombol Unduh Template -->
            <a href="{{ route('master.mata-kuliah.template') }}" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-sm" title="Unduh format berkas CSV">
                <i class="fa-solid fa-file-arrow-down text-emerald-600"></i> Unduh Template
            </a>

            <!-- Tombol Impor Data MK -->
            <button onclick="openModal('modalImporMk')" class="px-3.5 py-2.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-import text-emerald-600"></i> Impor Mata Kuliah
            </button>

            <!-- Tombol Tambah MK Baru -->
            <button onclick="openModal('modalTambahMk')" class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Tambah Mata Kuliah Baru
            </button>
        </div>
    </div>

    <!-- Tabel Master Mata Kuliah -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Kode MK</th>
                        <th class="py-3.5 px-4">Nama Mata Kuliah</th>
                        <th class="py-3.5 px-3 text-center">Bobot SKS</th>
                        <th class="py-3.5 px-3 text-center">Paket Sem</th>
                        <th class="py-3.5 px-4">Program Studi</th>
                        <th class="py-3.5 px-4">Deskripsi Silabus RPS</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mataKuliahs as $mk)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-[#154e85]">
                            {{ $mk->kode_mk }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $mk->nama_mk }}</div>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="bg-amber-100 text-amber-900 px-2 py-0.5 rounded font-extrabold">
                                {{ $mk->sks }} SKS
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700">
                            Semester {{ $mk->semester_paket }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-semibold text-slate-800">{{ $mk->prodi ? $mk->prodi->nama_prodi : 'Teknik Informatika' }}</span>
                        </td>
                        <td class="py-3 px-4 max-w-xs truncate text-slate-500" title="{{ $mk->deskripsi_rps }}">
                            {{ $mk->deskripsi_rps ?: 'Silabus RPS Lengkap' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Tombol Edit MK -->
                                <button onclick="editMk({{ $mk->id }}, '{{ addslashes($mk->kode_mk) }}', '{{ addslashes($mk->nama_mk) }}', {{ $mk->sks }}, {{ $mk->semester_paket }}, {{ $mk->prodi_id }}, '{{ addslashes($mk->deskripsi_rps) }}')"
                                    class="p-1.5 rounded-lg text-slate-600 hover:text-[#154e85] hover:bg-blue-50 transition" title="Edit Mata Kuliah">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Tombol Hapus MK -->
                                <form action="{{ route('master.mata-kuliah.destroy', $mk->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $mk->nama_mk }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Mata Kuliah">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            Belum ada mata kuliah terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-100">
            {{ $mataKuliahs->links() }}
        </div>
    </div>
</div>

<!-- MODAL TAMBAH MATA KULIAH -->
<div id="modalTambahMk" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-book-bookmark text-amber-300"></i>
                <h3 class="font-bold text-sm">Tambah Mata Kuliah Baru</h3>
            </div>
            <button onclick="closeModal('modalTambahMk')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.mata-kuliah.store') }}" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode MK *</label>
                    <input type="text" name="kode_mk" required placeholder="Contoh: TIF-101"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Bobot SKS *</label>
                    <input type="number" name="sks" min="1" max="8" value="3" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Paket Sem *</label>
                    <input type="number" name="semester_paket" min="1" max="8" value="1" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Mata Kuliah *</label>
                <input type="text" name="nama_mk" required placeholder="Contoh: Pemrograman Web Lanjut & API"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Program Studi *</label>
                <select name="prodi_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach($prodis as $pr)
                        <option value="{{ $pr->id }}">{{ $pr->nama_prodi }} ({{ $pr->jenjang }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi Silabus / Capaian RPS</label>
                <textarea name="deskripsi_rps" rows="3"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none"
                    placeholder="Tuliskan gambaran umum pokok bahasan dan capaian pembelajaran mata kuliah..."></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahMk')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-check"></i> Simpan Mata Kuliah
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT MATA KULIAH -->
<div id="modalEditMk" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i>
                <h3 class="font-bold text-sm">Edit Data Mata Kuliah</h3>
            </div>
            <button onclick="closeModal('modalEditMk')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditMk" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode MK *</label>
                    <input type="text" name="kode_mk" id="editMkKode" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Bobot SKS *</label>
                    <input type="number" name="sks" id="editMkSks" min="1" max="8" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Paket Sem *</label>
                    <input type="number" name="semester_paket" id="editMkSem" min="1" max="8" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Mata Kuliah *</label>
                <input type="text" name="nama_mk" id="editMkNama" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Program Studi *</label>
                <select name="prodi_id" id="editMkProdi" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                    @foreach($prodis as $pr)
                        <option value="{{ $pr->id }}">{{ $pr->nama_prodi }} ({{ $pr->jenjang }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Deskripsi Silabus / Capaian RPS</label>
                <textarea name="deskripsi_rps" id="editMkRps" rows="3"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditMk')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPOR MATA KULIAH VIA CSV -->
<div id="modalImporMk" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 bg-emerald-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-import text-emerald-300"></i>
                <h3 class="font-bold text-sm">Impor Mata Kuliah Sekaligus (CSV)</h3>
            </div>
            <button onclick="closeModal('modalImporMk')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.mata-kuliah.import') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4 text-xs">
            @csrf
            
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-950 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5 text-xs text-emerald-900">
                    <i class="fa-solid fa-circle-question text-emerald-600"></i> Format Berkas CSV Mata Kuliah:
                </div>
                <p class="text-[11px] leading-relaxed text-emerald-800">
                    Unggah berkas <strong>.CSV</strong>. Baris pertama berisi nama kolom. Anda dapat mengunduh contoh berkas template:
                </p>
                <div class="pt-1">
                    <a href="{{ route('master.mata-kuliah.template') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-white px-2.5 py-1 rounded border border-emerald-300 hover:bg-emerald-100 transition">
                        <i class="fa-solid fa-download"></i> Unduh Contoh Template CSV Mata Kuliah
                    </a>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Berkas CSV *</label>
                <input type="file" name="file_mk" accept=".csv,text/csv,text/plain" required
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 space-y-1">
                <div class="font-bold text-slate-800">Urutan Kolom Berkas CSV:</div>
                <div class="font-mono text-[10px] text-slate-500">kode_mk, nama_mk, sks, semester_paket, kode_prodi, deskripsi_rps</div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalImporMk')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
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

    function editMk(id, kode, nama, sks, sem, prodiId, rps) {
        document.getElementById('formEditMk').action = '/master/mata-kuliah/' + id;
        document.getElementById('editMkKode').value = kode;
        document.getElementById('editMkNama').value = nama;
        document.getElementById('editMkSks').value = sks;
        document.getElementById('editMkSem').value = sem;
        document.getElementById('editMkProdi').value = prodiId;
        document.getElementById('editMkRps').value = rps;
        openModal('modalEditMk');
    }
</script>
@endsection
