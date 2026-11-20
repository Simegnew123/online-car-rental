<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$page_title = 'GPS Tracking';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Real-Time GPS Fleet Tracking</h2>

<div class="map-info">
    <p>Live vehicle positions on map. Auto-refreshes every 30 seconds.</p>
    <div id="car-count" style="font-size:.9rem;color:var(--gray);">Loading...</div>
</div>

<div id="gps-map"></div>

<script>
// GPS map is auto-initialized by main.js (initGpsMap)
// Override the fetch to update count
const origLoad = loadCarLocations;
loadCarLocations = function() {
    if (!gpsMap) return;
    fetch(APP_URL + '/api/get_location.php')
        .then(r => r.json())
        .then(data => {
            carMarkers.forEach(m => gpsMap.removeLayer(m));
            carMarkers = [];
            const countEl = document.getElementById('car-count');
            if (countEl) countEl.textContent = data.length + ' vehicles with GPS data';
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
        });
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
