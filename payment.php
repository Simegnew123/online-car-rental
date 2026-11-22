<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$bookingId = (int)($_GET['booking_id'] ?? 0);

$stmt = db()->prepare(
    'SELECT b.*, c.name AS car_name, c.image, c.price_per_day
     FROM bookings b JOIN cars c ON b.car_id = c.id
     WHERE b.id = ? AND b.user_id = ?'
);
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('my_bookings.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? '';
    $validMethods = ['cash', 'telebirr', 'credit_card', 'bank_transfer'];

    if (!in_array($method, $validMethods)) {
        $error = 'Please select a valid payment method.';
    } else {
        $txnId = genTransactionId();

        $stmt = db()->prepare(
            'INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $bookingId,
            $booking['total_cost'],
            $method,
            $txnId,
            $method === 'cash' ? 'pending' : 'paid'
        ]);

        // Update booking status
        $newStatus = ($method === 'cash') ? 'confirmed' : 'confirmed';
        db()->prepare('UPDATE bookings SET status = ? WHERE id = ?')
            ->execute([$newStatus, $bookingId]);

        // Update car status to rented
        db()->prepare('UPDATE cars SET status = "rented" WHERE id = ?')
            ->execute([$booking['car_id']]);

        notify($_SESSION['user_id'], 'Payment Recorded', 'Payment of ' . money($booking['total_cost']) . ' via ' . $method . ' for booking #' . $bookingId, 'payment');

        $success = 'Payment successful! Your booking is confirmed. Transaction ID: ' . $txnId;
    }
}

$page_title = 'Payment';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Payment for Booking #<?= $booking['id'] ?></h2>

<?php if ($success): ?>
    <div class="alert alert-success">
        <h3>&#9989; <?= e($success) ?></h3>
        <p>Your booking is confirmed. You'll receive a notification when your vehicle is ready for pickup.</p>
        <a href="<?= APP_URL ?>/my_bookings.php" class="btn btn-primary">View My Bookings</a>
    </div>
<?php else: ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:800px;">
    <!-- Booking summary -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">Booking Summary</h3>
        <div class="car-brand" style="margin-bottom:12px;"><?= e($booking['car_name']) ?></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Period:</span>
            <span><?= formatDate($booking['start_date']) ?> - <?= formatDate($booking['end_date']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Days:</span>
            <span><?= daysBetween($booking['start_date'], $booking['end_date']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:1.3rem;font-weight:700;border-top:2px solid var(--gray-lt);padding-top:12px;margin-top:12px;">
            <span>Total:</span>
            <span style="color:var(--primary);"><?= money($booking['total_cost']) ?></span>
        </div>
    </div>

    <!-- Payment form -->
    <div class="form-card">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <h3 style="margin-bottom:16px;">Select Payment Method</h3>

            <label class="payment-option">
                <input type="radio" name="payment_method" value="telebirr" checked>
                <div class="payment-icon">&#128241;</div>
                <div>
                    <strong>Telebirr</strong>
                    <div style="font-size:.85rem;color:var(--gray);">Pay via USSD or Telebirr app</div>
                </div>
            </label>

            <label class="payment-option">
                <input type="telebirr" name="payment_method" value="credit_card" style="display:none;">
                <div class="payment-icon">&#128179;</div>
                <div>
                    <strong>Credit / Debit Card</strong>
                    <div style="font-size:.85rem;color:var(--gray);">Visa, Mastercard</div>
                </div>
            </label>

            <label class="payment-option">
                <input type="radio" name="payment_method" value="bank_transfer" style="display:none;">
                <div class="payment-icon">&#127974;</div>
                <div>
                    <strong>Bank Transfer</strong>
                    <div style="font-size:.85rem;color:var(--gray);">Direct bank deposit</div>
                </div>
            </label>

            <label class="payment-option">
                <input type="radio" name="payment_method" value="cash" style="display:none;">
                <div class="payment-icon">&#128176;</div>
                <div>
                    <strong>Cash on Pickup</strong>
                    <div style="font-size:.85rem;color:var(--gray);">Pay at the counter</div>
                </div>
            </label>

            <button type="submit" class="btn btn-success btn-block btn-lg">Confirm Payment</button>
        </form>
    </div>
</div>

<script>
// Fix radio inputs
document.querySelectorAll('.payment-option').forEach(function(opt, idx) {
    var input = opt.querySelector('input');
    if (input && input.type !== 'radio') {
        input.type = 'radio';
        input.name = 'payment_method';
    }
    if (idx === 0) input.checked = true;

    opt.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(function(o) {
            o.classList.remove('selected');
        });
        opt.classList.add('selected');
        input.checked = true;
    });
});
// Pre-select first
document.querySelector('.payment-option').classList.add('selected');
</script>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
