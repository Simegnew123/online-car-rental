<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$carId = (int)($_GET['id'] ?? 0);

// Check for active bookings
$stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE car_id = ? AND status IN ("pending","confirmed","active")');
$stmt->execute([$carId]);
$activeBookings = (int)$stmt->fetchColumn();

if ($activeBookings > 0) {
    // Can't delete, redirect with error
    redirect('admin/cars.php?error=active_bookings');
}

// Delete the car
db()->prepare('DELETE FROM cars WHERE id = ?')->execute([$carId]);

// Also clean up related data
db()->prepare('DELETE FROM reviews WHERE car_id = ?')->execute([$carId]);
db()->prepare('DELETE FROM gps_logs WHERE car_id = ?')->execute([$carId]);

redirect('admin/cars.php');
