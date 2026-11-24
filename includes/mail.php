<?php
/**
 * Email Notification Helper
 * Sends HTML emails using PHP mail() function.
 * On XAMPP, configure php.ini [mail function] or use a SMTP library.
 * Falls back to storing email in a log file if mail() is unavailable.
 */

require_once __DIR__ . '/functions.php';

/**
 * Send an HTML email.
 */
function sendEmail(string $to, string $subject, string $htmlBody, string $fromName = 'CarRental GPS'): bool {
    $fromEmail = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@carrental.et';
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";

    // Wrap body in email template
    $fullHtml = emailTemplate($subject, $htmlBody);

    $sent = @mail($to, $subject, $fullHtml, $headers);

    // Log email regardless (for demo / audit trail)
    $logEntry = date('Y-m-d H:i:s') . " | TO: {$to} | SUBJECT: {$subject} | STATUS: " . ($sent ? 'SENT' : 'LOGGED_ONLY') . "\n";
    $logFile = __DIR__ . '/../uploads/email_log.txt';
    @file_put_contents($logFile, $logEntry, FILE_APPEND);

    return $sent;
}

/**
 * Standard email template wrapper.
 */
function emailTemplate(string $subject, string $body): string {
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; background:#f0f4f8; color:#1a1a1a; line-height:1.6; }
        .email-wrapper { max-width:600px; margin:0 auto; padding:20px; }
        .email-header { background:linear-gradient(135deg,#2563eb,#1e40af); color:#fff; padding:24px; border-radius:8px 8px 0 0; text-align:center; }
        .email-header h1 { font-size:1.5rem; margin-bottom:4px; }
        .email-header p { font-size:.85rem; opacity:.9; }
        .email-body { background:#fff; padding:32px 24px; border:1px solid #e2e8f0; }
        .email-body h2 { font-size:1.2rem; margin-bottom:16px; color:#1e40af; }
        .email-body p { margin-bottom:12px; font-size:.95rem; }
        .email-body .detail-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin:16px 0; }
        .email-body .detail-box table { width:100%; border-collapse:collapse; }
        .email-body .detail-box td { padding:6px 0; font-size:.9rem; }
        .email-body .detail-box td:first-child { color:#64748b; }
        .email-body .detail-box td:last-child { font-weight:600; text-align:right; }
        .email-body .total-row { border-top:2px solid #e2e8f0; margin-top:8px; padding-top:10px; font-size:1.1rem; font-weight:800; color:#2563eb; }
        .email-body .btn { display:inline-block; padding:12px 28px; background:#2563eb; color:#fff !important; text-decoration:none; border-radius:8px; font-weight:600; margin:16px 0; }
        .email-footer { background:#1e293b; color:#94a3b8; padding:20px; border-radius:0 0 8px 8px; text-align:center; font-size:.8rem; }
        .email-footer a { color:#60a5fa; }
        .status-badge { display:inline-block; padding:4px 14px; border-radius:20px; font-size:.8rem; font-weight:700; }
        .status-confirmed { background:#dbeafe; color:#1e40af; }
        .status-active { background:#dcfce7; color:#16a34a; }
        .status-completed { background:#e0e7ff; color:#4338ca; }
        .status-cancelled { background:#fee2e2; color:#dc2626; }
        .status-pending { background:#fef3c7; color:#92400e; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h1>&#128663; CarRental GPS</h1>
            <p>Online Car Rental Management System</p>
        </div>
        <div class="email-body">
            <h2>{$subject}</h2>
            {$body}
        </div>
        <div class="email-footer">
            <p>&copy; {$year} CarRental GPS Ethiopia &mdash; Admas University, Kality Campus</p>
            <p style="margin-top:6px;">Addis Ababa, Ethiopia &middot; +251 11 234 5678 &middot; info@carrental.et</p>
            <p style="margin-top:8px;opacity:.6;">This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Send booking confirmation email.
 */
function emailBookingConfirmation(int $bookingId): void {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, c.brand, c.type, c.fuel_type, c.transmission,
                u.full_name, u.email
         FROM bookings b
         JOIN cars c ON b.car_id = c.id
         JOIN users u ON b.user_id = u.id
         WHERE b.id = ?'
    );
    $stmt->execute([$bookingId]);
    $b = $stmt->fetch();
    if (!$b) return;

    $days = daysBetween($b['start_date'], $b['end_date']);
    $appUrl = defined('APP_URL') ? APP_URL : 'http://localhost:8080/online-car-rental';

    $addons = '';
    if ($b['with_driver'])  $addons .= "<tr><td>Driver Service</td><td>" . money($b['driver_cost']) . "</td></tr>";
    if ($b['airport_pickup']) $addons .= "<tr><td>Airport Pickup</td><td>" . money($b['airport_cost']) . "</td></tr>";
    if ($b['insurance'])   $addons .= "<tr><td>Insurance</td><td>" . money($b['insurance_cost']) . "</td></tr>";

    $body = "
        <p>Dear <strong>" . e($b['full_name']) . "</strong>,</p>
        <p>Your booking has been <span class='status-badge status-confirmed'>Confirmed</span>. Here are the details:</p>
        <div class='detail-box'>
            <table>
                <tr><td>Booking ID</td><td>#{$b['id']}</td></tr>
                <tr><td>Vehicle</td><td>" . e($b['car_name']) . "</td></tr>
                <tr><td>Type</td><td>" . e($b['type']) . " / " . e($b['fuel_type']) . "</td></tr>
                <tr><td>Pickup Date</td><td>" . formatDate($b['start_date']) . "</td></tr>
                <tr><td>Return Date</td><td>" . formatDate($b['end_date']) . "</td></tr>
                <tr><td>Rental Period</td><td>{$days} days</td></tr>
                " . ($b['pickup_location'] ? "<tr><td>Pickup Location</td><td>" . e($b['pickup_location']) . "</td></tr>" : "") . "
                <tr><td>Base Rental</td><td>" . money($b['total_cost'] - $b['driver_cost'] - $b['airport_cost'] - $b['insurance_cost']) . "</td></tr>
                {$addons}
                <tr class='total-row'><td>Total</td><td>" . money($b['total_cost']) . "</td></tr>
            </table>
        </div>
        <p>Please complete your payment to finalize the booking. You can pay online or at pickup.</p>
        <a href='{$appUrl}/payment.php?booking_id={$b['id']}' class='btn'>Pay Now</a>
        <a href='{$appUrl}/invoice.php?booking_id={$b['id']}' class='btn' style='background:#64748b;'>View Invoice</a>
        <p style='margin-top:16px;font-size:.85rem;color:#64748b;'>Need help? Contact us at +251 11 234 5678 or info@carrental.et</p>
    ";

    sendEmail($b['email'], "Booking Confirmed #" . $b['id'] . " — " . $b['car_name'], $body);
}

/**
 * Send payment verified email.
 */
function emailPaymentVerified(int $bookingId): void {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, p.amount, p.payment_method, p.transaction_id,
                u.full_name, u.email
         FROM bookings b
         JOIN cars c ON b.car_id = c.id
         JOIN users u ON b.user_id = u.id
         LEFT JOIN payments p ON p.booking_id = b.id
         WHERE b.id = ?'
    );
    $stmt->execute([$bookingId]);
    $b = $stmt->fetch();
    if (!$b) return;

    $appUrl = defined('APP_URL') ? APP_URL : 'http://localhost:8080/online-car-rental';

    $body = "
        <p>Dear <strong>" . e($b['full_name']) . "</strong>,</p>
        <p>Your payment has been <span class='status-badge status-active'>Verified</span>.</p>
        <div class='detail-box'>
            <table>
                <tr><td>Booking ID</td><td>#{$b['id']}</td></tr>
                <tr><td>Vehicle</td><td>" . e($b['car_name']) . "</td></tr>
                <tr><td>Amount Paid</td><td>" . money($b['amount'] ?? $b['total_cost']) . "</td></tr>
                <tr><td>Payment Method</td><td>" . ucfirst($b['payment_method'] ?? '') . "</td></tr>
                <tr><td>Transaction ID</td><td>" . e($b['transaction_id'] ?? 'N/A') . "</td></tr>
            </table>
        </div>
        <p>Your booking is now fully confirmed. Please arrive at the pickup location on the scheduled date.</p>
        <a href='{$appUrl}/invoice.php?booking_id={$b['id']}' class='btn'>View Invoice</a>
        <p style='margin-top:16px;font-size:.85rem;color:#64748b;'>Thank you for choosing CarRental GPS!</p>
    ";

    sendEmail($b['email'], "Payment Verified — Booking #" . $b['id'], $body);
}

/**
 * Send rental started email.
 */
function emailRentalStarted(int $bookingId): void {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, c.plate_number,
                u.full_name, u.email
         FROM bookings b
         JOIN cars c ON b.car_id = c.id
         JOIN users u ON b.user_id = u.id
         WHERE b.id = ?'
    );
    $stmt->execute([$bookingId]);
    $b = $stmt->fetch();
    if (!$b) return;

    $appUrl = defined('APP_URL') ? APP_URL : 'http://localhost:8080/online-car-rental';

    $body = "
        <p>Dear <strong>" . e($b['full_name']) . "</strong>,</p>
        <p>Your rental has <span class='status-badge status-active'>Started</span>! The vehicle is now in your possession.</p>
        <div class='detail-box'>
            <table>
                <tr><td>Booking ID</td><td>#{$b['id']}</td></tr>
                <tr><td>Vehicle</td><td>" . e($b['car_name']) . "</td></tr>
                <tr><td>Plate Number</td><td>" . e($b['plate_number']) . "</td></tr>
                <tr><td>Return Date</td><td>" . formatDate($b['end_date']) . "</td></tr>
            </table>
        </div>
        <p>Your vehicle is equipped with <strong>real-time GPS tracking</strong>. You can track your vehicle's location on our map at any time.</p>
        <a href='{$appUrl}/admin/gps_tracking.php' class='btn'>Track Vehicle</a>
        <p style='margin-top:16px;font-size:.85rem;color:#64748b;'>Drive safely! In case of emergency, call +251 91 234 5678.</p>
    ";

    sendEmail($b['email'], "Rental Started — " . $b['car_name'], $body);
}

/**
 * Send booking cancelled email.
 */
function emailBookingCancelled(int $bookingId): void {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, u.full_name, u.email
         FROM bookings b
         JOIN cars c ON b.car_id = c.id
         JOIN users u ON b.user_id = u.id
         WHERE b.id = ?'
    );
    $stmt->execute([$bookingId]);
    $b = $stmt->fetch();
    if (!$b) return;

    $body = "
        <p>Dear <strong>" . e($b['full_name']) . "</strong>,</p>
        <p>Your booking has been <span class='status-badge status-cancelled'>Cancelled</span>.</p>
        <div class='detail-box'>
            <table>
                <tr><td>Booking ID</td><td>#{$b['id']}</td></tr>
                <tr><td>Vehicle</td><td>" . e($b['car_name']) . "</td></tr>
                <tr><td>Period</td><td>" . formatDate($b['start_date']) . " - " . formatDate($b['end_date']) . "</td></tr>
            </table>
        </div>
        <p>If you believe this is an error, please contact us immediately at +251 11 234 5678.</p>
        <p style='margin-top:16px;font-size:.85rem;color:#64748b;'>We hope to serve you again in the future.</p>
    ";

    sendEmail($b['email'], "Booking Cancelled #" . $b['id'], $body);
}

/**
 * Send rental completed email with review request.
 */
function emailRentalCompleted(int $bookingId): void {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, u.full_name, u.email
         FROM bookings b
         JOIN cars c ON b.car_id = c.id
         JOIN users u ON b.user_id = u.id
         WHERE b.id = ?'
    );
    $stmt->execute([$bookingId]);
    $b = $stmt->fetch();
    if (!$b) return;

    $appUrl = defined('APP_URL') ? APP_URL : 'http://localhost:8080/online-car-rental';

    $body = "
        <p>Dear <strong>" . e($b['full_name']) . "</strong>,</p>
        <p>Your rental has been <span class='status-badge status-completed'>Completed</span>. We hope you enjoyed your experience!</p>
        <div class='detail-box'>
            <table>
                <tr><td>Booking ID</td><td>#{$b['id']}</td></tr>
                <tr><td>Vehicle</td><td>" . e($b['car_name']) . "</td></tr>
                <tr><td>Total Paid</td><td>" . money($b['total_cost']) . "</td></tr>
            </table>
        </div>
        <p>We'd love to hear your feedback! Please take a moment to leave a review:</p>
        <a href='{$appUrl}/car_details.php?id={$b['car_id']}#reviews' class='btn'>Leave a Review</a>
        <p style='margin-top:16px;font-size:.85rem;color:#64748b;'>Thank you for choosing CarRental GPS. We look forward to serving you again!</p>
    ";

    sendEmail($b['email'], "Rental Completed — Please Review", $body);
}

/**
 * Send welcome email on registration.
 */
function emailWelcome(string $name, string $email): void {
    $appUrl = defined('APP_URL') ? APP_URL : 'http://localhost:8080/online-car-rental';

    $body = "
        <p>Dear <strong>" . e($name) . "</strong>,</p>
        <p>Welcome to <strong>CarRental GPS</strong>! Your account has been created successfully.</p>
        <p>With your account, you can:</p>
        <ul style='margin:12px 0 12px 24px;'>
            <li>Browse our fleet of vehicles</li>
            <li>Book vehicles with add-ons (driver, airport pickup, insurance)</li>
            <li>Track your rental in real-time with GPS</li>
            <li>View and print invoices</li>
            <li>Leave reviews on vehicles you've rented</li>
        </ul>
        <a href='{$appUrl}/cars.php' class='btn'>Browse Vehicles</a>
        <p style='margin-top:16px;font-size:.85rem;color:#64748b;'>If you have any questions, feel free to contact us at info@carrental.et</p>
    ";

    sendEmail($email, "Welcome to CarRental GPS!", $body);
}
