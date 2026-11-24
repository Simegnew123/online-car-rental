<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$carId = (int)($_GET['id'] ?? 0);
$car   = getCar($carId);

if (!$car) {
    redirect('admin/cars.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $brand      = trim($_POST['brand'] ?? '');
    $type       = $_POST['type'] ?? 'Sedan';
    $fuelType   = $_POST['fuel_type'] ?? 'Petrol';
    $transmission = $_POST['transmission'] ?? 'Automatic';
    $seats      = (int)($_POST['seats'] ?? 5);
    $doors      = (int)($_POST['doors'] ?? 4);
    $luggage    = (int)($_POST['luggage'] ?? 2);
    $price      = (float)($_POST['price_per_day'] ?? 0);
    $status     = $_POST['status'] ?? 'available';
    $ac         = isset($_POST['air_conditioning']) ? 1 : 0;
    $bluetooth  = isset($_POST['bluetooth']) ? 1 : 0;
    $featured   = isset($_POST['featured']) ? 1 : 0;
    $desc       = trim($_POST['description'] ?? '');
    $image      = uploadFile('image', 'car') ?? $car['image'];

    if (empty($name) || $price <= 0) {
        $error = 'Vehicle name and price are required.';
    } else {
        $stmt = db()->prepare(
            'UPDATE cars SET name=?, brand=?, type=?, fuel_type=?, transmission=?,
                             seats=?, doors=?, luggage=?, price_per_day=?,
                             air_conditioning=?, bluetooth=?, featured=?, description=?, image=?, status=?
             WHERE id=?'
        );
        $stmt->execute([
            $name, $brand, $type, $fuelType, $transmission, $seats, $doors, $luggage,
            $price, $ac, $bluetooth, $featured, $desc, $image, $status, $carId
        ]);
        redirect('admin/cars.php');
    }
}

$page_title = 'Edit Vehicle';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Edit: <?= e($car['name']) ?></h2>

<div class="form-card" style="max-width:700px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Vehicle Name <span class="req">*</span></label>
                <input type="text" name="name" required value="<?= e($car['name']) ?>">
            </div>
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" value="<?= e($car['brand']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <?php foreach (['Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric'] as $t): ?>
                        <option value="<?= $t ?>" <?= $car['type']===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Fuel Type</label>
                <select name="fuel_type">
                    <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                        <option value="<?= $f ?>" <?= $car['fuel_type']===$f?'selected':'' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Transmission</label>
                <select name="transmission">
                    <option value="Automatic" <?= $car['transmission']==='Automatic'?'selected':'' ?>>Automatic</option>
                    <option value="Manual" <?= $car['transmission']==='Manual'?'selected':'' ?>>Manual</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price per Day (ETB) <span class="req">*</span></label>
                <input type="number" name="price_per_day" required min="1" step="0.01" value="<?= $car['price_per_day'] ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Seats</label>
                <input type="number" name="seats" value="<?= $car['seats'] ?>" min="1" max="15">
            </div>
            <div class="form-group">
                <label>Doors</label>
                <input type="number" name="doors" value="<?= $car['doors'] ?>" min="2" max="6">
            </div>
            <div class="form-group">
                <label>Luggage</label>
                <input type="number" name="luggage" value="<?= $car['luggage'] ?>" min="0" max="10">
            </div>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php foreach (['available','rented','maintenance'] as $s): ?>
                    <option value="<?= $s ?>" <?= $car['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><input type="checkbox" name="air_conditioning" <?= $car['air_conditioning']?'checked':'' ?>> Air Conditioning</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="bluetooth" <?= $car['bluetooth']?'checked':'' ?>> Bluetooth</label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="featured" <?= $car['featured']?'checked':'' ?>> Featured</label>
            </div>
        </div>
        <div class="form-group">
            <label>Vehicle Image</label>
            <input type="file" name="image" accept="image/*">
            <?php if ($car['image']): ?>
                <small style="color:var(--gray);">Current: <?= e($car['image']) ?></small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?= e($car['description']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
        <a href="<?= APP_URL ?>/admin/cars.php" class="btn btn-outline">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
