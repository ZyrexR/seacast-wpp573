import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Make Leaflet globally available
window.L = L;

// Fix Leaflet marker icon URLs when bundled by Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: '/vendor/leaflet/images/marker-icon-2x.png',
    iconUrl: '/vendor/leaflet/images/marker-icon.png',
    shadowUrl: '/vendor/leaflet/images/marker-shadow.png',
});

// Reusable Alpine.js Leaflet Map Component
document.addEventListener('alpine:init', () => {
    window.Alpine.data('seacastMap', (config = {}) => ({
        map: null,
        loaded: false,
        error: null,
        zoom: config.zoom || 8,
        currentBasemap: config.defaultBasemap || 'dark',
        basemapLayers: {},

        get centerCoords() {
            let lat = -8.2833;
            let lon = 111.7275;
            if (Array.isArray(config.center) && config.center.length === 2) {
                if (config.center[0] > 0 && config.center[1] < 0) {
                    // [lon, lat] passed
                    lon = config.center[0];
                    lat = config.center[1];
                } else {
                    // [lat, lon] passed
                    lat = config.center[0];
                    lon = config.center[1];
                }
            }
            return [lat, lon];
        },

        initMap() {
            try {
                if (typeof L === 'undefined') {
                    throw new Error('Leaflet library is not loaded.');
                }

                const container = this.$refs.mapContainer;
                if (!container) return;

                // 1. Initialize Map with smooth canvas rendering
                this.map = L.map(container, {
                    center: this.centerCoords,
                    zoom: this.zoom,
                    attributionControl: false,
                    zoomControl: false,
                    preferCanvas: true,
                });

                // 2. High-contrast Multi-basemaps (Zero Watermark / No API key)
                this.basemapLayers = {
                    dark: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 16,
                        attribution: '&copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
                    }),
                    imagery: L.layerGroup([
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 18,
                            attribution: '&copy; Esri World Imagery',
                        }),
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 18,
                        })
                    ]),
                    ocean: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 13,
                        attribution: '&copy; Esri Ocean',
                    }),
                    topo: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 18,
                        attribution: '&copy; Esri Topo',
                    }),
                    osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap',
                    })
                };

                const initialBasemap = this.basemapLayers[this.currentBasemap] || this.basemapLayers.dark;
                initialBasemap.addTo(this.map);

                // 3. Nautical Scale Control
                L.control.scale({ imperial: true, metric: true, position: 'bottomleft' }).addTo(this.map);

                // 4. Custom Pulsing Homeport Marker (PPN Prigi)
                const homeportIcon = L.divIcon({
                    className: 'custom-homeport-beacon',
                    html: `
                        <div class="relative flex items-center justify-center w-7 h-7">
                            <span class="absolute inline-flex w-full h-full rounded-full bg-cyan-400/40 animate-ping opacity-75"></span>
                            <span class="relative inline-flex w-3.5 h-3.5 rounded-full bg-cyan-400 border-2 border-white shadow-[0_0_12px_rgba(34,211,238,0.9)]"></span>
                        </div>
                    `,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                });

                L.marker([-8.2833, 111.7275], { icon: homeportIcon })
                    .addTo(this.map)
                    .bindPopup(`
                        <div style="font-family: ui-sans-serif, system-ui; min-width: 180px; padding: 4px;">
                            <div style="font-weight: 700; color: #22d3ee; text-transform: uppercase; font-size: 11px; margin-bottom: 4px;">
                                PPN Prigi Homeport
                            </div>
                            <div style="font-size: 11px; color: #cbd5e1; font-family: monospace;">
                                Lat: -8.2833°S | Lon: 111.7275°E
                            </div>
                            <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">
                                Pangkalan Armada Tuna & Radar WPPNRI 573
                            </div>
                        </div>
                    `);

                // 5. Traditional & Commercial Range Rings from PPN Prigi
                // 12 NM = 22,224 meters (Zona Inshore Perahu Kecil <10 GT)
                L.circle([-8.2833, 111.7275], {
                    radius: 22224,
                    color: '#22d3ee',
                    weight: 1.2,
                    dashArray: '4, 6',
                    fill: false,
                    opacity: 0.6
                }).addTo(this.map).bindTooltip("Batas Zona Inshore 12 NM (< 10 GT)", { permanent: false, direction: 'top' });

                // 60 NM = 111,120 meters (Zona Offshore Kapal 10-30 GT)
                L.circle([-8.2833, 111.7275], {
                    radius: 111120,
                    color: '#0284c7',
                    weight: 1.0,
                    dashArray: '6, 8',
                    fill: false,
                    opacity: 0.4
                }).addTo(this.map).bindTooltip("Batas Zona Offshore 60 NM (10-30 GT)", { permanent: false, direction: 'top' });

                this.loaded = true;

                if (typeof config.onLoad === 'function') {
                    config.onLoad(this.map);
                }

                // Invalidate map size after DOM settles
                setTimeout(() => {
                    if (this.map) this.map.invalidateSize();
                }, 200);

            } catch (err) {
                console.error('Failed to initialize Leaflet map:', err);
                this.error = 'Gagal menginisialisasi peta Leaflet: ' + err.message;
            }
        },

        setBasemap(type) {
            this.currentBasemap = type;
            if (!this.map || !this.basemapLayers) return;

            Object.values(this.basemapLayers).forEach(layer => {
                if (layer && this.map.hasLayer(layer)) {
                    this.map.removeLayer(layer);
                }
            });

            const target = this.basemapLayers[type] || this.basemapLayers.dark;
            target.addTo(this.map);
        },

        resetView() {
            if (this.map) {
                this.map.setView(this.centerCoords, this.zoom);
            }
        }
    }));
});
