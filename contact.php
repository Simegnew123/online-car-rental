<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject    = trim($_POST['subject']   ?? '');
    $message    = trim($_POST['message']   ?? '');

    if (empty($subject) || empty($message)) {
        $error = 'Subject and message are required.';
    } else {
        db()->prepare(
            'INSERT INTO notifications (user_id, title, message, type)
             VALUES (?, ?, ?, ?)'
        )->execute([$_SESSION['user_id'], $subject, $message, 'system']);
        $success = 'Message sent successfully.';
    }
}

$page_title = 'Contact / Feedback';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Contact Us</h2>

<div class="form-card" style="max-width:500px;">
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Subject *</label>
            <input type="text" name="subject" required>
        </div>
        <div class="form-group">
            <label>Message *</label>
            <textarea name="message" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
