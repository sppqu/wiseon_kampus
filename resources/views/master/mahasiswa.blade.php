@extends('layouts.app')

@section('title', 'Data Master - Mahasiswa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Mahasiswa</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Daftar Mahasiswa</h1>
            <p class="text-xs text-slate-500">Data mahasiswa aktif peserta kelas perkuliahan dan presensi</p>
        </div>
        <div class="flex items-center gap-2">
            <button class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-excel text-emerald-600"></i> Impor Excel
            </button>
            <button class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-user-plus"></i> Tambah Mahasiswa
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">NIM</th>
                        <th class="py-3.5 px-4">Nama Mahasiswa</th>
                        <th class="py-3.5 px-3">Program Studi</th>
                        <th class="py-3.5 px-3 text-center">Angkatan</th>
                        <th class="py-3.5 px-3 text-center">Gender</th>
                        <th class="py-3.5 px-3">Email Kampus</th>
                        <th class="py-3.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($mahasiswas as $mhs)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-[#154e85]">
                            {{ $mhs->nim }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $mhs->nama_lengkap }}
                        </td>
                        <td class="py-3 px-3">
                            {{ $mhs->prodi ? $mhs->prodi->nama_prodi : 'Teknik Informatika' }}
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700">
                            {{ $mhs->angkatan }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $mhs->jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                                {{ $mhs->jenis_kelamin === 'L' ? 'L' : 'P' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-slate-500 font-mono text-[11px]">
                            {{ $mhs->email }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                {{ $mhs->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-slate-100">
            {{ $mahasiswas->links() }}
        </div>
    </div>
</div>
@endsection
