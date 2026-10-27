<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $mapConfigRaw = $getMapConfig();
        $mapConfigData = json_decode($mapConfigRaw, true) ?? [];
        $geoManData = $mapConfigData['geoMan'] ?? [];
        $strokeColor = $geoManData['color'] ?? '#002d62';
        $filledColor = $geoManData['filledColor'] ?? '#0284c7';
    @endphp

    <style>
        /* 1. Atasi bug z-index bawaan plugin agar layer SVG poligon selalu di atas tiles peta */
        .leaflet-pane.leaflet-tile-pane {
            z-index: 200 !important;
        }
        .leaflet-pane.leaflet-overlay-pane {
            z-index: 400 !important;
        }
        .leaflet-pane.leaflet-shadow-pane {
            z-index: 500 !important;
        }
        .leaflet-pane.leaflet-marker-pane {
            z-index: 600 !important;
        }
        .leaflet-pane.leaflet-tooltip-pane {
            z-index: 650 !important;
        }
        .leaflet-pane.leaflet-popup-pane {
            z-index: 700 !important;
        }

        /* 2. Pastikan SVG overlay pane dan path aktif serta memiliki pointer-events */
        .leaflet-overlay-pane svg {
            display: block !important;
            pointer-events: auto !important;
        }
        .leaflet-overlay-pane svg path.leaflet-interactive {
            pointer-events: auto !important;
            stroke-linejoin: round !important;
            stroke-linecap: round !important;
            stroke: {{ $strokeColor }} !important;
            stroke-width: 3px !important;
            stroke-opacity: 1 !important;
        }

        /* 3. Aturan fallback warna isian untuk elemen SVG poligon, lingkaran, dan kotak */
        .leaflet-overlay-pane svg path[fill]:not([fill="none"]) {
            fill: {{ $filledColor }} !important;
            fill-opacity: 0.45 !important;
        }

        /* 4. Penanda titik sudut saat menggambar/mengedit poligon */
        .marker-icon {
            background-color: #ffffff !important;
            border: 2px solid {{ $strokeColor }} !important;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35) !important;
        }

        /* 5. Garis panduan saat proses menggambar (hintline) */
        .leaflet-pm-hintline,
        .leaflet-pane svg path[stroke-dasharray] {
            stroke: {{ $strokeColor }} !important;
            stroke-width: 2px !important;
            stroke-opacity: 0.8 !important;
        }
    </style>

    <div x-data="mapPicker($wire, {{ $mapConfigRaw }})"
         x-init="async () => {
            do {
                await (new Promise(resolve => setTimeout(resolve, 100)));
            } while (!$refs.map);

            attach($refs.map);

            const mapConfig = {{ $mapConfigRaw }};
            const strokeColor = mapConfig?.geoMan?.color || '{{ $strokeColor }}';
            const fillColor = mapConfig?.geoMan?.filledColor || '{{ $filledColor }}';
            const shapeStyle = {
                color: strokeColor,
                fillColor: fillColor,
                fillOpacity: 0.45,
                weight: 3,
                opacity: 1
            };

            const applyGeomanStyles = () => {
                if (!map) return;

                // 1. Inisialisasi Geoman drawing path options
                if (map.pm) {
                    if (typeof map.pm.setPathOptions === 'function') {
                        map.pm.setPathOptions(shapeStyle);
                    }
                    if (typeof map.pm.setGlobalOptions === 'function') {
                        map.pm.setGlobalOptions({
                            pathOptions: shapeStyle,
                            templineStyle: { color: strokeColor, weight: 3 },
                            hintlineStyle: { color: strokeColor, dashArray: '5,5', weight: 2 }
                        });
                    }

                    // 2. Beri warna langsung saat layer baru (Polygon, Circle, Rectangle) selesai dibuat
                    map.on('pm:create', (e) => {
                        if (e.layer) {
                            if (typeof e.layer.setStyle === 'function') {
                                e.layer.setStyle(shapeStyle);
                            }
                            if (typeof e.layer.redraw === 'function') {
                                e.layer.redraw();
                            }
                        }
                    });
                }

                // 3. Beri warna pada semua layer yang sudah termuat dari database
                if (drawItems && typeof drawItems.eachLayer === 'function') {
                    drawItems.eachLayer((layer) => {
                        if (typeof layer.setStyle === 'function') {
                            layer.setStyle(shapeStyle);
                        }
                    });
                }
            };

            setTimeout(applyGeomanStyles, 150);
            setTimeout(applyGeomanStyles, 400);
            setTimeout(applyGeomanStyles, 800);
         }"
         wire:ignore
    >
        <div
            x-ref="map"
            class="w-full" style="min-height: 30vh; {{ $getExtraStyle() }}">
        </div>
        <input type="text" id="{{ $getStatePath() }}_fmrest" style="display:none"/>
    </div>
</x-dynamic-component>

