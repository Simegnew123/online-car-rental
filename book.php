<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$carId = (int)($_GET['car_id'] ?? 0);
$car = getCar($carId);

if (!$car) {
    echo '<div class="alert alert-danger">Vehicle not found.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_date'] ?? '';
    $end   = $_POST['end_date']   ?? '';
    $pickup = trim($_POST['pickup_location'] ?? '');

    if (empty($start) || empty($end)) {
        $error = 'Please select start and end dates.';
    } elseif (strtotime($end) < strtotime($start)) {
        $error = 'End date must be after start date.';
    } elseif (!isCarAvailable($carId, $start, $end)) {
        $error = 'This vehicle is already booked for the selected dates.';
    } else {
        $days  = daysBetween($start, $end);
        $total = $days * (float)$car['price_per_day'];

        $stmt = db()->prepare(
            'INSERT INTO bookings (user_id, car_id, start_date, end_date, pickup_location, total_cost, status)
             VALUES (?, ?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$_SESSION['user_id'], $carId, $start, $end, $pickup, $total]);

        $bookingId = (int)db()->lastInsertId();

        // Update car status
        db()->prepare('UPDATE cars SET status = "rented" WHERE id = ?')->execute([$carId]);

        // Notify admin
        $adminId = (int)db()->query('SELECT id FROM users WHERE role = "admin" LIMIT 1')->fetchColumn();
        notify($adminId, 'New Booking', 'New booking #' . $bookingId . ' for ' . $car['name'], 'booking');
        notify($_SESSION['user_id'], 'Booking Created', 'Your booking #' . $bookingId . ' is pending confirmation.', 'booking');

        redirect('payment.php?booking_id=' . $bookingId);
    }
}

$page_title = 'Book Vehicle';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Book: <?= e($car['name']) ?></h2>

<div class="form-card" style="max-width:550px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        <strong><?= e($car['name']) ?></strong> &mdash; <?= money($car['price_per_day']) ?>/day
    </div>

    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Start Date *</label>
                <input type="date" id="start_date" name="start_date" required min="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label>End Date *</label>
                <input type="date" id="end_date" name="end_date" required min="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Pickup Location</label>
            <input type="text" name="pickup_location" placeholder="e.g. Bole Airport, Addis Ababa">
        </div>
        <input type="hidden" id="price-per-day" value="<?= $car['price_per_day'] ?>">
        <div class="alert alert-success" id="total-cost" style="font-size:1.1rem;">Select dates to see total cost</div>
        <button type="submit" class="btn btn-success btn-block">Proceed to Payment</button>
    </form>

    <p class="text-center mt-2">
        <a href="<?= APP_URL ?>/car_details.php?id=<?= $carId ?>">Back to Vehicle</a>
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
