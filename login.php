<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (attemptLogin($email, $password)) {
        if (isAdmin()) {
            redirect('admin/dashboard.php');
        } else {
            redirect('index.php');
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

$page_title = 'Login';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Login</h2>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p class="text-center mt-2">
        Don't have an account? <a href="<?= APP_URL ?>/register.php">Register here</a>
    </p>
    <p class="text-center" style="font-size:.8rem;color:var(--gray);margin-top:10px;">
        Demo Admin: admin@carrental.com / admin123<br>
        Demo Customer: customer@test.com / customer123
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
