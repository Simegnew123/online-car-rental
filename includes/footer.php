</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-brand">
            <h3>&#128663; CarRental GPS</h3>
            <p>Online Car Rental Management System with real-time GPS tracking. Book vehicles online, track your rental on the map, and enjoy a seamless experience.</p>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <a href="<?= APP_URL ?>/index.php">Home</a>
            <a href="<?= APP_URL ?>/cars.php">Browse Vehicles</a>
            <a href="<?= APP_URL ?>/register.php">Register</a>
            <a href="<?= APP_URL ?>/contact.php">Contact</a>
        </div>
        <div class="footer-links">
            <h4>Company</h4>
            <a href="<?= APP_URL ?>/admin/dashboard.php">Admin Panel</a>
            <a href="<?= APP_URL ?>/admin/gps_tracking.php">GPS Tracking</a>
            <p style="margin-top:12px;font-size:.8rem;">
                Admas University<br>
                Kality Campus<br>
                Department of Computer Science
            </p>
        </div>
        <div class="footer-qr">
            <h4>&#128241; Open on Mobile</h4>
            <img src="<?= APP_URL ?>/assets/img/mobile-qr.png" alt="Scan to open on mobile" width="100" height="100">
            <p>Scan with your phone camera</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Online Car Rental Management System with GPS Tracking</p>
        <p style="margin-top:6px;opacity:.7;font-size:.78rem;">
            Developed by: Nardos Gizaw, Saladin Negash, Genet Tadele, Tekalegn Chefeko, Etagegnew Derese
        </p>
    </div>
</footer>

<!-- Leaflet JS for maps -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
