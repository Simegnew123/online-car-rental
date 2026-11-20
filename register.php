<?php
require_once __DIR__ . '/includes/auth.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    $license  = trim($_POST['license_number'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        if (registerUser($name, $email, $password, $phone, $license)) {
            attemptLogin($email, $password);
            redirect('index.php');
        } else {
            $error = 'Email already registered. Please use a different email or login.';
        }
    }
}

$page_title = 'Register';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Create Account</h2>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Driver's License Number</label>
            <input type="text" name="license_number" value="<?= e($_POST['license_number'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>

    <p class="text-center mt-2">
        Already have an account? <a href="<?= APP_URL ?>/login.php">Login here</a>
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
