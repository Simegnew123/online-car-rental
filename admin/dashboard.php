<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$page_title = 'Admin Dashboard';
$use_charts = true;
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Admin Dashboard</h2>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">&#128663;</div>
        <div class="stat-value"><?= totalCars() ?></div>
        <div class="stat-label">Total Vehicles</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">&#9989;</div>
        <div class="stat-value"><?= availableCars() ?></div>
        <div class="stat-label">Available</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">&#128221;</div>
        <div class="stat-value"><?= totalBookings() ?></div>
        <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128176;</div>
        <div class="stat-value"><?= money(totalRevenue()) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">&#128101;</div>
        <div class="stat-value"><?= totalCustomers() ?></div>
        <div class="stat-label">Customers</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon">&#128664;</div>
        <div class="stat-value"><?= activeRentals() ?></div>
        <div class="stat-label">Active Rentals</div>
    </div>
</div>

<div class="d-flex flex-wrap mb-2">
    <a href="<?= APP_URL ?>/admin/cars.php" class="btn btn-primary">Manage Vehicles</a>
    <a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-success">Manage Bookings</a>
    <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-warning">Manage Users</a>
    <a href="<?= APP_URL ?>/admin/gps_tracking.php" class="btn btn-outline">GPS Tracking</a>
    <a href="<?= APP_URL ?>/admin/reports.php" class="btn btn-primary">Reports</a>
    <a href="<?= APP_URL ?>/admin/settings.php" class="btn btn-outline">Settings</a>
</div>

<!-- Charts -->
<div class="chart-grid">
    <div class="chart-container">
        <h3 style="margin-bottom:16px;font-size:1.1rem;">Monthly Revenue</h3>
        <canvas id="revenueChart"></canvas>
    </div>
    <div class="chart-container">
        <h3 style="margin-bottom:16px;font-size:1.1rem;">Booking Status</h3>
        <canvas id="bookingChart"></canvas>
    </div>
</div>

<?php
// Revenue by month
$revenueByMonth = db()->query(
    'SELECT DATE_FORMAT(p.created_at, "%Y-%m") AS month,
            COALESCE(SUM(p.amount), 0) AS revenue
     FROM payments p WHERE p.status = "paid"
     GROUP BY month ORDER BY month ASC LIMIT 6'
)->fetchAll();

// Bookings by status
$bookingsByStatus = db()->query(
    'SELECT status, COUNT(*) AS count FROM bookings GROUP BY status'
)->fetchAll();

// Recent bookings
$stmt = db()->prepare(
    'SELECT b.*, c.name AS car_name, u.full_name AS customer_name
     FROM bookings b
     JOIN cars c ON b.car_id = c.id
     JOIN users u ON b.user_id = u.id
     ORDER BY b.created_at DESC LIMIT 5'
);
$stmt->execute();
$recent = $stmt->fetchAll();
?>

<script>
// Revenue chart
var revData = <?= json_encode($revenueByMonth) ?>;
var revCtx = document.getElementById('revenueChart');
if (revCtx && revData.length > 0 && typeof Chart !== 'undefined') {
    new Chart(revCtx, {
        type: 'bar',
        data: {
            labels: revData.map(function(d) { return d.month; }),
            datasets: [{
                label: 'Revenue (ETB)',
                data: revData.map(function(d) { return d.revenue; }),
                backgroundColor: 'rgba(37, 99, 235, .8)',
                borderRadius: 8
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
}

// Booking status chart
var bookData = <?= json_encode($bookingsByStatus) ?>;
var bookCtx = document.getElementById('bookingChart');
if (bookCtx && bookData.length > 0 && typeof Chart !== 'undefined') {
    new Chart(bookCtx, {
        type: 'doughnut',
        data: {
            labels: bookData.map(function(d) { return d.status; }),
            datasets: [{
                data: bookData.map(function(d) { return d.count; }),
                backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#8b5cf6']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}
</script>

<h3 class="page-title mt-2">Recent Bookings</h3>
<div class="table-card">
    <table>
        <thead><tr><th>#</th><th>Customer</th><th>Vehicle</th><th>Period</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $b): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td><?= e($b['customer_name']) ?></td>
                <td><?= e($b['car_name']) ?></td>
                <td><?= formatDate($b['start_date']) ?> &rarr; <?= formatDate($b['end_date']) ?></td>
                <td><?= money($b['total_cost']) ?></td>
                <td><span class="badge <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
