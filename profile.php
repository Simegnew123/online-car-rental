<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $license  = trim($_POST['license_number'] ?? '');

    if (empty($fullName) || empty($phone)) {
        $error = 'Name and phone are required.';
    } else {
        db()->prepare('UPDATE users SET full_name = ?, phone = ?, license_number = ? WHERE id = ?')
            ->execute([$fullName, $phone, $license, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $fullName;
        $success = 'Profile updated successfully!';
        $user = currentUser();
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        db()->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_BCRYPT), $_SESSION['user_id']]);
        $success = 'Password changed successfully!';
    }
}

// Get booking stats
$stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$bookingCount = (int)$stmt->fetchColumn();

$page_title = 'My Profile';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">My Profile</h2>

<div class="profile-header">
    <div class="profile-avatar" style="background:<?= avatarColor($user['full_name']) ?>;">
        <?= e(initials($user['full_name'])) ?>
    </div>
    <div>
        <h3 style="margin:0;"><?= e($user['full_name']) ?></h3>
        <div style="color:var(--gray);"><?= e($user['email']) ?></div>
        <span class="badge <?= $user['role'] ?>" style="margin-top:8px;"><?= ucfirst($user['role']) ?></span>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:800px;">
    <!-- Profile info -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">Profile Information</h3>
        <?php if ($success): ?>
            <script>showToast('<?= e($success) ?>', 'success');</script>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Driver's License</label>
                <input type="text" name="license_number" value="<?= e($user['license_number'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <!-- Password change -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">Change Password</h3>
        <form method="POST" action="">
            <input type="hidden" name="change_password" value="1">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required placeholder="Min 6 characters">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-warning">Change Password</button>
        </form>

        <div class="divider"></div>
        <div style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:var(--primary);"><?= $bookingCount ?></div>
            <div style="color:var(--gray);">Total Bookings</div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
