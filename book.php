<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$carId = (int)($_GET['car_id'] ?? 0);
$car   = getCar($carId);

if (!$car) {
    redirect('cars.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? '';
    $endDate   = $_POST['end_date'] ?? '';
    $notes     = trim($_POST['notes'] ?? '');

    if (empty($startDate) || empty($endDate)) {
        $error = 'Please select both start and end dates.';
    } elseif (strtotime($startDate) < strtotime(date('Y-m-d'))) {
        $error = 'Start date cannot be in the past.';
    } elseif (strtotime($endDate) <= strtotime($startDate)) {
        $error = 'End date must be after start date.';
    } elseif (!isCarAvailable($carId, $startDate, $endDate)) {
        $error = 'This vehicle is not available for the selected dates.';
    } else {
        $days = daysBetween($startDate, $endDate);
        $total = $days * (float)$car['price_per_day'];

        $stmt = db()->prepare(
            'INSERT INTO bookings (user_id, car_id, start_date, end_date, total_cost, notes)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$_SESSION['user_id'], $carId, $startDate, $endDate, $total, $notes]);
        $bookingId = (int)db()->lastInsertId();

        // Notify admin
        notify(1, 'New Booking', 'New booking #' . $bookingId . ' for ' . $car['name'], 'booking');
        // Notify customer
        notify($_SESSION['user_id'], 'Booking Created', 'Your booking #' . $bookingId . ' has been created. Total: ' . money($total), 'booking');

        redirect('payment.php?booking_id=' . $bookingId);
    }
}

$today = date('Y-m-d');

$page_title = 'Book Vehicle';
include __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <h2 class="page-title" style="margin-bottom:0;">Book: <?= e($car['name']) ?></h2>
    <a href="<?= APP_URL ?>/car_details.php?id=<?= $car['id'] ?>" class="btn btn-outline btn-sm">Back</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:800px;">
    <!-- Car summary -->
    <div class="car-card" style="margin:0;">
        <div class="car-card-img" style="height:200px;">
            <?php $imgUrl = carImageUrl($car['image']); ?>
            <?php if ($imgUrl): ?>
                <img src="<?= e($imgUrl) ?>" alt="<?= e($car['name']) ?>">
            <?php else: ?>
                &#128663;
            <?php endif; ?>
        </div>
        <div class="car-card-body">
            <h3><?= e($car['name']) ?></h3>
            <div class="car-meta">
                <span>&#9981; <?= e($car['fuel_type']) ?></span>
                <span>&#9881; <?= e($car['transmission']) ?></span>
                <span>&#128102; <?= $car['seats'] ?> seats</span>
            </div>
            <div class="car-price"><?= money($car['price_per_day']) ?><small>/day</small></div>
        </div>
    </div>

    <!-- Booking form -->
    <div class="form-card">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Start Date <span class="req">*</span></label>
                <input type="date" name="start_date" required min="<?= $today ?>" value="<?= e($_POST['start_date'] ?? $today) ?>">
            </div>
            <div class="form-group">
                <label>End Date <span class="req">*</span></label>
                <input type="date" name="end_date" required min="<?= $today ?>" value="<?= e($_POST['end_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Notes (optional)</label>
                <textarea name="notes" rows="2" placeholder="Pickup location, special requests..."><?= e($_POST['notes'] ?? '') ?></textarea>
            </div>
            <div id="booking-total" class="alert alert-info" style="display:none;">
                <strong>Total: </strong><span id="total-amount"></span>
            </div>
            <button type="submit" class="btn btn-success btn-block btn-lg">Proceed to Payment</button>
        </form>
    </div>
</div>

<script>
(function() {
    var pricePerDay = <?= (float)$car['price_per_day'] ?>;
    var startEl = document.querySelector('[name=start_date]');
    var endEl = document.querySelector('[name=end_date]');
    var totalDiv = document.getElementById('booking-total');
    var totalAmount = document.getElementById('total-amount');

    function calcTotal() {
        if (startEl.value && endEl.value) {
            var diff = (new Date(endEl.value) - new Date(startEl.value)) / 86400000;
            var days = Math.max(1, Math.ceil(diff));
            var total = days * pricePerDay;
            totalAmount.textContent = 'ETB ' + total.toFixed(2) + ' (' + days + ' day' + (days > 1 ? 's' : '') + ')';
            totalDiv.style.display = 'block';
        } else {
            totalDiv.style.display = 'none';
        }
    }
    startEl.addEventListener('change', calcTotal);
    endEl.addEventListener('change', calcTotal);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
