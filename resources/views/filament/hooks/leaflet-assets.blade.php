<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* Styling Tooltip Jarak Pengukuran */
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

    /* Styling Leaflet Popup */
    .leaflet-popup-content-wrapper {
        border-radius: 12px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        padding: 4px !important;
    }
    .leaflet-popup-content {
        margin: 10px 14px !important;
        line-height: 1.4 !important;
    }

    /* Animasi denyut pada pin titik pegawai absen */
    @keyframes pulse-ring {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.5); opacity: 0.25; }
        100% { transform: scale(2.0); opacity: 0; }
    }
    .employee-pulse-ring {
        animation: pulse-ring 2s cubic-bezier(0.2, 0.8, 0.2, 1) infinite;
    }
</style>
