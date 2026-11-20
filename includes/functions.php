<?php
// =====================================================
// Utility Functions
// =====================================================

// Sanitize output (prevent XSS)
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Format currency (ETB)
function money($amount): string {
    return 'ETB ' . number_format((float)$amount, 2);
}

// Format date for display
function formatDate($date): string {
    return date('M d, Y', strtotime($date));
}

// Format datetime
function formatDateTime($dt): string {
    return date('M d, Y H:i', strtotime($dt));
}

// Days between two dates
function daysBetween($start, $end): int {
    $diff = strtotime($end) - strtotime($start);
    return max(1, (int)round($diff / 86400));
}

// Generate a random transaction ID
function genTransactionId(): string {
    return 'TXN' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
}

// Create a notification for a user
function notify(int $userId, string $title, string $message, string $type = 'system'): void {
    $stmt = db()->prepare(
        'INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $title, $message, $type]);
}

// Count unread notifications for current user
function unreadCount(): int {
    if (!isLoggedIn()) return 0;
    $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}

// Upload a file and return the stored filename or null
function uploadFile(string $field, string $prefix = 'file'): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    $name = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_DIR . $name;
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
        return $name;
    }
    return null;
}

// Get average rating for a car
function carRating(int $carId): float {
    $stmt = db()->prepare('SELECT AVG(rating) FROM reviews WHERE car_id = ?');
    $stmt->execute([$carId]);
    return round((float)$stmt->fetchColumn(), 1);
}

// Get all cars (with optional filters)
function getCars(array $filters = []): array {
    $sql  = 'SELECT * FROM cars WHERE 1=1';
    $args = [];

    if (!empty($filters['type'])) {
        $sql .= ' AND type = ?';
        $args[] = $filters['type'];
    }
    if (!empty($filters['fuel'])) {
        $sql .= ' AND fuel_type = ?';
        $args[] = $filters['fuel'];
    }
    if (!empty($filters['min_price'])) {
        $sql .= ' AND price_per_day >= ?';
        $args[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= ' AND price_per_day <= ?';
        $args[] = $filters['max_price'];
    }
    if (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $args[] = $filters['status'];
    } else {
        $sql .= ' AND status != "unavailable"';
    }

    $sql .= ' ORDER BY price_per_day ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll();
}

// Get a single car by ID
function getCar(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM cars WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// Get bookings for a user
function getUserBookings(int $userId): array {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, c.brand AS car_brand, c.image AS car_image
         FROM bookings b JOIN cars c ON b.car_id = c.id
         WHERE b.user_id = ? ORDER BY b.created_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Check car availability for date range
function isCarAvailable(int $carId, string $startDate, string $endDate): bool {
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM bookings
         WHERE car_id = ? AND status NOT IN ("cancelled","completed")
         AND (start_date <= ? AND end_date >= ?)'
    );
    $stmt->execute([$carId, $endDate, $startDate]);
    return (int)$stmt->fetchColumn() === 0;
}

// Count total cars
function totalCars(): int {
    return (int)db()->query('SELECT COUNT(*) FROM cars')->fetchColumn();
}

// Count available cars
function availableCars(): int {
    return (int)db()->query('SELECT COUNT(*) FROM cars WHERE status = "available"')->fetchColumn();
}

// Count total bookings
function totalBookings(): int {
    return (int)db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
}

// Count total revenue (paid payments only)
function totalRevenue(): float {
    return (float)db()->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = "paid"')->fetchColumn();
}

// Count total users (customers only)
function totalCustomers(): int {
    return (int)db()->query('SELECT COUNT(*) FROM users WHERE role = "customer"')->fetchColumn();
}
