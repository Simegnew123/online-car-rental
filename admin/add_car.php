<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $brand        = trim($_POST['brand'] ?? '');
    $type         = $_POST['type'] ?? 'Sedan';
    $fuel_type    = $_POST['fuel_type'] ?? 'Petrol';
    $transmission = $_POST['transmission'] ?? 'Automatic';
    $seats        = (int)($_POST['seats'] ?? 5);
    $price        = (float)($_POST['price_per_day'] ?? 0);
    $description  = trim($_POST['description'] ?? '');
    $status       = $_POST['status'] ?? 'available';

    if (empty($name) || $price <= 0) {
        $error = 'Name and price are required.';
    } else {
        $image = uploadFile('image', 'car');

        $stmt = db()->prepare(
            'INSERT INTO cars (name, brand, type, fuel_type, transmission, seats, price_per_day, image, description, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $brand, $type, $fuel_type, $transmission, $seats, $price, $image, $description, $status]);
        redirect('admin/cars.php');
    }
}

$page_title = 'Add Vehicle';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Add New Vehicle</h2>

<div class="form-card" style="max-width:600px;">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Vehicle Name *</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand">
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <?php foreach (['Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric'] as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Fuel Type</label>
                <select name="fuel_type">
                    <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                        <option value="<?= $f ?>"><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Transmission</label>
                <select name="transmission">
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Seats</label>
                <input type="number" name="seats" value="5" min="1" max="15">
            </div>
            <div class="form-group">
                <label>Price per Day (ETB) *</label>
                <input type="number" name="price_per_day" required step="0.01" min="0">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Vehicle Image</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="available">Available</option>
                    <option value="under_repair">Under Repair</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-success btn-block">Add Vehicle</button>
        <a href="<?= APP_URL ?>/admin/cars.php" class="btn btn-outline btn-block mt-2">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
