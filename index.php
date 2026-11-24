<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$cars = getCars(['status' => 'available']);
$totalCars = totalCars();
$customers = totalCustomers();
$bookings = totalBookings();

$page_title = 'Home';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Rent a Car with Real-Time GPS Tracking</h1>
    <p>Book your vehicle online in minutes. Track your rental in real-time. Safe, reliable, and affordable — anywhere in Ethiopia.</p>
    <a href="<?= APP_URL ?>/cars.php" class="btn btn-warning btn-lg">Browse Vehicles</a>

    <div class="hero-stats">
        <div class="hero-stat">
            <div class="num"><?= number_format($totalCars) ?>+</div>
            <div class="label">Vehicles</div>
        </div>
        <div class="hero-stat">
            <div class="num"><?= number_format($customers) ?>+</div>
            <div class="label">Happy Customers</div>
        </div>
        <div class="hero-stat">
            <div class="num"><?= number_format($bookings) ?>+</div>
            <div class="label">Bookings</div>
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

<!-- Scan to Open on Mobile -->
<section class="qr-section">
    <div class="qr-card">
        <div class="qr-text">
            <h2>Open on Your Phone</h2>
            <p>Scan this QR code with your mobile camera to open the app on your phone. Book and track your rental on the go!</p>
            <ul class="qr-features">
                <li>&#128241; Works on any smartphone</li>
                <li>&#128205; Real-time GPS tracking</li>
                <li>&#128179; Book & pay from your phone</li>
                <li>&#128230; Installable as a web app</li>
            </ul>
            <p class="qr-note">Make sure your phone is on the same WiFi network as this computer.</p>
        </div>
        <div class="qr-code">
            <img src="<?= APP_URL ?>/assets/img/mobile-qr.png" alt="Scan to open on mobile" width="200" height="200">
            <div class="qr-label">Scan Me!</div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
