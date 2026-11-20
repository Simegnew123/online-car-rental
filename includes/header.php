<?php
if (!isset($page_title)) $page_title = APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> &mdash; Car Rental</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <!-- Leaflet for GPS map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
<header class="navbar">
    <div class="nav-container">
        <a href="<?= APP_URL ?>/index.php" class="nav-logo">
            <span class="logo-icon">&#128663;</span> CarRental GPS
        </a>
        <nav class="nav-links">
            <a href="<?= APP_URL ?>/index.php">Home</a>
            <a href="<?= APP_URL ?>/cars.php">Vehicles</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/my_bookings.php">My Bookings</a>
                <a href="<?= APP_URL ?>/profile.php">Profile</a>
                <?php if (isAdmin()): ?>
                    <a href="<?= APP_URL ?>/admin/dashboard.php" class="nav-admin">Admin Panel</a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/logout.php" class="nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/login.php" class="nav-login">Login</a>
                <a href="<?= APP_URL ?>/register.php" class="nav-register">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
