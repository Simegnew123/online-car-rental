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
    <div class="empty-state">
        <div class="icon">&#128221;</div>
        <h3>No bookings yet</h3>
        <p>Browse our fleet and book your first vehicle!</p>
        <a href="<?= APP_URL ?>/cars.php" class="btn btn-primary">Browse Vehicles</a>
    </div>
<?php else: ?>
    <div style="max-width:800px;">
        <?php foreach ($bookings as $b): ?>
            <div class="review-card" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div style="flex:1;min-width:200px;">
                    <div class="r-header" style="margin-bottom:8px;">
                        <div class="r-author">
                            <span style="font-size:1.2rem;">&#128663;</span>
                            <?= e($b['car_name']) ?>
                        </div>
                        <span class="badge <?= $b['status'] ?>"><?= $b['status'] ?></span>
                    </div>
                    <div style="margin-bottom:4px;">
                        <strong>Booking #<?= $b['id'] ?></strong> &middot;
                        <?= formatDate($b['start_date']) ?> &rarr; <?= formatDate($b['end_date']) ?>
                    </div>
                    <div style="color:var(--gray);font-size:.9rem;">
                        Total: <?= money($b['total_cost']) ?> &middot; Booked <?= timeAgo($b['created_at']) ?>
                    </div>
                </div>
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <?php if ($b['status'] === 'pending'): ?>
                        <a href="<?= APP_URL ?>/payment.php?booking_id=<?= $b['id'] ?>" class="btn btn-success btn-sm">Pay Now</a>
                        <a href="<?= APP_URL ?>/cancel_booking.php?id=<?= $b['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Cancel this booking?')">Cancel</a>
                    <?php elseif ($b['status'] === 'confirmed'): ?>
                        <span class="badge confirmed" style="padding:6px 12px;">Awaiting Pickup</span>
                    <?php elseif ($b['status'] === 'active'): ?>
                        <a href="<?= APP_URL ?>/admin/gps_tracking.php" class="btn btn-primary btn-sm">Track Vehicle</a>
                    <?php elseif ($b['status'] === 'completed'): ?>
                        <a href="<?= APP_URL ?>/car_details.php?id=<?= $b['car_id'] ?>#reviews" class="btn btn-outline btn-sm">Leave Review</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
