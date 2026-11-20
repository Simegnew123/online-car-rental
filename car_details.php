<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$carId = (int)($_GET['id'] ?? 0);
$car = getCar($carId);

if (!$car) {
    echo '<div class="alert alert-danger">Vehicle not found.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch reviews
$stmt = db()->prepare(
    'SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.car_id = ? ORDER BY r.created_at DESC'
);
$stmt->execute([$carId]);
$reviews = $stmt->fetchAll();

$page_title = e($car['name']);
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title"><?= e($car['name']) ?></h2>

<div class="car-card" style="max-width:700px;">
    <div class="car-card-img" style="height:250px;">&#128663;</div>
    <div class="car-card-body" style="padding:30px;">
        <div class="car-meta">
            <span><strong>Brand:</strong> <?= e($car['brand']) ?></span>
            <span><strong>Type:</strong> <?= e($car['type']) ?></span>
            <span><strong>Fuel:</strong> <?= e($car['fuel_type']) ?></span>
            <span><strong>Transmission:</strong> <?= e($car['transmission']) ?></span>
            <span><strong>Seats:</strong> <?= $car['seats'] ?></span>
        </div>
        <p style="margin:16px 0;"><?= e($car['description']) ?></p>
        <div class="car-price" style="font-size:1.5rem;"><?= money($car['price_per_day']) ?>/day</div>
        <p class="mt-2">
            <span class="badge <?= $car['status'] ?>"><?= str_replace('_', ' ', $car['status']) ?></span>
            <span style="margin-left:10px;">Rating: <?= carRating($carId) ?>/5</span>
        </p>
        <?php if ($car['status'] === 'available'): ?>
            <a href="<?= APP_URL ?>/book.php?car_id=<?= $car['id'] ?>" class="btn btn-success mt-2">Book Now</a>
        <?php else: ?>
            <button disabled class="btn btn-outline mt-2">Not Available</button>
        <?php endif; ?>
    </div>
</div>

<h3 class="page-title mt-2">Reviews (<?= count($reviews) ?>)</h3>
<div style="max-width:700px;">
    <?php if (empty($reviews)): ?>
        <p>No reviews yet.</p>
    <?php else: foreach ($reviews as $rev): ?>
        <div class="alert alert-info">
            <strong><?= e($rev['full_name']) ?></strong> &mdash; <?= str_repeat('&#9733;', $rev['rating']) ?>
            <p><?= e($rev['comment']) ?></p>
            <small><?= formatDateTime($rev['created_at']) ?></small>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
