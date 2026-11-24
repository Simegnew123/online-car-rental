<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$filters = [
    'type'       => $_GET['type'] ?? '',
    'fuel'       => $_GET['fuel'] ?? '',
    'min_price'  => $_GET['min_price'] ?? '',
    'max_price'  => $_GET['max_price'] ?? '',
    'search'     => $_GET['search'] ?? '',
];
$cars = getCars($filters);
$page_title = 'Vehicles';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Available Vehicles</h2>

<form method="GET" action="" class="filter-bar" id="filterForm">
    <div class="search-box">
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Search by name, brand..." oninput="autoFilter()">
    </div>
    <div class="form-group">
        <label>Type</label>
        <select name="type" onchange="autoFilter()">
            <option value="">All Types</option>
            <?php foreach (['Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric'] as $t): ?>
                <option value="<?= $t ?>" <?= $filters['type']===$t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Fuel</label>
        <select name="fuel" onchange="autoFilter()">
            <option value="">All Fuel</option>
            <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                <option value="<?= $f ?>" <?= $filters['fuel']===$f ? 'selected' : '' ?>><?= $f ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Min Price</label>
        <input type="number" name="min_price" value="<?= e($filters['min_price']) ?>" placeholder="0" oninput="autoFilter()">
    </div>
    <div class="form-group">
        <label>Max Price</label>
        <input type="number" name="max_price" value="<?= e($filters['max_price']) ?>" placeholder="10000" oninput="autoFilter()">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="<?= APP_URL ?>/cars.php" class="btn btn-outline">Reset</a>
    </div>
</form>

<?php if (empty($cars)): ?>
    <div class="empty-state">
        <div class="icon">&#128663;</div>
        <h3>No vehicles found</h3>
        <p>Try adjusting your filters or search terms.</p>
        <a href="<?= APP_URL ?>/cars.php" class="btn btn-outline">Clear Filters</a>
    </div>
<?php else: ?>

<div class="car-grid">
    <?php foreach ($cars as $car): ?>
    <div class="car-card">
        <div class="car-card-img">
            <?php $imgUrl = carImageUrl($car['image']); ?>
            <?php if ($imgUrl): ?>
                <img src="<?= e($imgUrl) ?>" alt="<?= e($car['name']) ?>">
            <?php else: ?>
                &#128663;
            <?php endif; ?>
            <?php if ($car['featured']): ?>
                <span class="featured-tag">Featured</span>
            <?php endif; ?>
        </div>
        <div class="car-card-body">
            <h3><?= e($car['name']) ?></h3>
            <div class="car-brand"><?= e($car['brand'] ?: 'Vehicle') ?> &middot; <?= e($car['type']) ?></div>
            <div class="car-meta">
                <span>&#9981; <?= e($car['fuel_type']) ?></span>
                <span>&#9881; <?= e($car['transmission']) ?></span>
                <span>&#128102; <?= $car['seats'] ?> seats</span>
                <?php if ($car['air_conditioning']): ?><span>&#10052; AC</span><?php endif; ?>
            </div>
            <div class="car-card-footer">
                <div>
                    <div class="car-price"><?= money($car['price_per_day']) ?><small>/day</small></div>
                    <span class="star-display" style="font-size:.8rem;"><?= str_repeat('&#9733;', (int)carRating($car['id'])) ?> <?= carRating($car['id']) ?>/5</span>
                </div>
                <a href="<?= APP_URL ?>/car_details.php?id=<?= $car['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<script>
var filterTimer = null;
function autoFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(function() {
        document.getElementById('filterForm').submit();
    }, 500);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
