<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$page_title = 'GPS Tracking';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Real-Time GPS Fleet Tracking</h2>

<div class="map-info">
    <div class="d-flex flex-wrap">
        <span class="live-indicator" style="background:var(--danger-lt);color:var(--danger);">
            <span class="pulse"></span> LIVE
        </span>
        <span id="car-count" style="font-size:.9rem;color:var(--gray);margin-left:8px;">Loading...</span>
    </div>
    <div class="map-controls">
        <button class="btn btn-success btn-sm" id="btn-refresh" onclick="toggleGpsRefresh()">&#9208; Pause</button>
        <button class="btn btn-primary btn-sm" onclick="startGpsSimulator()">&#128640; Simulate Movement</button>
    </div>
</div>

<div class="gps-layout">
    <div id="gps-map"></div>
    <div class="gps-sidebar" id="gps-sidebar">
        <p style="color:var(--gray);font-size:.85rem;text-align:center;padding:20px 0;">Loading vehicles...</p>
    </div>
</div>

<div class="alert alert-info" style="margin-top:20px;">
    <strong>&#128241; Real-Time Tracking:</strong> Map auto-refreshes every 5 seconds.
    Click a vehicle in the sidebar to focus on it. Green border = moving, gray border = parked.
    Use <strong>Simulate Movement</strong> to generate test GPS data for active rentals.
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
