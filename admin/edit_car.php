<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$carId = (int)($_GET['id'] ?? 0);
$car = getCar($carId);

if (!$car) {
    echo '<div class="alert alert-danger">Vehicle not found.</div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

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
        $image = $car['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadFile('image', 'car');
        }

        $stmt = db()->prepare(
            'UPDATE cars SET name=?, brand=?, type=?, fuel_type=?, transmission=?, seats=?, price_per_day=?, image=?, description=?, status=? WHERE id=?'
        );
        $stmt->execute([$name, $brand, $type, $fuel_type, $transmission, $seats, $price, $image, $description, $status, $carId]);
        redirect('admin/cars.php');
    }
}

$page_title = 'Edit Vehicle';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Edit: <?= e($car['name']) ?></h2>

<div class="form-card" style="max-width:600px;">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Vehicle Name *</label>
            <input type="text" name="name" required value="<?= e($car['name']) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" value="<?= e($car['brand']) ?>">
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <?php foreach (['Sedan','SUV','Hatchback','Pickup','Van','Luxury','Electric'] as $t): ?>
                        <option value="<?= $t ?>" <?= $car['type']===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Fuel Type</label>
                <select name="fuel_type">
                    <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                        <option value="<?= $f ?>" <?= $car['fuel_type']===$f?'selected':'' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Transmission</label>
                <select name="transmission">
                    <option value="Automatic" <?= $car['transmission']==='Automatic'?'selected':'' ?>>Automatic</option>
                    <option value="Manual" <?= $car['transmission']==='Manual'?'selected':'' ?>>Manual</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Seats</label>
                <input type="number" name="seats" value="<?= $car['seats'] ?>" min="1" max="15">
            </div>
            <div class="form-group">
                <label>Price per Day (ETB) *</label>
                <input type="number" name="price_per_day" required step="0.01" min="0" value="<?= $car['price_per_day'] ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3"><?= e($car['description']) ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Vehicle Image</label>
                <input type="file" name="image" accept="image/*">
                <?php if ($car['image']): ?>
                    <p style="font-size:.85rem;margin-top:6px;">
                        Current: <a href="<?= UPLOAD_URL ?>/<?= e($car['image']) ?>" target="_blank">View</a>
                    </p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['available','rented','under_repair','unavailable'] as $s): ?>
                        <option value="<?= $s ?>" <?= $car['status']===$s?'selected':'' ?>><?= str_replace('_',' ',$s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Vehicle</button>
        <a href="<?= APP_URL ?>/admin/cars.php" class="btn btn-outline btn-block mt-2">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
