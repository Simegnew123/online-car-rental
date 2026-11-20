<?php
// API endpoint: return latest GPS location for each car
// GET — returns JSON array of cars with their latest GPS coordinates

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Get latest GPS entry per car
$sql = 'SELECT c.id, c.name, c.status, c.brand,
               g.latitude, g.longitude, g.speed, g.device_id, g.recorded_at
        FROM cars c
        LEFT JOIN gps_logs g ON g.id = (
            SELECT MAX(id) FROM gps_logs WHERE car_id = c.id
        )
        WHERE c.status IN ("rented","available")
        ORDER BY c.id';

$rows = db()->query($sql)->fetchAll();

$result = [];
foreach ($rows as $row) {
    if ($row['latitude'] !== null) {
        $result[] = [
            'car_id'      => (int)$row['id'],
            'name'        => $row['name'],
            'brand'       => $row['brand'],
            'status'      => $row['status'],
            'latitude'    => (float)$row['latitude'],
            'longitude'   => (float)$row['longitude'],
            'speed'       => (float)$row['speed'],
            'device_id'   => $row['device_id'],
            'recorded_at' => $row['recorded_at']
        ];
    }
}

echo json_encode($result);
