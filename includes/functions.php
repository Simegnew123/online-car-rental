<?php
// =====================================================
// Utility Functions v2.0
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

// Time ago helper
function timeAgo($datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return formatDate($datetime);
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

// Generate avatar color from name
function avatarColor(string $name): string {
    $colors = ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#8b5cf6', '#0891b2', '#db2777'];
    return $colors[crc32($name) % count($colors)];
}

// Get initials from name
function initials(string $name): string {
    $parts = explode(' ', trim($name));
    $out = '';
    foreach ($parts as $p) { $out .= strtoupper($p[0] ?? ''); }
    return substr($out, 0, 2) ?: '?';
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
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if ($prefix === 'license') {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    }
    if (!in_array($ext, $allowed)) {
        return null;
    }
    $name = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    // Subdirectory for cars and licenses
    $subdir = '';
    if ($prefix === 'car') $subdir = 'cars/';
    elseif ($prefix === 'license') $subdir = 'licenses/';

    $destDir = UPLOAD_DIR . $subdir;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0777, true);
    }
    $dest = $destDir . $name;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
        return $subdir . $name;
    }
    return null;
}

// Get image URL for a car (fallback to placeholder)
function carImageUrl(?string $image): string {
    if ($image && file_exists(UPLOAD_DIR . $image)) {
        return UPLOAD_URL . '/' . $image;
    }
    return ''; // Empty triggers CSS placeholder
}

// Get average rating for a car
function carRating(int $carId): float {
    $stmt = db()->prepare('SELECT AVG(rating) FROM reviews WHERE car_id = ?');
    $stmt->execute([$carId]);
    return round((float)$stmt->fetchColumn(), 1);
}

// Get review count for a car
function carReviewCount(int $carId): int {
    $stmt = db()->prepare('SELECT COUNT(*) FROM reviews WHERE car_id = ?');
    $stmt->execute([$carId]);
    return (int)$stmt->fetchColumn();
}

// Get all cars (with optional filters)
function getCars(array $filters = []): array {
    $sql  = 'SELECT * FROM cars WHERE 1=1';
    $args = [];

    if (isset($filters['status']) && $filters['status'] !== '') {
        $sql .= ' AND status = ?';
        $args[] = $filters['status'];
    }
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
    if (!empty($filters['search'])) {
        $sql .= ' AND (name LIKE ? OR brand LIKE ? OR description LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $args[] = $term;
        $args[] = $term;
        $args[] = $term;
    }
    $sql .= ' ORDER BY featured DESC, id ASC';

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

// Check if car is available for given dates
function isCarAvailable(int $carId, string $start, string $end): bool {
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM bookings
         WHERE car_id = ? AND status IN ("pending","confirmed","active")
         AND NOT (end_date < ? OR start_date > ?)'
    );
    $stmt->execute([$carId, $start, $end]);
    return (int)$stmt->fetchColumn() === 0;
}

// Get user's bookings
function getUserBookings(int $userId): array {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, c.image AS car_image, c.type AS car_type
         FROM bookings b JOIN cars c ON b.car_id = c.id
         WHERE b.user_id = ? ORDER BY b.created_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// ---- Dashboard stats ----

function totalCars(): int {
    return (int)db()->query('SELECT COUNT(*) FROM cars')->fetchColumn();
}
function availableCars(): int {
    return (int)db()->query('SELECT COUNT(*) FROM cars WHERE status = "available"')->fetchColumn();
}
function totalBookings(): int {
    return (int)db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
}
function totalRevenue(): float {
    return (float)db()->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = "paid"')->fetchColumn();
}
function totalCustomers(): int {
    return (int)db()->query('SELECT COUNT(*) FROM users WHERE role = "customer"')->fetchColumn();
}
function activeRentals(): int {
    return (int)db()->query('SELECT COUNT(*) FROM bookings WHERE status = "active"')->fetchColumn();
}
