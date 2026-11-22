<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } elseif (attemptLogin($email, $password)) {
        redirect('index.php');
    } else {
        $error = 'Invalid email or password.';
    }
}

$page_title = 'Login';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Login</h2>

<div class="form-card" style="max-width:420px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com" autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Your password">
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
    </form>

    <p class="text-center mt-2">
        No account? <a href="<?= APP_URL ?>/register.php">Register here</a>
    </p>

    <div class="demo-credentials" style="margin-top:16px;padding:12px;background:var(--gray-lt);border-radius:8px;font-size:.85rem;">
        <strong>Demo Accounts:</strong><br>
        Admin: admin@carrental.com / admin123<br>
        Customer: customer@test.com / customer123
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
