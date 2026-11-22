<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$bookingId = (int)($_GET['id'] ?? 0);

// Verify booking belongs to user
$stmt = db()->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('my_bookings.php');
}

if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed') {
    // Update booking
    db()->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ?')
        ->execute([$bookingId]);

    // Make car available again
    db()->prepare('UPDATE cars SET status = "available" WHERE id = ?')
        ->execute([$booking['car_id']]);

    notify($_SESSION['user_id'], 'Booking Cancelled', 'Your booking #' . $bookingId . ' has been cancelled.', 'booking');
    notify(1, 'Booking Cancelled', 'Customer cancelled booking #' . $bookingId, 'booking');
}

redirect('my_bookings.php');
