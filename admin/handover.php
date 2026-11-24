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

// Add-on info
$addons = [];
if (!empty($b['with_driver'])) $addons[] = 'Driver';
if (!empty($b['airport_pickup'])) $addons[] = 'Airport';
if (!empty($b['insurance'])) $addons[] = 'Insurance';

$page_title = 'Handover Checklist';
include __DIR__ . '/../includes/header.php';
?>

<div class="section-header no-print">
    <h2 class="page-title" style="margin-bottom:0;">Vehicle Handover Checklist</h2>
    <a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-outline btn-sm">&larr; Back to Bookings</a>
</div>

<div style="max-width:800px;">
    <!-- Booking summary -->
    <div class="form-card no-print" style="margin-bottom:20px;">
        <h3 style="margin-bottom:16px;">Booking #<?= $b['id'] ?> — <?= e($b['car_name']) ?></h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <strong>Customer:</strong> <?= e($b['customer_name']) ?><br>
                <strong>Phone:</strong> <?= e($b['phone'] ?: 'N/A') ?><br>
                <strong>Email:</strong> <?= e($b['email']) ?>
            </div>
            <div>
                <strong>License #:</strong> <?= e($b['license_number'] ?: 'N/A') ?><br>
                <strong>Plate:</strong> <?= e($b['plate_number'] ?: 'N/A') ?><br>
                <strong>Period:</strong> <?= formatDate($b['start_date']) ?> - <?= formatDate($b['end_date']) ?>
            </div>
        </div>
        <?php if ($b['total_cost']): ?>
        <div style="margin-top:12px;">
            <strong>Total Cost:</strong> <?= money($b['total_cost']) ?>
            <?php if ($addons): ?>
                &nbsp;|&nbsp; <strong>Add-ons:</strong>
                <?php foreach ($addons as $a): ?>
                    <span class="badge badge-info" style="margin-left:4px;"><?= $a ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($b['license_image']): ?>
        <div style="margin-top:12px;">
            <a href="<?= APP_URL ?>/uploads/<?= e($b['license_image']) ?>" target="_blank" class="btn btn-outline btn-sm">&#128206; View License Photo</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Printable checklist document -->
    <div class="form-card" id="checklistCard">
        <!-- Print header -->
        <div class="print-header" style="display:none;">
            <div style="display:flex;justify-content:space-between;align-items:start;border-bottom:2px solid #1e3a5f;padding-bottom:12px;margin-bottom:20px;">
                <div>
                    <h1 style="color:#1e3a5f;margin:0;font-size:1.6rem;">CarRental GPS</h1>
                    <p style="margin:2px 0 0;color:#666;font-size:.85rem;">Online Car Rental Management System — Addis Ababa, Ethiopia</p>
                </div>
                <div style="text-align:right;">
                    <h2 style="margin:0;font-size:1.2rem;color:#1e3a5f;">Vehicle Handover Checklist</h2>
                    <p style="margin:4px 0 0;font-size:.85rem;">Booking #<?= $b['id'] ?> &middot; <?= date('M d, Y H:i') ?></p>
                </div>
            </div>
        </div>

        <!-- Vehicle & customer info table (visible in print) -->
        <table class="info-table" style="width:100%;border-collapse:collapse;margin-bottom:20px;font-size:.9rem;">
            <tr>
                <td style="padding:4px 12px 4px 0;"><strong>Customer:</strong></td>
                <td style="padding:4px 24px 4px 0;"><?= e($b['customer_name']) ?></td>
                <td style="padding:4px 12px 4px 0;"><strong>Vehicle:</strong></td>
                <td style="padding:4px 0;"><?= e($b['car_name']) ?></td>
            </tr>
            <tr>
                <td style="padding:4px 12px 4px 0;"><strong>Phone:</strong></td>
                <td style="padding:4px 24px 4px 0;"><?= e($b['phone'] ?: 'N/A') ?></td>
                <td style="padding:4px 12px 4px 0;"><strong>Plate:</strong></td>
                <td style="padding:4px 0;"><?= e($b['plate_number'] ?: 'N/A') ?></td>
            </tr>
            <tr>
                <td style="padding:4px 12px 4px 0;"><strong>License #:</strong></td>
                <td style="padding:4px 24px 4px 0;"><?= e($b['license_number'] ?: 'N/A') ?></td>
                <td style="padding:4px 12px 4px 0;"><strong>Fuel Type:</strong></td>
                <td style="padding:4px 0;"><?= e($b['fuel_type']) ?></td>
            </tr>
            <tr>
                <td style="padding:4px 12px 4px 0;"><strong>Pickup Date:</strong></td>
                <td style="padding:4px 24px 4px 0;"><?= formatDate($b['start_date']) ?></td>
                <td style="padding:4px 12px 4px 0;"><strong>Return Date:</strong></td>
                <td style="padding:4px 0;"><?= formatDate($b['end_date']) ?></td>
            </tr>
            <?php if ($addons): ?>
            <tr>
                <td style="padding:4px 12px 4px 0;"><strong>Add-ons:</strong></td>
                <td style="padding:4px 0;" colspan="3"><?= implode(', ', $addons) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <!-- Pickup section -->
        <h3 style="margin-bottom:12px;color:#1e3a5f;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">PICKUP CHECKLIST</h3>
        <div class="checklist">
            <label class="check-item"><input type="checkbox"> <span>Verify customer's driver's license in person</span></label>
            <label class="check-item"><input type="checkbox"> <span>Verify customer's ID/passport matches booking name</span></label>
            <label class="check-item"><input type="checkbox"> <span>Inspect vehicle exterior — note any existing scratches/dents below</span></label>
            <label class="check-item"><input type="checkbox"> <span>Test all lights (headlights, brake, turn signals, hazards)</span></label>
            <label class="check-item"><input type="checkbox"> <span>Check tires condition and spare tire</span></label>
            <label class="check-item"><input type="checkbox"> <span>Verify AC, wipers, horn, and dashboard indicators</span></label>
            <label class="check-item"><input type="checkbox"> <span>Provide key and explain vehicle controls</span></label>
            <label class="check-item"><input type="checkbox"> <span>Explain GPS tracking is active during rental period</span></label>
            <label class="check-item"><input type="checkbox"> <span>Review rental terms, insurance coverage, and fuel policy</span></label>
        </div>

        <!-- Pickup vehicle condition -->
        <div class="condition-grid">
            <div class="condition-item">
                <label>Fuel Level at Pickup:</label>
                <div class="fuel-options">
                    <label><input type="radio" name="pickup_fuel" value="Full"> Full</label>
                    <label><input type="radio" name="pickup_fuel" value="3/4"> 3/4</label>
                    <label><input type="radio" name="pickup_fuel" value="1/2"> 1/2</label>
                    <label><input type="radio" name="pickup_fuel" value="1/4"> 1/4</label>
                    <label><input type="radio" name="pickup_fuel" value="Empty"> Empty</label>
                </div>
            </div>
            <div class="condition-item">
                <label>Odometer at Pickup:</label>
                <input type="text" name="pickup_odometer" placeholder="_________ km" style="width:140px;">
            </div>
        </div>

        <!-- Pre-existing damage notes -->
        <div style="margin-top:16px;">
            <label style="font-weight:600;display:block;margin-bottom:6px;">Pre-existing Damage Notes:</label>
            <textarea name="pickup_damage" rows="2" style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;" placeholder="Describe any existing scratches, dents, or issues..."></textarea>
        </div>

        <!-- Return section -->
        <h3 style="margin:28px 0 12px;color:#1e3a5f;border-bottom:1px solid #e2e8f0;padding-bottom:8px;">RETURN CHECKLIST</h3>
        <div class="checklist">
            <label class="check-item"><input type="checkbox"> <span>Inspect vehicle exterior for new damage</span></label>
            <label class="check-item"><input type="checkbox"> <span>Check fuel level (must match pickup level or refill charged)</span></label>
            <label class="check-item"><input type="checkbox"> <span>Check interior cleanliness</span></label>
            <label class="check-item"><input type="checkbox"> <span>Collect keys and any accessories (GPS mount, phone charger)</span></label>
            <label class="check-item"><input type="checkbox"> <span>Process any additional charges (fuel, cleaning, damage)</span></label>
        </div>

        <!-- Return vehicle condition -->
        <div class="condition-grid">
            <div class="condition-item">
                <label>Fuel Level at Return:</label>
                <div class="fuel-options">
                    <label><input type="radio" name="return_fuel" value="Full"> Full</label>
                    <label><input type="radio" name="return_fuel" value="3/4"> 3/4</label>
                    <label><input type="radio" name="return_fuel" value="1/2"> 1/2</label>
                    <label><input type="radio" name="return_fuel" value="1/4"> 1/4</label>
                    <label><input type="radio" name="return_fuel" value="Empty"> Empty</label>
                </div>
            </div>
            <div class="condition-item">
                <label>Odometer at Return:</label>
                <input type="text" name="return_odometer" placeholder="_________ km" style="width:140px;">
            </div>
        </div>

        <!-- Return damage notes -->
        <div style="margin-top:16px;">
            <label style="font-weight:600;display:block;margin-bottom:6px;">New Damage / Additional Charges:</label>
            <textarea name="return_damage" rows="2" style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;" placeholder="Describe any new damage or additional charges..."></textarea>
        </div>

        <!-- Signatures -->
        <div style="margin-top:32px;display:grid;grid-template-columns:1fr 1fr;gap:32px;">
            <div>
                <strong>Customer Signature:</strong>
                <div style="border-bottom:1px solid #000;height:50px;margin-top:8px;"></div>
                <p style="margin-top:4px;font-size:.8rem;color:#666;">Date: ____________ Time: ________</p>
            </div>
            <div>
                <strong>Staff Signature:</strong>
                <div style="border-bottom:1px solid #000;height:50px;margin-top:8px;"></div>
                <p style="margin-top:4px;font-size:.8rem;color:#666;">Date: ____________ Time: ________</p>
            </div>
        </div>

        <!-- Print button -->
        <div class="no-print" style="margin-top:24px;text-align:center;">
            <button class="btn btn-primary" onclick="window.print()">&#128242; Print Checklist</button>
        </div>
    </div>
