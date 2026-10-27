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

    $statusColor = $record->status->color();
    $badgeClass = match ($statusColor) {
        'success' => 'att-badge-success',
        'warning' => 'att-badge-warning',
        default => 'att-badge-danger',
    };
    $dotColor = match ($statusColor) {
        'success' => '#22c55e',
        'warning' => '#f59e0b',
        default => '#ef4444',
    };

    $mapConfig = [
        'mapId' => 'attendance-map-'.$record->id,
        'userLat' => $userLat,
        'userLng' => $userLng,
        'userName' => $record->user->name,
        'userNip' => $record->user->nip ?? '-',
        'userCheckInTime' => $record->check_in_at?->format('H:i:s') ?? '-',
        'userStatus' => $record->status->label(),
        'userStatusColor' => $statusColor,
        'distance' => $record->distance_meters !== null ? number_format($record->distance_meters, 1) : null,
        'isInsideGeofence' => $isInsideGeofence,
        'officeName' => $record->officeLocation->name,
        'officeAddress' => $record->officeLocation->address ?? '-',
        'officeLat' => $officeLat,
        'officeLng' => $officeLng,
        'officeRadius' => $officeRadius,
        'polygonCoords' => $polygon,
    ];
@endphp

{{-- Leaflet Core CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<style>
    .att-modal {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        font-family: inherit;
        color: #1e293b;
    }
    .dark .att-modal {
        color: #f1f5f9;
    }

    /* Grid Layouts */
    .att-grid-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .att-grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .att-grid-3 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .att-grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* Kartu Card */
    .att-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }
    .dark .att-card {
        background-color: #1e293b;
        border-color: #334155;
    }

    .att-card-sub {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
    }
    .dark .att-card-sub {
        background-color: rgba(15, 23, 42, 0.5);
        border-color: #334155;
    }

    /* Geofence Alert Box */
    .att-card-success {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
    }
    .dark .att-card-success {
        background-color: rgba(6, 78, 59, 0.25);
        border-color: rgba(6, 95, 70, 0.6);
    }

    .att-card-danger {
        background-color: #fff1f2;
        border: 1px solid #fecdd3;
    }
    .dark .att-card-danger {
        background-color: rgba(136, 19, 55, 0.25);
        border-color: rgba(159, 18, 57, 0.6);
    }

    /* Avatar */
    .att-avatar {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Status Badges */
    .att-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.2rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.25;
    }
    .att-badge-dot {
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 9999px;
    }
    .att-badge-success {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
    }
    .dark .att-badge-success {
        background-color: rgba(22, 101, 52, 0.4);
        color: #86efac;
        border-color: #166534;
    }
    .att-badge-warning {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fcd34d;
    }
    .dark .att-badge-warning {
        background-color: rgba(180, 83, 9, 0.4);
        color: #fcd34d;
        border-color: #b45309;
    }
    .att-badge-danger {
        background-color: #ffe4e6;
        color: #be123c;
        border: 1px solid #fda4af;
    }
    .dark .att-badge-danger {
        background-color: rgba(190, 18, 60, 0.4);
        color: #fda4af;
        border-color: #be123c;
    }

    /* Peta Wrapper & Canvas */
    .att-map-wrapper {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }
    .dark .att-map-wrapper {
        border-color: #334155;
        background-color: #1e293b;
    }
    .att-map-header {
        padding: 0.75rem 1rem;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .dark .att-map-header {
        background-color: rgba(15, 23, 42, 0.5);
        border-color: #334155;
    }
    .att-map-body {
        position: relative;
        width: 100% !important;
        height: 420px !important;
        min-height: 420px !important;
        background-color: #f1f5f9;
    }
    .dark .att-map-body {
        background-color: #0f172a;
    }
    .att-map-canvas {
        width: 100% !important;
        height: 100% !important;
        min-height: 420px !important;
        position: absolute !important;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }

    /* PENTING: Kunci z-index Leaflet agar layer SVG vektor selalu berada di atas tile peta */
    .att-map-body .leaflet-pane.leaflet-tile-pane {
        z-index: 200 !important;
    }
    .att-map-body .leaflet-pane.leaflet-overlay-pane {
        z-index: 400 !important;
    }
    .att-map-body .leaflet-pane.leaflet-overlay-pane svg {
        z-index: 400 !important;
        position: relative !important;
        display: block !important;
        pointer-events: auto !important;
    }
    .att-map-body .leaflet-pane.leaflet-overlay-pane svg path {
        pointer-events: auto !important;
    }
    .att-map-body .leaflet-pane.leaflet-shadow-pane {
        z-index: 500 !important;
    }
    .att-map-body .leaflet-pane.leaflet-marker-pane {
        z-index: 600 !important;
    }
    .att-map-body .leaflet-pane.leaflet-tooltip-pane {
        z-index: 650 !important;
    }
    .att-map-body .leaflet-pane.leaflet-popup-pane {
        z-index: 700 !important;
    }

    /* Tooltip & Popups */
    .distance-tooltip {
        background-color: #0f172a !important;
        border: 1px solid #334155 !important;
        color: #38bdf8 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 3px 8px !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25) !important;
    }
    .distance-tooltip::before {
        display: none !important;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 10px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2) !important;
        padding: 2px !important;
    }
    .leaflet-popup-content {
        margin: 8px 12px !important;
        line-height: 1.4 !important;
    }

    /* Pulse animation pin pegawai */
    @keyframes att-pulse {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.6); opacity: 0.25; }
        100% { transform: scale(2.2); opacity: 0; }
    }
    .employee-pulse-ring {
        animation: att-pulse 2s cubic-bezier(0.2, 0.8, 0.2, 1) infinite;
    }

    /* Reset button */
    .att-btn {
        padding: 0.35rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .att-btn:hover {
        background-color: #f1f5f9;
        border-color: #94a3b8;
    }
    .dark .att-btn {
        background-color: #1e293b;
        color: #cbd5e1;
        border-color: #475569;
    }
    .dark .att-btn:hover {
        background-color: #334155;
    }
</style>

<div 
    class="att-modal"
    x-data="window.attendanceDetailMap ? window.attendanceDetailMap(@js($mapConfig)) : {
        init() {
            const check = setInterval(() => {
                if (window.attendanceDetailMap) {
                    clearInterval(check);
                    const comp = window.attendanceDetailMap(@js($mapConfig));
                    Object.assign(this, comp);
                    this.init();
                }
            }, 50);
            setTimeout(() => clearInterval(check), 5000);
        }
    }"
