<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in name, email, and message.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Store as notification for admin
        $fullMessage = "From: {$name} ({$email})\n\n" . $message;
        notify(1, 'Contact: ' . ($subject ?: 'No Subject'), $fullMessage, 'system');

        // If logged in, also store for the user
        if (isLoggedIn()) {
            notify($_SESSION['user_id'], 'Message Sent', 'Your message has been sent to our team. We will respond within 24 hours.', 'system');
        }

        $success = 'Thank you! Your message has been sent. We will get back to you within 24 hours.';
    }
}

$page_title = 'Contact Us';
include __DIR__ . '/includes/header.php';
?>

<h2 class="page-title">Contact Us</h2>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:800px;">
    <!-- Contact info -->
    <div>
        <div class="form-card" style="margin-bottom:16px;">
            <h3>&#128205; Our Office</h3>
            <p>Bole Road, Addis Ababa<br>Ethiopia</p>
        </div>
        <div class="form-card" style="margin-bottom:16px;">
            <h3>&#128222; Phone</h3>
            <p>+251 11 234 5678<br>+251 91 234 5678</p>
        </div>
        <div class="form-card">
            <h3>&#9993; Email</h3>
            <p>info@carrental.et<br>support@carrental.et</p>
        </div>
    </div>

    <!-- Contact form -->
    <div class="form-card">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h3>&#9989; Message Sent!</h3>
                <p><?= e($success) ?></p>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Your Name <span class="req">*</span></label>
                    <input type="text" name="name" required value="<?= e(isLoggedIn() ? currentUser()['full_name'] : '') ?>" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" name="email" required value="<?= e(isLoggedIn() ? currentUser()['email'] : '') ?>" placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="How can we help?">
                </div>
                <div class="form-group">
                    <label>Message <span class="req">*</span></label>
                    <textarea name="message" rows="5" required placeholder="Write your message here..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Send Message</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
