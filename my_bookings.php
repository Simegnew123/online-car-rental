<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$bookings = getUserBookings($_SESSION['user_id']);
$page_title = 'My Bookings';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">My Bookings</h2>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info">You have no bookings yet. <a href="<?= APP_URL ?>/cars.php">Browse vehicles</a> to get started.</div>
<?php else: ?>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Start</th>
                <th>End</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td><?= e($b['car_name']) ?></td>
                <td><?= formatDate($b['start_date']) ?></td>
                <td><?= formatDate($b['end_date']) ?></td>
                <td><?= money($b['total_cost']) ?></td>
                <td><span class="badge <?= $b['status'] === 'completed' ? 'available' : ($b['status'] === 'cancelled' ? 'unavailable' : 'rented') ?>"><?= $b['status'] ?></span></td>
                <td>
                    <?php if ($b['status'] === 'pending' || $b['status'] === 'confirmed'): ?>
                        <a href="<?= APP_URL ?>/cancel_booking.php?id=<?= $b['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirmAction('Cancel this booking?')">Cancel</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
