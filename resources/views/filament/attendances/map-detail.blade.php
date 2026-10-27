@php
    $geofencingService = app(\App\Services\GeofencingService::class);
    $polygon = $record->officeLocation->polygon_coordinates ?? [];
    $hasPolygon = is_array($polygon) && count($polygon) >= 3;

    $userLat = $record->check_in_latitude !== null ? (float) $record->check_in_latitude : null;
    $userLng = $record->check_in_longitude !== null ? (float) $record->check_in_longitude : null;

    $officeLat = $record->officeLocation->latitude !== null ? (float) $record->officeLocation->latitude : null;
    $officeLng = $record->officeLocation->longitude !== null ? (float) $record->officeLocation->longitude : null;
    $officeRadius = (int) ($record->officeLocation->radius_meters ?? 100);

    $isInsideGeofence = false;
    if ($userLat !== null && $userLng !== null) {
        if ($hasPolygon) {
            $isInsideGeofence = $geofencingService->isPointInPolygon($userLat, $userLng, $polygon);
        } else {
            $isInsideGeofence = ($record->distance_meters ?? 0) <= $officeRadius;
        }
    }

    $initials = collect(explode(' ', $record->user->name))
        ->map(fn ($segment) => mb_substr($segment, 0, 1))
        ->take(2)
        ->join('');
@endphp

<div 
    class="space-y-5"
    x-data="attendanceDetailMap({
        mapId: 'attendance-map-{{ $record->id }}',
        userLat: {{ $userLat !== null ? $userLat : 'null' }},
        userLng: {{ $userLng !== null ? $userLng : 'null' }},
        userName: @js($record->user->name),
        userNip: @js($record->user->nip ?? '-'),
        userCheckInTime: @js($record->check_in_at?->format('H:i:s') ?? '-'),
        userStatus: @js($record->status->label()),
        userStatusColor: @js($record->status->color()),
        distance: @js($record->distance_meters !== null ? number_format($record->distance_meters, 1) : null),
        isInsideGeofence: @js($isInsideGeofence),
        officeName: @js($record->officeLocation->name),
        officeAddress: @js($record->officeLocation->address ?? '-'),
        officeLat: {{ $officeLat !== null ? $officeLat : 'null' }},
        officeLng: {{ $officeLng !== null ? $officeLng : 'null' }},
        officeRadius: {{ $officeRadius }},
        polygonCoords: @js($polygon),
    })"
    x-init="init()"
