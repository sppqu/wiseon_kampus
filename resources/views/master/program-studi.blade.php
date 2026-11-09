@extends('layouts.app')

@section('title', 'Data Master - Program Studi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Program Studi</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Daftar Program Studi</h1>
            <p class="text-xs text-slate-500">Program studi aktif terhubung dengan Fakultas & pangkalan data PDDikti</p>
        </div>
        <button onclick="openModal('modalTambahProdi')" class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Program Studi Baru
        </button>
    </div>

    <!-- Tabel Program Studi -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Kode Prodi</th>
                        <th class="py-3.5 px-4">Nama Program Studi</th>
                        <th class="py-3.5 px-3">Fakultas</th>
                        <th class="py-3.5 px-3 text-center">Jenjang</th>
                        <th class="py-3.5 px-3 text-center">Akreditasi</th>
                        <th class="py-3.5 px-4">Ketua Program Studi (Kaprodi)</th>
                        <th class="py-3.5 px-3 text-center">Dosen / Mhs</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($prodis as $prodi)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-[#154e85]">
                            {{ $prodi->kode_prodi }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $prodi->nama_prodi }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">ID: {{ substr($prodi->id_feeder, 0, 16) }}...</div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-semibold text-slate-800">
                                {{ $prodi->fakultas ? $prodi->fakultas->nama_fakultas : 'Fakultas Teknologi Informasi' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="bg-blue-100 text-[#154e85] px-2 py-0.5 rounded font-bold">
                                {{ $prodi->jenjang }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                {{ $prodi->akreditasi ?: 'Baik' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $prodi->nama_kaprodi }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">NIDN: {{ $prodi->nidn_kaprodi ?: '-' }}</div>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700">
                            {{ $prodi->dosens_count }} / {{ $prodi->mahasiswas_count }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Tombol Edit -->
                                <button onclick="editProdi({{ $prodi->id }}, '{{ $prodi->fakultas_id }}', '{{ addslashes($prodi->kode_prodi) }}', '{{ addslashes($prodi->nama_prodi) }}', '{{ $prodi->jenjang }}', '{{ addslashes($prodi->akreditasi) }}', '{{ addslashes($prodi->nama_kaprodi) }}', '{{ addslashes($prodi->nidn_kaprodi) }}')"
                                    class="p-1.5 rounded-lg text-slate-600 hover:text-[#154e85] hover:bg-blue-50 transition" title="Edit Program Studi">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('master.program-studi.destroy', $prodi->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus prodi {{ $prodi->nama_prodi }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Program Studi">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-slate-400">
                            Belum ada program studi terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PROGRAM STUDI -->
<div id="modalTambahProdi" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-graduation-cap text-amber-300"></i>
                <h3 class="font-bold text-sm">Tambah Program Studi Baru</h3>
            </div>
            <button onclick="closeModal('modalTambahProdi')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.program-studi.store') }}" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Fakultas Naungan *</label>
                <select name="fakultas_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                    <option value="">-- Pilih Fakultas --</option>
                    @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}">{{ $f->nama_fakultas }} ({{ $f->kode_fakultas }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode Prodi (PDDikti) *</label>
                    <input type="text" name="kode_prodi" required placeholder="Contoh: 55201"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jenjang Pendidikan *</label>
                    <select name="jenjang" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="S1">S1 - Sarjana</option>
                        <option value="D3">D3 - Diploma Tiga</option>
                        <option value="D4">D4 - Sarjana Terapan</option>
                        <option value="S2">S2 - Magister</option>
                        <option value="S3">S3 - Doktoral</option>
                        <option value="Profesi">Profesi</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Program Studi *</label>
                <input type="text" name="nama_prodi" required placeholder="Contoh: Teknik Informatika"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Akreditasi Prodi *</label>
                <input type="text" name="akreditasi" required placeholder="Contoh: Unggul / Baik Sekali" value="Baik Sekali"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kaprodi *</label>
                    <input type="text" name="nama_kaprodi" required placeholder="Nama lengkap & gelar"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIDN Kaprodi</label>
                    <input type="text" name="nidn_kaprodi" placeholder="10 digit NIDN"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahProdi')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-check"></i> Simpan Program Studi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT PROGRAM STUDI -->
<div id="modalEditProdi" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i>
                <h3 class="font-bold text-sm">Edit Data Program Studi</h3>
            </div>
            <button onclick="closeModal('modalEditProdi')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditProdi" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Fakultas Naungan *</label>
                <select name="fakultas_id" id="editProdiFakultas" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                    @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}">{{ $f->nama_fakultas }} ({{ $f->kode_fakultas }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode Prodi (PDDikti) *</label>
                    <input type="text" name="kode_prodi" id="editProdiKode" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jenjang Pendidikan *</label>
                    <select name="jenjang" id="editProdiJenjang" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="S1">S1 - Sarjana</option>
                        <option value="D3">D3 - Diploma Tiga</option>
                        <option value="D4">D4 - Sarjana Terapan</option>
                        <option value="S2">S2 - Magister</option>
                        <option value="S3">S3 - Doktoral</option>
                        <option value="Profesi">Profesi</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Program Studi *</label>
                <input type="text" name="nama_prodi" id="editProdiNama" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Akreditasi Prodi *</label>
                <input type="text" name="akreditasi" id="editProdiAkreditasi" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kaprodi *</label>
                    <input type="text" name="nama_kaprodi" id="editProdiKaprodi" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIDN Kaprodi</label>
                    <input type="text" name="nidn_kaprodi" id="editProdiNidnKaprodi"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditProdi')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
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

    function editProdi(id, fakultasId, kode, nama, jenjang, akreditasi, kaprodi, nidn) {
        document.getElementById('formEditProdi').action = '/master/program-studi/' + id;
        document.getElementById('editProdiFakultas').value = fakultasId;
        document.getElementById('editProdiKode').value = kode;
        document.getElementById('editProdiNama').value = nama;
        document.getElementById('editProdiJenjang').value = jenjang;
        document.getElementById('editProdiAkreditasi').value = akreditasi;
        document.getElementById('editProdiKaprodi').value = kaprodi;
        document.getElementById('editProdiNidnKaprodi').value = nidn;
        openModal('modalEditProdi');
    }
</script>
@endsection
