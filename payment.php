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

// Check if payment already exists
$payStmt = db()->prepare('SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC LIMIT 1');
$payStmt->execute([$bookingId]);
$existingPayment = $payStmt->fetch();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? '';
    $validMethods = ['cash', 'telebirr', 'bank_transfer', 'chapa'];

    if (!in_array($method, $validMethods)) {
        $error = 'Please select a valid payment method.';
    } else {
        $txnId = genTransactionId();
        $receiptFile = uploadFile('receipt', 'receipt');

        // For bank_transfer and telebirr: upload receipt, status = pending (admin approves)
        // For chapa: would redirect to gateway (placeholder for now)
        // For cash: pay at pickup, status = pending
        $payStatus = 'pending';

        if ($existingPayment) {
            // Update existing payment record
            db()->prepare('UPDATE payments SET amount=?, payment_method=?, transaction_id=?, receipt_image=?, status=? WHERE id=?')
                ->execute([$booking['total_cost'], $method, $txnId, $receiptFile ?? $existingPayment['receipt_image'], $payStatus, $existingPayment['id']]);
        } else {
            $stmt = db()->prepare(
                'INSERT INTO payments (booking_id, amount, payment_method, transaction_id, receipt_image, status)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $bookingId,
                $booking['total_cost'],
                $method,
                $txnId,
                $receiptFile,
                $payStatus
            ]);
        }

        // Keep booking as pending until admin confirms payment
        notify($_SESSION['user_id'], 'Payment Submitted', 'Your payment for booking #' . $bookingId . ' via ' . $method . ' is pending verification. Transaction: ' . $txnId, 'payment');
        notify(1, 'Payment Pending Review', 'Payment of ' . money($booking['total_cost']) . ' via ' . $method . ' for booking #' . $bookingId . ' needs verification.', 'payment');

        $success = 'Payment submitted! Your transaction ID is ' . $txnId . '. An admin will verify your payment shortly.';
    }
}

$page_title = 'Payment';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Payment for Booking #<?= $booking['id'] ?></h2>

<?php if ($success): ?>
    <div class="alert alert-success">
        <h3 style="margin-bottom:8px;">&#9989; <?= e($success) ?></h3>
        <p>Booking status: <strong>Pending</strong> — waiting for admin to confirm your payment.</p>
        <div style="margin-top:16px;display:flex;gap:12px;">
            <a href="<?= APP_URL ?>/invoice.php?booking_id=<?= $booking['id'] ?>" class="btn btn-primary" target="_blank">&#128196; View Invoice</a>
            <a href="<?= APP_URL ?>/my_bookings.php" class="btn btn-outline">My Bookings</a>
        </div>
    </div>
<?php elseif ($existingPayment && $existingPayment['status'] === 'paid'): ?>
    <div class="alert alert-success">
        <h3 style="margin-bottom:8px;">&#9989; Payment Confirmed</h3>
        <p>Your payment of <?= money($booking['total_cost']) ?> has been verified.</p>
        <a href="<?= APP_URL ?>/invoice.php?booking_id=<?= $booking['id'] ?>" class="btn btn-primary" target="_blank">&#128196; View Invoice</a>
    </div>
