<?php
// API endpoint: GPS simulator — generate realistic moving vehicle data
// POST — inserts a batch of simulated GPS readings for each car
// Use for demo/testing when no real GPS devices are connected.

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Simple check: anyone can trigger for demo purposes
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

try {

    // Grab all cars
    $cars = db()->query('SELECT id, name, status FROM cars')->fetchAll();
    if (empty($cars)) {
        echo json_encode(['success' => false, 'error' => 'No cars found']);
        exit;
    }

    // Center around Addis Ababa
    $baseLat = 9.025;
    $baseLng = 38.747;

    $inserted = 0;
    foreach ($cars as $car) {

        // Latest position for this car (if any) — continue from there
        $stmt = db()->prepare(
            'SELECT latitude, longitude, speed, heading
             FROM gps_logs WHERE car_id = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$car['id']]);
        $last = $stmt->fetch();

        if ($last) {
            $lat = (float)$last['latitude'];
            $lng = (float)$last['longitude'];
            $heading = (float)$last['heading'];
        } else {
            // Random start near city center
            $lat = $baseLat + (mt_rand(-300, 300) / 10000);
            $lng = $baseLng + (mt_rand(-300, 300) / 10000);
            $heading = mt_rand(0, 359);
        }

        // Movement: cars with status "rented" move; others stay parked
        if ($car['status'] === 'rented') {
            $speed = mt_rand(30, 80);
            // Turn slightly
            $heading += mt_rand(-20, 20);
            if ($heading < 0) $heading += 360;
            if ($heading >= 360) $heading -= 360;

            // Visible movement: ~0.001-0.003 degrees per step (~100-300 meters)
            $moveStep = $speed / 30000;  // 0.001 to 0.0027 degrees
            $lat += cos(deg2rad($heading)) * $moveStep;
            $lng += sin(deg2rad($heading)) * $moveStep;

            // Keep within Addis Ababa bounds
            if ($lat < 8.95) { $lat = 8.95; $heading = mt_rand(0, 180); }
            if ($lat > 9.10) { $lat = 9.10; $heading = mt_rand(180, 360); }
            if ($lng < 38.68) { $lng = 38.68; $heading = mt_rand(270, 450) % 360; }
            if ($lng > 38.82) { $lng = 38.82; $heading = mt_rand(90, 270); }
        } else {
            // Parked cars don't move
            $speed = 0;
        }

        $deviceId = 'SIM-' . str_pad($car['id'], 3, '0', STR_PAD_LEFT);

        $stmt = db()->prepare(
            'INSERT INTO gps_logs (car_id, latitude, longitude, speed, heading, device_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$car['id'], $lat, $lng, $speed, $heading, $deviceId]);
        $inserted++;
    }

    echo json_encode([
        'success' => true,
        'message' => $inserted . ' vehicles updated — rented cars are moving!'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
