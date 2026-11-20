<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$bookingId = (int)($_GET['booking_id'] ?? 0);

// Fetch booking with car info
$stmt = db()->prepare(
    'SELECT b.*, c.name AS car_name, c.price_per_day
     FROM bookings b JOIN cars c ON b.car_id = c.id
     WHERE b.id = ? AND b.user_id = ?'
);
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    echo '<div class="alert alert-danger">Booking not found.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? 'cash';
    $txnId  = genTransactionId();

    // Create payment record
    $stmt = db()->prepare(
        'INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status)
         VALUES (?, ?, ?, ?, "paid")'
    );
    $stmt->execute([$bookingId, $booking['total_cost'], $method, $txnId]);

    // Update booking status to confirmed
    db()->prepare('UPDATE bookings SET status = "confirmed" WHERE id = ?')->execute([$bookingId]);

    // Notify
    notify($_SESSION['user_id'], 'Payment Confirmed',
        'Payment of ' . money($booking['total_cost']) . ' for booking #' . $bookingId . ' is confirmed.', 'payment');
    $adminId = (int)db()->query('SELECT id FROM users WHERE role = "admin" LIMIT 1')->fetchColumn();
    notify($adminId, 'Payment Received',
        'Payment of ' . money($booking['total_cost']) . ' received for booking #' . $bookingId, 'payment');

    $success = 'Payment successful! Your booking is confirmed. Transaction ID: ' . $txnId;
}

$page_title = 'Payment';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Payment</h2>

<div class="form-card" style="max-width:500px;">
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
        <p class="text-center">
            <a href="<?= APP_URL ?>/my_bookings.php" class="btn btn-primary">View My Bookings</a>
        </p>
    <?php else: ?>

    <div class="alert alert-info">
        <strong>Booking #<?= $booking['id'] ?></strong><br>
        Vehicle: <?= e($booking['car_name']) ?><br>
        Period: <?= formatDate($booking['start_date']) ?> &rarr; <?= formatDate($booking['end_date']) ?><br>
        <strong style="font-size:1.3rem;"><?= money($booking['total_cost']) ?></strong>
    </div>

    <form method="POST" action="">
        <div class="form-group">
            <label>Payment Method</label>
            <select name="payment_method">
                <option value="cash">Cash (Pay at Pickup)</option>
                <option value="telebirr">Telebirr</option>
                <option value="card">Credit/Debit Card</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success btn-block">Confirm Payment</button>
    </form>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
