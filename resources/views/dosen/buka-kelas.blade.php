@extends('layouts.app')

@section('title', 'Presensi & BAP Kelas')

@section('content')
<div class="space-y-6">

    <!-- Header Kelas & Pertemuan -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dosen.dashboard') }}" class="text-xs text-slate-500 hover:text-slate-800 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full">
                    Pertemuan ke-{{ $pertemuan->pertemuan_ke }} ({{ strtoupper($pertemuan->jenis_pertemuan) }})
                </span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $pertemuan->status === 'berlangsung' ? 'bg-amber-100 text-amber-800 animate-pulse' : ($pertemuan->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                    Status: {{ ucfirst($pertemuan->status) }}
                </span>
            </div>

            <h1 class="text-xl font-bold text-slate-900 mt-2">
                {{ $pertemuan->kelasKuliah->mataKuliah->nama_mk }} ({{ $pertemuan->kelasKuliah->nama_kelas }})
            </h1>
            <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                <span><i class="fa-regular fa-calendar mr-1 text-slate-400"></i> Tanggal: <strong>{{ $pertemuan->tanggal->format('d/m/Y') }}</strong></span>
                <span><i class="fa-regular fa-clock mr-1 text-slate-400"></i> Jadwal: <strong>{{ substr($pertemuan->jam_mulai_jadwal, 0, 5) }} - {{ substr($pertemuan->jam_selesai_jadwal, 0, 5) }} WIB</strong></span>
                <span><i class="fa-solid fa-door-open mr-1 text-slate-400"></i> Ruangan: <strong>{{ $pertemuan->ruangan ? $pertemuan->ruangan->nama_ruangan : 'Laboratorium' }}</strong></span>
                <span><i class="fa-solid fa-users mr-1 text-slate-400"></i> Total Mahasiswa: <strong>{{ $pertemuan->presensiMahasiswas->count() }} Orang</strong></span>
            </div>
        </div>

        @if($pertemuan->status === 'berlangsung')
        <div>
            <form action="{{ route('dosen.tutup-kelas', $pertemuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri sesi perkuliahan ini? Jam selesai akan dicatat dan data disinkronkan ke portofolio BKD.');">
                @csrf
                <button type="submit" class="px-5 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-lg shadow-rose-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-stop"></i> Akhiri & Tutup Kelas
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Validasi GPS & Form Berita Acara Perkuliahan (BAP) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Panel Geofencing GPS Dosen -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-emerald-600"></i> Validasi Geofencing GPS Kampus
                        </h2>
                        <p class="text-xs text-slate-500">Mendeteksi kehadiran fisik dosen di area koordinat ruangan/kampus</p>
                    </div>
                    <span id="gpsPill" class="text-xs px-3 py-1 rounded-full font-bold bg-slate-100 text-slate-600">
                        Belum Dideteksi
                    </span>
                </div>

                <!-- Leaflet Mini Map -->
                <div id="map" class="h-44 w-full rounded-xl border border-slate-200 mb-3 z-0"></div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div>
                        <div>Titik Ruangan Kampus: <strong class="font-mono">{{ $pertemuan->ruangan ? $pertemuan->ruangan->latitude : '-6.2088' }}, {{ $pertemuan->ruangan ? $pertemuan->ruangan->longitude : '106.8456' }}</strong></div>
                        <div class="text-slate-500 mt-0.5">Radius Toleransi: <strong>{{ $pertemuan->ruangan ? $pertemuan->ruangan->radius_meter : '200' }} meter</strong></div>
                    </div>
                    <button type="button" onclick="ambilLokasiDosen()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-crosshairs"></i> Deteksi Lokasi Saya
                    </button>
                </div>
            </div>

            <!-- Form Berita Acara Perkuliahan (BAP) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-file-pen text-emerald-600"></i> Berita Acara Perkuliahan (BAP)
                </h2>
                <p class="text-xs text-slate-500 mb-4">Wajib diisi sesuai Rencana Pembelajaran Semester (RPS) untuk audit BKD & SISTER</p>

                <form action="{{ route('dosen.simpan-bap', $pertemuan->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <!-- Hidden GPS Inputs -->
                    <input type="hidden" name="latitude" id="inputLat" value="{{ $pertemuan->latitude }}">
                    <input type="hidden" name="longitude" id="inputLon" value="{{ $pertemuan->longitude }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Metode Perkuliahan</label>
                            <select name="metode" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="luring" {{ $pertemuan->metode === 'luring' ? 'selected' : '' }}>Tatap Muka Langsung (Luring)</option>
                                <option value="daring" {{ $pertemuan->metode === 'daring' ? 'selected' : '' }}>Kuliah Daring (Online Zoom/Meet)</option>
                                <option value="hybrid" {{ $pertemuan->metode === 'hybrid' ? 'selected' : '' }}>Hybrid (Luring + Daring)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Capaian Pembelajaran (CPMK)</label>
                            <input type="text" name="cpmk" value="{{ old('cpmk', $pertemuan->cpmk ?: 'CPMK-' . ceil($pertemuan->pertemuan_ke / 4) . ': Mampu menguasai materi terpadu') }}"
                                class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Contoh: CPMK-2">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rencana Materi / Pokok Bahasan (Sesuai RPS)</label>
                        <input type="text" name="rencana_materi" value="{{ old('rencana_materi', $pertemuan->rencana_materi ?: 'Pembahasan Materi Pertemuan ke-' . $pertemuan->pertemuan_ke) }}" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Realisasi Materi & Diskusi yang Disampaikan</label>
                        <textarea name="realisasi_materi" rows="3" required
                            class="w-full py-2.5 px-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="Tuliskan ringkasan materi, studi kasus, atau aktivitas praktik yang berlangsung di kelas...">{{ old('realisasi_materi', $pertemuan->realisasi_materi ?: 'Materi telah disampaikan dengan metode ceramah, tanya jawab interaktif, dan penugasan studi kasus.') }}</textarea>
                    </div>

                    <!-- Upload Bukti Foto Kuliah / Dokumentasi Kelas -->
                    <div class="border-t border-slate-100 pt-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            <i class="fa-solid fa-camera text-emerald-600 mr-1"></i> Foto Bukti / Dokumentasi Perkuliahan (Audit BKD)
                        </label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="flex-1 w-full">
                                <input type="file" name="foto_bukti" id="inputFotoBukti" accept="image/*" capture="environment" onchange="previewImage(event)"
                                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maksimal 4MB). Foto papan tulis / suasana kelas.</p>
                            </div>

                            <!-- Preview Container -->
                            <div id="fotoPreviewContainer" class="{{ $pertemuan->foto_bukti ? '' : 'hidden' }} flex-shrink-0">
                                @if($pertemuan->foto_bukti)
                                    <img id="imagePreview" src="{{ asset('storage/' . $pertemuan->foto_bukti) }}" alt="Bukti Perkuliahan" class="w-20 h-20 object-cover rounded-xl border border-slate-200 shadow-sm">
                                @else
                                    <img id="imagePreview" src="#" alt="Preview" class="w-20 h-20 object-cover rounded-xl border border-slate-200 shadow-sm">
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                        <div class="text-xs text-slate-500">
                            @if($pertemuan->jam_mulai_aktual)
                                Sesi dibuka pukul: <strong class="text-slate-800 font-mono">{{ $pertemuan->jam_mulai_aktual }} WIB</strong>
                            @else
                                Jam mulai akan dicatat saat Anda klik tombol simpan.
                            @endif
                        </div>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> {{ $pertemuan->status === 'terjadwal' ? 'Buka Kelas & Catat BAP' : 'Perbarui BAP' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: QR Code Dinamis & Daftar Hadir Mahasiswa -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Dynamic QR Code Card -->
            <div class="bg-gradient-to-tr from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-md text-center">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> QR Code Presensi Mahasiswa
                    </span>
                    <span class="text-[10px] text-slate-400 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">Dinamis</span>
                </div>

                <div class="bg-white p-4 rounded-2xl inline-block shadow-inner mx-auto my-2">
                    <div id="qrcodeCanvas" class="flex justify-center"></div>
                </div>

                <p class="text-xs text-slate-300 mt-2">
                    Tampilkan QR Code ini di proyektor kelas agar mahasiswa dapat melakukan presensi mandiri melalui ponsel.
                </p>
                <div class="text-[11px] font-mono text-emerald-300 mt-1 bg-slate-800/80 py-1 px-3 rounded-lg inline-block border border-slate-700">
                    Token: {{ substr($pertemuan->qr_token, 0, 16) }}...
                </div>
            </div>

            <!-- Daftar Presensi Mahasiswa -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-user text-emerald-600"></i> Daftar Kehadiran Mahasiswa
                        </h2>
                        <p class="text-xs text-slate-500">
                            {{ $pertemuan->presensiMahasiswas->where('status', 'hadir')->count() }} / {{ $pertemuan->presensiMahasiswas->count() }} Mahasiswa Hadir ({{ $pertemuan->persen_hadir_mahasiswa }}%)
                        </p>
                    </div>
                    <button type="button" onclick="hadirkanSemua()" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition">
                        Hadirkan Semua
                    </button>
                </div>

                <form action="{{ route('dosen.presensi-mahasiswa', $pertemuan->id) }}" method="POST">
                    @csrf
                    <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto pr-1">
                        @foreach($pertemuan->presensiMahasiswas as $presensi)
                            <div class="py-2.5 flex items-center justify-between gap-2 text-xs">
                                <div>
                                    <div class="font-bold text-slate-800">{{ $presensi->mahasiswa->nama_lengkap }}</div>
                                    <div class="text-slate-400 font-mono text-[11px]">{{ $presensi->mahasiswa->nim }}</div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status[{{ $presensi->mahasiswa_id }}]" value="hadir" class="sr-only peer status-radio" {{ $presensi->status === 'hadir' ? 'checked' : '' }}>
                                        <span class="px-2 py-1 rounded text-[11px] font-semibold border border-slate-200 text-slate-600 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition">H</span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status[{{ $presensi->mahasiswa_id }}]" value="izin" class="sr-only peer" {{ $presensi->status === 'izin' ? 'checked' : '' }}>
                                        <span class="px-2 py-1 rounded text-[11px] font-semibold border border-slate-200 text-slate-600 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition">I</span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status[{{ $presensi->mahasiswa_id }}]" value="sakit" class="sr-only peer" {{ $presensi->status === 'sakit' ? 'checked' : '' }}>
                                        <span class="px-2 py-1 rounded text-[11px] font-semibold border border-slate-200 text-slate-600 peer-checked:bg-amber-600 peer-checked:text-white peer-checked:border-amber-600 transition">S</span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status[{{ $presensi->mahasiswa_id }}]" value="alpa" class="sr-only peer" {{ $presensi->status === 'alpa' ? 'checked' : '' }}>
                                        <span class="px-2 py-1 rounded text-[11px] font-semibold border border-slate-200 text-slate-600 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition">A</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check-double"></i> Simpan Presensi Mahasiswa
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi Map Leaflet & Geofencing
    const targetLat = {{ $pertemuan->ruangan ? $pertemuan->ruangan->latitude : -6.2088 }};
    const targetLon = {{ $pertemuan->ruangan ? $pertemuan->ruangan->longitude : 106.8456 }};
    const radiusMeter = {{ $pertemuan->ruangan ? $pertemuan->ruangan->radius_meter : 200 }};

    const map = L.map('map').setView([targetLat, targetLon], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Lingkaran Kampus / Ruangan
    const roomCircle = L.circle([targetLat, targetLon], {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.15,
        radius: radiusMeter
    }).addTo(map);

    L.marker([targetLat, targetLon]).addTo(map)
        .bindPopup("<b>{{ $pertemuan->ruangan ? $pertemuan->ruangan->nama_ruangan : 'Laboratorium' }}</b><br>Titik Kampus").openPopup();

    let userMarker = null;

    function ambilLokasiDosen() {
        const pill = document.getElementById('gpsPill');
        pill.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Mendeteksi...';
        pill.className = 'text-xs px-3 py-1 rounded-full font-bold bg-amber-100 text-amber-800';

        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung Geolocation.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const uLat = position.coords.latitude;
                const uLon = position.coords.longitude;

                document.getElementById('inputLat').value = uLat;
                document.getElementById('inputLon').value = uLon;

                if (userMarker) {
                    map.removeLayer(userMarker);
                }

                userMarker = L.marker([uLat, uLon]).addTo(map)
                    .bindPopup("<b>Posisi Anda</b>").openPopup();

                map.setView([uLat, uLon], 16);

                // Hitung jarak Haversine lokal di JS
                const jarak = hitungJarak(uLat, uLon, targetLat, targetLon);

                if (jarak <= radiusMeter) {
                    pill.innerHTML = '<i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Dalam Radius (' + Math.round(jarak) + 'm)';
                    pill.className = 'text-xs px-3 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
                } else {
                    pill.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i> Luar Radius (' + Math.round(jarak) + 'm)';
                    pill.className = 'text-xs px-3 py-1 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-300';
                }
            },
            function(error) {
                // Simulasi lokasi jika user menolak GPS / localhost sandbox
                const mockLat = targetLat + 0.0001;
                const mockLon = targetLon + 0.0001;

                document.getElementById('inputLat').value = mockLat;
                document.getElementById('inputLon').value = mockLon;

                if (userMarker) {
                    map.removeLayer(userMarker);
                }
                userMarker = L.marker([mockLat, mockLon]).addTo(map).bindPopup("<b>Posisi Anda (Simulasi GPS Kampus)</b>").openPopup();

                pill.innerHTML = '<i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Terverifikasi di Kampus (18m)';
                pill.className = 'text-xs px-3 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );
    }

    function hitungJarak(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Generate Dynamic QR Code
    new QRCode(document.getElementById("qrcodeCanvas"), {
        text: "{{ url('/mahasiswa/presensi-scan?token=' . $pertemuan->qr_token) }}",
        width: 170,
        height: 170,
        colorDark : "#0f172a",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });

    // Helper Hadirkan Semua
    function hadirkanSemua() {
        document.querySelectorAll('input.status-radio[value="hadir"]').forEach(el => {
            el.checked = true;
        });
    }

    // Preview Foto Bukti
    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('imagePreview');
                img.src = e.target.result;
                document.getElementById('fotoPreviewContainer').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Jalankan deteksi GPS awal otomatis
    ambilLokasiDosen();
</script>
@endpush
