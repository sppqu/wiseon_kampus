<?php

namespace App\Http\Controllers;

use App\Models\KelasKuliah;
use App\Models\PengesahanBkd;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BkdPdfController extends Controller
{
    public function cetakBkd(KelasKuliah $kelas, Request $request)
    {
        $dosenId = $request->input('dosen_id');

        $kelas->load([
            'mataKuliah.prodi',
            'tahunAkademik',
            'kelasDosens.dosen',
            'pertemuans.ruangan',
            'pertemuans.presensiMahasiswas',
            'pesertaKelas.mahasiswa',
            'pengesahanBkds.dosen'
        ]);

        $dosen = $dosenId
            ? $kelas->dosens->firstWhere('id', $dosenId)
            : $kelas->dosens->first();

        $pengesahan = $kelas->pengesahanBkds->firstWhere('dosen_id', $dosen ? $dosen->id : null);

        // Jika user minta format download PDF langsung
        if ($request->has('download')) {
            $pdf = Pdf::loadView('pdf.bkd-portofolio', compact('kelas', 'dosen', 'pengesahan'))
                ->setPaper('a4', 'portrait')
                ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

            $fileName = 'BKD_' . str_replace(' ', '_', $kelas->mataKuliah->nama_mk) . '_' . ($dosen ? str_replace(' ', '_', $dosen->nama_lengkap) : 'Dosen') . '.pdf';
            return $pdf->download($fileName);
        }

        // Tampilan cetak responsif (ready for browser print / save as PDF)
        return view('pdf.bkd-portofolio', compact('kelas', 'dosen', 'pengesahan'));
    }

    public function verifikasiQr(string $token)
    {
        $pengesahan = PengesahanBkd::with(['kelasKuliah.mataKuliah.prodi', 'dosen', 'tahunAkademik'])
            ->where('qr_verifikasi', $token)
            ->first();

        return view('pdf.verifikasi-qr', compact('pengesahan', 'token'));
    }
}
