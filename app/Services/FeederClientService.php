<?php

namespace App\Services;

use App\Models\FeederConfig;
use App\Models\FeederLog;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\KelasKuliah;
use App\Models\PertemuanKuliah;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FeederClientService
{
    protected ?FeederConfig $config = null;

    public function __construct()
    {
        $this->config = FeederConfig::first();
        if (!$this->config) {
            $this->config = FeederConfig::create([
                'url' => 'http://localhost:8100/ws/live2.php',
                'username' => 'feeder_admin',
                'password' => 'feeder_secret',
                'mode' => 'sandbox',
                'is_active' => true,
            ]);
        }
    }

    public function getConfig(): ?FeederConfig
    {
        return $this->config;
    }

    /**
     * Kirim request ke Web Service Neo Feeder PDDikti
     */
    public function sendRequest(string $action, array $params = []): array
    {
        $url = $this->config->url;
        $payload = array_merge(['act' => $action], $params);

        if ($action !== 'GetToken') {
            $token = $this->getToken();
            if (!$token) {
                return [
                    'status' => 'error',
                    'error_code' => 'AUTH_FAILED',
                    'error_desc' => 'Gagal mendapatkan token autentikasi Neo Feeder PDDikti.',
                    'data' => null,
                ];
            }
            $payload['token'] = $token;
        }

        try {
            $response = Http::timeout(10)->post($url, $payload);

            if ($response->successful()) {
                $result = $response->json();
                $isSuccess = ($result['error_code'] ?? '1') === '0' || ($result['error_code'] ?? 1) === 0;

                FeederLog::create([
                    'endpoint_action' => $action,
                    'status' => $isSuccess ? 'success' : 'error',
                    'error_code' => (string)($result['error_code'] ?? '0'),
                    'error_desc' => $result['error_desc'] ?? null,
                    'request_data' => json_encode($payload),
                    'response_data' => json_encode($result),
                    'created_at' => now(),
                ]);

                return [
                    'status' => $isSuccess ? 'success' : 'error',
                    'error_code' => (string)($result['error_code'] ?? '0'),
                    'error_desc' => $result['error_desc'] ?? null,
                    'data' => $result['data'] ?? null,
                ];
            } else {
                throw new Exception("HTTP error code: " . $response->status());
            }
        } catch (Exception $e) {
            // Mode Sandbox / Simulasi jika server Feeder belum dihidupkan
            FeederLog::create([
                'endpoint_action' => $action,
                'status' => 'error',
                'error_code' => 'CONNECTION_ERROR',
                'error_desc' => $e->getMessage(),
                'request_data' => json_encode($payload),
                'response_data' => null,
                'created_at' => now(),
            ]);

            return [
                'status' => 'error',
                'error_code' => 'CONNECTION_ERROR',
                'error_desc' => 'Tidak dapat terhubung ke Neo Feeder (' . $e->getMessage() . '). Pastikan service Neo Feeder aktif di port 8100.',
                'data' => null,
            ];
        }
    }

    /**
     * Dapatkan Token Session dari Feeder
     */
    public function getToken(): ?string
    {
        if ($this->config->token && $this->config->token_expired_at && $this->config->token_expired_at->isFuture()) {
            return $this->config->token;
        }

        $res = $this->sendRequest('GetToken', [
            'username' => $this->config->username,
            'password' => $this->config->password,
        ]);

        if ($res['status'] === 'success' && isset($res['data']['token'])) {
            $this->config->update([
                'token' => $res['data']['token'],
                'token_expired_at' => now()->addMinutes(110),
            ]);
            return $res['data']['token'];
        }

        return null;
    }

    /**
     * Test Koneksi Web Service Feeder
     */
    public function testConnection(): array
    {
        $res = $this->sendRequest('GetProfilPT');
        if ($res['status'] === 'success') {
            return [
                'connected' => true,
                'message' => 'Koneksi ke Neo Feeder PDDikti berhasil.',
                'pt' => $res['data'][0]['nama_perguruan_tinggi'] ?? 'Kampus Mitra',
            ];
        }

        return [
            'connected' => false,
            'message' => $res['error_desc'] ?? 'Gagal menghubungi Neo Feeder.',
        ];
    }

    /**
     * Push Realisasi Perkuliahan Dosen ke Feeder / SIAKAD
     * (Menyimpan data BAP dan kehadiran pertemuan ke PDDikti untuk pemenuhan syarat BKD di SISTER)
     */
    public function pushRealisasiPerkuliahan(PertemuanKuliah $pertemuan): array
    {
        $dosen = $pertemuan->dosen;
        $kelas = $pertemuan->kelasKuliah;

        $record = [
            'id_kelas_kuliah' => $kelas->id_feeder ?: 'LOCAL-' . $kelas->id,
            'id_dosen' => $dosen->id_feeder ?: 'LOCAL-' . $dosen->id,
            'pertemuan_ke' => $pertemuan->pertemuan_ke,
            'tanggal' => $pertemuan->tanggal->format('Y-m-d'),
            'jam_mulai' => $pertemuan->jam_mulai_aktual ?: $pertemuan->jam_mulai_jadwal,
            'jam_selesai' => $pertemuan->jam_selesai_aktual ?: $pertemuan->jam_selesai_jadwal,
            'materi_pokok' => $pertemuan->rencana_materi,
            'realisasi_materi' => $pertemuan->realisasi_materi,
            'metode_pembelajaran' => $pertemuan->metode,
            'jumlah_mahasiswa_hadir' => $pertemuan->presensiMahasiswas()->where('status', 'hadir')->count(),
        ];

        // Jalankan via API Feeder
        $res = $this->sendRequest('InsertAktivitasKuliah', ['record' => $record]);

        if ($res['status'] === 'success') {
            $pertemuan->update([
                'status_sync_feeder' => 'synced',
                'pesan_sync_feeder' => 'Tersinkron ke Feeder PDDikti pada ' . now()->toDateTimeString(),
            ]);
            return ['success' => true, 'message' => 'Berhasil disinkronkan ke Feeder PDDikti!'];
        }

        // Jika server feeder offline, catat status pending dan biarkan data lokal tetap aman (mode standalone)
        $pertemuan->update([
            'status_sync_feeder' => 'pending',
            'pesan_sync_feeder' => 'Antrian sinkronisasi lokal: ' . ($res['error_desc'] ?? 'Feeder Offline'),
        ]);

        return [
            'success' => false,
            'message' => 'Disimpan secara lokal. ' . ($res['error_desc'] ?? 'Feeder Offline, akan disinkronkan saat online.'),
        ];
    }
}
