<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $userId = (int)$_POST['user_id'];
    $action = $_POST['action'];

    if ($userId === (int)$_SESSION['user_id']) {
        // Can't modify own account
    } else {
        switch ($action) {
            case 'make_admin':
                db()->prepare('UPDATE users SET role = "admin" WHERE id = ?')->execute([$userId]);
                break;
            case 'make_customer':
                db()->prepare('UPDATE users SET role = "customer" WHERE id = ?')->execute([$userId]);
                break;
            case 'delete':
                db()->prepare('DELETE FROM users WHERE id = ? AND id != 1')->execute([$userId]);
                break;
        }
    }
    redirect('users.php');
}

$users = db()->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();

$page_title = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Manage Users</h2>

<div class="table-card">
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td>
                    <div class="r-avatar" style="display:inline-flex;background:<?= avatarColor($u['full_name']) ?>;width:28px;height:28px;font-size:.7rem;margin-right:8px;">
                        <?= e(initials($u['full_name'])) ?>
                    </div>
                    <?= e($u['full_name']) ?>
                </td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['phone'] ?: '-') ?></td>
                <td><span class="badge <?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                <td><?= formatDate($u['created_at']) ?></td>
                <td>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <div class="d-flex" style="gap:4px;">
                            <?php if ($u['role'] === 'customer'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="make_admin">
                                    <button type="submit" class="btn btn-primary btn-sm">Make Admin</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="make_customer">
                                    <button type="submit" class="btn btn-outline btn-sm">Make Customer</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($u['id'] != 1): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete user <?= e($u['full_name']) ?>?')">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span style="color:var(--gray);">You</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
