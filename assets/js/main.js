// =====================================================
// Online Car Rental System — Main JavaScript v2.0
// =====================================================

// Pass APP_URL from PHP to JS
var APP_URL = window.APP_URL || '';

// =====================================================
// TOAST NOTIFICATIONS
// =====================================================
function showToast(msg, type = 'info') {
    var container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    var icon = type === 'success' ? '\u2705' : type === 'error' ? '\u274C' : '\u2139\uFE0F';
    toast.innerHTML = icon + ' ' + msg;
    container.appendChild(toast);
    setTimeout(function() {
        toast.classList.add('removing');
        setTimeout(function() { toast.remove(); }, 300);
    }, 4000);
}

// =====================================================
// MOBILE MENU
// =====================================================
function toggleMobileMenu() {
    var links = document.querySelector('.nav-links');
    if (links) links.classList.toggle('open');
}

// =====================================================
// NOTIFICATIONS DROPDOWN
// =====================================================
function toggleNotifications() {
    var dd = document.getElementById('notif-dropdown');
    if (dd) dd.classList.toggle('show');
}

function loadNotifications() {
    if (!document.getElementById('notif-dropdown')) return;
    fetch(APP_URL + '/api/get_notifications.php', { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var dd = document.getElementById('notif-dropdown');
            var bellCount = document.getElementById('bell-count');
            if (!dd) return;

            if (bellCount) {
                bellCount.textContent = data.unread;
                bellCount.style.display = data.unread > 0 ? 'flex' : 'none';
            }

            if (data.notifications.length === 0) {
                dd.innerHTML = '<div class="notif-header">Notifications</div><div class="notif-empty">No notifications</div>';
                return;
            }

            var html = '<div class="notif-header">Notifications <a href="' + APP_URL + '/notifications.php" style="font-size:.8rem;font-weight:400;">View All</a></div>';
            data.notifications.forEach(function(n) {
                html += '<div class="notif-item' + (n.is_read == 0 ? ' unread' : '') + '" onclick="markRead(' + n.id + ')">';
                html += '<div class="n-title">' + escapeHtml(n.title) + '</div>';
                html += '<div class="n-msg">' + escapeHtml(n.message) + '</div>';
                html += '<div class="n-time">' + n.created_at + '</div>';
                html += '</div>';
            });
            dd.innerHTML = html;
        })
        .catch(function(err) { console.error('Notif load error:', err); });
}

