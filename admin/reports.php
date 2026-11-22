<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$page_title = 'Reports & Analytics';
$use_charts = true;

// Fetch report data
$revenueByMonth = db()->query(
    'SELECT DATE_FORMAT(p.created_at, "%Y-%m") AS month,
            COALESCE(SUM(p.amount), 0) AS revenue
     FROM payments p WHERE p.status = "paid"
     GROUP BY month ORDER BY month ASC LIMIT 12'
)->fetchAll();

$bookingsByMonth = db()->query(
    'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month,
            COUNT(*) AS count
     FROM bookings GROUP BY month ORDER BY month ASC LIMIT 12'
)->fetchAll();

$topCars = db()->query(
    'SELECT c.name, c.brand, COUNT(b.id) AS bookings,
            COALESCE(SUM(p.amount), 0) AS revenue
     FROM cars c
     LEFT JOIN bookings b ON b.car_id = c.id
     LEFT JOIN payments p ON p.booking_id = b.id AND p.status = "paid"
     GROUP BY c.id ORDER BY bookings DESC LIMIT 5'
)->fetchAll();

$carTypes = db()->query(
    'SELECT type, COUNT(*) AS count FROM cars GROUP BY type'
)->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Reports & Analytics</h2>

<!-- Summary stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">&#128176;</div>
        <div class="stat-value"><?= money(totalRevenue()) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">&#128221;</div>
        <div class="stat-value"><?= totalBookings() ?></div>
        <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">&#128663;</div>
        <div class="stat-value"><?= totalCars() ?></div>
        <div class="stat-label">Vehicles in Fleet</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128101;</div>
        <div class="stat-value"><?= totalCustomers() ?></div>
        <div class="stat-label">Customers</div>
    </div>
</div>

<!-- Charts row 1 -->
<div class="chart-grid">
    <div class="chart-container">
        <h3>Revenue Trend</h3>
        <canvas id="revenueChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Booking Trend</h3>
        <canvas id="bookingTrendChart"></canvas>
    </div>
</div>

<!-- Charts row 2 -->
<div class="chart-grid">
    <div class="chart-container">
        <h3>Vehicle Type Distribution</h3>
        <canvas id="typeChart"></canvas>
    </div>
    <div class="chart-container">
        <h3>Top 5 Vehicles by Bookings</h3>
        <canvas id="topCarsChart"></canvas>
    </div>
</div>

<!-- Top vehicles table -->
<h3 class="page-title mt-2">Top Performing Vehicles</h3>
<div class="table-card">
    <table>
        <thead>
            <tr><th>Rank</th><th>Vehicle</th><th>Brand</th><th>Bookings</th><th>Revenue</th></tr>
        </thead>
        <tbody>
            <?php foreach ($topCars as $i => $c): ?>
            <tr>
                <td>#<?= $i + 1 ?></td>
                <td><?= e($c['name']) ?></td>
                <td><?= e($c['brand']) ?></td>
                <td><?= $c['bookings'] ?></td>
                <td><?= money($c['revenue']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Revenue trend
var revData = <?= json_encode($revenueByMonth) ?>;
var revCtx = document.getElementById('revenueChart');
if (revCtx && revData.length > 0 && typeof Chart !== 'undefined') {
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: revData.map(d => d.month),
            datasets: [{
                label: 'Revenue (ETB)',
                data: revData.map(d => d.revenue),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.15)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
}

// Booking trend
var bookData = <?= json_encode($bookingsByMonth) ?>;
var bookCtx = document.getElementById('bookingTrendChart');
if (bookCtx && bookData.length > 0 && typeof Chart !== 'undefined') {
    new Chart(bookCtx, {
        type: 'bar',
        data: {
            labels: bookData.map(d => d.month),
            datasets: [{
                label: 'Bookings',
                data: bookData.map(d => d.count),
                backgroundColor: 'rgba(22,163,74,.8)',
                borderRadius: 8
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
}

// Vehicle types
var typeData = <?= json_encode($carTypes) ?>;
var typeCtx = document.getElementById('typeChart');
if (typeCtx && typeData.length > 0 && typeof Chart !== 'undefined') {
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: typeData.map(d => d.type),
            datasets: [{
                data: typeData.map(d => d.count),
                backgroundColor: ['#2563eb','#16a34a','#f59e0b','#dc2626','#8b5cf6','#06b6d4','#ec4899']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

// Top cars
var topData = <?= json_encode($topCars) ?>;
var topCtx = document.getElementById('topCarsChart');
if (topCtx && topData.length > 0 && typeof Chart !== 'undefined') {
    new Chart(topCtx, {
        type: 'bar',
        data: {
            labels: topData.map(d => d.name),
            datasets: [{
                label: 'Bookings',
                data: topData.map(d => d.bookings),
                backgroundColor: 'rgba(245,158,11,.8)',
                borderRadius: 8
            }]
        },
        options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } } }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
