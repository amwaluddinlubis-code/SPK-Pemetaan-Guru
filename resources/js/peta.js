// Entry khusus peta Leaflet — dibundle Vite agar tidak tergantung CDN.
// Dipakai di dashboard & komponen peta-picker via @vite('resources/js/peta.js').
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Expose global agar skrip inline Blade (L.map, L.marker, ...) tetap jalan.
window.L = L;

// Perbaiki path ikon marker bawaan Leaflet saat dibundle Vite.
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});
