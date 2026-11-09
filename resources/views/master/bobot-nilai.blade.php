@extends('layouts.app')

@section('title', 'Data Master - Bobot Nilai')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-xs text-slate-500 flex items-center gap-1.5">
                <span>Data Master</span>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Bobot Nilai</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1">Skala & Bobot Nilai Perkuliahan</h1>
            <p class="text-xs text-slate-500">Konversi nilai angka ke huruf mutu, bobot indeks prestasi (IP), dan batas kelulusan</p>
        </div>
        <button class="px-4 py-2.5 rounded-xl bg-[#154e85] hover:bg-[#123e6b] text-white font-bold text-xs transition flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Skala
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 text-center">Nilai Huruf</th>
                        <th class="py-3.5 px-4 text-center">Bobot Mutu</th>
                        <th class="py-3.5 px-4 text-center">Rentang Nilai Angka</th>
                        <th class="py-3.5 px-4">Predikat / Kategori</th>
                        <th class="py-3.5 px-4 text-center">Status Kelulusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($bobotList as $b)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 text-center">
                            <span class="inline-block w-8 h-8 rounded-xl font-black text-sm flex items-center justify-center mx-auto {{ $b['status'] === 'Lulus' ? 'bg-blue-50 text-[#154e85] border border-blue-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $b['huruf'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-sm text-slate-800 font-mono">
                            {{ number_format($b['bobot'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-slate-600">
                            {{ number_format($b['min'], 2) }} - {{ number_format($b['max'], 2) }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            {{ $b['predikat'] }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($b['status'] === 'Lulus')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-check mr-1"></i> LULUS
                                </span>
                            @else
                                <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-xmark mr-1"></i> TIDAK LULUS
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
