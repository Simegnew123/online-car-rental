<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $bookingId = (int)$_POST['booking_id'];
    $action    = $_POST['action'];

    $stmt = db()->prepare('SELECT b.*, c.name AS car_name, u.full_name AS customer FROM bookings b JOIN cars c ON b.car_id = c.id JOIN users u ON b.user_id = u.id WHERE b.id = ?');
    $stmt->execute([$bookingId]);
    $b = $stmt->fetch();

    if ($b) {
        switch ($action) {
            case 'confirm':
                db()->prepare('UPDATE bookings SET status = "confirmed" WHERE id = ?')->execute([$bookingId]);
                db()->prepare('UPDATE cars SET status = "rented" WHERE id = ?')->execute([$b['car_id']]);
                notify($b['user_id'], 'Booking Confirmed', 'Your booking #' . $bookingId . ' for ' . $b['car_name'] . ' is confirmed!', 'booking');
                break;
            case 'start':
                db()->prepare('UPDATE bookings SET status = "active" WHERE id = ?')->execute([$bookingId]);
                db()->prepare('UPDATE cars SET status = "rented" WHERE id = ?')->execute([$b['car_id']]);
                notify($b['user_id'], 'Rental Started', 'Your rental for ' . $b['car_name'] . ' has started. Track it on the map!', 'gps');
                break;
            case 'complete':
                db()->prepare('UPDATE bookings SET status = "completed" WHERE id = ?')->execute([$bookingId]);
                db()->prepare('UPDATE cars SET status = "available" WHERE id = ?')->execute([$b['car_id']]);
                notify($b['user_id'], 'Rental Completed', 'Your rental for ' . $b['car_name'] . ' is complete. Please leave a review!', 'booking');
                break;
            case 'cancel':
                db()->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ?')->execute([$bookingId]);
                db()->prepare('UPDATE cars SET status = "available" WHERE id = ?')->execute([$b['car_id']]);
                notify($b['user_id'], 'Booking Cancelled', 'Your booking #' . $bookingId . ' has been cancelled by admin.', 'booking');
                break;
        }
    }
    redirect('bookings.php');
}

// Fetch all bookings with filters
$statusFilter = $_GET['status'] ?? '';
$sql = 'SELECT b.*, c.name AS car_name, u.full_name AS customer_name, u.email AS customer_email
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        JOIN users u ON b.user_id = u.id';
if ($statusFilter) {
    $sql .= ' WHERE b.status = ' . db()->quote($statusFilter);
}
$sql .= ' ORDER BY b.created_at DESC';
$bookings = db()->query($sql)->fetchAll();

$page_title = 'Manage Bookings';
include __DIR__ . '/../includes/header.php';
?>

<div class="section-header">
    <h2 class="page-title" style="margin-bottom:0;">Manage Bookings</h2>
    <div class="d-flex">
        <a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-outline btn-sm <?= !$statusFilter?'active':'' ?>">All</a>
        <?php foreach (['pending','confirmed','active','completed','cancelled'] as $s): ?>
            <a href="?status=<?= $s ?>" class="btn btn-outline btn-sm <?= $statusFilter===$s?'active':'' ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr><th>#</th><th>Customer</th><th>Vehicle</th><th>Period</th><th>Total</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray);">No bookings found</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= $b['id'] ?></td>
                    <td>
                        <strong><?= e($b['customer_name']) ?></strong><br>
                        <small style="color:var(--gray);"><?= e($b['customer_email']) ?></small>
                    </td>
                    <td><?= e($b['car_name']) ?></td>
                    <td><?= formatDate($b['start_date']) ?><br><small><?= formatDate($b['end_date']) ?></small></td>
                    <td><?= money($b['total_cost']) ?></td>
                    <td><span class="badge <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                    <td>
                        <div class="d-flex" style="gap:4px;">
                            <?php if ($b['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="action" value="confirm">
                                    <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this booking?')">Cancel</button>
                                </form>
                            <?php elseif ($b['status'] === 'confirmed'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="action" value="start">
                                    <button type="submit" class="btn btn-primary btn-sm">Start Rental</button>
                                </form>
                            <?php elseif ($b['status'] === 'active'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="action" value="complete">
                                    <button type="submit" class="btn btn-success btn-sm">Complete</button>
                                </form>
                                <a href="<?= APP_URL ?>/admin/gps_tracking.php" class="btn btn-outline btn-sm">Track</a>
                            <?php else: ?>
                                <span style="color:var(--gray);">-</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