>
    {{-- 1. Kartu Profil Pegawai & Detail Transaksi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Identitas Pegawai --}}
        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs flex gap-3.5 items-start">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-base shadow-sm shrink-0">
                {{ $initials ?: 'PG' }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">{{ $record->user->name }}</h3>
                    @if($record->user->nip)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            NIP: {{ $record->user->nip }}
                        </span>
                    @endif
                </div>
                <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-300">
                    <p class="flex items-center gap-1.5 truncate">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span><strong class="font-medium text-gray-700 dark:text-gray-200">Unit:</strong> {{ $record->user->unit_kerja ?? 'Pusat' }}</span>
                    </p>
                    <p class="flex items-center gap-1.5 truncate">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span><strong class="font-medium text-gray-700 dark:text-gray-200">Jabatan:</strong> {{ $record->user->jabatan ?? 'Pegawai' }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Detail Transaksi Presensi --}}
        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status Presensi</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $record->status->color() }}-100 text-{{ $record->status->color() }}-800 dark:bg-{{ $record->status->color() }}-950/60 dark:text-{{ $record->status->color() }}-300 border border-{{ $record->status->color() }}-300 dark:border-{{ $record->status->color() }}-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $record->status->color() }}-500"></span>
                    {{ $record->status->label() }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <p class="text-gray-500 dark:text-gray-400 text-[11px]">Check-In</p>
                    <p class="font-bold text-gray-900 dark:text-white mt-0.5">
                        {{ $record->check_in_at ? $record->check_in_at->format('H:i:s') . ' WIB' : '-' }}
                    </p>
                </div>
                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-800">
                    <p class="text-gray-500 dark:text-gray-400 text-[11px]">Check-Out</p>
                    <p class="font-bold text-gray-900 dark:text-white mt-0.5">
                        {{ $record->check_out_at ? $record->check_out_at->format('H:i:s') . ' WIB' : 'Belum checkout' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-0.5">
                <span class="truncate">📱 {{ $record->device_name ?? 'Smartphone Pegawai' }}</span>
                <span class="font-mono text-[11px] shrink-0">IP: {{ $record->ip_address ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- 2. Ringkasan Validasi Posisi & Geofence (3 Kolom) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        {{-- Status Perimeter --}}
        <div class="p-3.5 rounded-xl border {{ $isInsideGeofence ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/60' : 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800/60' }}">
            <p class="text-xs font-medium {{ $isInsideGeofence ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">Validasi Geofence</p>
            <div class="flex items-center gap-1.5 mt-1">
                @if($isInsideGeofence)
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Di Dalam Area</span>
                @else
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-bold text-rose-900 dark:text-rose-100">Di Luar Area</span>
                @endif
            </div>
            <p class="text-[11px] mt-1 {{ $isInsideGeofence ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                {{ $hasPolygon ? 'Boundary Polygon Geofence' : 'Radius Kantor (' . $officeRadius . 'm)' }}
            </p>
        </div>

        {{-- Jarak Pengukuran --}}
        <div class="p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Jarak ke Titik Kantor</p>
            <p class="text-base font-extrabold text-gray-900 dark:text-white mt-1">
                {{ $record->distance_meters !== null ? number_format($record->distance_meters, 1) . ' m' : '-' }}
            </p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                Tujuan: <strong>{{ $record->officeLocation->name }}</strong>
            </p>
        </div>

        {{-- Koordinat GPS Pegawai --}}
        <div class="p-3.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xs">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Koordinat Titik Absen</p>
            <p class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 mt-1 truncate">
                {{ $userLat !== null && $userLng !== null ? number_format($userLat, 6) . ', ' . number_format($userLng, 6) : 'Tidak terekam' }}
            </p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                {{ $record->notes ?: 'Terekam via GPS perangkat' }}
            </p>
        </div>
    </div>

    {{-- 3. Visualisasi Peta (Leaflet Map) --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs overflow-hidden">
        {{-- Toolbar / Header Peta --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/60 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                    <span>Visualisasi Posisi Pegawai vs Area Kantor</span>
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Peta interaktif menampilkan titik absen dan boundary kantor tujuan</p>
            </div>

            {{-- Legend & Action --}}
            <div class="flex items-center gap-3 text-xs flex-wrap">
                <span class="inline-flex items-center gap-1.5 font-medium text-blue-700 dark:text-blue-300">
                    <span class="w-3 h-3 rounded-full bg-blue-600 border border-white shadow-xs inline-block"></span>
                    <span>Titik Absen</span>
                </span>
                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300">
                    <span class="w-3 h-3 rounded-full bg-slate-800 border border-white shadow-xs inline-block"></span>
                    <span>Kantor</span>
                </span>
                <span class="inline-flex items-center gap-1.5 font-medium text-sky-700 dark:text-sky-300">
                    <span class="w-3 h-3 rounded-sm bg-sky-500/40 border border-blue-900 inline-block"></span>
                    <span>Boundary</span>
                </span>
                <button 
                    type="button" 
                    @click="resetView()" 
                    class="px-2.5 py-1 rounded-md text-xs font-semibold bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 shadow-2xs transition"
                >
                    Reset Tampilan
                </button>
            </div>
        </div>

        {{-- Map Container --}}
        <div class="relative w-full h-96 bg-slate-100 dark:bg-slate-900">
            <div id="attendance-map-{{ $record->id }}" class="w-full h-full z-0"></div>

            @if($userLat === null || $userLng === null)
                <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-10">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg max-w-sm text-center">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Koordinat Absen Tidak Tersedia</p>
                        <p class="text-xs text-gray-500 mt-1">Data absensi ini tidak mencantumkan koordinat GPS pegawai saat check-in.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function attendanceDetailMap(data) {
        return {
            map: null,
            bounds: [],

            init() {
                this.$nextTick(() => {
                    this.ensureLeafletLoaded().then(() => {
                        setTimeout(() => {
                            this.renderMap();
                        }, 200);
                    });
                });
            },

            async ensureLeafletLoaded() {
                if (window.L) {
                    return Promise.resolve();
                }

                return new Promise((resolve, reject) => {
                    if (!document.getElementById('leaflet-css')) {
                        const link = document.createElement('link');
                        link.id = 'leaflet-css';
                        link.rel = 'stylesheet';
                        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        document.head.appendChild(link);
                    }

                    if (window.L) return resolve();

                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject();
                    document.head.appendChild(script);
                });
            },

            renderMap() {
                const el = document.getElementById(data.mapId);
                if (!el) return;

                // Bersihkan instans map jika sudah pernah dibuat
                if (this.map) {
                    this.map.remove();
                    this.map = null;
                }

                const centerLat = data.userLat || data.officeLat || -6.175392;
                const centerLng = data.userLng || data.officeLng || 106.827153;

                this.map = L.map(data.mapId, {
                    zoomControl: true,
                    attributionControl: false
                }).setView([centerLat, centerLng], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(this.map);

                L.control.attribution({ position: 'bottomright', prefix: false })
                    .addAttribution('&copy; OpenStreetMap')
                    .addTo(this.map);

                this.bounds = [];

                // 1. Gambar Area Kantor (Polygon Geofence atau Radius Circle)
                if (Array.isArray(data.polygonCoords) && data.polygonCoords.length >= 3) {
                    const latLngs = data.polygonCoords.map(pt => {
                        const lat = pt.lat !== undefined ? pt.lat : pt.latitude;
                        const lng = pt.lng !== undefined ? pt.lng : pt.longitude;
                        this.bounds.push([lat, lng]);
                        return [lat, lng];
                    });

                    const polygonLayer = L.polygon(latLngs, {
                        color: '#002d62',
                        weight: 3,
                        fillColor: '#0284c7',
                        fillOpacity: 0.25,
                        dashArray: null
                    }).addTo(this.map);

                    polygonLayer.bindPopup(`
                        <div class="p-1 space-y-1 text-xs">
                            <div class="font-bold text-sm text-slate-900">${data.officeName}</div>
                            <div class="text-slate-500">Tipe: <strong>Polygon Geofence</strong></div>
                            <div class="text-slate-500">${data.officeAddress}</div>
                        </div>
                    `);
                }

                // Gambar Lingkaran Radius Kantor
                if (data.officeLat !== null && data.officeLng !== null) {
                    this.bounds.push([data.officeLat, data.officeLng]);

                    L.circle([data.officeLat, data.officeLng], {
                        radius: data.officeRadius || 100,
                        color: '#10b981',
                        weight: 1.5,
                        dashArray: '4, 4',
                        fillColor: '#10b981',
                        fillOpacity: 0.08
                    }).addTo(this.map).bindPopup(`Radius Izin Kantor: ${data.officeRadius} meter`);

                    // Marker Pusat Kantor
                    const officeIcon = L.divIcon({
                        className: 'office-pin-marker',
                        html: `
                            <div class="relative flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-slate-900 text-white shadow-lg border-2 border-white ring-2 ring-slate-700 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <span class="mt-1 px-1.5 py-0.5 rounded bg-slate-900/90 text-[10px] font-bold text-white shadow-md whitespace-nowrap">
                                    Kantor
                                </span>
                            </div>
                        `,
                        iconSize: [36, 48],
                        iconAnchor: [18, 16],
                        popupAnchor: [0, -20]
                    });

                    L.marker([data.officeLat, data.officeLng], { icon: officeIcon })
                        .addTo(this.map)
                        .bindPopup(`
                            <div class="p-1 space-y-1 text-xs">
                                <div class="font-bold text-sm text-slate-900">${data.officeName}</div>
                                <div class="text-slate-500">Titik Pusat Kantor</div>
                                <div class="text-slate-400 font-mono text-[11px]">${data.officeLat.toFixed(6)}, ${data.officeLng.toFixed(6)}</div>
                            </div>
                        `);
                }

                // 2. Gambar Titik Pegawai Absen (Highlight Pulse Pin)
                if (data.userLat !== null && data.userLng !== null) {
                    this.bounds.push([data.userLat, data.userLng]);

                    const statusBg = data.isInsideGeofence ? 'bg-blue-600' : 'bg-rose-600';
                    const ringColor = data.isInsideGeofence ? 'bg-blue-400' : 'bg-rose-400';
                    const labelBadge = data.isInsideGeofence ? 'bg-blue-700' : 'bg-rose-700';

                    const userIcon = L.divIcon({
                        className: 'employee-pulse-marker',
                        html: `
                            <div class="relative flex flex-col items-center">
                                <span class="absolute top-0 -left-1 inline-flex h-10 w-10 rounded-full ${ringColor} opacity-70 employee-pulse-ring"></span>
                                <div class="relative w-8 h-8 rounded-full ${statusBg} text-white shadow-xl border-2 border-white ring-2 ring-blue-300 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span class="mt-1 px-1.5 py-0.5 rounded ${labelBadge} text-[10px] font-bold text-white shadow-md whitespace-nowrap">
                                    Titik Absen
                                </span>
                            </div>
                        `,
                        iconSize: [36, 48],
                        iconAnchor: [18, 16],
                        popupAnchor: [0, -20]
                    });

                    const userMarker = L.marker([data.userLat, data.userLng], { 
                        icon: userIcon,
                        zIndexOffset: 1000 
                    })
                    .addTo(this.map)
                    .bindPopup(`
                        <div class="p-1 space-y-1 text-xs min-w-[160px]">
                            <div class="font-bold text-sm text-gray-900">${data.userName}</div>
                            <div class="text-gray-600">Waktu Absen: <strong>${data.userCheckInTime} WIB</strong></div>
                            <div class="text-gray-600">Status: <span class="font-semibold text-blue-600">${data.userStatus}</span></div>
                            <div class="text-gray-600">Jarak: <strong class="text-emerald-600">${data.distance || 0} m</strong></div>
                            <div class="pt-1 text-[11px] font-mono text-gray-400 border-t border-gray-100">
                                ${data.userLat.toFixed(6)}, ${data.userLng.toFixed(6)}
                            </div>
                        </div>
                    `);

                    // Otomatis buka popup titik absen pegawai agar langsung terlihat detailnya
                    setTimeout(() => {
                        userMarker.openPopup();
                    }, 400);

                    // 3. Garis Pengukuran Jarak Antara Pegawai dan Kantor
                    if (data.officeLat !== null && data.officeLng !== null) {
                        const distanceLine = L.polyline([[data.userLat, data.userLng], [data.officeLat, data.officeLng]], {
                            color: '#2563eb',
                            weight: 2.5,
                            dashArray: '6, 6',
                            opacity: 0.85
                        }).addTo(this.map);

                        if (data.distance) {
                            distanceLine.bindTooltip(`Jarak: ${data.distance} m`, {
                                permanent: true,
                                direction: 'center',
                                className: 'distance-tooltip'
                            });
                        }
                    }
                }

                // 4. Auto-fit bounds agar semua titik terlihat
                this.resetView();

                // Invalidate size setelah modal selesai animasi render
                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize(true);
                    }
                }, 350);
                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize(true);
                    }
                }, 700);
            },

            resetView() {
                if (!this.map || this.bounds.length === 0) return;

                if (this.bounds.length === 1) {
                    this.map.setView(this.bounds[0], 17);
                } else {
                    this.map.fitBounds(this.bounds, { 
                        padding: [50, 50],
                        maxZoom: 17 
                    });
                }
            }
        };
    }
</script>
