<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\PertemuanKuliah;
use App\Models\FeederConfig;
use App\Models\FeederLog;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\KelasDosen;
use App\Models\PengesahanBkd;
use App\Models\JadwalKerjaDosen;
use App\Models\PresensiHarianDosen;
use App\Models\PerguruanTinggi;
use App\Services\FeederClientService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalDosen = Dosen::count();
        $totalMhs = Mahasiswa::count();
        $totalKelas = KelasKuliah::count();
        $totalPertemuan = PertemuanKuliah::where('status', 'selesai')->count();

        $feederConfig = FeederConfig::first();
        $feederLogs = FeederLog::orderBy('created_at', 'desc')->take(6)->get();

        // Pemantauan Kelas Hari Ini (Berlangsung & Terjadwal)
        $kelasHariIni = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'dosen', 'ruangan', 'presensiMahasiswas'])
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->orderByRaw("FIELD(status, 'berlangsung', 'terjadwal', 'selesai')")
            ->orderBy('jam_mulai_jadwal', 'asc')
            ->get();

        // Jika hari ini belum ada jadwal terjadwal khusus, ambil sample pertemuan berlangsung/aktif untuk monitoring live
        if ($kelasHariIni->isEmpty()) {
            $kelasHariIni = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'dosen', 'ruangan', 'presensiMahasiswas'])
                ->whereIn('status', ['berlangsung', 'terjadwal'])
                ->take(5)
                ->get();
        }

        // Monitoring Kesiapan BKD Serdos
        $allKelas = KelasKuliah::withCount(['pertemuanKuliahs' => function ($q) {
            $q->where('status', 'selesai');
        }])->get();

        $kelasBkdMemenuhi = $allKelas->where('pertemuan_kuliahs_count', '>=', 16)->count();
        $kelasBkdBelum = $allKelas->where('pertemuan_kuliahs_count', '<', 16)->count();

        // Statistik Feeder
        $feederSynced = PertemuanKuliah::where('status_sync_feeder', 'synced')->count();
        $feederPending = PertemuanKuliah::where('status_sync_feeder', 'pending')->count();
        $feederError = PertemuanKuliah::where('status_sync_feeder', 'error')->count();

        $pertemuanTerbaru = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'dosen'])
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalDosen', 'totalMhs', 'totalKelas', 'totalPertemuan', 'feederConfig', 'feederLogs', 'pertemuanTerbaru',
            'kelasHariIni', 'kelasBkdMemenuhi', 'kelasBkdBelum', 'feederSynced', 'feederPending', 'feederError'
        ));
    }

    public function laporanKehadiran(Request $request)
    {
        $taAktif = TahunAkademik::where('is_active', true)->first();
        $semuaTa = TahunAkademik::orderBy('kode', 'desc')->get();
        $semuaProdi = ProgramStudi::orderBy('nama_prodi')->get();

        $selectedTa = $request->input('tahun_akademik_id', $taAktif ? $taAktif->id : null);
        $selectedProdi = $request->input('program_studi_id');
        $statusBkd = $request->input('status_bkd', 'semua');
        $search = $request->input('search');

        $query = KelasKuliah::with([
            'mataKuliah.programStudi',
            'tahunAkademik',
            'dosens',
            'pertemuanKuliahs',
            'pengesahanBkd'
        ]);

        if ($selectedTa) {
            $query->where('tahun_akademik_id', $selectedTa);
        }

        if ($selectedProdi) {
            $query->whereHas('mataKuliah', function ($q) use ($selectedProdi) {
                $q->where('program_studi_id', $selectedProdi);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhereHas('mataKuliah', function ($mq) use ($search) {
                      $mq->where('nama_mk', 'like', "%{$search}%")
                         ->orWhere('kode_mk', 'like', "%{$search}%");
                  })
                  ->orWhereHas('dosens', function ($dq) use ($search) {
                      $dq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nidn', 'like', "%{$search}%");
                  });
            });
        }

        $allResults = $query->get();

        // Olah data agregat kelayakan BKD & sync Feeder
        $rekapData = $allResults->map(function ($kelas) {
            $selesai = $kelas->pertemuanKuliahs->where('status', 'selesai')->count();
            $syncedFeeder = $kelas->pertemuanKuliahs->where('status', 'selesai')->where('status_sync_feeder', 'synced')->count();
            $pendingFeeder = $kelas->pertemuanKuliahs->where('status', 'selesai')->where('status_sync_feeder', 'pending')->count();
            $persen = round(($selesai / 16) * 100);
            $memenuhiBkd = $selesai >= 16;

            return (object)[
                'kelas' => $kelas,
                'total_selesai' => $selesai,
                'target_sesi' => 16,
                'persentase' => $persen,
                'is_memenuhi_bkd' => $memenuhiBkd,
                'synced_feeder' => $syncedFeeder,
                'pending_feeder' => $pendingFeeder,
                'pengesahan' => $kelas->pengesahanBkd,
            ];
        });

        // Filter status BKD jika dipilih
        if ($statusBkd === 'memenuhi') {
            $rekapData = $rekapData->where('is_memenuhi_bkd', true);
        } elseif ($statusBkd === 'belum') {
            $rekapData = $rekapData->where('is_memenuhi_bkd', false);
        }

        // Summary Metric
        $summary = [
            'total_kelas' => $rekapData->count(),
            'total_memenuhi' => $rekapData->where('is_memenuhi_bkd', true)->count(),
            'total_belum' => $rekapData->where('is_memenuhi_bkd', false)->count(),
            'total_pertemuan_selesai' => $rekapData->sum('total_selesai'),
            'total_synced_feeder' => $rekapData->sum('synced_feeder'),
        ];

        return view('admin.laporan-kehadiran', compact(
            'rekapData', 'semuaTa', 'semuaProdi', 'selectedTa', 'selectedProdi', 'statusBkd', 'search', 'summary'
        ));
    }

    public function exportLaporanCsv(Request $request)
    {
        $selectedTa = $request->input('tahun_akademik_id');
        $selectedProdi = $request->input('program_studi_id');
        $statusBkd = $request->input('status_bkd', 'semua');
        $search = $request->input('search');

        $query = KelasKuliah::with([
            'mataKuliah.programStudi',
            'tahunAkademik',
            'dosens',
            'pertemuanKuliahs',
            'pengesahanBkd'
        ]);

        if ($selectedTa) {
            $query->where('tahun_akademik_id', $selectedTa);
        }
        if ($selectedProdi) {
            $query->whereHas('mataKuliah', function ($q) use ($selectedProdi) {
                $q->where('program_studi_id', $selectedProdi);
            });
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhereHas('mataKuliah', function ($mq) use ($search) {
                      $mq->where('nama_mk', 'like', "%{$search}%");
                  })
                  ->orWhereHas('dosens', function ($dq) use ($search) {
                      $dq->where('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rekapitulasi_kehadiran_dosen_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($records, $statusBkd) {
            $file = fopen('php://output', 'w');
            // Menulis UTF-8 BOM agar rapi di Microsoft Excel
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No',
                'NIDN',
                'Nama Dosen Pengampu',
                'Program Studi',
                'Kode MK',
                'Nama Mata Kuliah',
                'Kelas',
                'SKS',
                'Tahun Akademik',
                'Sesi Selesai',
                'Target Sesi',
                'Persentase Kehadiran (%)',
                'Kelayakan BKD Serdos',
                'Sesi Synced Feeder',
                'Status Pengesahan Kaprodi',
                'Tanggal Pengesahan'
            ]);

            $no = 1;
            foreach ($records as $kelas) {
                $selesai = $kelas->pertemuanKuliahs->where('status', 'selesai')->count();
                $isMemenuhi = $selesai >= 16;

                if ($statusBkd === 'memenuhi' && !$isMemenuhi) continue;
                if ($statusBkd === 'belum' && $isMemenuhi) continue;

                $dosenList = $kelas->dosens->pluck('nama_lengkap')->join(' & ');
                $nidnList = $kelas->dosens->pluck('nidn')->join(' / ');
                $synced = $kelas->pertemuanKuliahs->where('status', 'selesai')->where('status_sync_feeder', 'synced')->count();
                $statusPengesahan = $kelas->pengesahanBkd && $kelas->pengesahanBkd->status_pengesahan === 'sah'
                    ? 'Sudah Disahkan'
                    : 'Belum Disahkan';
                $tglPengesahan = $kelas->pengesahanBkd && $kelas->pengesahanBkd->tanggal_pengesahan
                    ? $kelas->pengesahanBkd->tanggal_pengesahan->format('d/m/Y H:i')
                    : '-';

                fputcsv($file, [
                    $no++,
                    $nidnList ?: '-',
                    $dosenList ?: 'Belum Ditentukan',
                    $kelas->mataKuliah->programStudi->nama_prodi ?? '-',
                    $kelas->mataKuliah->kode_mk ?? '-',
                    $kelas->mataKuliah->nama_mk ?? '-',
                    $kelas->nama_kelas,
                    $kelas->mataKuliah->sks ?? 0,
                    $kelas->tahunAkademik->nama_semester ?? '-',
                    $selesai,
                    16,
                    round(($selesai / 16) * 100) . '%',
                    $isMemenuhi ? 'MEMENUHI SYARAT' : 'BELUM MEMENUHI',
                    "{$synced} / {$selesai} Sesi",
                    $statusPengesahan,
                    $tglPengesahan
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function pushSinglePertemuan(PertemuanKuliah $pertemuan)
    {
        $service = new FeederClientService();
        $res = $service->pushRealisasiPerkuliahan($pertemuan);

        if ($res['success']) {
            return back()->with('success', "Pertemuan ke-{$pertemuan->pertemuan_ke} ({$pertemuan->kelasKuliah->nama_kelas}) berhasil disinkronkan ke Feeder PDDikti.");
        }

        return back()->with('warning', "Sinkronisasi dicatat: " . $res['message']);
    }

    public function pushKelasBatch(KelasKuliah $kelas)
    {
        $service = new FeederClientService();
        $pertemuans = $kelas->pertemuanKuliahs()->where('status', 'selesai')->get();

        $sukses = 0;
        foreach ($pertemuans as $ptm) {
            $res = $service->pushRealisasiPerkuliahan($ptm);
            if ($res['success']) {
                $sukses++;
            }
        }

        return back()->with('success', "Proses sinkronisasi kelas {$kelas->nama_kelas} selesai. Sebanyak {$sukses} pertemuan berhasil di-push ke Feeder PDDikti.");
    }

    public function feederManager()
    {
        $config = FeederConfig::first();
        $logs = FeederLog::orderBy('created_at', 'desc')->paginate(15);
        $pertemuanPending = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'dosen'])
            ->where('status', 'selesai')
            ->where('status_sync_feeder', 'pending')
            ->get();

        return view('admin.feeder', compact('config', 'logs', 'pertemuanPending'));
    }

    public function updateFeederConfig(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'username' => 'required|string',
            'password' => 'required|string',
            'kode_pt' => 'nullable|string',
            'nama_pt' => 'nullable|string',
            'mode' => 'required|in:sandbox,live',
        ]);

        $config = FeederConfig::first();
        $config->update($request->only('url', 'username', 'password', 'kode_pt', 'nama_pt', 'mode'));

        return back()->with('success', 'Konfigurasi Web Service Feeder PDDikti berhasil diperbarui.');
    }

    public function testFeederConnection()
    {
        $service = new FeederClientService();
        $res = $service->testConnection();

        if ($res['connected']) {
            return back()->with('success', $res['message'] . ' (PT: ' . $res['pt'] . ')');
        } else {
            return back()->with('warning', 'Peringatan: ' . $res['message']);
        }
    }

    public function pushFeederBatch()
    {
        $service = new FeederClientService();
        $pending = PertemuanKuliah::where('status', 'selesai')
            ->where('status_sync_feeder', 'pending')
            ->take(20)
            ->get();

        $sukses = 0;
        foreach ($pending as $ptm) {
            $res = $service->pushRealisasiPerkuliahan($ptm);
            if ($res['success']) {
                $sukses++;
            }
        }

        return back()->with('success', "Proses sinkronisasi batch selesai. Berhasil menyinkronkan $sukses pertemuan ke Feeder PDDikti.");
    }

    public function pengaturanPresensi()
    {
        $settingsPath = storage_path('app/settings_presensi.json');
        $defaultSettings = [
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'radius_meter' => 200,
            'radius_kantor_meter' => 200,
            'jam_masuk_kantor' => '08:00',
            'jam_pulang_kantor' => '16:00',
            'toleransi_menit_kantor' => 15,
            'minimal_hadir_persen' => 75,
            'target_pertemuan_bkd' => 16,
            'toleransi_menit' => 15,
            'wajib_foto_bukti' => 1,
            'mode_geofencing' => 'strict',
            'kampus_lat' => -6.2088,
            'kampus_lon' => 106.8456,
        ];

        $settings = file_exists($settingsPath)
            ? array_merge($defaultSettings, json_decode(file_get_contents($settingsPath), true) ?: [])
            : $defaultSettings;

        $totalRuangan = \App\Models\Ruangan::count();

        return view('admin.pengaturan-presensi', compact('settings', 'totalRuangan'));
    }

    public function updatePengaturanPresensi(Request $request)
    {
        $request->validate([
            'timezone' => 'nullable|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura,UTC',
            'radius_meter' => 'required|numeric|min:10|max:2000',
            'radius_kantor_meter' => 'required|numeric|min:10|max:2000',
            'jam_masuk_kantor' => 'required|string',
            'jam_pulang_kantor' => 'required|string',
            'toleransi_menit_kantor' => 'required|numeric|min:0|max:120',
            'minimal_hadir_persen' => 'required|numeric|min:1|max:100',
            'target_pertemuan_bkd' => 'required|numeric|min:1|max:32',
            'toleransi_menit' => 'required|numeric|min:0|max:120',
            'mode_geofencing' => 'required|in:strict,warning',
            'kampus_lat' => 'required|numeric',
            'kampus_lon' => 'required|numeric',
        ]);

        $timezone = $request->input('timezone', config('app.timezone', 'Asia/Jakarta'));

        $settings = [
            'timezone' => $timezone,
            'radius_meter' => (int)$request->radius_meter,
            'radius_kantor_meter' => (int)$request->radius_kantor_meter,
            'jam_masuk_kantor' => $request->jam_masuk_kantor,
            'jam_pulang_kantor' => $request->jam_pulang_kantor,
            'toleransi_menit_kantor' => (int)$request->toleransi_menit_kantor,
            'minimal_hadir_persen' => (int)$request->minimal_hadir_persen,
            'target_pertemuan_bkd' => (int)$request->target_pertemuan_bkd,
            'toleransi_menit' => (int)$request->toleransi_menit,
            'wajib_foto_bukti' => $request->has('wajib_foto_bukti') ? 1 : 0,
            'mode_geofencing' => $request->mode_geofencing,
            'kampus_lat' => (float)$request->kampus_lat,
            'kampus_lon' => (float)$request->kampus_lon,
        ];

        file_put_contents(storage_path('app/settings_presensi.json'), json_encode($settings, JSON_PRETTY_PRINT));

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        return back()->with('success', 'Pengaturan kebijakan presensi, zona waktu (' . $timezone . '), dan jam kerja kantor berhasil disimpan.');
    }

    public function presensiHarianDosen(Request $request)
    {
        $tanggalPilih = $request->input('tanggal', now()->format('Y-m-d'));
        $prodiPilih = $request->input('program_studi_id');
        $statusPilih = $request->input('status');
        $search = $request->input('search');

        $semuaProdi = ProgramStudi::orderBy('nama_prodi')->get();

        $query = Dosen::with(['prodi', 'presensiHarians' => function ($q) use ($tanggalPilih) {
            $q->whereDate('tanggal', $tanggalPilih);
        }]);

        if ($prodiPilih) {
            $query->where('prodi_id', $prodiPilih);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%");
            });
        }

        $allDosen = $query->orderBy('nama_lengkap')->get();

        // Olah status kehadiran
        $dataPresensi = $allDosen->map(function ($dsn) use ($tanggalPilih) {
            $presensi = $dsn->presensiHarians->first();

            return (object)[
                'dosen' => $dsn,
                'presensi' => $presensi,
                'status_kehadiran' => $presensi ? $presensi->status : 'belum_absen',
                'jam_masuk' => $presensi ? $presensi->jam_masuk : null,
                'jam_pulang' => $presensi ? $presensi->jam_pulang : null,
                'durasi' => $presensi ? $presensi->durasi_kerja : '-',
                'validasi_lokasi' => $presensi ? $presensi->validasi_lokasi : null,
                'jarak_meter' => $presensi ? $presensi->jarak_masuk_meter : null,
                'foto_masuk' => $presensi ? $presensi->foto_masuk : null,
                'foto_pulang' => $presensi ? $presensi->foto_pulang : null,
            ];
        });

        if ($statusPilih && $statusPilih !== 'semua') {
            $dataPresensi = $dataPresensi->where('status_kehadiran', $statusPilih);
        }

        $summary = [
            'total_dosen' => $allDosen->count(),
            'hadir_tepat_waktu' => $dataPresensi->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $dataPresensi->where('status_kehadiran', 'terlambat')->count(),
            'belum_absen' => $dataPresensi->where('status_kehadiran', 'belum_absen')->count(),
            'izin_sakit' => $dataPresensi->whereIn('status_kehadiran', ['izin', 'sakit', 'dinas_luar'])->count(),
        ];

        return view('admin.presensi-harian-dosen', compact(
            'dataPresensi', 'semuaProdi', 'tanggalPilih', 'prodiPilih', 'statusPilih', 'search', 'summary'
        ));
    }

    public function exportPresensiHarianCsv(Request $request)
    {
        $tanggalPilih = $request->input('tanggal', now()->format('Y-m-d'));
        $prodiPilih = $request->input('program_studi_id');
        $statusPilih = $request->input('status');
        $search = $request->input('search');

        $query = Dosen::with(['prodi', 'presensiHarians' => function ($q) use ($tanggalPilih) {
            $q->whereDate('tanggal', $tanggalPilih);
        }]);

        if ($prodiPilih) {
            $query->where('prodi_id', $prodiPilih);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%");
            });
        }

        $allDosen = $query->orderBy('nama_lengkap')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="presensi_harian_dosen_' . $tanggalPilih . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($allDosen, $statusPilih, $tanggalPilih) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No',
                'NIDN',
                'Nama Dosen',
                'Program Studi',
                'Tanggal',
                'Jam Masuk (In)',
                'Jam Pulang (Out)',
                'Durasi Kerja',
                'Status Kehadiran',
                'Validasi Geofencing GPS',
                'Jarak ke Kampus (Meter)'
            ]);

            $no = 1;
            foreach ($allDosen as $dsn) {
                $presensi = $dsn->presensiHarians->first();
                $status = $presensi ? $presensi->status : 'belum_absen';

                if ($statusPilih && $statusPilih !== 'semua' && $status !== $statusPilih) {
                    continue;
                }

                $statusLabel = match($status) {
                    'hadir' => 'HADIR TEPAT WAKTU',
                    'terlambat' => 'TERLAMBAT',
                    'izin' => 'IZIN',
                    'sakit' => 'SAKIT',
                    'dinas_luar' => 'DINAS LUAR',
                    default => 'BELUM ABSEN'
                };

                fputcsv($file, [
                    $no++,
                    $dsn->nidn ?: '-',
                    $dsn->nama_lengkap,
                    $dsn->prodi->nama_prodi ?? '-',
                    $tanggalPilih,
                    $presensi && $presensi->jam_masuk ? $presensi->jam_masuk . ' WIB' : '-',
                    $presensi && $presensi->jam_pulang ? $presensi->jam_pulang . ' WIB' : '-',
                    $presensi ? ($presensi->durasi_kerja ?: '-') : '-',
                    $statusLabel,
                    $presensi ? strtoupper($presensi->validasi_lokasi) : '-',
                    $presensi ? ($presensi->jarak_masuk_meter ?? 0) . ' m' : '-'
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function jadwalKerjaDosen(Request $request)
    {
        $prodiPilih = $request->input('program_studi_id');
        $search = $request->input('search');

        $semuaProdi = ProgramStudi::orderBy('nama_prodi')->get();

        $query = Dosen::with(['prodi', 'jadwalKerja']);

        if ($prodiPilih) {
            $query->where('prodi_id', $prodiPilih);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%");
            });
        }

        $allDosen = $query->orderBy('nama_lengkap')->get();

        // Default setting kampus
        $settingsPath = storage_path('app/settings_presensi.json');
        $defaultSettings = [
            'jam_masuk_kantor' => '08:00',
            'jam_pulang_kantor' => '16:00',
            'toleransi_menit_kantor' => 15,
            'hari_kerja' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
        ];

        if (file_exists($settingsPath)) {
            $defaultSettings = array_merge($defaultSettings, json_decode(file_get_contents($settingsPath), true) ?: []);
        }

        $summary = [
            'total_dosen' => $allDosen->count(),
            'jadwal_khusus' => $allDosen->whereNotNull('jadwalKerja')->where('jadwalKerja.is_custom', true)->count(),
            'jadwal_standar' => $allDosen->where(fn($d) => !$d->jadwalKerja || !$d->jadwalKerja->is_custom)->count(),
        ];

        return view('admin.jadwal-kerja-dosen', compact('allDosen', 'semuaProdi', 'prodiPilih', 'search', 'defaultSettings', 'summary'));
    }

    public function updateJadwalKerjaDosen(Request $request, Dosen $dosen)
    {
        $request->validate([
            'jam_masuk' => 'required|string',
            'jam_pulang' => 'required|string',
            'toleransi_menit' => 'required|numeric|min:0|max:120',
            'hari_kerja' => 'required|array|min:1',
            'hari_kerja.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'keterangan' => 'nullable|string|max:100',
        ]);

        JadwalKerjaDosen::updateOrCreate(
            ['dosen_id' => $dosen->id],
            [
                'jam_masuk' => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
                'toleransi_menit' => (int)$request->toleransi_menit,
                'hari_kerja' => $request->hari_kerja,
                'is_custom' => true,
                'keterangan' => $request->keterangan,
            ]
        );

        return back()->with('success', "Jadwal kerja dan hari kerja khusus untuk dosen {$dosen->nama_lengkap} berhasil diperbarui.");
    }

    public function resetJadwalKerjaDosen(Dosen $dosen)
    {
        if ($dosen->jadwalKerja) {
            $dosen->jadwalKerja->delete();
        }

        return back()->with('success', "Jadwal kerja dosen {$dosen->nama_lengkap} telah direset ke Standar Kampus.");
    }

    public function terapkanMassalJadwalKerja(Request $request)
    {
        $request->validate([
            'target_prodi_id' => 'nullable|string',
            'jam_masuk' => 'required|string',
            'jam_pulang' => 'required|string',
            'toleransi_menit' => 'required|numeric|min:0|max:120',
            'hari_kerja' => 'required|array|min:1',
            'hari_kerja.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'keterangan' => 'nullable|string|max:100',
        ]);

        $query = Dosen::query();
        if ($request->filled('target_prodi_id') && $request->target_prodi_id !== 'semua') {
            $query->where('prodi_id', $request->target_prodi_id);
        }

        $dosens = $query->get();
        $count = 0;

        foreach ($dosens as $dsn) {
            JadwalKerjaDosen::updateOrCreate(
                ['dosen_id' => $dsn->id],
                [
                    'jam_masuk' => $request->jam_masuk,
                    'jam_pulang' => $request->jam_pulang,
                    'toleransi_menit' => (int)$request->toleransi_menit,
                    'hari_kerja' => $request->hari_kerja,
                    'is_custom' => true,
                    'keterangan' => $request->keterangan ?: 'Penerapan Massal Sistem',
                ]
            );
            $count++;
        }

        return back()->with('success', "Berhasil menerapkan jadwal kerja khusus ke {$count} dosen sekaligus.");
    }

    public function rekapKehadiranDosen(Request $request)
    {
        $bulanPilih = (int)$request->input('bulan', now()->month);
        $tahunPilih = (int)$request->input('tahun', now()->year);
        $prodiPilih = $request->input('program_studi_id');
        $search = $request->input('search');

        $semuaProdi = ProgramStudi::orderBy('nama_prodi')->get();

        // Default setting hari kerja kampus
        $settingsPath = storage_path('app/settings_presensi.json');
        $defaultHariKerja = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        if (file_exists($settingsPath)) {
            $json = json_decode(file_get_contents($settingsPath), true);
            if (!empty($json['hari_kerja'])) {
                $defaultHariKerja = $json['hari_kerja'];
            }
        }

        // Tentukan range tanggal bulan terpilih
        $startOfMonth = \Carbon\Carbon::createFromDate($tahunPilih, $bulanPilih, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now();
        $limitDate = ($tahunPilih == $today->year && $bulanPilih == $today->month) ? $today : $endOfMonth;

        // Query Dosen
        $query = Dosen::with(['prodi', 'jadwalKerja', 'presensiHarians' => function ($q) use ($bulanPilih, $tahunPilih) {
            $q->whereMonth('tanggal', $bulanPilih)->whereYear('tanggal', $tahunPilih);
        }]);

        if ($prodiPilih) {
            $query->where('prodi_id', $prodiPilih);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $allDosen = $query->orderBy('nama_lengkap')->get();

        // Olah data agregat rekap bulanan per dosen
        $rekapDosen = $allDosen->map(function ($dsn) use ($startOfMonth, $limitDate, $defaultHariKerja) {
            $hariKerja = ($dsn->jadwalKerja && $dsn->jadwalKerja->is_custom && !empty($dsn->jadwalKerja->hari_kerja))
                ? $dsn->jadwalKerja->hari_kerja
                : $defaultHariKerja;

            // Hitung hari kerja efektif
            $hariEfektif = 0;
            $cursor = $startOfMonth->copy();
            while ($cursor->lte($limitDate)) {
                $namaHari = $cursor->locale('id')->isoFormat('dddd');
                if (in_array($namaHari, $hariKerja)) {
                    $hariEfektif++;
                }
                $cursor->addDay();
            }
            if ($hariEfektif <= 0) $hariEfektif = 1;

            $hadir = $dsn->presensiHarians->where('status', 'hadir')->count();
            $terlambat = $dsn->presensiHarians->where('status', 'terlambat')->count();
            $izinSakit = $dsn->presensiHarians->whereIn('status', ['izin', 'sakit', 'dinas_luar'])->count();
            $totalMasuk = $hadir + $terlambat;
            $alpa = max(0, $hariEfektif - ($totalMasuk + $izinSakit));
            $persen = round(($totalMasuk / $hariEfektif) * 100);

            // Hitung rata-rata durasi kerja
            $totalMenitKerja = 0;
            $countValidPulang = 0;
            foreach ($dsn->presensiHarians as $pr) {
                if ($pr->jam_masuk && $pr->jam_pulang) {
                    $m = \Carbon\Carbon::parse($pr->jam_masuk);
                    $p = \Carbon\Carbon::parse($pr->jam_pulang);
                    if ($p->gt($m)) {
                        $totalMenitKerja += $m->diffInMinutes($p);
                        $countValidPulang++;
                    }
                }
            }

            $avgDurasi = '-';
            if ($countValidPulang > 0) {
                $avgMnt = round($totalMenitKerja / $countValidPulang);
                $jam = floor($avgMnt / 60);
                $sisaMenit = $avgMnt % 60;
                $avgDurasi = "{$jam}j {$sisaMenit}m";
            }

            return (object)[
                'dosen' => $dsn,
                'hari_efektif' => $hariEfektif,
                'hadir_tepat' => $hadir,
                'terlambat' => $terlambat,
                'izin_sakit' => $izinSakit,
                'alpa' => $alpa,
                'total_masuk' => $totalMasuk,
                'persentase' => min(100, $persen),
                'avg_durasi' => $avgDurasi,
                'logs' => $dsn->presensiHarians->sortByDesc('tanggal'),
            ];
        });

        // Summary KPI Card
        $kpi = [
            'total_dosen' => $rekapDosen->count(),
            'avg_kehadiran' => $rekapDosen->count() > 0 ? round($rekapDosen->avg('persentase')) : 0,
            'total_hadir_tepat' => $rekapDosen->sum('hadir_tepat'),
            'total_terlambat' => $rekapDosen->sum('terlambat'),
            'total_izin_sakit' => $rekapDosen->sum('izin_sakit'),
            'total_alpa' => $rekapDosen->sum('alpa'),
        ];

        return view('admin.rekap-kehadiran-dosen', compact(
            'rekapDosen', 'semuaProdi', 'bulanPilih', 'tahunPilih', 'prodiPilih', 'search', 'kpi'
        ));
    }

    public function exportRekapKehadiranCsv(Request $request)
    {
        $bulanPilih = (int)$request->input('bulan', now()->month);
        $tahunPilih = (int)$request->input('tahun', now()->year);
        $prodiPilih = $request->input('program_studi_id');

        $namaBulan = \Carbon\Carbon::createFromDate($tahunPilih, $bulanPilih, 1)->locale('id')->isoFormat('MMMM');
        $filename = "rekap_kehadiran_dosen_{$namaBulan}_{$tahunPilih}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // Default setting hari kerja kampus
        $settingsPath = storage_path('app/settings_presensi.json');
        $defaultHariKerja = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        if (file_exists($settingsPath)) {
            $json = json_decode(file_get_contents($settingsPath), true);
            if (!empty($json['hari_kerja'])) {
                $defaultHariKerja = $json['hari_kerja'];
            }
        }

        $startOfMonth = \Carbon\Carbon::createFromDate($tahunPilih, $bulanPilih, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now();
        $limitDate = ($tahunPilih == $today->year && $bulanPilih == $today->month) ? $today : $endOfMonth;

        $query = Dosen::with(['prodi', 'jadwalKerja', 'presensiHarians' => function ($q) use ($bulanPilih, $tahunPilih) {
            $q->whereMonth('tanggal', $bulanPilih)->whereYear('tanggal', $tahunPilih);
        }]);

        if ($prodiPilih) {
            $query->where('prodi_id', $prodiPilih);
        }

        $allDosen = $query->orderBy('nama_lengkap')->get();

        $callback = function () use ($allDosen, $startOfMonth, $limitDate, $defaultHariKerja, $namaBulan, $tahunPilih) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Excel

            fputcsv($file, ["LAPORAN REKAPITULASI KEHADIRAN DOSEN & KARYAWAN"]);
            fputcsv($file, ["Periode Bulan", "{$namaBulan} {$tahunPilih}"]);
            fputcsv($file, []);

            fputcsv($file, [
                'No',
                'NIDN / NIP',
                'Nama Lengkap Dosen / Karyawan',
                'Program Studi',
                'Hari Efektif',
                'Hadir Tepat Waktu',
                'Terlambat',
                'Izin / Sakit / Dinas',
                'Tanpa Keterangan (Alpa)',
                'Total Masuk',
                'Persentase Kehadiran (%)',
                'Rata-rata Durasi Kerja'
            ]);

            $no = 1;
            foreach ($allDosen as $dsn) {
                $hariKerja = ($dsn->jadwalKerja && $dsn->jadwalKerja->is_custom && !empty($dsn->jadwalKerja->hari_kerja))
                    ? $dsn->jadwalKerja->hari_kerja
                    : $defaultHariKerja;

                $hariEfektif = 0;
                $cursor = $startOfMonth->copy();
                while ($cursor->lte($limitDate)) {
                    $namaHari = $cursor->locale('id')->isoFormat('dddd');
                    if (in_array($namaHari, $hariKerja)) {
                        $hariEfektif++;
                    }
                    $cursor->addDay();
                }
                if ($hariEfektif <= 0) $hariEfektif = 1;

                $hadir = $dsn->presensiHarians->where('status', 'hadir')->count();
                $terlambat = $dsn->presensiHarians->where('status', 'terlambat')->count();
                $izinSakit = $dsn->presensiHarians->whereIn('status', ['izin', 'sakit', 'dinas_luar'])->count();
                $totalMasuk = $hadir + $terlambat;
                $alpa = max(0, $hariEfektif - ($totalMasuk + $izinSakit));
                $persen = round(($totalMasuk / $hariEfektif) * 100);

                // Hitung durasi rata-rata
                $totalMenit = 0;
                $countPulang = 0;
                foreach ($dsn->presensiHarians as $pr) {
                    if ($pr->jam_masuk && $pr->jam_pulang) {
                        $m = \Carbon\Carbon::parse($pr->jam_masuk);
                        $p = \Carbon\Carbon::parse($pr->jam_pulang);
                        if ($p->gt($m)) {
                            $totalMenit += $m->diffInMinutes($p);
                            $countPulang++;
                        }
                    }
                }
                $avgDur = '-';
                if ($countPulang > 0) {
                    $avgMnt = round($totalMenit / $countPulang);
                    $avgDur = floor($avgMnt / 60) . 'j ' . ($avgMnt % 60) . 'm';
                }

                fputcsv($file, [
                    $no++,
                    $dsn->nidn ?: ($dsn->nip ?: '-'),
                    $dsn->nama_lengkap,
                    $dsn->prodi->nama_prodi ?? '-',
                    $hariEfektif,
                    $hadir,
                    $terlambat,
                    $izinSakit,
                    $alpa,
                    $totalMasuk,
                    min(100, $persen) . '%',
                    $avgDur
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function cetakRekapKehadiran(Request $request)
    {
        $bulanPilih = (int)$request->input('bulan', now()->month);
        $tahunPilih = (int)$request->input('tahun', now()->year);
        $prodiPilih = $request->input('program_studi_id');

        $pt = PerguruanTinggi::first();

        // Default setting hari kerja kampus
        $settingsPath = storage_path('app/settings_presensi.json');
        $defaultHariKerja = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        if (file_exists($settingsPath)) {
            $json = json_decode(file_get_contents($settingsPath), true);
            if (!empty($json['hari_kerja'])) {
                $defaultHariKerja = $json['hari_kerja'];
            }
        }

        $startOfMonth = \Carbon\Carbon::createFromDate($tahunPilih, $bulanPilih, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now();
        $limitDate = ($tahunPilih == $today->year && $bulanPilih == $today->month) ? $today : $endOfMonth;

        $query = Dosen::with(['prodi', 'jadwalKerja', 'presensiHarians' => function ($q) use ($bulanPilih, $tahunPilih) {
            $q->whereMonth('tanggal', $bulanPilih)->whereYear('tanggal', $tahunPilih);
        }]);

        if ($prodiPilih) {
            $query->where('prodi_id', $prodiPilih);
        }

        $allDosen = $query->orderBy('nama_lengkap')->get();

        $rekapDosen = $allDosen->map(function ($dsn) use ($startOfMonth, $limitDate, $defaultHariKerja) {
            $hariKerja = ($dsn->jadwalKerja && $dsn->jadwalKerja->is_custom && !empty($dsn->jadwalKerja->hari_kerja))
                ? $dsn->jadwalKerja->hari_kerja
                : $defaultHariKerja;

            $hariEfektif = 0;
            $cursor = $startOfMonth->copy();
            while ($cursor->lte($limitDate)) {
                $namaHari = $cursor->locale('id')->isoFormat('dddd');
                if (in_array($namaHari, $hariKerja)) {
                    $hariEfektif++;
                }
                $cursor->addDay();
            }
            if ($hariEfektif <= 0) $hariEfektif = 1;

            $hadir = $dsn->presensiHarians->where('status', 'hadir')->count();
            $terlambat = $dsn->presensiHarians->where('status', 'terlambat')->count();
            $izinSakit = $dsn->presensiHarians->whereIn('status', ['izin', 'sakit', 'dinas_luar'])->count();
            $totalMasuk = $hadir + $terlambat;
            $alpa = max(0, $hariEfektif - ($totalMasuk + $izinSakit));
            $persen = round(($totalMasuk / $hariEfektif) * 100);

            return (object)[
                'dosen' => $dsn,
                'hari_efektif' => $hariEfektif,
                'hadir_tepat' => $hadir,
                'terlambat' => $terlambat,
                'izin_sakit' => $izinSakit,
                'alpa' => $alpa,
                'total_masuk' => $totalMasuk,
                'persentase' => min(100, $persen),
            ];
        });

        $namaBulan = \Carbon\Carbon::createFromDate($tahunPilih, $bulanPilih, 1)->locale('id')->isoFormat('MMMM');

        return view('admin.cetak-rekap-kehadiran', compact(
            'rekapDosen', 'pt', 'bulanPilih', 'tahunPilih', 'namaBulan'
        ));
    }
}
