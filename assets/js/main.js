// =====================================================
// Online Car Rental System — Main JavaScript
// =====================================================

// ---- GPS Map (Leaflet) ----
let gpsMap = null;
let carMarkers = [];

function initGpsMap() {
    const mapEl = document.getElementById('gps-map');
    if (!mapEl) return;

    // Default center: Addis Ababa
    gpsMap = L.map('gps-map').setView([9.0250, 38.7469], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(gpsMap);

    loadCarLocations();
    // Auto-refresh every 30 seconds
    setInterval(loadCarLocations, 30000);
}

function loadCarLocations() {
    if (!gpsMap) return;
    fetch(APP_URL + '/api/get_location.php')
        .then(r => r.json())
        .then(data => {
            // Clear old markers
            carMarkers.forEach(m => gpsMap.removeLayer(m));
            carMarkers = [];

            data.forEach(car => {
                const icon = L.divIcon({
                    html: '<span style="font-size:24px">&#128663;</span>',
                    iconSize: [30, 30],
                    className: 'car-marker'
                });
                const marker = L.marker([car.latitude, car.longitude], { icon })
                    .addTo(gpsMap)
                    .bindPopup(
                        '<b>' + car.name + '</b><br>' +
                        'Speed: ' + car.speed + ' km/h<br>' +
                        'Status: ' + car.status + '<br>' +
                        '<small>Updated: ' + car.recorded_at + '</small>'
                    );
                carMarkers.push(marker);
            });
        })
        .catch(err => console.error('GPS fetch error:', err));
}

// ---- Booking price calculator ----
function calcBookingCost() {
    const start = document.getElementById('start_date');
    const end   = document.getElementById('end_date');
    const priceEl = document.getElementById('price-per-day');
    const totalEl = document.getElementById('total-cost');

    if (!start || !end || !priceEl || !totalEl) return;

    if (start.value && end.value) {
        const s = new Date(start.value);
        const e = new Date(end.value);
        const days = Math.max(1, Math.ceil((e - s) / 86400000));
        const price = parseFloat(priceEl.value);
        totalEl.textContent = 'Total: ETB ' + (days * price).toLocaleString(undefined, {minimumFractionDigits: 2});
    }
}

// ---- Confirm delete/Cancel ----
function confirmAction(msg) {
    return confirm(msg);
}

// ---- Page initialization ----
document.addEventListener('DOMContentLoaded', function() {
    initGpsMap();

    // Auto-calculate booking cost on date change
    const start = document.getElementById('start_date');
    const end   = document.getElementById('end_date');
    if (start) start.addEventListener('change', calcBookingCost);
    if (end)   end.addEventListener('change', calcBookingCost);
    calcBookingCost(); // initial
});
