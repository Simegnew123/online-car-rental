<?php
if (!isset($page_title)) $page_title = APP_NAME;
if (!isset($body_class)) $body_class = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Book vehicles online with real-time GPS tracking. Safe, reliable, and affordable car rental service in Ethiopia.">
    <meta name="theme-color" content="#2563eb">
    <!-- PWA -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/img/icon-192.svg">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/favicon.svg">
    <title><?= e($page_title) ?> &mdash; Car Rental GPS</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <!-- Leaflet for GPS map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- Chart.js for reports -->
    <?php if (isset($use_charts) && $use_charts): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    <script>window.APP_URL = '<?= APP_URL ?>';</script>
</head>
<body class="<?= e($body_class) ?>">

<header class="navbar">
    <div class="nav-container">
        <a href="<?= APP_URL ?>/index.php" class="nav-logo">
            <span class="logo-icon">&#128663;</span>
            CarRental <span class="gps-badge">GPS</span>
        </a>
        <button class="nav-toggle" onclick="toggleMobileMenu()" aria-label="Menu">&#9776;</button>
        <nav class="nav-links">
            <a href="<?= APP_URL ?>/index.php">Home</a>
            <a href="<?= APP_URL ?>/cars.php">Vehicles</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/my_bookings.php">My Bookings</a>
                <a href="<?= APP_URL ?>/profile.php">Profile</a>
                <?php if (isAdmin()): ?>
                    <a href="<?= APP_URL ?>/admin/dashboard.php" class="nav-admin">Admin Panel</a>
                <?php endif; ?>
                <!-- Notification bell -->
                <a href="javascript:void(0)" class="nav-bell" id="nav-bell" onclick="toggleNotifications()">
                    <span class="bell-icon">&#128276;</span>
                    <?php $unread = unreadCount(); ?>
                    <span class="bell-count" id="bell-count" style="display:<?= $unread>0?'flex':'none' ?>"><?= $unread ?></span>
                </a>
                <a href="<?= APP_URL ?>/logout.php" class="nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/login.php" class="nav-login">Login</a>
                <a href="<?= APP_URL ?>/register.php" class="nav-register">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- Notification dropdown (only for logged-in users) -->
<?php if (isLoggedIn()): ?>
<div class="notif-dropdown" id="notif-dropdown"></div>
<?php endif; ?>

<!-- Toast container -->
<div class="toast-container"></div>

<!-- Loading overlay -->
<div class="loading-overlay" id="loading-overlay"><div class="spinner"></div></div>

<main class="container">
