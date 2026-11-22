<?php
// =====================================================
// Database Configuration
// Supports both local (XAMPP/WAMP) and cloud (Render/Docker) deployment
// =====================================================

// Application constants — env-configurable for cloud deployment
define('APP_NAME',    'Online Car Rental System');
define('APP_URL',     getenv('APP_URL') ?: 'http://localhost/online-car-rental');

// Database credentials — from env vars or local defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'car_rental_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_SSL',  getenv('DB_SSL')  ?: 'false');

// Upload paths
define('UPLOAD_DIR',  __DIR__ . '/../uploads/');
define('UPLOAD_URL',  APP_URL . '/uploads');

// Attempt database connection
function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // Enable SSL for cloud databases (e.g. TiDB Serverless)
            if (DB_SSL === 'true') {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}
