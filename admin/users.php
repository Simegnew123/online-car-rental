<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Handle suspend/activate
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($userId !== (int)$_SESSION['user_id']) { // can't suspend yourself
        if ($action === 'suspend') {
            db()->prepare('UPDATE users SET status = "suspended" WHERE id = ?')->execute([$userId]);
        } elseif ($action === 'activate') {
            db()->prepare('UPDATE users SET status = "active" WHERE id = ?')->execute([$userId]);
        }
    }
    redirect('admin/users.php');
}

$stmt = db()->query('SELECT * FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

$page_title = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<h2 class="page-title">Manage Users</h2>

<div class="table-card">
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= e($u['full_name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['phone']) ?></td>
                <td><?= e($u['role']) ?></td>
                <td>
                    <span class="badge <?= $u['status']==='active'?'available':($u['status']==='suspended'?'under_repair':'rented') ?>">
                        <?= str_replace('_', ' ', $u['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <?php if ($u['status'] === 'active'): ?>
                            <a href="?action=suspend&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm">Suspend</a>
                        <?php else: ?>
                            <a href="?action=activate&id=<?= $u['id'] ?>" class="btn btn-success btn-sm">Activate</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <small>(you)</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
