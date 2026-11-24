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
            case 'verify_payment':
                db()->prepare('UPDATE payments SET status = "paid" WHERE booking_id = ?')->execute([$bookingId]);
                notify($b['user_id'], 'Payment Verified', 'Your payment for booking #' . $bookingId . ' has been verified. Thank you!', 'payment');
                break;
        }
    }
    redirect('admin/bookings.php');
}

// Fetch all bookings with filters
$statusFilter = $_GET['status'] ?? '';
$sql = 'SELECT b.*, c.name AS car_name, u.full_name AS customer_name, u.email AS customer_email,
               p.payment_method, p.transaction_id, p.status AS pay_status, p.receipt_image
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        JOIN users u ON b.user_id = u.id
        LEFT JOIN payments p ON p.booking_id = b.id';
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
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Period</th>
                <th>Add-ons</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--gray);">No bookings found</td></tr>
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
                    <td>
                        <?php if ($b['with_driver']): ?><span class="badge" style="background:#dbeafe;color:#1e40af;font-size:.7rem;" title="With Driver">Driver</span> <?php endif; ?>
                        <?php if ($b['airport_pickup']): ?><span class="badge" style="background:#fef3c7;color:#92400e;font-size:.7rem;" title="Airport Pickup">Airport</span> <?php endif; ?>
                        <?php if ($b['insurance']): ?><span class="badge" style="background:#dcfce7;color:#16a34a;font-size:.7rem;" title="Insurance">Insured</span> <?php endif; ?>
                        <?php if (!$b['with_driver'] && !$b['airport_pickup'] && !$b['insurance']): ?>
                            <span style="color:var(--gray);font-size:.78rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= money($b['total_cost']) ?></strong></td>
                    <td>
                        <?php if ($b['pay_status'] === 'paid'): ?>
                            <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:.72rem;">Paid</span>
                            <small style="display:block;color:var(--gray);"><?= ucfirst(e($b['payment_method'])) ?></small>
                        <?php elseif ($b['pay_status'] === 'pending'): ?>
                            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.72rem;">Pending</span>
                            <small style="display:block;color:var(--gray);"><?= ucfirst(e($b['payment_method'])) ?></small>
                            <?php if ($b['receipt_image']): ?>
                                <a href="<?= APP_URL ?>/uploads/<?= e($b['receipt_image']) ?>" target="_blank" style="font-size:.72rem;color:var(--primary);">View Receipt</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:var(--gray);font-size:.78rem;">No payment</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                    <td>
                        <div class="d-flex" style="gap:4px;flex-wrap:wrap;">
                            <?php if ($b['pay_status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="action" value="verify_payment">
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Mark payment as verified/paid?')">Verify Pay</button>
                                </form>
                            <?php endif; ?>
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
                            <?php endif; ?>
                            <a href="<?= APP_URL ?>/admin/handover.php?booking_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">Handover</a>
                            <a href="<?= APP_URL ?>/invoice.php?booking_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm" target="_blank">Invoice</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
