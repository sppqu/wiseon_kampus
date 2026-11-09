@extends('layouts.app')

@section('title', 'Data Master - Dosen')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Dosen</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Daftar Dosen & Tenaga Pengajar</h1>
            <p class="text-xs text-slate-500">Pangkalan data dosen tetap, NIDN, penugasan mengajar, dan akun presensi</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Tombol Unduh Template -->
            <a href="{{ route('master.dosen.template') }}" class="px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-sm" title="Unduh format berkas CSV untuk impor data">
                <i class="fa-solid fa-file-arrow-down text-emerald-600"></i> Unduh Template
            </a>

            <!-- Tombol Impor Data Dosen -->
            <button onclick="openModal('modalImporDosen')" class="px-3.5 py-2.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-import text-emerald-600"></i> Impor Data Dosen
            </button>

            <!-- Tombol Tambah Dosen Baru -->
            <button onclick="openModal('modalTambahDosen')" class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-user-plus"></i> Tambah Dosen Baru
            </button>
        </div>
    </div>

    <!-- Tabel Master Dosen -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">NIDN / NIP</th>
                        <th class="py-3.5 px-4">Nama Lengkap & Gelar</th>
                        <th class="py-3.5 px-3">Program Studi</th>
                        <th class="py-3.5 px-3">Kontak / Email</th>
                        <th class="py-3.5 px-3 text-center">Status BKD</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dosens as $dosen)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4">
                            <div class="font-mono font-bold text-[#154e85]">{{ $dosen->nidn }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">NIP: {{ $dosen->nip ?: '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $dosen->nama_gelar }}</div>
                            <div class="text-[10px] text-slate-500">Gender: {{ $dosen->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="font-semibold text-slate-800">{{ $dosen->prodi ? $dosen->prodi->nama_prodi : 'Teknik Informatika' }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="text-slate-700 font-medium">{{ $dosen->email }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $dosen->no_hp ?: '-' }}</div>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                Lapor BKD
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Tombol Edit Dosen -->
                                <button onclick="editDosen({{ $dosen->id }}, '{{ addslashes($dosen->nidn) }}', '{{ addslashes($dosen->nip) }}', '{{ addslashes($dosen->nama_lengkap) }}', '{{ addslashes($dosen->gelar_depan) }}', '{{ addslashes($dosen->gelar_belakang) }}', '{{ $dosen->prodi_id }}', '{{ $dosen->jenis_kelamin }}', '{{ addslashes($dosen->email) }}', '{{ addslashes($dosen->no_hp) }}')"
                                    class="p-1.5 rounded-lg text-slate-600 hover:text-[#154e85] hover:bg-blue-50 transition" title="Edit Data Dosen">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Tombol Hapus Dosen -->
                                <form action="{{ route('master.dosen.destroy', $dosen->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dosen {{ $dosen->nama_gelar }}? Akun login terkait juga akan dinonaktifkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Data Dosen">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            Belum ada data dosen terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-100">
            {{ $dosens->links() }}
        </div>
    </div>
</div>

<!-- MODAL TAMBAH DOSEN BARU -->
<div id="modalTambahDosen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-amber-300"></i>
                <h3 class="font-bold text-sm">Tambah Dosen Baru</h3>
            </div>
            <button onclick="closeModal('modalTambahDosen')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.dosen.store') }}" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIDN (Wajib 10 Digit) *</label>
                    <input type="text" name="nidn" required placeholder="Contoh: 0412088801"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIP (Opsional)</label>
                    <input type="text" name="nip" placeholder="Nomor Induk Pegawai"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap (Tanpa Gelar) *</label>
                <input type="text" name="nama_lengkap" required placeholder="Contoh: Hendra Pratama"
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Gelar Depan</label>
                    <input type="text" name="gelar_depan" placeholder="Contoh: Dr. / Prof. Dr. Ir."
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Gelar Belakang</label>
                    <input type="text" name="gelar_belakang" placeholder="Contoh: M.Kom. / S.Kom., M.T."
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Program Studi *</label>
                    <select name="prodi_id" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="">-- Pilih Prodi --</option>
                        @foreach($prodis as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->nama_prodi }} ({{ $pr->jenjang }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Email Kampus (Akun Login) *</label>
                    <input type="email" name="email" required placeholder="dosen@kampus.ac.id"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" placeholder="0812xxxxxxxx"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-[11px] text-blue-900">
                <i class="fa-solid fa-circle-info mr-1 text-blue-600"></i> Akun login web otomatis dibuat dengan kata sandi default: <strong>password</strong>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahDosen')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-check"></i> Simpan Dosen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT DOSEN -->
<div id="modalEditDosen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-4 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-amber-300"></i>
                <h3 class="font-bold text-sm">Edit Data Dosen</h3>
            </div>
            <button onclick="closeModal('modalEditDosen')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditDosen" method="POST" class="p-5 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIDN *</label>
                    <input type="text" name="nidn" id="editDosenNidn" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIP (Opsional)</label>
                    <input type="text" name="nip" id="editDosenNip"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap (Tanpa Gelar) *</label>
                <input type="text" name="nama_lengkap" id="editDosenNama" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Gelar Depan</label>
                    <input type="text" name="gelar_depan" id="editDosenGelarDepan"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Gelar Belakang</label>
                    <input type="text" name="gelar_belakang" id="editDosenGelarBelakang"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Program Studi *</label>
                    <select name="prodi_id" id="editDosenProdi" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        @foreach($prodis as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->nama_prodi }} ({{ $pr->jenjang }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" id="editDosenJk" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Email Kampus *</label>
                    <input type="email" name="email" id="editDosenEmail" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor HP</label>
                    <input type="text" name="no_hp" id="editDosenHp"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditDosen')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPOR DOSEN VIA CSV -->
<div id="modalImporDosen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
        <div class="p-4 bg-emerald-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-file-import text-emerald-300"></i>
                <h3 class="font-bold text-sm">Impor Data Dosen Sekaligus (CSV)</h3>
            </div>
            <button onclick="closeModal('modalImporDosen')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('master.dosen.import') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4 text-xs">
            @csrf
            
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-950 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5 text-xs text-emerald-900">
                    <i class="fa-solid fa-circle-question text-emerald-600"></i> Petunjuk Format Berkas Impor:
                </div>
                <p class="text-[11px] leading-relaxed text-emerald-800">
                    Gunakan file berformat <strong>.CSV</strong> dengan pemisah koma (,) atau titik-koma (;). Anda dapat mengunduh contoh berkas yang sudah disesuaikan dengan mengeklik tombol di bawah.
                </p>
                <div class="pt-1">
                    <a href="{{ route('master.dosen.template') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-white px-2.5 py-1 rounded border border-emerald-300 hover:bg-emerald-100 transition">
                        <i class="fa-solid fa-download"></i> Unduh Contoh Berkas Template CSV
                    </a>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Berkas CSV Data Dosen *</label>
                <input type="file" name="file_dosen" accept=".csv,text/csv,text/plain" required
                    class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <span class="text-[10px] text-slate-400 mt-1 block">Maksimal ukuran berkas: 5 MB</span>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 space-y-1">
                <div class="font-bold text-slate-800">Urutan Kolom Berkas CSV:</div>
                <div class="font-mono text-[10px] text-slate-500">nidn, nip, nama_lengkap, gelar_depan, gelar_belakang, kode_prodi, jenis_kelamin, email, no_hp</div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalImporDosen')" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
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

    function editDosen(id, nidn, nip, nama, gelarDepan, gelarBelakang, prodiId, jk, email, hp) {
        document.getElementById('formEditDosen').action = '/master/dosen/' + id;
        document.getElementById('editDosenNidn').value = nidn;
        document.getElementById('editDosenNip').value = nip;
        document.getElementById('editDosenNama').value = nama;
        document.getElementById('editDosenGelarDepan').value = gelarDepan;
        document.getElementById('editDosenGelarBelakang').value = gelarBelakang;
        document.getElementById('editDosenProdi').value = prodiId;
        document.getElementById('editDosenJk').value = jk;
        document.getElementById('editDosenEmail').value = email;
        document.getElementById('editDosenHp').value = hp;
        openModal('modalEditDosen');
    }
</script>
@endsection
