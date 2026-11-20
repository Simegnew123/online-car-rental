<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$bookingId = (int)($_GET['id'] ?? 0);

// Verify booking belongs to user
$stmt = db()->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if ($booking && in_array($booking['status'], ['pending', 'confirmed'])) {
    // Cancel booking
    db()->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ?')->execute([$bookingId]);

    // Make car available again
    db()->prepare('UPDATE cars SET status = "available" WHERE id = ?')->execute([$booking['car_id']]);

    // Refund payment if exists
    db()->prepare('UPDATE payments SET status = "refunded" WHERE booking_id = ?')->execute([$bookingId]);

    notify($_SESSION['user_id'], 'Booking Cancelled', 'Booking #' . $bookingId . ' has been cancelled.', 'booking');
}

redirect('my_bookings.php');
