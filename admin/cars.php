<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$cars = getCars([]);

$page_title = 'Manage Vehicles';
include __DIR__ . '/../includes/header.php';
?>

<div class="section-header">
    <h2 class="page-title" style="margin-bottom:0;">Manage Vehicles</h2>
    <a href="<?= APP_URL ?>/admin/add_car.php" class="btn btn-success">&#43; Add Vehicle</a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr><th>ID</th><th>Vehicle</th><th>Type</th><th>Price/Day</th><th>Status</th><th>Featured</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($cars as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td>
                    <strong><?= e($c['name']) ?></strong><br>
                    <small style="color:var(--gray);"><?= e($c['brand'] ?: '') ?></small>
                </td>
                <td><?= e($c['type']) ?></td>
                <td><?= money($c['price_per_day']) ?></td>
                <td><span class="badge <?= $c['status'] ?>"><?= $c['status'] ?></span></td>
                <td><?= $c['featured'] ? '&#11088;' : '-' ?></td>
                <td>
                    <a href="<?= APP_URL ?>/admin/edit_car.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <a href="<?= APP_URL ?>/admin/delete_car.php?id=<?= $c['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete <?= e($c['name']) ?>?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
