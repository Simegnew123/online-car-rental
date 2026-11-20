<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$page_title = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Admin Dashboard</h2>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">&#128663;</div>
        <div class="stat-value"><?= totalCars() ?></div>
        <div class="stat-label">Total Vehicles</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#9989;</div>
        <div class="stat-value"><?= availableCars() ?></div>
        <div class="stat-label">Available</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128221;</div>
        <div class="stat-value"><?= totalBookings() ?></div>
        <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128176;</div>
        <div class="stat-value"><?= money(totalRevenue()) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128101;</div>
        <div class="stat-value"><?= totalCustomers() ?></div>
        <div class="stat-label">Customers</div>
    </div>
</div>

<div class="d-flex flex-wrap">
    <a href="<?= APP_URL ?>/admin/cars.php" class="btn btn-primary">Manage Vehicles</a>
    <a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-success">Manage Bookings</a>
    <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-warning">Manage Users</a>
    <a href="<?= APP_URL ?>/admin/gps_tracking.php" class="btn btn-outline">GPS Tracking</a>
    <a href="<?= APP_URL ?>/admin/reports.php" class="btn btn-primary">Reports</a>
</div>

<?php
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
                <td><span class="badge <?= $b['status']==='completed'?'available':($b['status']==='cancelled'?'unavailable':'rented') ?>"><?= $b['status'] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
