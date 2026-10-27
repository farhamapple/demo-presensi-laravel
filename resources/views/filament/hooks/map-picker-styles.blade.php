<style>
    /* Styling khusus polygon geofence pada peta Leaflet Geoman */
    .leaflet-pane svg path[fill]:not([fill="none"]),
    .leaflet-overlay-pane svg path[fill]:not([fill="none"]) {
        fill: #0284c7 !important;
        fill-opacity: 0.35 !important;
    }

    .leaflet-pane svg path.leaflet-interactive,
    .leaflet-overlay-pane svg path.leaflet-interactive {
        stroke: #002d62 !important;
        stroke-width: 3px !important;
        stroke-opacity: 1 !important;
        stroke-linejoin: round !important;
        stroke-linecap: round !important;
    }

    /* Penanda titik sudut polygon saat edit/gambar */
    .marker-icon {
        background-color: #ffffff !important;
        border: 2px solid #002d62 !important;
        border-radius: 3px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35) !important;
    }

    /* Garis panduan saat menggambar (hintline) */
    .leaflet-pm-hintline,
    .leaflet-pane svg path[stroke-dasharray] {
        stroke: #0284c7 !important;
        stroke-width: 2px !important;
        stroke-opacity: 0.9 !important;
    }
</style>

<script>
    (function() {
        const DEFAULT_STROKE = '#002d62';
        const DEFAULT_FILL = '#0284c7';
        const DEFAULT_OPACITY = 0.35;
        const DEFAULT_WEIGHT = 3;

        function applyPolygonStyle(layer, config) {
            if (!layer || typeof layer.setStyle !== 'function') {
                return;
            }
            const strokeColor = config?.geoMan?.color || DEFAULT_STROKE;
            const fillColor = config?.geoMan?.filledColor || DEFAULT_FILL;
            layer.setStyle({
                color: strokeColor,
                fillColor: fillColor,
                fillOpacity: DEFAULT_OPACITY,
                weight: DEFAULT_WEIGHT,
                opacity: 1,
            });
        }

        function enhanceMapInstance(instance, config) {
            if (!instance || !instance.map) {
                return;
            }

            const map = instance.map;
            const strokeColor = config?.geoMan?.color || DEFAULT_STROKE;
            const fillColor = config?.geoMan?.filledColor || DEFAULT_FILL;

            const polygonStyle = {
                color: strokeColor,
                fillColor: fillColor,
                fillOpacity: DEFAULT_OPACITY,
                weight: DEFAULT_WEIGHT,
                opacity: 1,
            };

            if (map.pm) {
                if (typeof map.pm.setPathOptions === 'function') {
                    map.pm.setPathOptions(polygonStyle);
                }
                if (typeof map.pm.setGlobalOptions === 'function') {
                    map.pm.setGlobalOptions({
                        pathOptions: polygonStyle,
                        templineStyle: {
                            color: strokeColor,
                            weight: DEFAULT_WEIGHT,
                        },
                        hintlineStyle: {
                            color: strokeColor,
                            dashArray: '5,5',
                            weight: 2,
                        },
                    });
                }

                // Beri warna langsung ketika polygon selesai digambar oleh user
                map.on('pm:create', (e) => {
                    if (e.layer) {
                        applyPolygonStyle(e.layer, config);
                    }
                });
            }

            // Beri warna pada polygon yang sudah dimuat dari GeoJSON database
            if (instance.drawItems && typeof instance.drawItems.eachLayer === 'function') {
                instance.drawItems.eachLayer((layer) => {
                    applyPolygonStyle(layer, config);
                });
            }
        }

        function setupMapPickerHook() {
            if (typeof window.mapPicker !== 'function' || window.mapPicker._enhanced) {
                return;
            }

            const originalMapPicker = window.mapPicker;

            window.mapPicker = function($wire, config) {
                const instance = originalMapPicker($wire, config);
                const originalCreateMap = instance.createMap;

                instance.createMap = function(el) {
                    originalCreateMap.call(this, el);
                    enhanceMapInstance(this, config);
                };

                return instance;
            };

            window.mapPicker._enhanced = true;
        }

        if (window.mapPicker) {
            setupMapPickerHook();
        } else {
            window.addEventListener('map-script-loaded', setupMapPickerHook, { once: true });
            window.addEventListener('DOMContentLoaded', setupMapPickerHook);
        }
    })();
</script>

