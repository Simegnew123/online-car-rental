<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $bookingId = (int)$_GET['id'];
    $action    = $_GET['action'];

    $stmt = db()->prepare('SELECT * FROM bookings WHERE id = ?');
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if ($booking) {
        switch ($action) {
            case 'confirm':
                db()->prepare('UPDATE bookings SET status = "confirmed" WHERE id = ?')->execute([$bookingId]);
                notify($booking['user_id'], 'Booking Confirmed', 'Booking #' . $bookingId . ' has been confirmed.', 'booking');
                break;
            case 'activate':
                db()->prepare('UPDATE bookings SET status = "active" WHERE id = ?')->execute([$bookingId]);
                db()->prepare('UPDATE cars SET status = "rented" WHERE id = ?')->execute([$booking['car_id']]);
                notify($booking['user_id'], 'Rental Active', 'Booking #' . $bookingId . ' is now active. Vehicle handed over.', 'booking');
                break;
            case 'complete':
                db()->prepare('UPDATE bookings SET status = "completed" WHERE id = ?')->execute([$bookingId]);
                db()->prepare('UPDATE cars SET status = "available" WHERE id = ?')->execute([$booking['car_id']]);
                notify($booking['user_id'], 'Rental Completed', 'Booking #' . $bookingId . ' completed. Vehicle returned.', 'booking');
                break;
            case 'cancel':
                db()->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ?')->execute([$bookingId]);
                db()->prepare('UPDATE cars SET status = "available" WHERE id = ?')->execute([$booking['car_id']]);
                db()->prepare('UPDATE payments SET status = "refunded" WHERE booking_id = ?')->execute([$bookingId]);
                notify($booking['user_id'], 'Booking Cancelled', 'Booking #' . $bookingId . ' has been cancelled by admin.', 'booking');
                break;
        }
    }
    redirect('admin/bookings.php');
}

// Fetch all bookings
$stmt = db()->prepare(
    'SELECT b.*, c.name AS car_name, u.full_name AS customer_name, u.email AS customer_email
     FROM bookings b
     JOIN cars c  ON b.car_id  = c.id
     JOIN users u ON b.user_id = u.id
     ORDER BY b.created_at DESC'
);
$stmt->execute();
$bookings = $stmt->fetchAll();

$page_title = 'Manage Bookings';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Manage Bookings</h2>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Period</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td><?= e($b['customer_name']) ?><br><small><?= e($b['customer_email']) ?></small></td>
                <td><?= e($b['car_name']) ?></td>
                <td><?= formatDate($b['start_date']) ?><br>&rarr; <?= formatDate($b['end_date']) ?></td>
                <td><?= money($b['total_cost']) ?></td>
                <td><span class="badge <?= $b['status']==='completed'?'available':($b['status']==='cancelled'?'unavailable':'rented') ?>"><?= $b['status'] ?></span></td>
                <td class="d-flex flex-wrap">
                    <?php if ($b['status'] === 'pending'): ?>
                        <a href="?action=confirm&id=<?= $b['id'] ?>" class="btn btn-success btn-sm">Confirm</a>
                        <a href="?action=cancel&id=<?= $b['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirmAction('Cancel this booking?')">Cancel</a>
                    <?php elseif ($b['status'] === 'confirmed'): ?>
                        <a href="?action=activate&id=<?= $b['id'] ?>" class="btn btn-primary btn-sm">Activate</a>
                    <?php elseif ($b['status'] === 'active'): ?>
                        <a href="?action=complete&id=<?= $b['id'] ?>" class="btn btn-success btn-sm">Complete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
