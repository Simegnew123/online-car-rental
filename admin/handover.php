<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$bookingId = (int)($_GET['booking_id'] ?? 0);

$stmt = db()->prepare(
    'SELECT b.*, c.name AS car_name, c.plate_number, c.fuel_type, c.transmission, c.seats, c.image,
            u.full_name AS customer_name, u.email, u.phone, u.license_number, u.license_image
     FROM bookings b
     JOIN cars c ON b.car_id = c.id
     JOIN users u ON b.user_id = u.id
     WHERE b.id = ?'
);
$stmt->execute([$bookingId]);
$b = $stmt->fetch();

if (!$b) {
    redirect('admin/bookings.php');
}

$page_title = 'Handover Checklist';
include __DIR__ . '/../includes/header.php';
?>

<div class="section-header">
    <h2 class="page-title" style="margin-bottom:0;">Vehicle Handover Checklist</h2>
    <a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-outline btn-sm">&larr; Back to Bookings</a>
</div>

<div style="max-width:800px;">
    <div class="form-card" style="margin-bottom:20px;">
        <h3 style="margin-bottom:16px;">Booking #<?= $b['id'] ?> — <?= e($b['car_name']) ?></h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <strong>Customer:</strong> <?= e($b['customer_name']) ?><br>
                <strong>Phone:</strong> <?= e($b['phone'] ?: 'N/A') ?><br>
                <strong>Email:</strong> <?= e($b['email']) ?>
            </div>
            <div>
                <strong>License #:</strong> <?= e($b['license_number'] ?: 'N/A') ?><br>
                <strong>Plate:</strong> <?= e($b['plate_number']) ?><br>
                <strong>Period:</strong> <?= formatDate($b['start_date']) ?> - <?= formatDate($b['end_date']) ?>
            </div>
        </div>
        <?php if ($b['license_image']): ?>
        <div style="margin-top:12px;">
            <a href="<?= APP_URL ?>/uploads/<?= e($b['license_image']) ?>" target="_blank" class="btn btn-outline btn-sm">&#128206; View License Photo</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Printable checklist -->
    <div class="form-card" id="checklistCard">
        <h3 style="margin-bottom:16px;">Pickup Checklist</h3>
        <div class="checklist">
            <label class="check-item">
                <input type="checkbox"> <span>Verify customer's driver's license in person</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Verify customer's ID/passport matches booking name</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Inspect vehicle exterior — note any existing scratches/dents</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Check fuel level (record: ___ Full / Half / Quarter / Empty)</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Check odometer reading (record: _________ km)</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Test all lights (headlights, brake, turn signals, hazards)</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Check tires condition and spare tire</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Verify AC, wipers, horn, and dashboard indicators</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Provide key and explain vehicle controls</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Explain GPS tracking is active during rental period</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Review rental terms, insurance coverage, and fuel policy</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Customer signs handover agreement</span>
            </label>
        </div>

        <h3 style="margin:24px 0 16px;">Return Checklist</h3>
        <div class="checklist">
            <label class="check-item">
                <input type="checkbox"> <span>Inspect vehicle exterior for new damage</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Check fuel level (must match pickup level or refill charged)</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Record return odometer reading (_______ km)</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Check interior cleanliness</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Collect keys and any accessories (GPS mount, phone charger)</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Process any additional charges (fuel, cleaning, damage)</span>
            </label>
            <label class="check-item">
                <input type="checkbox"> <span>Customer signs return agreement</span>
            </label>
        </div>

        <div style="margin-top:24px;display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <strong>Customer Signature:</strong>
                <div style="border-bottom:1px solid #000;height:50px;margin-top:8px;"></div>
            </div>
            <div>
                <strong>Staff Signature:</strong>
                <div style="border-bottom:1px solid #000;height:50px;margin-top:8px;"></div>
            </div>
        </div>

        <div style="margin-top:20px;text-align:center;">
            <button class="btn btn-primary" onclick="window.print()">&#128242; Print Checklist</button>
        </div>
    </div>
</div>

<style>
.checklist { display:flex; flex-direction:column; gap:10px; }
.check-item { display:flex; align-items:center; gap:10px; padding:8px 12px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer; font-size:.92rem; }
.check-item input { width:18px; height:18px; cursor:pointer; }
.check-item:has(input:checked) { background:var(--success-lt); border-color:var(--success); text-decoration:line-through; color:var(--gray); }
@media print {
    .navbar, .footer, .section-header .btn, .print-btn { display:none !important; }
    .form-card { border:none; box-shadow:none; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