</div>

<style>
.checklist { display:flex; flex-direction:column; gap:8px; }
.check-item {
    display:flex; align-items:center; gap:10px;
    padding:8px 12px; border:1px solid #e2e8f0; border-radius:6px;
    cursor:pointer; font-size:.92rem; transition:all .15s;
}
.check-item input { width:18px; height:18px; cursor:pointer; flex-shrink:0; }
.check-item:has(input:checked) {
    background:#f0fdf4; border-color:#22c55e; text-decoration:line-through; color:#6b7280;
}
.condition-grid {
    display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;
}
.condition-item label { font-weight:600; display:block; margin-bottom:6px; font-size:.9rem; }
.fuel-options { display:flex; gap:12px; flex-wrap:wrap; }
.fuel-options label { font-weight:normal; display:flex; align-items:center; gap:4px; cursor:pointer; font-size:.88rem; }
.fuel-options input { width:14px; height:14px; }
.badge-info { background:#dbeafe; color:#1e40af; padding:2px 8px; border-radius:10px; font-size:.75rem; }

@media print {
    .no-print { display:none !important; }
    .print-header { display:block !important; }
    .form-card { border:none; box-shadow:none; padding:0; }
    .condition-grid { gap:24px; }
    .check-item { border:1px solid #999; }
    .fuel-options label { font-size:.85rem; }
    body { font-size:12px; }
    @page { margin:1.5cm; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
