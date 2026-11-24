<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$cars = getCars(['status' => 'available']);

$page_title = 'Home';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Rent a Car with Real-Time GPS Tracking</h1>
    <p>Book your vehicle online in minutes. Track your rental in real-time. Safe, reliable, and affordable — anywhere in Ethiopia.</p>
    <a href="<?= APP_URL ?>/cars.php" class="btn btn-hero btn-lg">Browse Vehicles</a>

    <div class="hero-features">
        <div class="hero-feature">
            <span class="hero-feature-icon">&#128205;</span>
            <span class="hero-feature-text">Real-Time GPS Tracking</span>
        </div>
        <div class="hero-feature">
            <span class="hero-feature-icon">&#9889;</span>
            <span class="hero-feature-text">Instant Online Booking</span>
        </div>
        <div class="hero-feature">
            <span class="hero-feature-icon">&#128241;</span>
            <span class="hero-feature-text">24/7 Customer Support</span>
        </div>
    </div>
</section>

<div class="d-flex justify-between mb-2">
    <h2 class="page-title" style="margin:0;">Featured Vehicles</h2>
    <a href="<?= APP_URL ?>/cars.php" class="btn btn-outline btn-sm">View All &rarr;</a>
</div>

<div class="car-grid">
    <?php foreach (array_slice($cars, 0, 6) as $car): ?>
    <div class="car-card">
        <div class="car-card-img">
            <?php $img = carImageUrl($car['image']); ?>
            <?php if ($img): ?>
                <img src="<?= $img ?>" alt="<?= e($car['name']) ?>" loading="lazy">
            <?php else: ?>
                <span>&#128663;</span>
            <?php endif; ?>
            <?php if ($car['featured']): ?><span class="featured-tag">Featured</span><?php endif; ?>
        </div>
        <div class="car-card-body">
            <h3><?= e($car['name']) ?></h3>
            <div class="car-brand"><?= e($car['brand']) ?></div>
            <div class="car-meta">
                <span><?= e($car['type']) ?></span>
                <span>&#128663; <?= e($car['fuel_type']) ?></span>
                <span>&#128100; <?= (int)$car['seats'] ?> seats</span>
                <span><?= e($car['transmission']) ?></span>
            </div>
            <div class="car-price"><?= money($car['price_per_day']) ?> <small>/ day</small></div>
            <div class="car-card-footer">
                <span class="badge <?= $car['status'] ?>"><?= $car['status'] ?></span>
                <a href="<?= APP_URL ?>/car_details.php?id=<?= $car['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (count($cars) === 0): ?>
    <div class="alert alert-info">No vehicles currently available. Check back soon!</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
