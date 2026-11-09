<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\ProgramStudi;
use App\Models\PerguruanTinggi;
use App\Models\Dosen;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Ruangan;
use App\Models\AsistenDosen;
use App\Models\KelasKuliah;
use App\Models\FeederConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    // ==========================================
    // 1. PERGURUAN TINGGI (DENGAN EDIT MANUAL)
    // ==========================================
    public function perguruanTinggi()
    {
        $pt = PerguruanTinggi::first();
        if (!$pt) {
            $pt = PerguruanTinggi::create([
                'kode_pt' => '031001',
                'nama_pt' => 'Universitas Teknologi Nusantara',
                'singkatan' => 'UTN',
                'akreditasi' => 'Unggul (BAN-PT)',
                'sk_pendirian' => 'SK Mendikbud No. 142/E/O/2012',
                'tanggal_sk' => '2012-08-12',
                'nama_rektor' => 'Prof. Dr. H. Faisal Anwar, M.Eng.',
                'email' => 'info@kampus.ac.id',
                'telepon' => '(021) 789-0123',
                'website' => 'https://kampus.ac.id',
                'alamat' => 'Jl. Kampus Merdeka No. 101, Gedung Perkuliahan Terpadu',
            ]);
        }
        return view('master.perguruan-tinggi', compact('pt'));
    }

    public function updatePerguruanTinggi(Request $request)
    {
        $pt = PerguruanTinggi::firstOrFail();

        $validated = $request->validate([
            'nama_pt' => 'required|string|max:255',
            'kode_pt' => 'required|string|max:50',
            'singkatan' => 'nullable|string|max:50',
            'akreditasi' => 'required|string|max:50',
            'sk_pendirian' => 'required|string|max:255',
            'tanggal_sk' => 'nullable|date',
            'nama_rektor' => 'required|string|max:255',
            'nip_rektor' => 'nullable|string|max:50',
            'email' => 'required|email|max:100',
            'telepon' => 'required|string|max:50',
            'website' => 'required|url|max:255',
            'alamat' => 'required|string',
        ]);

        $pt->update($validated);

        $feeder = FeederConfig::first();
        if ($feeder) {
            $feeder->update([
                'kode_pt' => $validated['kode_pt'],
                'nama_pt' => $validated['nama_pt'],
            ]);
        }

        return back()->with('success', 'Profil institusi perguruan tinggi berhasil diperbarui secara manual!');
    }

    // ==========================================
    // 2. FAKULTAS (TAMBAH, EDIT, HAPUS)
    // ==========================================
    public function fakultas()
    {
        $fakultasList = Fakultas::withCount('programStudis')->get();
        return view('master.fakultas', compact('fakultasList'));
    }

    public function storeFakultas(Request $request)
    {
        $validated = $request->validate([
            'kode_fakultas' => 'required|string|max:20|unique:fakultas,kode_fakultas',
            'nama_fakultas' => 'required|string|max:255',
            'dekan' => 'nullable|string|max:255',
            'gedung' => 'nullable|string|max:255',
        ]);

        Fakultas::create($validated);

        return back()->with('success', 'Fakultas baru berhasil ditambahkan!');
    }

    public function updateFakultas(Request $request, Fakultas $fakulta)
    {
        $validated = $request->validate([
            'kode_fakultas' => 'required|string|max:20|unique:fakultas,kode_fakultas,' . $fakulta->id,
            'nama_fakultas' => 'required|string|max:255',
            'dekan' => 'nullable|string|max:255',
            'gedung' => 'nullable|string|max:255',
        ]);

        $fakulta->update($validated);

        return back()->with('success', 'Data fakultas ' . $fakulta->nama_fakultas . ' berhasil diperbarui!');
    }

    public function destroyFakultas(Fakultas $fakulta)
    {
        if ($fakulta->programStudis()->count() > 0) {
            return back()->with('warning', 'Tidak dapat menghapus fakultas yang masih memiliki program studi terdaftar.');
        }

        $nama = $fakulta->nama_fakultas;
        $fakulta->delete();

        return back()->with('success', 'Fakultas ' . $nama . ' berhasil dihapus.');
    }

    // ==========================================
    // 3. PROGRAM STUDI (TAMBAH, EDIT, HAPUS)
    // ==========================================
    public function programStudi()
    {
        $prodis = ProgramStudi::with('fakultas')->withCount(['dosens', 'mahasiswas', 'mataKuliahs'])->get();
        $fakultasList = Fakultas::all();
        return view('master.program-studi', compact('prodis', 'fakultasList'));
    }

    public function storeProgramStudi(Request $request)
    {
        $validated = $request->validate([
            'fakultas_id' => 'required|exists:fakultas,id',
            'kode_prodi' => 'required|string|max:20|unique:program_studis,kode_prodi',
            'nama_prodi' => 'required|string|max:255',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3,Profesi',
            'akreditasi' => 'required|string|max:50',
            'nama_kaprodi' => 'required|string|max:255',
            'nidn_kaprodi' => 'nullable|string|max:20',
        ]);

        $validated['id_feeder'] = (string) Str::uuid();

        ProgramStudi::create($validated);

        return back()->with('success', 'Program Studi baru berhasil ditambahkan!');
    }

    public function updateProgramStudi(Request $request, ProgramStudi $programStudi)
    {
        $validated = $request->validate([
            'fakultas_id' => 'required|exists:fakultas,id',
            'kode_prodi' => 'required|string|max:20|unique:program_studis,kode_prodi,' . $programStudi->id,
            'nama_prodi' => 'required|string|max:255',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3,Profesi',
            'akreditasi' => 'required|string|max:50',
            'nama_kaprodi' => 'required|string|max:255',
            'nidn_kaprodi' => 'nullable|string|max:20',
        ]);

        $programStudi->update($validated);

        return back()->with('success', 'Data Program Studi ' . $programStudi->nama_prodi . ' berhasil diperbarui!');
    }

    public function destroyProgramStudi(ProgramStudi $programStudi)
    {
        if ($programStudi->dosens()->count() > 0 || $programStudi->mahasiswas()->count() > 0) {
            return back()->with('warning', 'Tidak dapat menghapus program studi yang sudah memiliki dosen atau mahasiswa terdaftar.');
        }

        $nama = $programStudi->nama_prodi;
        $programStudi->delete();

        return back()->with('success', 'Program Studi ' . $nama . ' berhasil dihapus.');
    }

    // ==========================================
    // 4. DOSEN (TAMBAH, EDIT, HAPUS, & IMPOR)
    // ==========================================
    public function dosen()
    {
        $dosens = Dosen::with('prodi')->paginate(15);
        $prodis = ProgramStudi::all();
        return view('master.dosen', compact('dosens', 'prodis'));
    }

    public function storeDosen(Request $request)
    {
        $request->validate([
            'nidn' => 'required|string|max:20|unique:dosens,nidn',
            'nip' => 'nullable|string|max:30',
            'nama_lengkap' => 'required|string|max:255',
            'gelar_depan' => 'nullable|string|max:30',
            'gelar_belakang' => 'nullable|string|max:50',
            'prodi_id' => 'required|exists:program_studis,id',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|max:100|unique:users,email',
            'no_hp' => 'nullable|string|max:25',
        ]);

        $user = User::create([
            'name' => trim(($request->gelar_depan ? $request->gelar_depan . ' ' : '') . $request->nama_lengkap . ($request->gelar_belakang ? ', ' . $request->gelar_belakang : '')),
            'email' => $request->email,
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);

        Dosen::create([
            'user_id' => $user->id,
            'nidn' => $request->nidn,
            'nip' => $request->nip,
            'nama_lengkap' => $request->nama_lengkap,
            'gelar_depan' => $request->gelar_depan,
            'gelar_belakang' => $request->gelar_belakang,
            'prodi_id' => $request->prodi_id,
            'jenis_kelamin' => $request->jenis_kelamin,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'is_active' => true,
            'id_feeder' => (string) Str::uuid(),
        ]);

        return back()->with('success', 'Dosen baru berhasil ditambahkan! Akun login otomatis dibuat dengan kata sandi default: "password".');
    }

    public function updateDosen(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nidn' => 'required|string|max:20|unique:dosens,nidn,' . $dosen->id,
            'nip' => 'nullable|string|max:30',
            'nama_lengkap' => 'required|string|max:255',
            'gelar_depan' => 'nullable|string|max:30',
            'gelar_belakang' => 'nullable|string|max:50',
            'prodi_id' => 'required|exists:program_studis,id',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|max:100',
            'no_hp' => 'nullable|string|max:25',
        ]);

        $dosen->update([
            'nidn' => $request->nidn,
            'nip' => $request->nip,
            'nama_lengkap' => $request->nama_lengkap,
            'gelar_depan' => $request->gelar_depan,
            'gelar_belakang' => $request->gelar_belakang,
            'prodi_id' => $request->prodi_id,
            'jenis_kelamin' => $request->jenis_kelamin,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        if ($dosen->user) {
            $dosen->user->update([
                'name' => $dosen->nama_gelar,
                'email' => $request->email,
            ]);
        }

        return back()->with('success', 'Data dosen ' . $dosen->nama_gelar . ' berhasil diperbarui!');
    }

    public function destroyDosen(Dosen $dosen)
    {
        if ($dosen->kelasDosens()->count() > 0) {
            return back()->with('warning', 'Tidak dapat menghapus dosen yang sedang mengampu kelas perkuliahan aktif.');
        }

        $nama = $dosen->nama_gelar;
        if ($dosen->user) {
            $dosen->user->delete();
        }
        $dosen->delete();

        return back()->with('success', 'Dosen ' . $nama . ' berhasil dihapus.');
    }

    public function importDosen(Request $request)
    {
        $request->validate([
            'file_dosen' => 'required|file|max:5120',
        ]);

        $file = $request->file('file_dosen');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('warning', 'Gagal membaca berkas yang diunggah.');
        }

        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);

        $rowNumber = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $defaultProdi = ProgramStudi::first();

        while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
            $rowNumber++;
            if ($rowNumber === 1 && isset($row[0]) && stripos($row[0], 'nidn') !== false) {
                continue;
            }
            if (empty($row[0])) {
                continue;
            }

            $nidn = trim($row[0]);
            $nip = isset($row[1]) ? trim($row[1]) : null;
            $namaLengkap = isset($row[2]) ? trim($row[2]) : 'Dosen Pengajar';
            $gelarDepan = isset($row[3]) ? trim($row[3]) : null;
            $gelarBelakang = isset($row[4]) ? trim($row[4]) : null;
            $kodeProdi = isset($row[5]) ? trim($row[5]) : null;
            $jk = isset($row[6]) && in_array(strtoupper(trim($row[6])), ['L', 'P']) ? strtoupper(trim($row[6])) : 'L';
            $email = isset($row[7]) && filter_var(trim($row[7]), FILTER_VALIDATE_EMAIL) ? trim($row[7]) : strtolower(str_replace(' ', '', $namaLengkap)) . '@kampus.ac.id';
            $noHp = isset($row[8]) ? trim($row[8]) : null;

            if (Dosen::where('nidn', $nidn)->exists()) {
                $skippedCount++;
                continue;
            }

            $prodi = $kodeProdi ? ProgramStudi::where('kode_prodi', $kodeProdi)->first() : null;
            $prodiId = $prodi ? $prodi->id : ($defaultProdi ? $defaultProdi->id : null);

            $user = User::where('email', $email)->first();
            if (!$user) {
                $namaGelarLengkap = trim(($gelarDepan ? $gelarDepan . ' ' : '') . $namaLengkap . ($gelarBelakang ? ', ' . $gelarBelakang : ''));
                $user = User::create([
                    'name' => $namaGelarLengkap,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'dosen',
                    'is_active' => true,
                ]);
            }

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $nidn,
                'nip' => $nip,
                'nama_lengkap' => $namaLengkap,
                'gelar_depan' => $gelarDepan,
                'gelar_belakang' => $gelarBelakang,
                'prodi_id' => $prodiId,
                'jenis_kelamin' => $jk,
                'email' => $email,
                'no_hp' => $noHp,
                'is_active' => true,
                'id_feeder' => (string) Str::uuid(),
            ]);

            $importedCount++;
        }

        fclose($handle);

        $pesan = "Proses impor selesai! Berhasil menambahkan $importedCount dosen baru.";
        if ($skippedCount > 0) {
            $pesan .= " Sebanyak $skippedCount baris dilewati karena NIDN sudah terdaftar sebelumnya.";
        }

        return back()->with('success', $pesan);
    }

    public function downloadTemplateDosen()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_impor_dosen.csv"',
        ];

        $columns = ['nidn', 'nip', 'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'kode_prodi', 'jenis_kelamin', 'email', 'no_hp'];
        $exampleRows = [
            ['0412088801', '198808122015041001', 'Hendra Pratama', 'Dr.', 'M.Kom.', '55201', 'L', 'hendra@kampus.ac.id', '081234567890'],
            ['0425099002', '199009252019032002', 'Siti Rahmawati', '', 'S.Kom., M.T.', '55201', 'P', 'siti.rahma@kampus.ac.id', '081298765432'],
        ];

        $callback = function () use ($columns, $exampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($exampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 5. ASISTEN DOSEN (TAMBAH, EDIT, HAPUS & IMPOR)
    // ==========================================
    public function asistenDosen()
    {
        $asdosList = AsistenDosen::with(['mataKuliah', 'dosen', 'kelasKuliah'])->paginate(15);
        $mataKuliahs = MataKuliah::all();
        $dosens = Dosen::all();
        $kelasList = KelasKuliah::all();
        return view('master.asisten-dosen', compact('asdosList', 'mataKuliahs', 'dosens', 'kelasList'));
    }

    public function storeAsistenDosen(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:30',
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|string|max:25',
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'dosen_id' => 'required|exists:dosens,id',
            'nama_kelas' => 'required|string|max:30',
            'status' => 'required|in:Aktif,Selesai,Nonaktif',
        ]);

        AsistenDosen::create($validated);

        return back()->with('success', 'Asisten dosen baru berhasil ditambahkan!');
    }

    public function updateAsistenDosen(Request $request, AsistenDosen $asistenDosen)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:30',
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'no_hp' => 'nullable|string|max:25',
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'dosen_id' => 'required|exists:dosens,id',
            'nama_kelas' => 'required|string|max:30',
            'status' => 'required|in:Aktif,Selesai,Nonaktif',
        ]);

        $asistenDosen->update($validated);

        return back()->with('success', 'Data asisten dosen ' . $asistenDosen->nama . ' berhasil diperbarui!');
    }

    public function destroyAsistenDosen(AsistenDosen $asistenDosen)
    {
        $nama = $asistenDosen->nama;
        $asistenDosen->delete();
        return back()->with('success', 'Asisten dosen ' . $nama . ' berhasil dihapus.');
    }

    public function importAsistenDosen(Request $request)
    {
        $request->validate([
            'file_asdos' => 'required|file|max:5120',
        ]);

        $file = $request->file('file_asdos');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('warning', 'Gagal membaca berkas CSV.');
        }

        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);

        $rowNumber = 0;
        $importedCount = 0;
        $defaultMk = MataKuliah::first();
        $defaultDosen = Dosen::first();

        while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
            $rowNumber++;
            if ($rowNumber === 1 && isset($row[0]) && stripos($row[0], 'nim') !== false) {
                continue;
            }
            if (empty($row[0])) {
                continue;
            }

            // Columns: 0: nim, 1: nama, 2: email, 3: no_hp, 4: kode_mk, 5: nidn_dosen, 6: nama_kelas, 7: status
            $nim = trim($row[0]);
            $nama = isset($row[1]) ? trim($row[1]) : 'Asisten Dosen';
            $email = isset($row[2]) ? trim($row[2]) : null;
            $noHp = isset($row[3]) ? trim($row[3]) : null;
            $kodeMk = isset($row[4]) ? trim($row[4]) : null;
            $nidnDosen = isset($row[5]) ? trim($row[5]) : null;
            $namaKelas = isset($row[6]) ? trim($row[6]) : 'TI-1A';
            $status = isset($row[7]) && in_array(trim($row[7]), ['Aktif', 'Selesai', 'Nonaktif']) ? trim($row[7]) : 'Aktif';

            $mk = $kodeMk ? MataKuliah::where('kode_mk', $kodeMk)->first() : null;
            $mkId = $mk ? $mk->id : ($defaultMk ? $defaultMk->id : null);

            $dosen = $nidnDosen ? Dosen::where('nidn', $nidnDosen)->first() : null;
            $dosenId = $dosen ? $dosen->id : ($defaultDosen ? $defaultDosen->id : null);

            AsistenDosen::create([
                'nim' => $nim,
                'nama' => $nama,
                'email' => $email,
                'no_hp' => $noHp,
                'mata_kuliah_id' => $mkId,
                'dosen_id' => $dosenId,
                'nama_kelas' => $namaKelas,
                'status' => $status,
            ]);

            $importedCount++;
        }

        fclose($handle);

        return back()->with('success', "Proses impor selesai! Berhasil menambahkan $importedCount data asisten dosen.");
    }

    public function downloadTemplateAsistenDosen()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_impor_asisten_dosen.csv"',
        ];

        $columns = ['nim', 'nama', 'email', 'no_hp', 'kode_mk', 'nidn_dosen', 'nama_kelas', 'status'];
        $exampleRows = [
            ['202255201001', 'Ahmad Fauzi', 'ahmad@student.kampus.ac.id', '081234567801', 'TIF-301', '0412088801', 'TI-5A', 'Aktif'],
            ['202255201002', 'Bima Sakti', 'bima@student.kampus.ac.id', '081234567802', 'TIF-302', '0425099002', 'TI-5B', 'Aktif'],
        ];

        $callback = function () use ($columns, $exampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($exampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 6. MATA KULIAH (TAMBAH, EDIT, HAPUS & IMPOR)
    // ==========================================
    public function mataKuliah()
    {
        $mataKuliahs = MataKuliah::with('prodi')->paginate(15);
        $prodis = ProgramStudi::all();
        return view('master.mata-kuliah', compact('mataKuliahs', 'prodis'));
    }

    public function storeMataKuliah(Request $request)
    {
        $validated = $request->validate([
            'kode_mk' => 'required|string|max:20|unique:mata_kuliahs,kode_mk',
            'nama_mk' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:8',
            'semester_paket' => 'required|integer|min:1|max:8',
            'prodi_id' => 'required|exists:program_studis,id',
            'deskripsi_rps' => 'nullable|string',
        ]);

        $validated['id_feeder'] = (string) Str::uuid();

        MataKuliah::create($validated);

        return back()->with('success', 'Mata kuliah baru berhasil ditambahkan!');
    }

    public function updateMataKuliah(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validate([
            'kode_mk' => 'required|string|max:20|unique:mata_kuliahs,kode_mk,' . $mataKuliah->id,
            'nama_mk' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:8',
            'semester_paket' => 'required|integer|min:1|max:8',
            'prodi_id' => 'required|exists:program_studis,id',
            'deskripsi_rps' => 'nullable|string',
        ]);

        $mataKuliah->update($validated);

        return back()->with('success', 'Data mata kuliah ' . $mataKuliah->nama_mk . ' berhasil diperbarui!');
    }

    public function destroyMataKuliah(MataKuliah $mataKuliah)
    {
        if ($mataKuliah->kelasKuliahs()->count() > 0) {
            return back()->with('warning', 'Tidak dapat menghapus mata kuliah yang sudah memiliki kelas perkuliahan aktif.');
        }

        $nama = $mataKuliah->nama_mk;
        $mataKuliah->delete();

        return back()->with('success', 'Mata kuliah ' . $nama . ' berhasil dihapus.');
    }

    public function importMataKuliah(Request $request)
    {
        $request->validate([
            'file_mk' => 'required|file|max:5120',
        ]);

        $file = $request->file('file_mk');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('warning', 'Gagal membaca berkas CSV.');
        }

        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);

        $rowNumber = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $defaultProdi = ProgramStudi::first();

        while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
            $rowNumber++;
            if ($rowNumber === 1 && isset($row[0]) && stripos($row[0], 'kode') !== false) {
                continue;
            }
            if (empty($row[0])) {
                continue;
            }

            // Columns: 0: kode_mk, 1: nama_mk, 2: sks, 3: semester_paket, 4: kode_prodi, 5: deskripsi_rps
            $kodeMk = trim($row[0]);
            $namaMk = isset($row[1]) ? trim($row[1]) : 'Mata Kuliah';
            $sks = isset($row[2]) ? (int)trim($row[2]) : 2;
            $sem = isset($row[3]) ? (int)trim($row[3]) : 1;
            $kodeProdi = isset($row[4]) ? trim($row[4]) : null;
            $rps = isset($row[5]) ? trim($row[5]) : null;

            if (MataKuliah::where('kode_mk', $kodeMk)->exists()) {
                $skippedCount++;
                continue;
            }

            $prodi = $kodeProdi ? ProgramStudi::where('kode_prodi', $kodeProdi)->first() : null;
            $prodiId = $prodi ? $prodi->id : ($defaultProdi ? $defaultProdi->id : null);

            MataKuliah::create([
                'kode_mk' => $kodeMk,
                'nama_mk' => $namaMk,
                'sks' => $sks,
                'semester_paket' => $sem,
                'prodi_id' => $prodiId,
                'deskripsi_rps' => $rps,
                'id_feeder' => (string) Str::uuid(),
            ]);

            $importedCount++;
        }

        fclose($handle);

        $pesan = "Proses impor selesai! Berhasil menambahkan $importedCount mata kuliah.";
        if ($skippedCount > 0) {
            $pesan .= " Sebanyak $skippedCount baris dilewati karena Kode MK sudah terdaftar.";
        }

        return back()->with('success', $pesan);
    }

    public function downloadTemplateMataKuliah()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_impor_mata_kuliah.csv"',
        ];

        $columns = ['kode_mk', 'nama_mk', 'sks', 'semester_paket', 'kode_prodi', 'deskripsi_rps'];
        $exampleRows = [
            ['TIF-101', 'Algoritma & Pemrograman Dasar', '3', '1', '55201', 'Konsep dasar algoritma, flowchart, struktur sekuensial dan perulangan.'],
            ['TIF-102', 'Matematika Diskrit', '3', '1', '55201', 'Himpunan, relasi, fungsi, aljabar boolean, dan teori graf.'],
        ];

        $callback = function () use ($columns, $exampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($exampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 7. RUANG (TAMBAH, EDIT, HAPUS & IMPOR)
    // ==========================================
    public function ruang()
    {
        $ruangans = Ruangan::all();
        return view('master.ruang', compact('ruangans'));
    }

    public function storeRuang(Request $request)
    {
        $validated = $request->validate([
            'kode_ruangan' => 'required|string|max:20|unique:ruangans,kode_ruangan',
            'nama_ruangan' => 'required|string|max:255',
            'gedung' => 'required|string|max:255',
            'lantai' => 'required|integer|min:1|max:20',
            'kapasitas' => 'required|integer|min:1|max:500',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:10|max:1000',
        ]);

        Ruangan::create($validated);

        return back()->with('success', 'Ruangan baru berhasil ditambahkan!');
    }

    public function updateRuang(Request $request, Ruangan $ruang)
    {
        $validated = $request->validate([
            'kode_ruangan' => 'required|string|max:20|unique:ruangans,kode_ruangan,' . $ruang->id,
            'nama_ruangan' => 'required|string|max:255',
            'gedung' => 'required|string|max:255',
            'lantai' => 'required|integer|min:1|max:20',
            'kapasitas' => 'required|integer|min:1|max:500',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:10|max:1000',
        ]);

        $ruang->update($validated);

        return back()->with('success', 'Data ruangan ' . $ruang->nama_ruangan . ' berhasil diperbarui!');
    }

    public function destroyRuang(Ruangan $ruang)
    {
        if ($ruang->jadwals()->count() > 0) {
            return back()->with('warning', 'Tidak dapat menghapus ruangan yang sedang digunakan dalam jadwal perkuliahan aktif.');
        }

        $nama = $ruang->nama_ruangan;
        $ruang->delete();

        return back()->with('success', 'Ruangan ' . $nama . ' berhasil dihapus.');
    }

    public function importRuang(Request $request)
    {
        $request->validate([
            'file_ruang' => 'required|file|max:5120',
        ]);

        $file = $request->file('file_ruang');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('warning', 'Gagal membaca berkas CSV.');
        }

        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);

        $rowNumber = 0;
        $importedCount = 0;
        $skippedCount = 0;

        while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
            $rowNumber++;
            if ($rowNumber === 1 && isset($row[0]) && stripos($row[0], 'kode') !== false) {
                continue;
            }
            if (empty($row[0])) {
                continue;
            }

            // Columns: 0: kode_ruangan, 1: nama_ruangan, 2: gedung, 3: lantai, 4: kapasitas, 5: latitude, 6: longitude, 7: radius_meter
            $kode = trim($row[0]);
            $nama = isset($row[1]) ? trim($row[1]) : 'Ruang Kuliah';
            $gedung = isset($row[2]) ? trim($row[2]) : 'Gedung Terpadu';
            $lantai = isset($row[3]) ? (int)trim($row[3]) : 1;
            $kapasitas = isset($row[4]) ? (int)trim($row[4]) : 40;
            $lat = isset($row[5]) ? (float)trim($row[5]) : -6.2088;
            $lon = isset($row[6]) ? (float)trim($row[6]) : 106.8456;
            $radius = isset($row[7]) ? (int)trim($row[7]) : 200;

            if (Ruangan::where('kode_ruangan', $kode)->exists()) {
                $skippedCount++;
                continue;
            }

            Ruangan::create([
                'kode_ruangan' => $kode,
                'nama_ruangan' => $nama,
                'gedung' => $gedung,
                'lantai' => $lantai,
                'kapasitas' => $kapasitas,
                'latitude' => $lat,
                'longitude' => $lon,
                'radius_meter' => $radius,
            ]);

            $importedCount++;
        }

        fclose($handle);

        $pesan = "Proses impor selesai! Berhasil menambahkan $importedCount ruangan baru.";
        if ($skippedCount > 0) {
            $pesan .= " Sebanyak $skippedCount baris dilewati karena Kode Ruangan sudah terdaftar.";
        }

        return back()->with('success', $pesan);
    }

    public function downloadTemplateRuang()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_impor_ruang.csv"',
        ];

        $columns = ['kode_ruangan', 'nama_ruangan', 'gedung', 'lantai', 'kapasitas', 'latitude', 'longitude', 'radius_meter'];
        $exampleRows = [
            ['R-101', 'Ruang Teori 101', 'Gedung Terpadu', '1', '45', '-6.20880000', '106.84560000', '200'],
            ['LAB-AI', 'Laboratorium Artificial Intelligence', 'Gedung Rektorat Baru', '3', '35', '-6.20880000', '106.84560000', '150'],
        ];

        $callback = function () use ($columns, $exampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($exampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 8. MAHASISWA & BOBOT NILAI
    // ==========================================
    public function mahasiswa()
    {
        $mahasiswas = Mahasiswa::with('prodi')->paginate(15);
        return view('master.mahasiswa', compact('mahasiswas'));
    }

    public function bobotNilai()
    {
        $bobotList = [
            ['huruf' => 'A', 'bobot' => 4.00, 'min' => 85.00, 'max' => 100.00, 'status' => 'Lulus', 'predikat' => 'Sangat Memuaskan / Cumlaude'],
            ['huruf' => 'A-', 'bobot' => 3.75, 'min' => 80.00, 'max' => 84.99, 'status' => 'Lulus', 'predikat' => 'Memuaskan'],
            ['huruf' => 'B+', 'bobot' => 3.25, 'min' => 75.00, 'max' => 79.99, 'status' => 'Lulus', 'predikat' => 'Sangat Baik'],
            ['huruf' => 'B', 'bobot' => 3.00, 'min' => 70.00, 'max' => 74.99, 'status' => 'Lulus', 'predikat' => 'Baik'],
            ['huruf' => 'B-', 'bobot' => 2.75, 'min' => 65.00, 'max' => 69.99, 'status' => 'Lulus', 'predikat' => 'Cukup Baik'],
            ['huruf' => 'C+', 'bobot' => 2.25, 'min' => 60.00, 'max' => 64.99, 'status' => 'Lulus', 'predikat' => 'Cukup'],
            ['huruf' => 'C', 'bobot' => 2.00, 'min' => 55.00, 'max' => 59.99, 'status' => 'Lulus', 'predikat' => 'Kurang'],
            ['huruf' => 'D', 'bobot' => 1.00, 'min' => 40.00, 'max' => 54.99, 'status' => 'Tidak Lulus', 'predikat' => 'Sangat Kurang'],
            ['huruf' => 'E', 'bobot' => 0.00, 'min' => 0.00, 'max' => 39.99, 'status' => 'Tidak Lulus', 'predikat' => 'Gagal'],
        ];
        return view('master.bobot-nilai', compact('bobotList'));
    }
}
