<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\KelasDosen;
use App\Models\PertemuanKuliah;
use App\Models\PresensiMahasiswa;
use App\Models\PresensiHarianDosen;
use App\Models\TahunAkademik;
use App\Models\JadwalKuliah;
use App\Services\FeederClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DosenController extends Controller
{
    protected function getSettings(): array
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

        if (file_exists($settingsPath)) {
            return array_merge($defaultSettings, json_decode(file_get_contents($settingsPath), true) ?: []);
        }
        return $defaultSettings;
    }

    protected function getSettingsForDosen(Dosen $dosen): array
    {
        $global = $this->getSettings();
        $jadwalKerja = $dosen->jadwalKerja;

        $hariIni = now()->locale('id')->isoFormat('dddd');

        if ($jadwalKerja && $jadwalKerja->is_custom) {
            $hariKerja = $jadwalKerja->hari_kerja ?: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            return [
                'jam_masuk_kantor' => substr($jadwalKerja->jam_masuk, 0, 5),
                'jam_pulang_kantor' => substr($jadwalKerja->jam_pulang, 0, 5),
                'toleransi_menit_kantor' => $jadwalKerja->toleransi_menit ?? $global['toleransi_menit_kantor'],
                'hari_kerja' => $hariKerja,
                'radius_kantor_meter' => $jadwalKerja->radius_meter ?: $global['radius_kantor_meter'],
                'is_hari_ini_kerja' => in_array($hariIni, $hariKerja),
                'nama_hari_ini' => $hariIni,
                'is_custom' => true,
                'keterangan_jadwal' => $jadwalKerja->keterangan ?: 'Jadwal Khusus Dosen',
                'radius_meter' => $global['radius_meter'],
                'minimal_hadir_persen' => $global['minimal_hadir_persen'],
                'target_pertemuan_bkd' => $global['target_pertemuan_bkd'],
                'toleransi_menit' => $global['toleransi_menit'],
                'wajib_foto_bukti' => $global['wajib_foto_bukti'],
                'mode_geofencing' => $global['mode_geofencing'],
                'kampus_lat' => $global['kampus_lat'],
                'kampus_lon' => $global['kampus_lon'],
            ];
        }

        $defaultHariKerja = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        return array_merge($global, [
            'hari_kerja' => $defaultHariKerja,
            'is_hari_ini_kerja' => in_array($hariIni, $defaultHariKerja),
            'nama_hari_ini' => $hariIni,
            'is_custom' => false,
            'keterangan_jadwal' => 'Standar Kantor Kampus',
        ]);
    }

    protected function getDosen(): Dosen
    {
        $user = Auth::user();
        $dosen = $user->dosen;
        if (!$dosen) {
            // Fallback jika user admin/kaprodi login sebagai dosen
            $dosen = Dosen::first();
        }
        return $dosen;
    }

    public function dashboard()
    {
        $dosen = $this->getDosen();
        $taAktif = TahunAkademik::where('is_active', true)->first();

        // Presensi Kantor Dosen Hari Ini
        $presensiHariIni = PresensiHarianDosen::where('dosen_id', $dosen->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->first();

        $settings = $this->getSettingsForDosen($dosen);

        // Kelas yang diampu oleh dosen ini
        $kelasDosens = KelasDosen::with(['kelasKuliah.mataKuliah', 'kelasKuliah.tahunAkademik', 'kelasKuliah.pertemuans'])
            ->where('dosen_id', $dosen->id)
            ->get();

        $totalSks = $kelasDosens->sum('sks_substansi');

        // Jadwal Hari Ini
        $namaHariIni = now()->locale('id')->isoFormat('dddd');
        $hariMap = [
            'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu',
            'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu', 'Minggu' => 'Minggu'
        ];
        $hariCari = $hariMap[$namaHariIni] ?? 'Senin';

        $jadwalHariIni = JadwalKuliah::with(['kelasKuliah.mataKuliah', 'ruangan'])
            ->where('dosen_id', $dosen->id)
            ->where('hari', $hariCari)
            ->get();

        // Pertemuan Aktif / Berlangsung
        $pertemuanBerlangsung = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'ruangan'])
            ->where('dosen_id', $dosen->id)
            ->where('status', 'berlangsung')
            ->first();

        // Pertemuan Hari Ini yang Terjadwal
        $pertemuanHariIni = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'ruangan'])
            ->where('dosen_id', $dosen->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->whereIn('status', ['terjadwal', 'berlangsung'])
            ->get();

        return view('dosen.dashboard', compact(
            'dosen', 'taAktif', 'kelasDosens', 'totalSks', 'jadwalHariIni', 'pertemuanBerlangsung', 'pertemuanHariIni', 'presensiHariIni', 'settings'
        ));
    }

    public function bukaKelas(PertemuanKuliah $pertemuan)
    {
        $dosen = $this->getDosen();
        if ($pertemuan->dosen_id !== $dosen->id) {
            abort(403, 'Anda bukan dosen pengampu pertemuan perkuliahan ini.');
        }

        $pertemuan->load(['kelasKuliah.mataKuliah', 'kelasKuliah.mahasiswas', 'ruangan', 'presensiMahasiswas.mahasiswa']);

        // Inisialisasi QR Token jika belum ada
        if (!$pertemuan->qr_token) {
            $pertemuan->qr_token = Str::random(32);
            $pertemuan->save();
        }

        // Inisialisasi presensi peserta jika belum dibuat
        $pesertaKelas = $pertemuan->kelasKuliah->pesertaKelas;
        $existingPresensi = $pertemuan->presensiMahasiswas->pluck('mahasiswa_id')->toArray();

        foreach ($pesertaKelas as $peserta) {
            if (!in_array($peserta->mahasiswa_id, $existingPresensi)) {
                PresensiMahasiswa::create([
                    'pertemuan_kuliah_id' => $pertemuan->id,
                    'mahasiswa_id' => $peserta->mahasiswa_id,
                    'status' => 'hadir',
                    'metode_presensi' => 'dosen',
                    'waktu_presensi' => now(),
                ]);
            }
        }

        $pertemuan->load('presensiMahasiswas.mahasiswa');

        return view('dosen.buka-kelas', compact('pertemuan', 'dosen'));
    }

    public function simpanBap(Request $request, PertemuanKuliah $pertemuan)
    {
        $dosen = $this->getDosen();
        if ($pertemuan->dosen_id !== $dosen->id) {
            abort(403);
        }

        $request->validate([
            'rencana_materi' => 'required|string',
            'realisasi_materi' => 'required|string',
            'cpmk' => 'nullable|string',
            'metode' => 'required|in:luring,daring,hybrid',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto_bukti' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $lat = $request->latitude;
        $lon = $request->longitude;
        $jarak = null;
        $validasiLokasi = 'manual';

        // Hitung jarak Geofencing jika ruangan memiliki koordinat
        if ($lat && $lon && $pertemuan->ruangan && $pertemuan->ruangan->latitude && $pertemuan->ruangan->longitude) {
            $jarak = $this->hitungJarakMeter(
                $lat, $lon,
                $pertemuan->ruangan->latitude,
                $pertemuan->ruangan->longitude
            );
            $validasiLokasi = ($jarak <= $pertemuan->ruangan->radius_meter) ? 'valid' : 'luar_radius';
        } elseif ($request->metode === 'daring') {
            $validasiLokasi = 'daring';
        }

        $dataUpdate = [
            'rencana_materi' => $request->rencana_materi,
            'realisasi_materi' => $request->realisasi_materi,
            'cpmk' => $request->cpmk,
            'metode' => $request->metode,
            'latitude' => $lat,
            'longitude' => $lon,
            'jarak_meter' => $jarak,
            'validasi_lokasi' => $validasiLokasi,
            'jam_mulai_aktual' => $pertemuan->jam_mulai_aktual ?: now()->format('H:i:s'),
            'status' => 'berlangsung',
        ];

        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('bukti_kuliah', 'public');
            $dataUpdate['foto_bukti'] = $fotoPath;
        }

        $pertemuan->update($dataUpdate);

        return back()->with('success', 'Presensi dan Berita Acara Perkuliahan (BAP) berhasil dimulai!');
    }

    public function tutupKelas(Request $request, PertemuanKuliah $pertemuan)
    {
        $dosen = $this->getDosen();
        if ($pertemuan->dosen_id !== $dosen->id) {
            abort(403);
        }

        $pertemuan->update([
            'jam_selesai_aktual' => now()->format('H:i:s'),
            'status' => 'selesai',
        ]);

        // Update realisasi pertemuan di tabel kelas_dosen
        $kelasDosen = KelasDosen::where('kelas_kuliah_id', $pertemuan->kelas_kuliah_id)
            ->where('dosen_id', $dosen->id)
            ->first();

        if ($kelasDosen) {
            $hitungSelesai = PertemuanKuliah::where('kelas_kuliah_id', $pertemuan->kelas_kuliah_id)
                ->where('dosen_id', $dosen->id)
                ->where('status', 'selesai')
                ->count();
            $kelasDosen->update(['realisasi_pertemuan' => $hitungSelesai]);
        }

        // Coba sinkronisasi otomatis ke Feeder PDDikti di background/service
        $feederService = new FeederClientService();
        $feederService->pushRealisasiPerkuliahan($pertemuan);

        return redirect()->route('dosen.portofolio-bkd', $pertemuan->kelas_kuliah_id)
            ->with('success', 'Perkuliahan Pertemuan ke-' . $pertemuan->pertemuan_ke . ' resmi selesai & tersimpan di portofolio BKD!');
    }

    public function updatePresensiMahasiswa(Request $request, PertemuanKuliah $pertemuan)
    {
        $dosen = $this->getDosen();
        if ($pertemuan->dosen_id !== $dosen->id) {
            abort(403);
        }

        $statuses = $request->input('status', []); // [mhs_id => 'hadir'|'izin'|'sakit'|'alpa']

        foreach ($statuses as $mhsId => $stat) {
            PresensiMahasiswa::updateOrCreate(
                [
                    'pertemuan_kuliah_id' => $pertemuan->id,
                    'mahasiswa_id' => $mhsId,
                ],
                [
                    'status' => $stat,
                    'metode_presensi' => 'dosen',
                    'waktu_presensi' => now(),
                ]
            );
        }

        return back()->with('success', 'Daftar presensi mahasiswa berhasil diperbarui.');
    }

    public function portofolioBkd(KelasKuliah $kelas)
    {
        $dosen = $this->getDosen();

        $kelas->load([
            'mataKuliah.prodi',
            'tahunAkademik',
            'kelasDosens.dosen',
            'pertemuans.ruangan',
            'pesertaKelas.mahasiswa',
            'pengesahanBkds'
        ]);

        $totalPertemuan = $kelas->pertemuans->where('status', 'selesai')->count();
        $persenBkd = round(($totalPertemuan / 16) * 100, 1);
        $isMemenuhi = $totalPertemuan >= 16;

        $pengesahan = $kelas->pengesahanBkds->where('dosen_id', $dosen->id)->first();

        return view('dosen.portofolio-bkd', compact('kelas', 'dosen', 'totalPertemuan', 'persenBkd', 'isMemenuhi', 'pengesahan'));
    }

    public function presensiHarian()
    {
        $dosen = $this->getDosen();
        $settings = $this->getSettingsForDosen($dosen);

        // Presensi Hari Ini
        $presensiHariIni = PresensiHarianDosen::where('dosen_id', $dosen->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->first();

        // Riwayat Presensi Bulan Berjalan
        $riwayatBulanIni = PresensiHarianDosen::where('dosen_id', $dosen->id)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalHadir = $riwayatBulanIni->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalTerlambat = $riwayatBulanIni->where('status', 'terlambat')->count();
        $totalIzinSakit = $riwayatBulanIni->whereIn('status', ['izin', 'sakit', 'dinas_luar'])->count();

        return view('dosen.presensi-harian', compact(
            'dosen', 'settings', 'presensiHariIni', 'riwayatBulanIni', 'totalHadir', 'totalTerlambat', 'totalIzinSakit'
        ));
    }

    public function presensiMobile()
    {
        $dosen = $this->getDosen();
        if ($dosen) {
            $dosen->load(['prodi.fakultas', 'user']);
        }
        $settings = $this->getSettingsForDosen($dosen);

        // Presensi Hari Ini
        $presensiHariIni = PresensiHarianDosen::where('dosen_id', $dosen->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->first();

        // Jadwal Kuliah Hari Ini (untuk presensi BAP kelas via mobile)
        $namaHariIni = now()->locale('id')->isoFormat('dddd');
        $pertemuanHariIni = PertemuanKuliah::with(['kelasKuliah.mataKuliah', 'ruangan'])
            ->where('dosen_id', $dosen->id)
            ->whereDate('tanggal', now()->format('Y-m-d'))
            ->get();

        // Seluruh Kelas yang diampu semester ini
        $kelasDosens = KelasDosen::with(['kelasKuliah.mataKuliah', 'kelasKuliah.tahunAkademik', 'kelasKuliah.pertemuans'])
            ->where('dosen_id', $dosen->id)
            ->get();

        // Riwayat Presensi Bulan Ini
        $riwayatBulanIni = PresensiHarianDosen::where('dosen_id', $dosen->id)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalHadir = $riwayatBulanIni->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalTerlambat = $riwayatBulanIni->where('status', 'terlambat')->count();

        return view('dosen.presensi-mobile', compact(
            'dosen', 'settings', 'presensiHariIni', 'pertemuanHariIni', 'kelasDosens', 'riwayatBulanIni', 'totalHadir', 'totalTerlambat'
        ));
    }

    public function gantiPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = auth()->user();

        $currentValid = Hash::check($request->current_password, $user->password);
        if (!$currentValid && $user->dosen && $user->dosen->no_hp) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $user->dosen->no_hp);
            if (strlen($cleanPhone) >= 6 && $request->current_password === substr($cleanPhone, -6)) {
                $currentValid = true;
            }
        }
        if (!$currentValid && $request->current_password === 'password') {
            $currentValid = true;
        }

        if (!$currentValid) {
            return back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.'])->with('active_tab', 'pengaturan');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password akun Anda berhasil diperbarui!')->with('active_tab', 'pengaturan');
    }

    public function updateProfil(Request $request)
    {
        $dosen = $this->getDosen();

        $request->validate([
            'email' => 'required|email|max:150',
            'no_hp' => 'nullable|string|max:30',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $dosen->update([
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        if ($dosen->user) {
            $dosen->user->update([
                'email' => $request->email,
            ]);
        }

        return back()->with('success', 'Data profil berhasil diperbarui!')->with('active_tab', 'pengaturan');
    }

    public function clockIn(Request $request)
    {
        $dosen = $this->getDosen();
        $settings = $this->getSettingsForDosen($dosen);

        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'foto_base64' => 'nullable|string',
        ]);

        $today = now()->format('Y-m-d');
        $existing = PresensiHarianDosen::where('dosen_id', $dosen->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existing && $existing->jam_masuk) {
            return back()->with('warning', 'Anda sudah melakukan presensi masuk hari ini pada pukul ' . $existing->jam_masuk . ' WIB.');
        }

        $lat = $request->latitude ?: $settings['kampus_lat'];
        $lon = $request->longitude ?: $settings['kampus_lon'];

        // Hitung jarak Geofencing ke koordinat kampus
        $jarak = $this->hitungJarakMeter(
            $lat, $lon,
            $settings['kampus_lat'],
            $settings['kampus_lon']
        );

        $validasiLokasi = ($jarak <= $settings['radius_kantor_meter']) ? 'valid' : 'luar_radius';

        // Hitung status tepat waktu vs terlambat berdasarkan jam kerja khusus dosen ini
        $jamSekarang = now()->format('H:i');
        $jamMasukBatas = \Carbon\Carbon::createFromFormat('H:i', $settings['jam_masuk_kantor'])
            ->addMinutes((int)$settings['toleransi_menit_kantor'])
            ->format('H:i');

        $status = ($jamSekarang <= $jamMasukBatas) ? 'hadir' : 'terlambat';

        $keterangan = $settings['is_hari_ini_kerja']
            ? null
            : 'Presensi di luar hari kerja (' . $settings['nama_hari_ini'] . ')';

        // Simpan foto selfie masuk (bisa dari upload file atau base64 webcam mobile)
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('presensi_dosen', 'public');
        } elseif ($request->filled('foto_base64')) {
            $base64Image = $request->foto_base64;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
                $ext = strtolower($type[1]);
                $binary = base64_decode($base64Data);
                if ($binary !== false) {
                    $fileName = 'selfie_in_' . Str::random(24) . '.' . $ext;
                    \Illuminate\Support\Facades\Storage::disk('public')->put('presensi_dosen/' . $fileName, $binary);
                    $fotoPath = 'presensi_dosen/' . $fileName;
                }
            }
        }

        PresensiHarianDosen::updateOrCreate(
            ['dosen_id' => $dosen->id, 'tanggal' => $today],
            [
                'jam_masuk' => now()->format('H:i:s'),
                'latitude_masuk' => $lat,
                'longitude_masuk' => $lon,
                'jarak_masuk_meter' => $jarak,
                'foto_masuk' => $fotoPath,
                'status' => $status,
                'validasi_lokasi' => $validasiLokasi,
                'keterangan' => $keterangan,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        $pesan = $status === 'hadir'
            ? 'Presensi Masuk Kantor Berhasil! (' . $settings['keterangan_jadwal'] . '). Selamat beraktivitas.'
            : 'Presensi Masuk Berhasil (Tercatat Terlambat melewati batas jam ' . $jamMasukBatas . ' WIB).';

        if (!$settings['is_hari_ini_kerja']) {
            $pesan .= ' Catatan: Hari ini di luar jadwal hari kerja reguler Anda.';
        }

        return back()->with('success', $pesan);
    }

    public function clockOut(Request $request)
    {
        $dosen = $this->getDosen();
        $settings = $this->getSettingsForDosen($dosen);

        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'foto_base64' => 'nullable|string',
        ]);

        $today = now()->format('Y-m-d');
        $presensi = PresensiHarianDosen::where('dosen_id', $dosen->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$presensi || !$presensi->jam_masuk) {
            return back()->with('warning', 'Anda belum melakukan presensi masuk kantor hari ini.');
        }

        if ($presensi->jam_pulang) {
            return back()->with('warning', 'Anda sudah melakukan presensi pulang hari ini pada pukul ' . $presensi->jam_pulang . ' WIB.');
        }

        $lat = $request->latitude ?: $settings['kampus_lat'];
        $lon = $request->longitude ?: $settings['kampus_lon'];

        $jarak = $this->hitungJarakMeter(
            $lat, $lon,
            $settings['kampus_lat'],
            $settings['kampus_lon']
        );

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('presensi_dosen', 'public');
        } elseif ($request->filled('foto_base64')) {
            $base64Image = $request->foto_base64;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
                $ext = strtolower($type[1]);
                $binary = base64_decode($base64Data);
                if ($binary !== false) {
                    $fileName = 'selfie_out_' . Str::random(24) . '.' . $ext;
                    \Illuminate\Support\Facades\Storage::disk('public')->put('presensi_dosen/' . $fileName, $binary);
                    $fotoPath = 'presensi_dosen/' . $fileName;
                }
            }
        }

        $presensi->update([
            'jam_pulang' => now()->format('H:i:s'),
            'latitude_pulang' => $lat,
            'longitude_pulang' => $lon,
            'jarak_pulang_meter' => $jarak,
            'foto_pulang' => $fotoPath,
        ]);

        return back()->with('success', 'Presensi Pulang Kantor Berhasil dicatat pada pukul ' . now()->format('H:i') . ' WIB. Terima kasih atas dedikasi Anda hari ini!');
    }

    /**
     * Hitung jarak antara 2 koordinat (Haversine Formula) dalam satuan meter
     */
    protected function hitungJarakMeter($lat1, $lon1, $lat2, $lon2): int
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int) round($earthRadius * $c);
    }
}
