<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$carId = (int)($_GET['id'] ?? 0);

// Check no active bookings
$stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE car_id = ? AND status IN ("pending","confirmed","active")');
$stmt->execute([$carId]);
if ((int)$stmt->fetchColumn() > 0) {
    echo '<div class="alert alert-danger">Cannot delete: this vehicle has active bookings. Cancel them first.</div>';
    echo '<p><a href="' . APP_URL . '/admin/cars.php" class="btn btn-primary">Back</a></p>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

db()->prepare('DELETE FROM cars WHERE id = ?')->execute([$carId]);
redirect('admin/cars.php');
