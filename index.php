<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$cars = getCars(['status' => 'available']);
$page_title = 'Home';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Rent a Car with Real-Time GPS Tracking</h1>
    <p>Book your vehicle online in minutes. Track your rental in real-time. Safe, reliable, and affordable.</p>
    <a href="<?= APP_URL ?>/cars.php" class="btn btn-warning">Browse Vehicles</a>
</section>

<h2 class="page-title">Featured Vehicles</h2>
<div class="car-grid">
    <?php foreach (array_slice($cars, 0, 4) as $car): ?>
    <div class="car-card">
        <div class="car-card-img"><?= $car['image'] ? '' : '&#128663;' ?></div>
        <div class="car-card-body">
            <h3><?= e($car['name']) ?></h3>
            <div class="car-meta">
                <span><?= e($car['type']) ?></span>
                <span><?= e($car['fuel_type']) ?></span>
                <span><?= e($car['transmission']) ?></span>
            </div>
            <div class="car-price"><?= money($car['price_per_day']) ?>/day</div>
            <p class="mt-2">
                <a href="<?= APP_URL ?>/car_details.php?id=<?= $car['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
            </p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="text-center mt-2">
    <a href="<?= APP_URL ?>/cars.php" class="btn btn-outline">View All Vehicles</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
