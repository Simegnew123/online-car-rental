<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone   = trim($_POST['phone'] ?? '');
    $license = trim($_POST['license_number'] ?? '');
    $newPass = $_POST['new_password'] ?? '';

    if (empty($phone)) {
        $error = 'Phone number is required.';
    } else {
        $licenseImage = $user['license_image'];

        // Handle license image upload
        if (!empty($_FILES['license_image']['name'])) {
            $uploaded = uploadFile('license_image', 'license');
            if ($uploaded) {
                $licenseImage = $uploaded;
            }
        }

        // Update user record
        if ($newPass) {
            if (strlen($newPass) < 6) {
                $error = 'New password must be at least 6 characters.';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                db()->prepare('UPDATE users SET phone=?, license_number=?, license_image=?, password=? WHERE id=?')
                    ->execute([$phone, $license, $licenseImage, $hash, $_SESSION['user_id']]);
                $success = 'Profile updated successfully (including new password).';
            }
        } else {
            db()->prepare('UPDATE users SET phone=?, license_number=?, license_image=? WHERE id=?')
                ->execute([$phone, $license, $licenseImage, $_SESSION['user_id']]);
            $success = 'Profile updated successfully.';
        }

        $user = currentUser();
    }
}

$page_title = 'My Profile';
include __DIR__ . '/includes/header.php';
?>


<h2 class="page-title">My Profile</h2>

<?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:20px;"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;max-width:900px;">
    <!-- Profile sidebar -->
    <div class="form-card" style="text-align:center;">
        <div style="width:80px;height:80px;border-radius:50%;background:<?= e($user['avatar_color'] ?? '#2563eb') ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;margin:0 auto 16px;">
            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
        </div>
        <h3 style="margin-bottom:4px;"><?= e($user['full_name']) ?></h3>
        <p style="color:var(--gray);font-size:.88rem;"><?= e($user['email']) ?></p>
        <span class="badge" style="background:var(--primary-lt);color:var(--primary);margin-top:8px;display:inline-block;padding:4px 12px;border-radius:20px;font-size:.78rem;">
            <?= ucfirst(e($user['role'])) ?>
        </span>
    </div>

    <!-- Edit form -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">Edit Profile</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Email <span style="color:var(--gray);font-weight:400;">(cannot change)</span></label>
                <input type="email" value="<?= e($user['email']) ?>" disabled style="background:var(--lighter);">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Phone <span class="req">*</span></label>
                    <input type="text" name="phone" required value="<?= e($user['phone'] ?? '') ?>" placeholder="0912345678">
                </div>
                <div class="form-group">
                    <label>Driver's License Number</label>
                    <input type="text" name="license_number" value="<?= e($user['license_number'] ?? '') ?>" placeholder="DL-2024-XXXX">
                </div>
            </div>
            <div class="form-group">
                <label>Driver's License Photo</label>
                <?php if ($user['license_image']): ?>
                    <div style="margin-bottom:8px;">
                        <a href="<?= APP_URL ?>/uploads/<?= e($user['license_image']) ?>" target="_blank" style="color:var(--primary);font-size:.85rem;">&#128206; View current license</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="license_image" accept="image/*,.pdf" style="padding:8px;border:1.5px solid #cbd5e1;border-radius:var(--radius-sm);width:100%;">
                <small style="color:var(--gray);">Upload a new photo to replace existing (JPG, PNG, PDF — max 5MB)</small>
            </div>
            <div class="form-group">
                <label>New Password <span style="color:var(--gray);font-weight:400;">(leave blank to keep current)</span></label>
                <input type="password" name="new_password" placeholder="Enter new password to change">
            </div>
            <button type="submit" class="btn btn-primary btn-block">&#9989; Save Changes</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
