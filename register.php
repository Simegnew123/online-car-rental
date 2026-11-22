<?php
require_once __DIR__ . '/includes/auth.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    $license  = trim($_POST['license_number'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
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

<div class="form-card" style="max-width:500px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name <span class="req">*</span></label>
            <input type="text" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="John Doe">
        </div>
        <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password <span class="req">*</span></label>
                <input type="password" name="password" required placeholder="Min 6 characters">
            </div>
            <div class="form-group">
                <label>Confirm Password <span class="req">*</span></label>
                <input type="password" name="confirm_password" required placeholder="Repeat password">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Phone <span class="req">*</span></label>
                <input type="text" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>" placeholder="0912345678">
            </div>
            <div class="form-group">
                <label>Driver's License</label>
                <input type="text" name="license_number" value="<?= e($_POST['license_number'] ?? '') ?>" placeholder="DL-2024-XXXX">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
    </form>

    <p class="text-center mt-2">
        Already have an account? <a href="<?= APP_URL ?>/login.php">Login here</a>
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
