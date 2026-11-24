<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    $license  = trim($_POST['license_number'] ?? '');
    $licenseImage = null;

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Handle license image upload
        if (!empty($_FILES['license_image']['name'])) {
            $licenseImage = uploadFile('license_image', 'license');
            if (!$licenseImage) {
                $error = 'Failed to upload license image. Please ensure it is JPG, PNG, or PDF (max 5MB).';
            }
        }

        if (!$error && registerUser($name, $email, $password, $phone, $license, $licenseImage)) {
            attemptLogin($email, $password);
            redirect('index.php');
        } elseif (!$error) {
            $error = 'Email already registered. Please use a different email or login.';
        }
    }
}

$page_title = 'Register';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Create Account</h2>

<div class="form-card" style="max-width:520px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
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
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repeat password">
                    <button type="button" onclick="togglePwd('confirm_password', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray);">&#128065;</button>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Phone <span class="req">*</span></label>
                <input type="text" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>" placeholder="0912345678">
            </div>
            <div class="form-group">
                <label>Driver's License Number</label>
                <input type="text" name="license_number" value="<?= e($_POST['license_number'] ?? '') ?>" placeholder="DL-2024-XXXX">
            </div>
        </div>
        <div class="form-group">
            <label>Driver's License Photo <span style="color:var(--gray);font-weight:400;">(optional but recommended)</span></label>
            <input type="file" name="license_image" accept="image/*,.pdf" style="padding:8px;border:1.5px solid #cbd5e1;border-radius:var(--radius-sm);width:100%;">
            <small style="color:var(--gray);">Upload a photo or scan of your driver's license (JPG, PNG, PDF — max 5MB). This speeds up booking approval.</small>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">&#128221; Create Account</button>
    </form>
    <p style="text-align:center;margin-top:16px;color:var(--gray);">
        Already have an account? <a href="<?= APP_URL ?>/login.php" style="color:var(--primary);font-weight:600;">Login here</a>
    </p>
</div>

<script>
function togglePwd(id, btn) {
    var el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
    btn.textContent = el.type === 'password' ? '\uD83D\uDC41' : '\uD83D\uDC41‍\u200D\u0F3F';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
