<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mail.php';
requireAdmin();

$success = '';
$error   = '';

// Handle test email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    $testEmail = trim($_POST['test_email'] ?? '');
    if (empty($testEmail)) {
        $error = 'Please enter an email address.';
    } elseif (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $body = "
            <p>This is a test email from <strong>CarRental GPS</strong>.</p>
            <p>If you're receiving this, the email notification system is working correctly.</p>
            <div class='detail-box'>
                <table>
                    <tr><td>Sent At</td><td>" . date('Y-m-d H:i:s') . "</td></tr>
                    <tr><td>From</td><td>" . MAIL_FROM . "</td></tr>
                    <tr><td>System</td><td>XAMPP / PHP mail()</td></tr>
                </table>
            </div>
            <p>Email notifications are sent automatically when:</p>
            <ul style='margin:12px 0 12px 24px;'>
                <li>A new user registers (welcome email)</li>
                <li>A booking is confirmed</li>
                <li>A payment is verified</li>
                <li>A rental starts or completes</li>
                <li>A booking is cancelled</li>
            </ul>
        ";
        $sent = sendEmail($testEmail, 'Test Email — CarRental GPS', $body);
        if ($sent) {
            $success = "Test email sent to {$testEmail} via PHP mail(). Check your inbox (or spam folder).";
        } else {
            $success = "Email was logged but could not be sent via mail(). On XAMPP, you need to configure sendmail in php.ini. The email content has been logged to uploads/email_log.txt for verification.";
        }
    }
}

// Read email log
$emailLog = '';
$logFile = __DIR__ . '/../../uploads/email_log.txt';
if (file_exists($logFile)) {
    $emailLog = file_get_contents($logFile);
    // Show last 50 lines
    $lines = explode("\n", trim($emailLog));
    $emailLog = implode("\n", array_slice($lines, -50));
}

// System info
$systemInfo = [
    'PHP Version' => PHP_VERSION,
    'Server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'Database' => DB_NAME . ' @ ' . DB_HOST,
    'App URL' => APP_URL,
    'Mail From' => MAIL_FROM,
    'Upload Dir' => UPLOAD_DIR,
    'Total Cars' => totalCars(),
    'Total Bookings' => totalBookings(),
    'Total Users' => totalCustomers(),
    'Total Revenue' => money(totalRevenue()),
];

$page_title = 'System Settings';
include __DIR__ . '/../includes/header.php';
?>


<h2 class="page-title">System Settings</h2>

<?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:20px;"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:1000px;">
    <!-- Email test -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">&#9993; Email Notification System</h3>
        <p style="color:var(--gray);font-size:.88rem;margin-bottom:16px;">
            The system sends automatic email notifications for booking events.
            Use the form below to test if email delivery is working.
        </p>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:16px;font-size:.85rem;">
            <strong>Email triggers:</strong>
            <ul style="margin:8px 0 0 20px;line-height:1.8;color:var(--gray);">
                <li>New user registration &rarr; Welcome email</li>
                <li>Booking confirmed &rarr; Confirmation email</li>
                <li>Payment verified &rarr; Payment receipt email</li>
                <li>Rental started &rarr; Rental active email</li>
                <li>Rental completed &rarr; Review request email</li>
                <li>Booking cancelled &rarr; Cancellation email</li>
            </ul>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label>Send Test Email To</label>
                <input type="email" name="test_email" placeholder="you@example.com" required>
            </div>
            <button type="submit" name="send_test" class="btn btn-primary btn-block">&#9993; Send Test Email</button>
        </form>
    </div>

    <!-- Add-on pricing info -->
    <div class="form-card">
        <h3 style="margin-bottom:16px;">&#128176; Add-on Pricing</h3>
        <p style="color:var(--gray);font-size:.88rem;margin-bottom:16px;">
            Default pricing for optional add-ons (configured in <code>config/database.php</code>).
        </p>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0;">
                <span>&#128110; Driver / Chauffeur</span>
                <strong><?= money(DRIVER_RATE_PER_DAY) ?>/day</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0;">
                <span>&#9992; Airport Pickup</span>
                <strong><?= money(AIRPORT_FEE_FLAT) ?> (flat)</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;">
                <span>&#128737; Insurance</span>
                <strong><?= (INSURANCE_RATE * 100) ?>% of base</strong>
            </div>
        </div>
    </div>
</div>

<!-- System info -->
<h3 style="margin:30px 0 16px;">System Information</h3>
<div class="form-card">
    <table style="width:100%;border-collapse:collapse;">
        <?php foreach ($systemInfo as $label => $value): ?>
        <tr>
            <td style="padding:8px 16px;color:var(--gray);font-size:.9rem;width:200px;border-bottom:1px solid #f1f5f9;"><?= e($label) ?></td>
            <td style="padding:8px 16px;font-weight:600;font-size:.9rem;border-bottom:1px solid #f1f5f9;"><?= e($value) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Email log -->
<?php if ($emailLog): ?>
<h3 style="margin:30px 0 16px;">Email Log (last 50 entries)</h3>
<div class="form-card">
    <pre style="font-size:.8rem;color:var(--gray);white-space:pre-wrap;overflow-x:auto;max-height:300px;"><?= e($emailLog) ?></pre>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
