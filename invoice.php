<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$bookingId = (int)($_GET['booking_id'] ?? 0);

// Admin can view any invoice; customer can view their own
if (isAdmin()) {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, c.brand, c.type, c.fuel_type, c.transmission, c.plate_number,
                u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                u.license_number,
                p.amount AS paid_amount, p.payment_method, p.transaction_id, p.status AS pay_status, p.created_at AS pay_date
         FROM bookings b
         JOIN cars c ON b.car_id = c.id
         JOIN users u ON b.user_id = u.id
         LEFT JOIN payments p ON p.booking_id = b.id
         WHERE b.id = ?'
    );
    $stmt->execute([$bookingId]);
} else {
    $stmt = db()->prepare(
        'SELECT b.*, c.name AS car_name, c.brand, c.type, c.fuel_type, c.transmission, c.plate_number,
                u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                u.license_number,
                p.amount AS paid_amount, p.payment_method, p.transaction_id, p.status AS pay_status, p.created_at AS pay_date
         FROM bookings b
         JOIN cars c ON b.car_id = c.id
         JOIN users u ON b.user_id = u.id
         LEFT JOIN payments p ON p.booking_id = b.id
         WHERE b.id = ? AND b.user_id = ?'
    );
    $stmt->execute([$bookingId, $_SESSION['user_id']]);
}
$b = $stmt->fetch();

if (!$b) {
    redirect('my_bookings.php');
}

