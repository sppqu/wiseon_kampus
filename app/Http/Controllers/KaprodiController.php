<?php

namespace App\Http\Controllers;

use App\Models\KelasKuliah;
use App\Models\PertemuanKuliah;
use App\Models\PengesahanBkd;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KaprodiController extends Controller
{
    public function dashboard()
    {
        $taAktif = TahunAkademik::where('is_active', true)->first();
        $prodi = ProgramStudi::first();

        // Ambil semua kelas di tahun akademik aktif
        $kelasList = KelasKuliah::with(['mataKuliah', 'kelasDosens.dosen', 'pertemuans', 'pengesahanBkds'])
            ->where('tahun_akademik_id', $taAktif ? $taAktif->id : 0)
            ->get();

        $totalKelas = $kelasList->count();
        $kelasMemenuhiBkd = $kelasList->filter(fn($k) => $k->pertemuans->where('status', 'selesai')->count() >= 16)->count();
        $kelasMenujuBkd = $totalKelas - $kelasMemenuhiBkd;

        // Pertemuan yang butuh verifikasi BAP
        $pertemuanPending = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'dosen'])
            ->where('approval_kaprodi', 'pending')
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get();

        return view('kaprodi.dashboard', compact(
            'taAktif', 'prodi', 'kelasList', 'totalKelas', 'kelasMemenuhiBkd', 'kelasMenujuBkd', 'pertemuanPending'
        ));
    }

    public function approvePertemuan(Request $request, PertemuanKuliah $pertemuan)
    {
        $action = $request->input('action', 'approved'); // approved / rejected
        $pertemuan->update([
            'approval_kaprodi' => $action,
            'catatan_kaprodi' => $request->input('catatan', 'Disetujui oleh Kaprodi.'),
        ]);

        return back()->with('success', 'Status BAP Pertemuan ke-' . $pertemuan->pertemuan_ke . ' berhasil diperbarui.');
    }

    public function sahkanBkd(Request $request, KelasKuliah $kelas)
    {
        $user = Auth::user();
        $dosenId = $request->input('dosen_id');

        $totalSelesai = $kelas->pertemuans()->where('status', 'selesai')->count();
        $persen = round(($totalSelesai / 16) * 100, 1);
        $kelayakan = ($totalSelesai >= 16) ? 'memenuhi' : 'tidak_memenuhi';

        $qrCode = 'VERIF-BKD-' . strtoupper(Str::random(12));

        PengesahanBkd::updateOrCreate(
            [
                'kelas_kuliah_id' => $kelas->id,
                'dosen_id' => $dosenId,
                'tahun_akademik_id' => $kelas->tahun_akademik_id,
            ],
            [
                'total_pertemuan' => $totalSelesai,
                'persen_kehadiran' => $persen,
                'status_kelayakan_bkd' => $kelayakan,
                'status_pengesahan' => 'disahkan',
                'disahkan_oleh' => $user->name,
                'nidn_pengesah' => '0015087501',
                'tanggal_pengesahan' => now()->format('Y-m-d'),
                'qr_verifikasi' => $qrCode,
                'catatan' => $request->input('catatan', 'Disahkan dan diverifikasi untuk kelengkapan pelaporan BKD di SISTER.'),
            ]
        );

        return back()->with('success', 'Dokumen Portofolio BKD Kelas ' . $kelas->nama_kelas . ' resmi disahkan Kaprodi!');
    }
}
