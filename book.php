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

// Add-on pricing (ETB)
$DRIVER_RATE   = 500;  // per day
$AIRPORT_FEE   = 300;  // flat
$INSURANCE_RATE = 0.10; // 10% of base rental

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? '';
    $endDate   = $_POST['end_date'] ?? '';
    $notes     = trim($_POST['notes'] ?? '');
    $pickup    = trim($_POST['pickup_location'] ?? '');
    $withDriver  = isset($_POST['with_driver']) ? 1 : 0;
    $airportPickup = isset($_POST['airport_pickup']) ? 1 : 0;
    $insurance   = isset($_POST['insurance']) ? 1 : 0;

    if (empty($startDate) || empty($endDate)) {
        $error = 'Please select both start and end dates.';
    } elseif (strtotime($startDate) < strtotime(date('Y-m-d'))) {
        $error = 'Start date cannot be in the past.';
    } elseif (strtotime($endDate) <= strtotime($startDate)) {
        $error = 'End date must be after start date.';
    } elseif (!isCarAvailable($carId, $startDate, $endDate)) {
        $error = 'This vehicle is already booked for the selected dates. Please choose different dates.';
    } else {
        $days = daysBetween($startDate, $endDate);
        $baseCost = $days * (float)$car['price_per_day'];
        $driverCost = $withDriver ? $DRIVER_RATE * $days : 0;
        $airportCost = $airportPickup ? $AIRPORT_FEE : 0;
        $insuranceCost = $insurance ? round($baseCost * $INSURANCE_RATE, 2) : 0;
        $total = $baseCost + $driverCost + $airportCost + $insuranceCost;

        $stmt = db()->prepare(
            'INSERT INTO bookings (user_id, car_id, start_date, end_date, pickup_location, total_cost,
                                   with_driver, airport_pickup, insurance, driver_cost, airport_cost, insurance_cost, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $_SESSION['user_id'], $carId, $startDate, $endDate, $pickup, $total,
            $withDriver, $airportPickup, $insurance, $driverCost, $airportCost, $insuranceCost, $notes
        ]);
        $bookingId = (int)db()->lastInsertId();

        notify(1, 'New Booking', 'New booking #' . $bookingId . ' for ' . $car['name'] . ' (' . money($total) . ')', 'booking');
        notify($_SESSION['user_id'], 'Booking Created', 'Your booking #' . $bookingId . ' has been created. Total: ' . money($total), 'booking');

        redirect('payment.php?booking_id=' . $bookingId);
    }
}

$today = date('Y-m-d');
$dailyPrice = (float)$car['price_per_day'];

