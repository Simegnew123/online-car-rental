<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$cars = getCars(['status' => '']); // all cars regardless of status
$page_title = 'Manage Vehicles';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Manage Vehicles</h2>
<p><a href="<?= APP_URL ?>/admin/add_car.php" class="btn btn-success">+ Add New Vehicle</a></p>

<div class="table-card mt-2">
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Type</th><th>Fuel</th><th>Price/Day</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($cars as $car): ?>
            <tr>
                <td><?= $car['id'] ?></td>
                <td><?= e($car['name']) ?></td>
                <td><?= e($car['type']) ?></td>
                <td><?= e($car['fuel_type']) ?></td>
                <td><?= money($car['price_per_day']) ?></td>
                <td><span class="badge <?= $car['status'] ?>"><?= str_replace('_', ' ', $car['status']) ?></span></td>
                <td class="d-flex">
                    <a href="<?= APP_URL ?>/admin/edit_car.php?id=<?= $car['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="<?= APP_URL ?>/admin/delete_car.php?id=<?= $car['id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirmAction('Delete this vehicle?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
