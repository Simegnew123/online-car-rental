<?php
// =====================================================
// Database Configuration (XAMPP / WAMP)
// =====================================================

define('APP_NAME', 'Online Car Rental System');
define('APP_URL',  'http://localhost:8080/online-car-rental');

// Database credentials — default XAMPP settings
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'car_rental_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Upload paths
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads');

// Email configuration
define('MAIL_FROM', 'noreply@carrental.et');
define('MAIL_FROM_NAME', 'CarRental GPS');

// Add-on pricing defaults (ETB)
define('DRIVER_RATE_PER_DAY', 500);
define('AIRPORT_FEE_FLAT', 300);
define('INSURANCE_RATE', 0.10);

// Database connection
function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}
