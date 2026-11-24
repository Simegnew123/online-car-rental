<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $plate      = trim($_POST['plate_number'] ?? '');
    $brand      = trim($_POST['brand'] ?? '');
    $type       = $_POST['type'] ?? 'Sedan';
    $fuelType   = $_POST['fuel_type'] ?? 'Petrol';
    $transmission = $_POST['transmission'] ?? 'Automatic';
    $seats      = (int)($_POST['seats'] ?? 5);
    $doors      = (int)($_POST['doors'] ?? 4);
    $luggage    = (int)($_POST['luggage'] ?? 2);
    $price      = (float)($_POST['price_per_day'] ?? 0);
    $ac         = isset($_POST['air_conditioning']) ? 1 : 0;
    $bluetooth  = isset($_POST['bluetooth']) ? 1 : 0;
    $featured   = isset($_POST['featured']) ? 1 : 0;
    $desc       = trim($_POST['description'] ?? '');
    $image      = uploadFile('image', 'car') ?? '';

    if (empty($name) || $price <= 0) {
        $error = 'Vehicle name and price are required.';
    } else {
        $stmt = db()->prepare(
            'INSERT INTO cars (name, plate_number, brand, type, fuel_type, transmission, seats, doors, luggage,
                               price_per_day, air_conditioning, bluetooth, featured, description, image, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, "available")'
        );
        $stmt->execute([
            $name, $plate, $brand, $type, $fuelType, $transmission, $seats, $doors, $luggage,
            $price, $ac, $bluetooth, $featured, $desc, $image
        ]);
        redirect('admin/cars.php');
    }
}

$page_title = 'Add Vehicle';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Add New Vehicle</h2>

<div class="form-card" style="max-width:700px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Vehicle Name <span class="req">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Toyota Corolla 2024">
            </div>
            <div class="form-group">
                <label>Plate Number</label>
                <input type="text" name="plate_number" placeholder="e.g. AA 12345">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" placeholder="e.g. Toyota">
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <?php foreach (['Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric'] as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Fuel Type</label>
                <select name="fuel_type">
                    <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                        <option value="<?= $f ?>"><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Transmission</label>
                <select name="transmission">
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price per Day (ETB) <span class="req">*</span></label>
                <input type="number" name="price_per_day" required min="1" step="0.01" placeholder="1500">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Seats</label>
                <input type="number" name="seats" value="5" min="1" max="15">
            </div>
            <div class="form-group">
                <label>Doors</label>
                <input type="number" name="doors" value="4" min="2" max="6">
            </div>
            <div class="form-group">
                <label>Luggage</label>
                <input type="number" name="luggage" value="2" min="0" max="10">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><input type="checkbox" name="air_conditioning" checked> Air Conditioning</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="bluetooth" checked> Bluetooth</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="featured"> Featured</label>
            </div>
        </div>
        <div class="form-group">
            <label>Vehicle Image</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Describe the vehicle..."></textarea>
        </div>
        <button type="submit" class="btn btn-success btn-lg">Add Vehicle</button>
        <a href="<?= APP_URL ?>/admin/cars.php" class="btn btn-outline">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