<?php else: ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:900px;">
    <!-- Booking summary -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">Booking Summary</h3>
        <div class="car-brand" style="margin-bottom:12px;"><?= e($booking['car_name']) ?></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Period:</span>
            <span><?= formatDate($booking['start_date']) ?> - <?= formatDate($booking['end_date']) ?></span>
        </div>
        <?php if ($booking['pickup_location']): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Pickup:</span>
            <span><?= e($booking['pickup_location']) ?></span>
        </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Days:</span>
            <span><?= daysBetween($booking['start_date'], $booking['end_date']) ?></span>
        </div>
        <?php if ($booking['with_driver']): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Driver service:</span>
            <span><?= money($booking['driver_cost']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($booking['airport_pickup']): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Airport pickup:</span>
            <span><?= money($booking['airport_cost']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($booking['insurance']): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Insurance:</span>
            <span><?= money($booking['insurance_cost']) ?></span>
        </div>
        <?php endif; ?>
        <div class="price-breakdown" style="margin-top:16px;padding:12px;background:var(--primary);color:#fff;border-radius:var(--radius-sm);">
            <div style="display:flex;justify-content:space-between;font-size:1.2rem;font-weight:800;">
                <span>Total Due:</span><span><?= money($booking['total_cost']) ?></span>
            </div>
        </div>
    </div>

    <!-- Payment form -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">Select Payment Method</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom:16px;"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($existingPayment): ?>
            <div class="alert alert-info" style="margin-bottom:16px;">
                Previous payment (<?= e($existingPayment['payment_method']) ?>) is <strong><?= e($existingPayment['status']) ?></strong>.
                You may resubmit below.
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" id="paymentForm">
            <div class="payment-methods">
                <label class="pay-option">
                    <input type="radio" name="payment_method" value="telebirr" required onchange="toggleReceipt()">
                    <div class="pay-option-body">
                        <div class="pay-option-title">&#128241; Telebirr</div>
                        <div class="pay-option-desc">Pay via Telebirr app or USSD</div>
                    </div>
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment_method" value="bank_transfer" onchange="toggleReceipt()">
                    <div class="pay-option-body">
                        <div class="pay-option-title">&#127974; Bank Transfer</div>
                        <div class="pay-option-desc">Transfer to our bank account and upload receipt</div>
                    </div>
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment_method" value="chapa" onchange="toggleReceipt()">
                    <div class="pay-option-body">
                        <div class="pay-option-title">&#128179; Chapa (Card / Wallet)</div>
                        <div class="pay-option-desc">Pay online with card or local wallet via Chapa</div>
                    </div>
                </label>

                <label class="pay-option">
                    <input type="radio" name="payment_method" value="cash" onchange="toggleReceipt()">
                    <div class="pay-option-body">
                        <div class="pay-option-title">&#128176; Cash at Pickup</div>
                        <div class="pay-option-desc">Pay with cash when you pick up the vehicle</div>
                    </div>
                </label>
            </div>

            <!-- Receipt upload (shown for telebirr, bank_transfer, chapa) -->
            <div id="receiptSection" style="display:none;margin-top:16px;">
                <div class="form-group">
                    <label>Upload Payment Receipt <span style="color:var(--gray);font-weight:400;">(screenshot or photo)</span></label>
                    <input type="file" name="receipt" accept="image/*,.pdf" style="padding:8px;border:1.5px solid #cbd5e1;border-radius:var(--radius-sm);width:100%;">
                    <small style="color:var(--gray);">Accepted: JPG, PNG, PDF. Max 5MB. Admin will verify before confirming.</small>
                </div>

                <div class="bank-details" id="bankDetails" style="display:none;">
                    <h4 style="margin-bottom:8px;font-size:.95rem;">Bank Transfer Details:</h4>
                    <div style="background:var(--light);padding:12px;border-radius:var(--radius-sm);font-size:.88rem;line-height:1.8;">
                        <strong>Bank:</strong> Commercial Bank of Ethiopia (CBE)<br>
                        <strong>Account Name:</strong> CarRental Ethiopia Ltd.<br>
                        <strong>Account No:</strong> 1000-2003-4567-8901<br>
                        <strong>Reference:</strong> Booking #<?= $booking['id'] ?>
                    </div>
                </div>
            </div>

            <!-- Cash instructions -->
            <div id="cashSection" style="display:none;margin-top:16px;">
                <div class="alert alert-info">
                    <strong>Cash Payment:</strong> Please bring <?= money($booking['total_cost']) ?> in cash when picking up your vehicle at the agreed location. Your booking will be confirmed by the admin.
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:16px;">&#9989; Submit Payment</button>
        </form>
    </div>
</div>

<style>
.payment-methods { display:flex; flex-direction:column; gap:10px; }
.pay-option {
    display:flex; align-items:center; gap:12px;
    padding:14px 16px; border:1.5px solid #e2e8f0; border-radius:var(--radius-sm);
    cursor:pointer; transition:var(--transition);
}
.pay-option:hover { border-color:var(--primary-lt); background:#f8fafc; }
.pay-option input[type="radio"] { width:20px; height:20px; cursor:pointer; flex-shrink:0; }
.pay-option-body { flex:1; }
.pay-option-title { font-weight:700; font-size:.92rem; color:var(--dark); }
.pay-option-desc { font-size:.8rem; color:var(--gray); }
.pay-option:has(input:checked) { border-color:var(--primary); background:#eff6ff; }
</style>

<script>
function toggleReceipt() {
    var checked = document.querySelector('input[name="payment_method"]:checked');
    if (!checked) return;
    var method = checked.value;
    var receiptSection = document.getElementById('receiptSection');
    var bankDetails = document.getElementById('bankDetails');
    var cashSection = document.getElementById('cashSection');

    if (method === 'cash') {
        receiptSection.style.display = 'none';
        bankDetails.style.display = 'none';
        cashSection.style.display = 'block';
    } else {
        cashSection.style.display = 'none';
        receiptSection.style.display = 'block';
        bankDetails.style.display = (method === 'bank_transfer') ? 'block' : 'none';
    }
}
</script>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