$page_title = 'Book Vehicle';
include __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <h2 class="page-title" style="margin-bottom:0;">Book: <?= e($car['name']) ?></h2>
    <a href="<?= APP_URL ?>/car_details.php?id=<?= $car['id'] ?>" class="btn btn-outline btn-sm">&larr; Back</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:900px;">
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
                <?php if ($car['air_conditioning']): ?><span>&#10052; AC</span><?php endif; ?>
            </div>
            <div class="car-price"><?= money($car['price_per_day']) ?><small>/day</small></div>
        </div>
    </div>

    <!-- Booking form -->
    <div class="form-card">
        <form method="POST" action="" id="bookingForm">
            <div class="form-row">
                <div class="form-group">
                    <label>Pickup Date <span class="req">*</span></label>
                    <input type="date" name="start_date" id="start_date" min="<?= $today ?>" value="<?= e($_POST['start_date'] ?? '') ?>" required onchange="calcTotal()">
                </div>
                <div class="form-group">
                    <label>Return Date <span class="req">*</span></label>
                    <input type="date" name="end_date" id="end_date" min="<?= $today ?>" value="<?= e($_POST['end_date'] ?? '') ?>" required onchange="calcTotal()">
                </div>
            </div>

            <div class="form-group">
                <label>Pickup Location</label>
                <input type="text" name="pickup_location" placeholder="e.g. Bole Airport, Megenagna, Piazza" value="<?= e($_POST['pickup_location'] ?? '') ?>">
            </div>

            <!-- Add-ons -->
            <h4 style="margin:20px 0 12px;font-size:1rem;color:var(--dark2);">Optional Add-ons</h4>
            <div class="addon-list">
                <label class="addon-item">
                    <input type="checkbox" name="with_driver" id="with_driver" onchange="calcTotal()">
                    <div class="addon-info">
                        <div class="addon-title">&#128110; With Driver / Chauffeur</div>
                        <div class="addon-desc">Professional driver for your entire rental period</div>
                    </div>
                    <div class="addon-price">+<?= money($DRIVER_RATE) ?>/day</div>
                </label>

                <label class="addon-item">
                    <input type="checkbox" name="airport_pickup" id="airport_pickup" onchange="calcTotal()">
                    <div class="addon-info">
                        <div class="addon-title">&#9992; Airport Pickup / Drop-off</div>
                        <div class="addon-desc">Meet &amp; greet at Bole International Airport</div>
                    </div>
                    <div class="addon-price">+<?= money($AIRPORT_FEE) ?></div>
                </label>

                <label class="addon-item">
                    <input type="checkbox" name="insurance" id="insurance" onchange="calcTotal()">
                    <div class="addon-info">
                        <div class="addon-title">&#128737; Full Insurance Coverage</div>
                        <div class="addon-desc">Comprehensive coverage (10% of rental base)</div>
                    </div>
                    <div class="addon-price">+10% of base</div>
                </label>
            </div>

            <!-- Live price calculator -->
            <div class="price-breakdown" id="priceBreakdown">
                <div class="price-row"><span>Daily rate</span><span id="row-daily"><?= money($dailyPrice) ?></span></div>
                <div class="price-row"><span>Rental days</span><span id="row-days">0</span></div>
                <div class="price-row"><span>Base rental</span><span id="row-base"><?= money(0) ?></span></div>
                <div class="price-row" id="row-driver-wrap" style="display:none;"><span>Driver service</span><span id="row-driver"><?= money(0) ?></span></div>
                <div class="price-row" id="row-airport-wrap" style="display:none;"><span>Airport pickup</span><span id="row-airport"><?= money(0) ?></span></div>
                <div class="price-row" id="row-insurance-wrap" style="display:none;"><span>Insurance</span><span id="row-insurance"><?= money(0) ?></span></div>
                <div class="price-row price-total"><span>Total</span><span id="row-total"><?= money(0) ?></span></div>
            </div>

            <div class="form-group">
                <label>Special Requests / Notes</label>
                <textarea name="notes" rows="2" placeholder="Any special requests..."><?= e($_POST['notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">&#128221; Confirm Booking</button>
        </form>
    </div>
</div>

<style>
.addon-list { display:flex; flex-direction:column; gap:10px; margin-bottom:20px; }
.addon-item {
    display:flex; align-items:center; gap:12px;
    padding:12px 16px; border:1.5px solid #e2e8f0; border-radius:var(--radius-sm);
    cursor:pointer; transition:var(--transition);
}
.addon-item:hover { border-color:var(--primary-lt); background:#f8fafc; }
.addon-item input[type="checkbox"] { width:20px; height:20px; cursor:pointer; flex-shrink:0; }
.addon-info { flex:1; }
.addon-title { font-weight:600; font-size:.9rem; color:var(--dark); }
.addon-desc { font-size:.78rem; color:var(--gray); }
.addon-price { font-weight:700; font-size:.85rem; color:var(--primary); white-space:nowrap; }
.addon-item:has(input:checked) { border-color:var(--success); background:var(--success-lt); }
.price-breakdown {
    background:var(--lighter); border:1px solid #e2e8f0; border-radius:var(--radius-sm);
    padding:16px; margin-bottom:20px;
}
.price-row { display:flex; justify-content:space-between; font-size:.88rem; padding:4px 0; color:var(--gray); }
.price-row span:last-child { font-weight:600; color:var(--dark); }
.price-total { border-top:2px solid #e2e8f0; margin-top:8px; padding-top:10px; font-size:1.05rem; font-weight:800; }
.price-total span:last-child { color:var(--primary); font-size:1.15rem; }
@media(max-width:768px){ .addon-item{ flex-wrap:wrap; } .addon-price{ width:100%; text-align:right; } }
</style>

<script>
var DAILY_PRICE = <?= $dailyPrice ?>;
var DRIVER_RATE = <?= $DRIVER_RATE ?>;
var AIRPORT_FEE = <?= $AIRPORT_FEE ?>;
var INSURANCE_RATE = <?= $INSURANCE_RATE ?>;

function calcTotal() {
    var start = document.getElementById('start_date').value;
    var end = document.getElementById('end_date').value;
    var days = 0;

    if (start && end) {
        var diff = (new Date(end) - new Date(start)) / 86400000;
        days = Math.max(1, Math.round(diff));
    }

    var base = days * DAILY_PRICE;
    var withDriver = document.getElementById('with_driver').checked;
    var airport = document.getElementById('airport_pickup').checked;
    var insurance = document.getElementById('insurance').checked;

    var driverCost = withDriver ? DRIVER_RATE * days : 0;
    var airportCost = airport ? AIRPORT_FEE : 0;
    var insuranceCost = insurance ? Math.round(base * INSURANCE_RATE * 100) / 100 : 0;
    var total = base + driverCost + airportCost + insuranceCost;

    document.getElementById('row-days').textContent = days + (days === 1 ? ' day' : ' days');
    document.getElementById('row-base').textContent = 'ETB ' + base.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});

    document.getElementById('row-driver-wrap').style.display = withDriver ? 'flex' : 'none';
    document.getElementById('row-driver').textContent = 'ETB ' + driverCost.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});

    document.getElementById('row-airport-wrap').style.display = airport ? 'flex' : 'none';
    document.getElementById('row-airport').textContent = 'ETB ' + airportCost.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});

    document.getElementById('row-insurance-wrap').style.display = insurance ? 'flex' : 'none';
    document.getElementById('row-insurance').textContent = 'ETB ' + insuranceCost.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});

    document.getElementById('row-total').textContent = 'ETB ' + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}
calcTotal();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
