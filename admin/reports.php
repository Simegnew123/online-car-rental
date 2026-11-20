<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Revenue by month
$revenueByMonth = db()->query(
    'SELECT DATE_FORMAT(p.created_at, "%Y-%m") AS month,
            COALESCE(SUM(p.amount), 0) AS revenue,
            COUNT(*) AS transactions
     FROM payments p WHERE p.status = "paid"
     GROUP BY month ORDER BY month DESC LIMIT 12'
)->fetchAll();

// Bookings by status
$bookingsByStatus = db()->query(
    'SELECT status, COUNT(*) AS count FROM bookings GROUP BY status'
)->fetchAll();

// Top rented cars
$topCars = db()->query(
    'SELECT c.name, COUNT(b.id) AS bookings, COALESCE(SUM(b.total_cost), 0) AS revenue
     FROM cars c LEFT JOIN bookings b ON c.id = b.car_id AND b.status != "cancelled"
     GROUP BY c.id ORDER BY bookings DESC LIMIT 10'
)->fetchAll();

// Fleet utilization
$utilization = db()->query(
    'SELECT status, COUNT(*) AS count FROM cars GROUP BY status'
)->fetchAll();

$page_title = 'Reports';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Reports & Analytics</h2>

<!-- Revenue Summary -->
<h3>Revenue Summary</h3>
<div class="table-card mb-2">
    <table>
        <thead><tr><th>Month</th><th>Revenue</th><th>Transactions</th></tr></thead>
        <tbody>
            <?php foreach ($revenueByMonth as $r): ?>
            <tr>
                <td><?= $r['month'] ?></td>
                <td><?= money($r['revenue']) ?></td>
                <td><?= $r['transactions'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Booking Status Distribution -->
<h3>Booking Status Distribution</h3>
<div class="table-card mb-2">
    <table>
        <thead><tr><th>Status</th><th>Count</th></tr></thead>
        <tbody>
            <?php foreach ($bookingsByStatus as $b): ?>
            <tr><td><span class="badge <?= $b['status']==='completed'?'available':($b['status']==='cancelled'?'unavailable':'rented') ?>"><?= $b['status'] ?></span></td><td><?= $b['count'] ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Top Rented Cars -->
<h3>Top Rented Vehicles</h3>
<div class="table-card mb-2">
    <table>
        <thead><tr><th>Vehicle</th><th>Bookings</th><th>Revenue</th></tr></thead>
        <tbody>
            <?php foreach ($topCars as $c): ?>
            <tr><td><?= e($c['name']) ?></td><td><?= $c['bookings'] ?></td><td><?= money($c['revenue']) ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Fleet Utilization -->
<h3>Fleet Utilization</h3>
<div class="table-card">
    <table>
        <thead><tr><th>Status</th><th>Count</th></tr></thead>
        <tbody>
            <?php foreach ($utilization as $u): ?>
            <tr><td><span class="badge <?= $u['status'] ?>"><?= str_replace('_',' ',$u['status']) ?></span></td><td><?= $u['count'] ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
