@extends('layouts.app')

@section('title', 'Data Master - Perguruan Tinggi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Perguruan Tinggi</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Profil Institusi Perguruan Tinggi</h1>
            <p class="text-xs text-slate-500">Informasi identitas institusi kampus & sinkronisasi PDDikti</p>
        </div>
        <button onclick="openModal('modalEditPt')" class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-pen-to-square"></i> Edit Profil Institusi Manual
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Logo & Kartu Utama -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm text-center space-y-4">
            <div class="w-24 h-24 mx-auto rounded-3xl bg-[#154e85] text-amber-300 text-4xl font-black flex items-center justify-center shadow-lg shadow-blue-900/20">
                {{ $pt->singkatan ?: 'UTN' }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $pt->nama_pt }}</h2>
                <div class="text-xs text-slate-500 font-mono mt-0.5">Kode PT: <strong>{{ $pt->kode_pt }}</strong></div>
                <div class="mt-2 inline-block bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold px-3 py-0.5 rounded-full">
                    Terakreditasi {{ $pt->akreditasi }}
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-600 space-y-2.5 text-left">
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-user-graduate text-slate-400 w-4 text-center mt-0.5"></i>
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Rektor</div>
                        <div class="font-bold text-slate-800">{{ $pt->nama_rektor }}</div>
                        @if($pt->nip_rektor)
                            <div class="text-[10px] text-slate-500 font-mono">NIP: {{ $pt->nip_rektor }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-phone text-slate-400 w-4 text-center mt-0.5"></i>
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Telepon</div>
                        <div class="font-bold text-slate-800">{{ $pt->telepon }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-envelope text-slate-400 w-4 text-center mt-0.5"></i>
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Email Resmi</div>
                        <div class="font-bold text-slate-800">{{ $pt->email }}</div>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-globe text-slate-400 w-4 text-center mt-0.5"></i>
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Website Resmi</div>
                        <a href="{{ $pt->website }}" target="_blank" class="font-bold text-[#154e85] hover:underline">{{ $pt->website }}</a>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-location-dot text-slate-400 w-4 text-center mt-0.5"></i>
                    <div>
                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Alamat Kampus</div>
                        <div class="text-slate-700 leading-relaxed">{{ $pt->alamat }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Legalitas & Status Feeder -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-id-card-clip text-[#154e85]"></i> Legalitas & Status Integrasi Sistem
                </h3>
                <span class="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold px-2.5 py-0.5 rounded-full">
                    Sistem Aktif
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-slate-400 font-semibold uppercase text-[10px]">SK Pendirian PT</div>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">{{ $pt->sk_pendirian }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Tanggal SK: {{ $pt->tanggal_sk ? $pt->tanggal_sk->format('d F Y') : '12 Agustus 2012' }}</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-slate-400 font-semibold uppercase text-[10px]">Status Akreditasi Institusi</div>
                    <div class="font-bold text-emerald-700 text-sm mt-0.5">{{ $pt->akreditasi }}</div>
                    <div class="text-[11px] text-slate-500 mt-1">Lembaga Akreditasi: BAN-PT</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-slate-400 font-semibold uppercase text-[10px]">Tahun Akademik Berjalan</div>
                    <div class="font-bold text-slate-800 text-sm mt-0.5">2024/2025 Ganjil</div>
                    <div class="text-[11px] text-slate-500 mt-1">Periode Semester Aktif</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-slate-400 font-semibold uppercase text-[10px]">Sinkronisasi Feeder PDDikti</div>
                    <div class="font-bold text-blue-700 text-sm mt-0.5">Live2 Web Service Ready</div>
                    <div class="text-[11px] text-slate-500 mt-1">Terhubung ke Pangkalan Data Dikti</div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5 text-blue-950">
                    <i class="fa-solid fa-circle-info text-blue-600"></i> Mode Mandiri & Integrasi (Standalone & Feeder Sync)
                </div>
                <p class="text-blue-800 leading-relaxed">
                    Data identitas perguruan tinggi ini digunakan sebagai kop resmi pada berkas cetak Portofolio Beban Kerja Dosen (BKD), Berita Acara Perkuliahan (BAP), dan lembar pengesahan tanda tangan digital.
                </p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Terakhir diperbarui: <strong>{{ $pt->updated_at->diffForHumans() }}</strong></span>
                <button onclick="openModal('modalEditPt')" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                    Ubah Data Institusi &rarr;
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT PROFIL PERGURUAN TINGGI MANUAL -->
<div id="modalEditPt" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200">
        <div class="p-5 bg-[#154e85] text-white flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-building-columns text-amber-300"></i>
                <h3 class="font-bold text-base">Edit Profil Perguruan Tinggi Manual</h3>
            </div>
            <button onclick="closeModal('modalEditPt')" class="text-white/70 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('master.perguruan-tinggi.update') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Perguruan Tinggi *</label>
                    <input type="text" name="nama_pt" value="{{ old('nama_pt', $pt->nama_pt) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Singkatan</label>
                    <input type="text" name="singkatan" value="{{ old('singkatan', $pt->singkatan) }}"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none" placeholder="UTN">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Kode PT (PDDikti) *</label>
                    <input type="text" name="kode_pt" value="{{ old('kode_pt', $pt->kode_pt) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Status Akreditasi Institusi *</label>
                    <input type="text" name="akreditasi" value="{{ old('akreditasi', $pt->akreditasi) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">SK Pendirian PT *</label>
                    <input type="text" name="sk_pendirian" value="{{ old('sk_pendirian', $pt->sk_pendirian) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal SK Pendirian</label>
                    <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk', $pt->tanggal_sk ? $pt->tanggal_sk->format('Y-m-d') : '2012-08-12') }}"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama Rektor Lengkap & Gelar *</label>
                    <input type="text" name="nama_rektor" value="{{ old('nama_rektor', $pt->nama_rektor) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">NIP / NIDN Rektor</label>
                    <input type="text" name="nip_rektor" value="{{ old('nip_rektor', $pt->nip_rektor) }}"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Email Resmi *</label>
                    <input type="email" name="email" value="{{ old('email', $pt->email) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor Telepon *</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $pt->telepon) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Website Resmi *</label>
                    <input type="url" name="website" value="{{ old('website', $pt->website) }}" required
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Alamat Kampus Terpadu *</label>
                <textarea name="alamat" rows="2.5" required
                    class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#154e85] focus:outline-none">{{ old('alamat', $pt->alamat) }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditPt')" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-blue-900/20">
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
</script>
@endsection