$days = daysBetween($b['start_date'], $b['end_date']);
$baseRental = $days * ($b['total_cost'] - $b['driver_cost'] - $b['airport_cost'] - $b['insurance_cost']) / max(1, $days);
$baseTotal = $b['total_cost'] - $b['driver_cost'] - $b['airport_cost'] - $b['insurance_cost'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $b['id'] ?> — CarRental GPS</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; color:#1a1a1a; background:#f0f0f0; padding:20px; }
        .invoice { max-width:800px; margin:0 auto; background:#fff; padding:40px; border-radius:8px; box-shadow:0 2px 12px rgba(0,0,0,.1); }
        .invoice-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:30px; padding-bottom:20px; border-bottom:3px solid #2563eb; }
        .logo { font-size:1.8rem; font-weight:800; color:#2563eb; }
        .logo span { color:#f59e0b; font-size:1rem; }
        .invoice-meta { text-align:right; }
        .invoice-meta h1 { font-size:2rem; color:#1a1a1a; }
        .invoice-meta .inv-no { color:#64748b; font-size:.9rem; margin-top:4px; }
        .invoice-meta .inv-date { color:#64748b; font-size:.85rem; margin-top:2px; }
        .parties { display:flex; gap:40px; margin-bottom:30px; }
        .party { flex:1; }
        .party h3 { font-size:.8rem; text-transform:uppercase; color:#94a3b8; letter-spacing:1px; margin-bottom:8px; }
        .party .name { font-size:1.1rem; font-weight:700; margin-bottom:4px; }
        .party .detail { font-size:.88rem; color:#64748b; line-height:1.6; }
        .section-title { font-size:.8rem; text-transform:uppercase; color:#94a3b8; letter-spacing:1px; margin:24px 0 10px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:24px; }
        .info-item { font-size:.88rem; }
        .info-item .label { color:#94a3b8; font-size:.78rem; display:block; margin-bottom:2px; }
        .info-item .value { font-weight:600; }
        table { width:100%; border-collapse:collapse; margin-bottom:24px; }
        table th { background:#f8fafc; text-align:left; padding:12px; font-size:.82rem; text-transform:uppercase; color:#64748b; border-bottom:2px solid #e2e8f0; }
        table td { padding:12px; font-size:.9rem; border-bottom:1px solid #f1f5f9; }
        table td:last-child, table th:last-child { text-align:right; }
        .totals { margin-left:auto; width:300px; }
        .total-row { display:flex; justify-content:space-between; padding:6px 0; font-size:.9rem; }
        .total-row.grand { border-top:2px solid #1a1a1a; margin-top:8px; padding-top:12px; font-size:1.15rem; font-weight:800; }
        .total-row.grand span:last-child { color:#2563eb; }
        .status-badge { display:inline-block; padding:4px 14px; border-radius:20px; font-size:.8rem; font-weight:700; text-transform:uppercase; }
        .status-paid { background:#dcfce7; color:#16a34a; }
        .status-pending { background:#fef3c7; color:#92400e; }
        .status-confirmed { background:#dbeafe; color:#1e40af; }
        .status-active { background:#dcfce7; color:#16a34a; }
        .status-completed { background:#e0e7ff; color:#4338ca; }
        .footer { margin-top:40px; padding-top:20px; border-top:1px solid #e2e8f0; text-align:center; color:#94a3b8; font-size:.8rem; line-height:1.8; }
        .print-btn { display:block; margin:0 auto 20px; padding:10px 30px; background:#2563eb; color:#fff; border:none; border-radius:8px; font-size:1rem; cursor:pointer; font-weight:600; }
        .print-btn:hover { background:#1e40af; }
        @media print {
            body { background:#fff; padding:0; }
            .print-btn { display:none; }
            .invoice { box-shadow:none; border-radius:0; padding:30px; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">&#128242; Print Invoice</button>

    <div class="invoice">
        <div class="invoice-header">
            <div>
                <div class="logo">CarRental <span>GPS</span></div>
                <div style="font-size:.85rem; color:#64748b; margin-top:4px;">Online Car Rental Management System</div>
                <div style="font-size:.8rem; color:#94a3b8; margin-top:2px;">Addis Ababa, Ethiopia</div>
            </div>
            <div class="invoice-meta">
                <h1>INVOICE</h1>
                <div class="inv-no">Invoice #INV-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></div>
                <div class="inv-date">Issued: <?= formatDate($b['created_at']) ?></div>
                <div style="margin-top:8px;">
                    <span class="status-badge status-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span>
                </div>
            </div>
        </div>

        <div class="parties">
            <div class="party">
                <h3>Bill To</h3>
                <div class="name"><?= e($b['customer_name']) ?></div>
                <div class="detail">
                    <?= e($b['customer_email']) ?><br>
                    <?= e($b['customer_phone'] ?: 'N/A') ?><br>
                    <?php if ($b['license_number']): ?>License: <?= e($b['license_number']) ?><?php endif; ?>
                </div>
            </div>
            <div class="party">
                <h3>From</h3>
                <div class="name">CarRental GPS Ethiopia</div>
                <div class="detail">
                    Bole Road, Addis Ababa<br>
                    +251 11 234 5678<br>
                    info@carrental.et
                </div>
            </div>
        </div>

        <div class="section-title">Rental Details</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Vehicle</span>
                <span class="value"><?= e($b['car_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Type</span>
                <span class="value"><?= e($b['type']) ?> / <?= e($b['fuel_type']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Transmission</span>
                <span class="value"><?= e($b['transmission']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Pickup Date</span>
                <span class="value"><?= formatDate($b['start_date']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Return Date</span>
                <span class="value"><?= formatDate($b['end_date']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Rental Period</span>
                <span class="value"><?= $days ?> <?= $days === 1 ? 'day' : 'days' ?></span>
            </div>
            <?php if ($b['pickup_location']): ?>
            <div class="info-item">
                <span class="label">Pickup Location</span>
                <span class="value"><?= e($b['pickup_location']) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Vehicle rental — <?= e($b['car_name']) ?></td>
                    <td><?= $days ?> days</td>
                    <td><?= money($baseTotal / max(1, $days)) ?></td>
                    <td><?= money($baseTotal) ?></td>
                </tr>
                <?php if ($b['with_driver']): ?>
                <tr>
                    <td>Driver / Chauffeur service</td>
                    <td><?= $days ?> days</td>
                    <td><?= money($b['driver_cost'] / max(1, $days)) ?></td>
                    <td><?= money($b['driver_cost']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($b['airport_pickup']): ?>
                <tr>
                    <td>Airport pickup / drop-off</td>
                    <td>1</td>
                    <td><?= money($b['airport_cost']) ?></td>
                    <td><?= money($b['airport_cost']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($b['insurance']): ?>
                <tr>
                    <td>Full insurance coverage</td>
                    <td>1</td>
                    <td>—</td>
                    <td><?= money($b['insurance_cost']) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row"><span>Subtotal</span><span><?= money($b['total_cost']) ?></span></div>
            <div class="total-row grand"><span>Total Due</span><span><?= money($b['total_cost']) ?></span></div>
            <?php if ($b['pay_status'] === 'paid'): ?>
            <div class="total-row" style="color:#16a34a;font-weight:600;"><span>Paid</span><span><?= money($b['paid_amount']) ?></span></div>
            <div class="total-row" style="font-weight:700;"><span>Balance Due</span><span><?= money(0) ?></span></div>
            <?php elseif ($b['pay_status'] === 'pending'): ?>
            <div class="total-row" style="color:#92400e;"><span>Payment Status</span><span>Pending Verification</span></div>
            <?php else: ?>
            <div class="total-row" style="color:#dc2626;"><span>Payment Status</span><span>Not Paid</span></div>
            <?php endif; ?>
        </div>

        <?php if ($b['transaction_id']): ?>
        <div style="margin-top:20px;padding:12px;background:#f8fafc;border-radius:6px;font-size:.85rem;">
            <strong>Transaction ID:</strong> <?= e($b['transaction_id']) ?>
            &nbsp;|&nbsp; <strong>Method:</strong> <?= ucfirst(e($b['payment_method'])) ?>
            <?php if ($b['pay_date']): ?>&nbsp;|&nbsp; <strong>Date:</strong> <?= formatDate($b['pay_date']) ?><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($b['notes']): ?>
        <div style="margin-top:20px;">
            <div class="section-title">Notes</div>
            <div style="font-size:.88rem; color:#475569;"><?= e($b['notes']) ?></div>
        </div>
        <?php endif; ?>

        <div class="footer">
            <strong>CarRental GPS Ethiopia</strong> — Online Car Rental Management System with Real-Time GPS Tracking<br>
            Admas University, Kality Campus — Department of Computer Science<br>
            This is a computer-generated invoice and does not require a signature.<br>
            For inquiries: +251 11 234 5678 | info@carrental.et
        </div>
    </div>
</body>
</html>
