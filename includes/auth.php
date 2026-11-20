<?php
// =====================================================
// Authentication Helper Functions
// =====================================================

session_start();

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// Check if logged-in user is admin
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Redirect helper
function redirect(string $path): void {
    header("Location: " . APP_URL . "/" . ltrim($path, '/'));
    exit;
}

// Require login — redirect if not authenticated
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Require admin — redirect if not admin
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php');
    }
}

// Attempt login with email + password
function attemptLogin(string $email, string $password): bool {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'suspended') {
            return false;
        }
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['email']     = $user['email'];
        return true;
    }
    return false;
}

// Register a new user
function registerUser(string $name, string $email, string $password, string $phone, string $license = ''): bool {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO users (full_name, email, password, phone, license_number, role, status)
         VALUES (?, ?, ?, ?, ?, "customer", "active")'
    );
    try {
        $stmt->execute([$name, $email, $hash, $phone, $license]);
        return true;
    } catch (PDOException $e) {
        return false; // duplicate email etc.
    }
}

// Logout
function logout(): void {
    session_unset();
    session_destroy();
}

// Get current user record
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
