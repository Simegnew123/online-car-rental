<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$carId = (int)($_GET['id'] ?? 0);
$car = getCar($carId);

if (!$car) {
    echo '<div class="alert alert-danger">Vehicle not found.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Handle review submission
$reviewError = '';
$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    $rating  = (int)($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $reviewError = 'Invalid rating.';
    } elseif (empty($comment)) {
        $reviewError = 'Please write a comment.';
    } else {
        $stmt = db()->prepare(
            'INSERT INTO reviews (user_id, car_id, rating, comment) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$_SESSION['user_id'], $carId, $rating, $comment]);
        $reviewSuccess = 'Review posted successfully!';
    }
}

// Fetch reviews with user info
$stmt = db()->prepare(
    'SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.car_id = ? ORDER BY r.created_at DESC'
);
$stmt->execute([$carId]);
$reviews = $stmt->fetchAll();

$page_title = e($car['name']);
include __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <h2 class="page-title" style="margin-bottom:0;"><?= e($car['name']) ?></h2>
    <span class="badge <?= $car['status'] ?>"><?= str_replace('_', ' ', $car['status']) ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:900px;">
    <!-- Car image -->
    <div class="car-card" style="margin:0;">
        <div class="car-card-img" style="height:280px;">
            <?php $imgUrl = carImageUrl($car['image']); ?>
            <?php if ($imgUrl): ?>
                <img src="<?= e($imgUrl) ?>" alt="<?= e($car['name']) ?>">
            <?php else: ?>
                &#128663;
            <?php endif; ?>
        </div>
    </div>

    <!-- Car details -->
    <div>
        <div class="car-brand" style="font-size:1.1rem;margin-bottom:14px;"><?= e($car['brand'] ?: 'Vehicle') ?> &middot; <?= e($car['type']) ?></div>
        <div class="car-meta" style="margin-bottom:16px;">
            <span>&#9981; <?= e($car['fuel_type']) ?></span>
            <span>&#9881; <?= e($car['transmission']) ?></span>
            <span>&#128102; <?= $car['seats'] ?> seats</span>
            <span>&#128274; <?= $car['doors'] ?> doors</span>
            <span>&#128188; <?= $car['luggage'] ?> bags</span>
            <?php if ($car['air_conditioning']): ?><span>&#10052; AC</span><?php endif; ?>
            <?php if ($car['bluetooth']): ?><span>&#127911; Bluetooth</span><?php endif; ?>
        </div>
        <p style="margin:16px 0;color:var(--gray);"><?= e($car['description'] ?: 'No description available.') ?></p>
        <div class="car-price" style="font-size:1.8rem;margin-bottom:16px;"><?= money($car['price_per_day']) ?><small style="font-size:1rem;">/day</small></div>
        <div style="margin-bottom:16px;">
            <span class="star-display"><?= str_repeat('&#9733;', (int)carRating($carId)) ?></span>
            <strong><?= carRating($carId) ?>/5</strong>
            <span style="color:var(--gray);"> (<?= carReviewCount($carId) ?> reviews)</span>
        </div>
        <?php if ($car['status'] === 'available'): ?>
            <a href="<?= APP_URL ?>/book.php?car_id=<?= $car['id'] ?>" class="btn btn-success btn-lg">Book Now</a>
        <?php else: ?>
            <button disabled class="btn btn-outline btn-lg">Currently Unavailable</button>
        <?php endif; ?>
    </div>
</div>

<div class="divider"></div>

<!-- Reviews section -->
<h3 class="page-title">Reviews (<?= count($reviews) ?>)</h3>

<!-- Review form (only for logged-in users) -->
<?php if (isLoggedIn()): ?>
<div class="form-card" style="max-width:600px;margin-bottom:24px;">
    <?php if ($reviewSuccess): ?>
        <script>showToast('<?= e($reviewSuccess) ?>', 'success');</script>
    <?php endif; ?>
    <?php if ($reviewError): ?>
        <div class="alert alert-danger"><?= e($reviewError) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="hidden" name="review_submit" value="1">
        <div class="form-group">
            <label>Your Rating</label>
            <div class="star-rating" data-rating="5">
                <span class="star active" data-val="1">&#9733;</span>
                <span class="star active" data-val="2">&#9733;</span>
                <span class="star active" data-val="3">&#9733;</span>
                <span class="star active" data-val="4">&#9733;</span>
                <span class="star active" data-val="5">&#9733;</span>
            </div>
            <input type="hidden" name="rating" value="5">
        </div>
        <div class="form-group">
            <label>Your Review</label>
            <textarea name="comment" rows="3" placeholder="Share your experience with this vehicle..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Review</button>
    </form>
</div>
<?php else: ?>
<p style="margin-bottom:20px;"><a href="<?= APP_URL ?>/login.php">Login</a> to write a review.</p>
<?php endif; ?>

<!-- Review list -->
<?php if (empty($reviews)): ?>
    <div class="empty-state" style="padding:30px;">
        <p>No reviews yet. Be the first to review!</p>
    </div>
<?php else: ?>
    <?php foreach ($reviews as $rev): ?>
        <div class="review-card">
            <div class="r-header">
                <div class="r-author">
                    <div class="r-avatar" style="background:<?= avatarColor($rev['full_name']) ?>;">
                        <?= e(initials($rev['full_name'])) ?>
                    </div>
                    <?= e($rev['full_name']) ?>
                </div>
                <span class="star-display"><?= str_repeat('&#9733;', $rev['rating']) ?></span>
            </div>
            <div class="r-comment"><?= e($rev['comment']) ?></div>
            <div class="r-date"><?= timeAgo($rev['created_at']) ?></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