function markRead(id) {
    fetch(APP_URL + '/api/mark_read.php?id=' + id, { credentials: 'same-origin' })
        .then(function() { loadNotifications(); });
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// =====================================================
// GPS MAP — REAL-TIME TRACKING
// =====================================================
var gpsMap = null;
var carMarkers = {};
var carTrails = {};
var gpsRefreshMs = 5000; // 5-second real-time refresh
var gpsInterval = null;
var gpsAutoRefresh = true;

function initGpsMap() {
    var mapEl = document.getElementById('gps-map');
    if (!mapEl) return;

    gpsMap = L.map('gps-map', { zoomControl: true }).setView([9.0250, 38.7469], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(gpsMap);

    loadCarLocations();

    if (gpsAutoRefresh) {
        gpsInterval = setInterval(loadCarLocations, gpsRefreshMs);
    }
}

function loadCarLocations() {
    if (!gpsMap) return;

    fetch(APP_URL + '/api/get_location.php', { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var countEl = document.getElementById('car-count');
            if (countEl) countEl.textContent = data.length + ' vehicles tracked';

            // Update sidebar
            updateGpsSidebar(data);

            data.forEach(function(car) {
                var lat = car.latitude;
                var lng = car.longitude;
                var isMoving = car.speed > 0;
                var iconHtml = isMoving ?
                    '<span style="font-size:22px">\uD83D\uDE97</span>' :
                    '<span style="font-size:22px">\uD83D\uDE97</span>';

                var bgColor = car.status === 'rented' ? '#dcfce7' : '#f1f5f9';
                var icon = L.divIcon({
                    html: '<div style="background:' + bgColor + ';border:2px solid ' +
                          (isMoving ? '#16a34a' : '#64748b') +
                          ';border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.3);">' +
                          iconHtml + '</div>',
                    iconSize: [36, 36],
                    className: 'car-marker',
                    iconAnchor: [18, 18]
                });

                if (carMarkers[car.car_id]) {
                    // Smoothly move existing marker
                    carMarkers[car.car_id].setLatLng([lat, lng]);
                    carMarkers[car.car_id].setIcon(icon);
                } else {
                    var marker = L.marker([lat, lng], { icon: icon }).addTo(gpsMap);
                    carMarkers[car.car_id] = marker;
                }

                // Update popup
                var popupHtml = '<div style="min-width:160px">' +
                    '<strong>' + escapeHtml(car.name) + '</strong><br>' +
                    '<span class="badge ' + car.status + '">' + car.status + '</span><br>' +
                    '\u26A1 Speed: <strong>' + car.speed + ' km/h</strong><br>' +
                    '\uD83D\uDCCD ' + lat.toFixed(4) + ', ' + lng.toFixed(4) + '<br>' +
                    '<small style="color:#64748b">Updated: ' + car.recorded_at + '</small>' +
                    '</div>';
                carMarkers[car.car_id].bindPopup(popupHtml);

                // Draw trail if available
                if (car.trail && car.trail.length > 1) {
                    var trailPoints = car.trail.map(function(t) {
                        return [parseFloat(t.latitude), parseFloat(t.longitude)];
                    });
                    if (carTrails[car.car_id]) {
                        gpsMap.removeLayer(carTrails[car.car_id]);
                    }
                    carTrails[car.car_id] = L.polyline(trailPoints, {
                        color: '#2563eb',
                        weight: 3,
                        opacity: 0.6,
                        dashArray: '5, 8'
                    }).addTo(gpsMap);
                }
            });

            // Remove markers for cars no longer in data
            var currentIds = data.map(function(c) { return c.car_id; });
            Object.keys(carMarkers).forEach(function(id) {
                if (currentIds.indexOf(parseInt(id)) === -1) {
                    gpsMap.removeLayer(carMarkers[id]);
                    delete carMarkers[id];
                    if (carTrails[id]) {
                        gpsMap.removeLayer(carTrails[id]);
                        delete carTrails[id];
                    }
                }
            });
        })
        .catch(function(err) { console.error('GPS fetch error:', err); });
}

function updateGpsSidebar(data) {
    var sidebar = document.getElementById('gps-sidebar');
    if (!sidebar) return;

    var html = '';
    data.forEach(function(car) {
        var isMoving = car.speed > 0;
        html += '<div class="gps-vehicle-item" onclick="focusVehicle(' + car.car_id + ', ' + car.latitude + ', ' + car.longitude + ')">';
        html += '<div class="v-name">' + escapeHtml(car.name) + '</div>';
        html += '<div class="v-info">';
        html += '<span class="badge ' + car.status + '">' + car.status + '</span> ';
        html += '<span class="v-speed">' + (isMoving ? car.speed + ' km/h' : 'Parked') + '</span>';
        html += '</div>';
        html += '</div>';
    });
    sidebar.innerHTML = html;
}

function focusVehicle(id, lat, lng) {
    if (gpsMap) {
        gpsMap.setView([lat, lng], 16);
        if (carMarkers[id]) carMarkers[id].openPopup();
    }
    document.querySelectorAll('.gps-vehicle-item').forEach(function(el) {
        el.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}

function toggleGpsRefresh() {
    if (gpsInterval) {
        clearInterval(gpsInterval);
        gpsInterval = null;
        gpsAutoRefresh = false;
        var btn = document.getElementById('btn-refresh');
        if (btn) {
            btn.innerHTML = '\u25B6 Resume';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-success');
        }
        showToast('GPS auto-refresh paused', 'info');
    } else {
        loadCarLocations();
        gpsInterval = setInterval(loadCarLocations, gpsRefreshMs);
        gpsAutoRefresh = true;
        var btn = document.getElementById('btn-refresh');
        if (btn) {
            btn.innerHTML = '\u23F8 Pause';
            btn.classList.add('btn-danger');
            btn.classList.remove('btn-success');
        }
        showToast('GPS auto-refresh resumed', 'success');
    }
}

// =====================================================
// GPS SIMULATOR — sends fake GPS data to API
// =====================================================
var simInterval = null;
var simRunning = false;

function startGpsSimulator() {
    var btn = event ? event.target : null;

    if (simRunning) {
        // Stop simulation
        clearInterval(simInterval);
        simInterval = null;
        simRunning = false;
        if (btn) {
            btn.innerHTML = '\uD83D\uDE40 Simulate Movement';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-primary');
        }
        showToast('GPS simulation stopped', 'info');
        return;
    }

    if (!confirm('Start GPS simulation? This will generate fake vehicle movement data for rented cars every 3 seconds.')) return;

    simRunning = true;
    if (btn) {
        btn.innerHTML = '\u23F9 Stop Simulation';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-danger');
    }

    // Run once immediately
    runSimStep();

    // Then keep running every 3 seconds
    simInterval = setInterval(runSimStep, 3000);
}

function runSimStep() {
    fetch(APP_URL + '/api/gps_simulator.php', { method: 'POST', credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                loadCarLocations();  // Refresh map immediately
            } else {
                showToast('Simulation error: ' + data.error, 'error');
            }
        })
        .catch(function(err) { showToast('Simulation failed: ' + err, 'error'); });
}

// =====================================================
// BOOKING PRICE CALCULATOR
// =====================================================
function calcBookingCost() {
    var start = document.getElementById('start_date');
    var end = document.getElementById('end_date');
    var priceEl = document.getElementById('price-per-day');
    var totalEl = document.getElementById('total-cost');
    var daysEl = document.getElementById('total-days');

    if (!start || !end || !priceEl || !totalEl) return;

    if (start.value && end.value) {
        var s = new Date(start.value);
        var e = new Date(end.value);
        var days = Math.max(1, Math.ceil((e - s) / 86400000));
        var price = parseFloat(priceEl.value);
        var total = days * price;
        totalEl.textContent = 'ETB ' + total.toLocaleString(undefined, { minimumFractionDigits: 2 });
        if (daysEl) daysEl.textContent = days;
    }
}

// =====================================================
// STAR RATING INPUT
// =====================================================
function setupStarRating() {
    var containers = document.querySelectorAll('.star-rating');
    containers.forEach(function(container) {
        var stars = container.querySelectorAll('.star');
        var input = container.parentElement.querySelector('input[name="rating"]');
        var currentVal = input ? parseInt(input.value) : 5;

        stars.forEach(function(star, idx) {
            if (idx < currentVal) star.classList.add('active');

            star.addEventListener('click', function() {
                var val = parseInt(star.dataset.val);
                if (input) input.value = val;
                stars.forEach(function(s, i) {
                    if (i < val) s.classList.add('active');
                    else s.classList.remove('active');
                });
            });
        });
    });
}

// =====================================================
// CONFIRM DIALOG
// =====================================================
function confirmAction(msg) {
    return confirm(msg);
}

// =====================================================
// LOADING OVERLAY
// =====================================================
function showLoading() {
    var overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.add('show');
}
function hideLoading() {
    var overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.remove('show');
}

// =====================================================
// CLOSE DROPDOWN ON OUTSIDE CLICK
// =====================================================
document.addEventListener('click', function(e) {
    var dd = document.getElementById('notif-dropdown');
    var bell = document.getElementById('nav-bell');
    if (dd && dd.classList.contains('show') && bell && !bell.contains(e.target) && !dd.contains(e.target)) {
        dd.classList.remove('show');
    }
    var mobileMenu = document.querySelector('.nav-links');
    var toggle = document.querySelector('.nav-toggle');
    if (mobileMenu && mobileMenu.classList.contains('open') && toggle && !toggle.contains(e.target) && !mobileMenu.contains(e.target)) {
        mobileMenu.classList.remove('open');
    }
});

// =====================================================
// PWA INSTALL PROMPT
// =====================================================
var deferredPrompt = null;

window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    deferredPrompt = e;
    showInstallButton();
});

function showInstallButton() {
    var existing = document.getElementById('pwa-install-btn');
    if (existing) return;

    var btn = document.createElement('button');
    btn.id = 'pwa-install-btn';
    btn.innerHTML = '&#128229; Install App';
    btn.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;background:#2563eb;color:#fff;border:none;padding:12px 20px;border-radius:8px;font-size:14px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.3);font-family:sans-serif;';
    btn.onclick = function() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function(choice) {
            if (choice.outcome === 'accepted') {
                console.log('PWA installed');
            }
            deferredPrompt = null;
            btn.remove();
        });
    };
    document.body.appendChild(btn);
}

// Detect if already installed
window.addEventListener('appinstalled', function() {
    var btn = document.getElementById('pwa-install-btn');
    if (btn) btn.remove();
});

// =====================================================
// SERVICE WORKER REGISTRATION
// =====================================================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register(APP_URL + '/sw.js').then(function(reg) {
            console.log('SW registered:', reg.scope);
        }).catch(function(err) {
            console.log('SW registration failed:', err);
        });
    });
}

// =====================================================
// PAGE INIT
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    initGpsMap();

    var start = document.getElementById('start_date');
    var end = document.getElementById('end_date');
    if (start) start.addEventListener('change', calcBookingCost);
    if (end) end.addEventListener('change', calcBookingCost);
    calcBookingCost();

    setupStarRating();

    // Load notifications every 30s if bell exists
    if (document.getElementById('nav-bell')) {
        loadNotifications();
        setInterval(loadNotifications, 30000);
    }

    // Close mobile menu on link click
    document.querySelectorAll('.nav-links a').forEach(function(link) {
        link.addEventListener('click', function() {
            var menu = document.querySelector('.nav-links');
            if (menu) menu.classList.remove('open');
        });
    });
});