>
    {{-- 1. Kartu Profil Pegawai & Detail Transaksi --}}
    <div class="att-grid-2">
        {{-- Identitas Pegawai --}}
        <div class="att-card" style="display: flex; gap: 0.875rem; align-items: flex-start;">
            <div class="att-avatar">
                {{ $initials ?: 'PG' }}
            </div>
            <div style="min-width: 0; flex: 1;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $record->user->name }}
                    </h3>
                    @if($record->user->nip)
                        <span style="display: inline-flex; align-items: center; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 11px; font-family: monospace; background-color: #f1f5f9; color: #475569;">
                            NIP: {{ $record->user->nip }}
                        </span>
                    @endif
                </div>
                <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.25rem; font-size: 0.75rem; color: #64748b;">
                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                        <svg style="width: 0.875rem; height: 0.875rem; color: #94a3b8; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span><strong style="color: #334155;">Unit:</strong> {{ $record->user->unit_kerja ?? 'Pusat' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                        <svg style="width: 0.875rem; height: 0.875rem; color: #94a3b8; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span><strong style="color: #334155;">Jabatan:</strong> {{ $record->user->jabatan ?? 'Pegawai' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Transaksi Presensi --}}
        <div class="att-card" style="display: flex; flex-direction: column; gap: 0.625rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Status Presensi
                </span>
                <span class="att-badge {{ $badgeClass }}">
                    <span class="att-badge-dot" style="background-color: {{ $dotColor }};"></span>
                    {{ $record->status->label() }}
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; font-size: 0.75rem; padding-top: 0.25rem;">
                <div class="att-card-sub">
                    <p style="margin: 0; color: #64748b; font-size: 11px;">Check-In</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #0f172a;">
                        {{ $record->check_in_at ? $record->check_in_at->format('H:i:s') . ' WIB' : '-' }}
                    </p>
                </div>
                <div class="att-card-sub">
                    <p style="margin: 0; color: #64748b; font-size: 11px;">Check-Out</p>
                    <p style="margin: 0.25rem 0 0 0; font-weight: 700; color: #0f172a;">
                        {{ $record->check_out_at ? $record->check_out_at->format('H:i:s') . ' WIB' : 'Belum checkout' }}
                    </p>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: #64748b; padding-top: 0.125rem;">
                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">📱 {{ $record->device_name ?? 'Smartphone Pegawai' }}</span>
                <span style="font-family: monospace; font-size: 11px; flex-shrink: 0;">IP: {{ $record->ip_address ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- 2. Ringkasan Validasi Posisi & Geofence (3 Kolom) --}}
    <div class="att-grid-3">
        {{-- Status Perimeter --}}
        <div class="att-card {{ $isInsideGeofence ? 'att-card-success' : 'att-card-danger' }}">
            <p style="margin: 0; font-size: 0.75rem; font-weight: 600; color: {{ $isInsideGeofence ? '#15803d' : '#be123c' }};">
                Validasi Geofence
            </p>
            <div style="display: flex; align-items: center; gap: 0.375rem; margin-top: 0.35rem;">
                @if($isInsideGeofence)
                    <svg style="width: 1.25rem; height: 1.25rem; color: #16a34a; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span style="font-size: 0.875rem; font-weight: 700; color: #14532d;">Di Dalam Area</span>
                @else
                    <svg style="width: 1.25rem; height: 1.25rem; color: #dc2626; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span style="font-size: 0.875rem; font-weight: 700; color: #881337;">Di Luar Area</span>
                @endif
            </div>
            <p style="margin: 0.25rem 0 0 0; font-size: 11px; color: {{ $isInsideGeofence ? '#16a34a' : '#e11d48' }};">
                {{ $hasPolygon ? 'Boundary Polygon Geofence' : 'Radius Kantor (' . $officeRadius . 'm)' }}
            </p>
        </div>

        {{-- Jarak Pengukuran --}}
        <div class="att-card">
            <p style="margin: 0; font-size: 0.75rem; font-weight: 600; color: #64748b;">Jarak ke Titik Kantor</p>
            <p style="margin: 0.35rem 0 0 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">
                {{ $record->distance_meters !== null ? number_format($record->distance_meters, 1) . ' m' : '-' }}
            </p>
            <p style="margin: 0.25rem 0 0 0; font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                Tujuan: <strong style="color: #334155;">{{ $record->officeLocation->name }}</strong>
            </p>
        </div>

        {{-- Koordinat GPS Pegawai --}}
        <div class="att-card">
            <p style="margin: 0; font-size: 0.75rem; font-weight: 600; color: #64748b;">Koordinat Titik Absen</p>
            <p style="margin: 0.35rem 0 0 0; font-size: 0.75rem; font-family: monospace; font-weight: 700; color: #2563eb; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $userLat !== null && $userLng !== null ? number_format($userLat, 6) . ', ' . number_format($userLng, 6) : 'Tidak terekam' }}
            </p>
            <p style="margin: 0.25rem 0 0 0; font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $record->notes ?: 'Terekam via GPS perangkat' }}
            </p>
        </div>
    </div>

    {{-- 3. Visualisasi Peta (Leaflet Map) --}}
    <div class="att-map-wrapper">
        {{-- Toolbar / Header Peta --}}
        <div class="att-map-header">
            <div>
                <h4 style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #0f172a;">
                    Visualisasi Posisi Pegawai vs Area Kantor
                </h4>
                <p style="margin: 0.15rem 0 0 0; font-size: 0.75rem; color: #64748b;">
                    Peta interaktif menampilkan titik presensi pegawai dan perimeter kantor
                </p>
            </div>

            {{-- Legend & Reset Action --}}
            <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.75rem; flex-wrap: wrap;">
                <span style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; color: #1d4ed8;">
                    <span style="width: 0.75rem; height: 0.75rem; border-radius: 9999px; background-color: #2563eb; border: 1.5px solid #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.15); display: inline-block;"></span>
                    <span>Titik Absen</span>
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; color: #334155;">
                    <span style="width: 0.75rem; height: 0.75rem; border-radius: 9999px; background-color: #0f172a; border: 1.5px solid #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.15); display: inline-block;"></span>
                    <span>Kantor</span>
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; color: #002d62;">
                    <span style="width: 0.75rem; height: 0.75rem; border-radius: 0.2rem; background-color: #0284c7; opacity: 0.7; border: 1.5px solid #002d62; display: inline-block;"></span>
                    <span>Boundary Kantor</span>
                </span>
                <button 
                    type="button" 
                    @click="resetView()" 
                    class="att-btn"
                >
                    Reset Tampilan
                </button>
            </div>
        </div>

        {{-- Map Container dengan tinggi eksplisit 420px --}}
        <div class="att-map-body">
            <div id="attendance-map-{{ $record->id }}" class="att-map-canvas"></div>

            @if($userLat === null || $userLng === null)
                <div style="position: absolute; inset: 0; background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(2px); display: flex; align-items: center; justify-content: center; padding: 1rem; z-index: 50;">
                    <div style="background-color: #ffffff; padding: 1rem 1.25rem; border-radius: 0.75rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); max-width: 22rem; text-align: center;">
                        <p style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #0f172a;">Koordinat Absen Tidak Tersedia</p>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.75rem; color: #64748b;">Data absensi ini tidak mencantumkan koordinat GPS pegawai saat check-in.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    if (typeof window.attendanceDetailMap !== 'function') {
        window.attendanceDetailMap = function(data) {
            return {
                map: null,
                bounds: [],
                isRendered: false,
                resizeObserver: null,

                init() {
                    this.$nextTick(() => {
                        this.ensureLeafletLoaded().then(() => {
                            setTimeout(() => {
                                this.renderMap();
                            }, 100);
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
                    if (this.isRendered && this.map) {
                        this.resetView();
                        return;
                    }

                    const el = document.getElementById(data.mapId);
                    if (!el) return;

                    // Bersihkan jika ada instance map lama pada container
                    if (this.map) {
                        try {
                            this.map.remove();
                        } catch (e) {}
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

                    // 1. Gambar Boundary Area Kantor (Polygon Geofence)
                    const hasValidPolygon = Array.isArray(data.polygonCoords) && data.polygonCoords.length >= 3;
                    if (hasValidPolygon) {
                        const latLngs = data.polygonCoords.map(pt => {
                            const lat = pt.lat !== undefined ? pt.lat : pt.latitude;
                            const lng = pt.lng !== undefined ? pt.lng : pt.longitude;
                            this.bounds.push([lat, lng]);
                            return [lat, lng];
                        });

                        const polygonLayer = L.polygon(latLngs, {
                            color: '#002d62',
                            weight: 3.5,
                            opacity: 1,
                            fillColor: '#0284c7',
                            fillOpacity: 0.45,
                            smoothFactor: 1
                        }).addTo(this.map);

                        polygonLayer.bringToFront();

                        polygonLayer.bindPopup(`
                            <div style="font-size: 12px; padding: 2px;">
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">${data.officeName}</div>
                                <div style="color: #0369a1; font-weight: 600; margin-top: 2px;">Boundary Polygon Geofence</div>
                                <div style="color: #64748b; margin-top: 2px;">${data.officeAddress}</div>
                            </div>
                        `);
                    } else if (data.officeLat !== null && data.officeLng !== null) {
                        // Fallback jika tidak ada polygon: gunakan lingkaran radius
                        this.bounds.push([data.officeLat, data.officeLng]);

                        const circleLayer = L.circle([data.officeLat, data.officeLng], {
                            radius: data.officeRadius || 100,
                            color: '#002d62',
                            weight: 2.5,
                            opacity: 1,
                            fillColor: '#0284c7',
                            fillOpacity: 0.35,
                            dashArray: '4, 4'
                        }).addTo(this.map).bindPopup(`Radius Izin Kantor: ${data.officeRadius} meter`);

                        circleLayer.bringToFront();
                    }

                    // Marker Titik Pusat Kantor
                    if (data.officeLat !== null && data.officeLng !== null) {
                        this.bounds.push([data.officeLat, data.officeLng]);

                        const officeIcon = L.divIcon({
                            className: 'office-pin-marker',
                            html: `
                                <div style="position: relative; display: flex; flex-direction: column; align-items: center;">
                                    <div style="width: 2rem; height: 2rem; border-radius: 9999px; background-color: #0f172a; color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.3); border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center;">
                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <span style="margin-top: 0.25rem; padding: 0.125rem 0.375rem; border-radius: 0.25rem; background-color: rgba(15, 23, 42, 0.9); font-size: 10px; font-weight: 700; color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.2); white-space: nowrap;">
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
                                <div style="font-size: 12px; padding: 2px;">
                                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">${data.officeName}</div>
                                    <div style="color: #64748b; margin-top: 2px;">Titik Pusat Kantor</div>
                                    <div style="color: #94a3b8; font-family: monospace; font-size: 11px; margin-top: 2px;">${data.officeLat.toFixed(6)}, ${data.officeLng.toFixed(6)}</div>
                                </div>
                            `);
                    }

                    // 2. Gambar Titik Pegawai Absen (Highlight Pulse Pin)
                    if (data.userLat !== null && data.userLng !== null) {
                        this.bounds.push([data.userLat, data.userLng]);

                        const statusBg = data.isInsideGeofence ? '#2563eb' : '#e11d48';
                        const ringColor = data.isInsideGeofence ? '#60a5fa' : '#fb7185';
                        const labelBadge = data.isInsideGeofence ? '#1d4ed8' : '#be123c';

                        const userIcon = L.divIcon({
                            className: 'employee-pulse-marker',
                            html: `
                                <div style="position: relative; display: flex; flex-direction: column; align-items: center;">
                                    <span class="employee-pulse-ring" style="position: absolute; top: 0; left: -2px; display: inline-flex; width: 2.25rem; height: 2.25rem; border-radius: 9999px; background-color: ${ringColor}; opacity: 0.7;"></span>
                                    <div style="position: relative; width: 2rem; height: 2rem; border-radius: 9999px; background-color: ${statusBg}; color: #ffffff; box-shadow: 0 4px 8px rgba(0,0,0,0.3); border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center;">
                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <span style="margin-top: 0.25rem; padding: 0.125rem 0.375rem; border-radius: 0.25rem; background-color: ${labelBadge}; font-size: 10px; font-weight: 700; color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.2); white-space: nowrap;">
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
                            <div style="font-size: 12px; min-width: 160px; padding: 2px;">
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">${data.userName}</div>
                                <div style="color: #475569; margin-top: 2px;">Waktu: <strong>${data.userCheckInTime} WIB</strong></div>
                                <div style="color: #475569; margin-top: 2px;">Status: <span style="font-weight: 700; color: #2563eb;">${data.userStatus}</span></div>
                                <div style="color: #475569; margin-top: 2px;">Jarak: <strong style="color: #16a34a;">${data.distance || 0} m</strong></div>
                                <div style="margin-top: 4px; padding-top: 4px; font-size: 11px; font-family: monospace; color: #94a3b8; border-top: 1px solid #f1f5f9;">
                                    ${data.userLat.toFixed(6)}, ${data.userLng.toFixed(6)}
                                </div>
                            </div>
                        `);

                        userMarker.bringToFront();

                        // Buka otomatis popup titik absen pegawai
                        setTimeout(() => {
                            try {
                                userMarker.openPopup();
                            } catch (e) {}
                        }, 400);

                        // 3. Garis Pengukuran Jarak Antara Pegawai dan Kantor
                        if (data.officeLat !== null && data.officeLng !== null) {
                            const distanceLine = L.polyline([[data.userLat, data.userLng], [data.officeLat, data.officeLng]], {
                                color: '#2563eb',
                                weight: 2.5,
                                dashArray: '6, 6',
                                opacity: 0.9
                            }).addTo(this.map);

                            distanceLine.bringToFront();

                            if (data.distance) {
                                distanceLine.bindTooltip(`Jarak: ${data.distance} m`, {
                                    permanent: true,
                                    direction: 'center',
                                    className: 'distance-tooltip'
                                });
                            }
                        }
                    }

                    this.isRendered = true;

                    // 4. Sesuaikan tampilan peta
                    this.resetView();

                    // Resize Observer & Timers agar saat modal selesai animasi, Leaflet langsung menyesuaikan ukuran
                    const triggerResize = () => {
                        if (this.map) {
                            this.map.invalidateSize(true);
                            this.resetView();
                        }
                    };

                    setTimeout(triggerResize, 200);
                    setTimeout(triggerResize, 450);
                    setTimeout(triggerResize, 800);

                    if (window.ResizeObserver && el) {
                        this.resizeObserver = new ResizeObserver(() => {
                            if (this.map) {
                                this.map.invalidateSize(false);
                            }
                        });
                        this.resizeObserver.observe(el);
                    }
                },

                resetView() {
                    if (!this.map || this.bounds.length === 0) return;

                    try {
                        if (this.bounds.length === 1) {
                            this.map.setView(this.bounds[0], 17);
                        } else {
                            this.map.fitBounds(this.bounds, { 
                                padding: [40, 40],
                                maxZoom: 17 
                            });
                        }
                    } catch (e) {}
                }
            };
        };

        if (window.Alpine) {
            window.Alpine.data('attendanceDetailMap', window.attendanceDetailMap);
        }
    }
</script>
