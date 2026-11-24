<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Get bookings with payment info
$stmt = db()->prepare(
    'SELECT b.*, c.name AS car_name, c.image, c.type,
            p.payment_method, p.transaction_id, p.status AS pay_status
     FROM bookings b
     JOIN cars c ON b.car_id = c.id
     LEFT JOIN payments p ON p.booking_id = b.id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

$page_title = 'My Bookings';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">My Bookings</h2>

<?php if (empty($bookings)): ?>
    <div class="empty-state">
        <div class="icon">&#128221;</div>
        <h3>No bookings yet</h3>
        <p>Browse our fleet and book your first vehicle!</p>
        <a href="<?= APP_URL ?>/cars.php" class="btn btn-primary">Browse Vehicles</a>
    </div>
<?php else: ?>
    <div style="max-width:850px;">
        <?php foreach ($bookings as $b): ?>
            <div class="review-card" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
                <div style="flex:1;min-width:240px;">
                    <div class="r-header" style="margin-bottom:8px;">
                        <div class="r-author">
                            <span style="font-size:1.2rem;">&#128663;</span>
                            <?= e($b['car_name']) ?>
                        </div>
                        <span class="badge <?= $b['status'] ?>"><?= $b['status'] ?></span>
                    </div>
                    <div style="margin-bottom:6px;">
                        <strong>Booking #<?= $b['id'] ?></strong> &middot;
                        <?= formatDate($b['start_date']) ?> &rarr; <?= formatDate($b['end_date']) ?>
                        (<?= daysBetween($b['start_date'], $b['end_date']) ?> days)
                    </div>
                    <div style="color:var(--gray);font-size:.88rem;margin-bottom:6px;">
                        Total: <strong style="color:var(--dark);"><?= money($b['total_cost']) ?></strong>
                        &middot; Booked <?= timeAgo($b['created_at']) ?>
                    </div>
                    <!-- Add-ons -->
                    <?php if ($b['with_driver'] || $b['airport_pickup'] || $b['insurance']): ?>
                    <div style="margin-bottom:6px;">
                        <?php if ($b['with_driver']): ?><span class="badge" style="background:#dbeafe;color:#1e40af;font-size:.72rem;">Driver</span> <?php endif; ?>
                        <?php if ($b['airport_pickup']): ?><span class="badge" style="background:#fef3c7;color:#92400e;font-size:.72rem;">Airport</span> <?php endif; ?>
                        <?php if ($b['insurance']): ?><span class="badge" style="background:#dcfce7;color:#16a34a;font-size:.72rem;">Insured</span> <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <!-- Payment status -->
                    <div style="font-size:.85rem;">
                        <?php if ($b['pay_status'] === 'paid'): ?>
                            <span style="color:#16a34a;">&#9989; Payment verified (<?= ucfirst(e($b['payment_method'])) ?>)</span>
                        <?php elseif ($b['pay_status'] === 'pending'): ?>
                            <span style="color:#92400e;">&#9203; Payment pending verification (<?= ucfirst(e($b['payment_method'])) ?>)</span>
                        <?php else: ?>
                            <span style="color:#dc2626;">&#9940; No payment submitted yet</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex flex-wrap" style="gap:8px;align-items:center;">
                    <?php if ($b['status'] === 'pending'): ?>
                        <?php if ($b['pay_status'] !== 'paid'): ?>
                            <a href="<?= APP_URL ?>/payment.php?booking_id=<?= $b['id'] ?>" class="btn btn-success btn-sm">Pay Now</a>
                        <?php endif; ?>
                        <a href="<?= APP_URL ?>/cancel_booking.php?id=<?= $b['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Cancel this booking?')">Cancel</a>
                    <?php elseif ($b['status'] === 'confirmed'): ?>
                        <span class="badge confirmed" style="padding:6px 12px;">Awaiting Pickup</span>
                    <?php elseif ($b['status'] === 'active'): ?>
                        <a href="<?= APP_URL ?>/admin/gps_tracking.php" class="btn btn-primary btn-sm">Track Vehicle</a>
                    <?php elseif ($b['status'] === 'completed'): ?>
                        <a href="<?= APP_URL ?>/car_details.php?id=<?= $b['car_id'] ?>#reviews" class="btn btn-outline btn-sm">Leave Review</a>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/invoice.php?booking_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm" target="_blank">Invoice</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
