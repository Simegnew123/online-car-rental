<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user  = currentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['full_name'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $license = trim($_POST['license_number'] ?? '');

    // Upload license image
    $imgName = $user['license_image'];
    if (isset($_FILES['license_image']) && $_FILES['license_image']['error'] === UPLOAD_ERR_OK) {
        $imgName = uploadFile('license_image', 'license');
    }

    if (empty($name) || empty($phone)) {
        $error = 'Name and phone are required.';
    } else {
        $stmt = db()->prepare(
            'UPDATE users SET full_name = ?, phone = ?, license_number = ?, license_image = ? WHERE id = ?'
        );
        $stmt->execute([$name, $phone, $license, $imgName, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $name;
        $success = 'Profile updated successfully.';
        $user = currentUser();
    }
}

$page_title = 'My Profile';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">My Profile</h2>

<div class="form-card" style="max-width:550px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        <strong>Email:</strong> <?= e($user['email']) ?><br>
        <strong>Role:</strong> <?= e($user['role']) ?><br>
        <strong>Member since:</strong> <?= formatDate($user['created_at']) ?>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?= e($user['full_name']) ?>">
        </div>
        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" required value="<?= e($user['phone']) ?>">
        </div>
        <div class="form-group">
            <label>Driver's License Number</label>
            <input type="text" name="license_number" value="<?= e($user['license_number']) ?>">
        </div>
        <div class="form-group">
            <label>License Image Upload</label>
            <input type="file" name="license_image" accept="image/*">
            <?php if ($user['license_image']): ?>
                <p style="font-size:.85rem;margin-top:6px;">
                    Current: <a href="<?= UPLOAD_URL ?>/<?= e($user['license_image']) ?>" target="_blank">View</a>
                </p>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
