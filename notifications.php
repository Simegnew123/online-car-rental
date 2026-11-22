<?php
require_once __DIR__ . '/includes/auth.php';

requireLogin();

// Handle "mark all read"
if (isset($_GET['read_all'])) {
    db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')
        ->execute([$_SESSION['user_id']]);
    redirect('notifications.php');
}

$stmt = db()->prepare(
    'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

$page_title = 'Notifications';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-between mb-2">
    <h2 class="page-title" style="margin:0;">Notifications</h2>
    <?php if (count($notifications) > 0): ?>
        <a href="<?= APP_URL ?>/notifications.php?read_all=1" class="btn btn-outline btn-sm">Mark All Read</a>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
    <div class="alert alert-info">You have no notifications yet.</div>
<?php else: ?>
<div class="notif-list">
    <?php foreach ($notifications as $n): ?>
    <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
        <div class="notif-type-icon">
            <?php
            $icon = $n['type'] === 'booking' ? '&#128197;' : ($n['type'] === 'payment' ? '&#128176;' : '&#128276;');
            echo $icon;
            ?>
        </div>
        <div class="notif-body">
            <div class="notif-title"><?= e($n['title']) ?></div>
            <div class="notif-msg"><?= e($n['message']) ?></div>
            <div class="notif-time"><?= formatDateTime($n['created_at']) ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
