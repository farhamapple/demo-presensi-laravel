<div class="space-y-6">
    {{-- 1. Kartu Profil Pegawai & Status Absensi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
        <div>
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Informasi Pegawai</h4>
            <div class="mt-2 space-y-1">
                <p class="text-base font-bold text-gray-900 dark:text-white">{{ $record->user->name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-500">NIP:</span> {{ $record->user->nip ?? '-' }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-500">Unit Kerja:</span> {{ $record->user->unit_kerja ?? '-' }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-500">Jabatan:</span> {{ $record->user->jabatan ?? '-' }}</p>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Detail Transaksi Absensi</h4>
            <div class="mt-2 space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-500">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $record->status->color() }}-100 text-{{ $record->status->color() }}-800 dark:bg-{{ $record->status->color() }}-900/40 dark:text-{{ $record->status->color() }}-300">
                        {{ $record->status->label() }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-500">Waktu Masuk:</span> {{ $record->check_in_at?->format('H:i:s') ?? '-' }} WIB</p>
                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-500">Waktu Pulang:</span> {{ $record->check_out_at?->format('H:i:s') ?? 'Belum check-out' }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-500">Perangkat:</span> {{ $record->device_name ?? 'Smartphone Pegawai' }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-500">IP Address:</span> {{ $record->ip_address ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- 2. Ringkasan Validasi Lokasi & Jarak --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900">
            <p class="text-xs font-medium text-blue-700 dark:text-blue-300">Titik Kantor Tujuan</p>
            <p class="text-sm font-bold text-blue-950 dark:text-blue-100 mt-1">{{ $record->officeLocation->name }}</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Radius aman: {{ $record->officeLocation->radius_meters ?? 100 }} m</p>
        </div>

        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Jarak dari Titik Kantor</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                {{ $record->distance_meters !== null ? number_format($record->distance_meters, 1) . ' meter' : '-' }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5">
                @if(($record->distance_meters ?? 0) <= ($record->officeLocation->radius_meters ?? 100))
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">✓ Berada dalam radius</span>
                @else
                    <span class="text-rose-600 dark:text-rose-400 font-medium">⚠ Di luar radius izin</span>
                @endif
            </p>
        </div>

        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Koordinat GPS Pegawai</p>
            <p class="text-xs font-mono font-bold text-gray-900 dark:text-white mt-1">
                {{ $record->check_in_latitude ?? '-' }}, {{ $record->check_in_longitude ?? '-' }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $record->notes ?? 'Tidak ada catatan' }}</p>
        </div>
    </div>

    {{-- 3. Visualisasi Peta Interaktif (Leaflet OpenStreetMap) --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                <span>Visualisasi Posisi Pegawai vs Area Kantor</span>
            </h4>
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Posisi Pegawai
                </span>
                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Area Kantor (Radius & Polygon)
                </span>
            </div>
        </div>

        <div id="attendance-map-{{ $record->id }}" class="w-full h-80 rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden shadow-inner bg-gray-100 z-0"></div>
    </div>

    {{-- Leaflet CSS & JS Assets --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initAttendanceMap();
        });

        // Trigger map initialization when Filament modal becomes visible
        setTimeout(function() {
            initAttendanceMap();
        }, 300);

        function initAttendanceMap() {
            const mapContainer = document.getElementById('attendance-map-{{ $record->id }}');
            if (!mapContainer || mapContainer.dataset.initialized) return;

            mapContainer.dataset.initialized = 'true';

            const userLat = {{ $record->check_in_latitude ?? 'null' }};
            const userLng = {{ $record->check_in_longitude ?? 'null' }};

            const officeLat = {{ $record->officeLocation->latitude ?? 'null' }};
            const officeLng = {{ $record->officeLocation->longitude ?? 'null' }};
            const officeRadius = {{ $record->officeLocation->radius_meters ?? 100 }};
            const polygonCoords = @json($record->officeLocation->polygon_coordinates ?? []);

            const defaultLat = userLat || officeLat || -6.175392;
            const defaultLng = userLng || officeLng || 106.827153;

            const map = L.map('attendance-map-{{ $record->id }}').setView([defaultLat, defaultLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const bounds = [];

            // 1. Gambar Radius & Marker Kantor
            if (officeLat !== null && officeLng !== null) {
                const officeMarker = L.marker([officeLat, officeLng])
                    .addTo(map)
                    .bindPopup('<b>{{ addslashes($record->officeLocation->name) }}</b><br>Titik Pusat Kantor');

                bounds.push([officeLat, officeLng]);

                // Lingkaran Radius Izin Absen
                L.circle([officeLat, officeLng], {
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 0.15,
                    radius: officeRadius
                }).addTo(map);
            }

            // 2. Gambar Polygon Geofence jika tersedia
            if (Array.isArray(polygonCoords) && polygonCoords.length >= 3) {
                const latLngs = polygonCoords.map(function(pt) {
                    const lat = pt.lat || pt.latitude;
                    const lng = pt.lng || pt.longitude;
                    bounds.push([lat, lng]);
                    return [lat, lng];
                });

                L.polygon(latLngs, {
                    color: '#059669',
                    weight: 2,
                    fillColor: '#34d399',
                    fillOpacity: 0.2
                }).addTo(map).bindPopup('Area Polygon Geofence Kantor');
            }

            // 3. Gambar Posisi Pegawai saat Absen
            if (userLat !== null && userLng !== null) {
                const userColor = '{{ $record->status === \App\Enums\AttendanceStatus::Present ? "#10b981" : ($record->status === \App\Enums\AttendanceStatus::Late ? "#f59e0b" : "#ef4444") }}';
                
                const userIcon = L.divIcon({
                    className: 'custom-user-marker',
                    html: `<div style="background-color: ${userColor}; width: 18px; height: 18px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 6px rgba(0,0,0,0.4);"></div>`,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9]
                });

                L.marker([userLat, userLng], { icon: userIcon })
                    .addTo(map)
                    .bindPopup('<b>{{ addslashes($record->user->name) }}</b><br>Posisi Absen: {{ $record->check_in_at?->format("H:i:s") }}<br>Jarak: {{ number_format($record->distance_meters ?? 0, 1) }} m')
                    .openPopup();

                bounds.push([userLat, userLng]);

                // Garis putus-putus antara titik pegawai dan pusat kantor
                if (officeLat !== null && officeLng !== null) {
                    L.polyline([[userLat, userLng], [officeLat, officeLng]], {
                        color: userColor,
                        weight: 2,
                        dashArray: '5, 5',
                        opacity: 0.8
                    }).addTo(map);
                }
            }

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [40, 40] });
            }

            setTimeout(function() {
                map.invalidateSize();
            }, 400);
        }
    </script>
</div>

