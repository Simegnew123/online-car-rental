<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$filters = [
    'type'       => $_GET['type'] ?? '',
    'fuel'       => $_GET['fuel'] ?? '',
    'min_price'  => $_GET['min_price'] ?? '',
    'max_price'  => $_GET['max_price'] ?? '',
];
$cars = getCars($filters);
$page_title = 'Vehicles';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Available Vehicles</h2>

<form method="GET" action="" class="filter-bar">
    <div class="form-group">
        <label>Type</label>
        <select name="type">
            <option value="">All</option>
            <?php foreach (['Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric'] as $t): ?>
                <option value="<?= $t ?>" <?= $filters['type']===$t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Fuel</label>
        <select name="fuel">
            <option value="">All</option>
            <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                <option value="<?= $f ?>" <?= $filters['fuel']===$f ? 'selected' : '' ?>><?= $f ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Min Price</label>
        <input type="number" name="min_price" value="<?= e($filters['min_price']) ?>" placeholder="0">
    </div>
    <div class="form-group">
        <label>Max Price</label>
        <input type="number" name="max_price" value="<?= e($filters['max_price']) ?>" placeholder="5000">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="<?= APP_URL ?>/cars.php" class="btn btn-outline">Reset</a>
    </div>
</form>

<div class="car-grid">
    <?php if (empty($cars)): ?>
        <p>No vehicles found matching your filters.</p>
    <?php else: foreach ($cars as $car): ?>
    <div class="car-card">
        <div class="car-card-img">&#128663;</div>
        <div class="car-card-body">
            <h3><?= e($car['name']) ?></h3>
            <div class="car-meta">
                <span><?= e($car['type']) ?></span>
                <span><?= e($car['fuel_type']) ?></span>
                <span><?= e($car['transmission']) ?></span>
                <span><?= $car['seats'] ?> seats</span>
            </div>
            <div class="car-price"><?= money($car['price_per_day']) ?>/day</div>
            <div class="d-flex mt-2">
                <span class="badge <?= $car['status'] ?>"><?= str_replace('_', ' ', $car['status']) ?></span>
                <a href="<?= APP_URL ?>/car_details.php?id=<?= $car['id'] ?>" class="btn btn-primary btn-sm">View</a>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
