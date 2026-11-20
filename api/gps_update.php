<?php
// API endpoint: receive GPS data from tracking devices
// POST JSON: {"device_id":"GPS-001","car_id":1,"latitude":9.0085,"longitude":38.7575,"speed":0,"heading":180}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Also accept form POST
    $input = $_POST;
}

$carId     = (int)($input['car_id'] ?? 0);
$deviceId  = trim($input['device_id'] ?? '');
$latitude  = (float)($input['latitude'] ?? 0);
$longitude = (float)($input['longitude'] ?? 0);
$speed     = (float)($input['speed'] ?? 0);
$heading   = (float)($input['heading'] ?? 0);

if ($carId === 0 || $latitude === 0 || $longitude === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields (car_id, latitude, longitude)']);
    exit;
}

try {
    $stmt = db()->prepare(
        'INSERT INTO gps_logs (car_id, latitude, longitude, speed, heading, device_id)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$carId, $latitude, $longitude, $speed, $heading, $deviceId]);

    echo json_encode([
        'success'    => true,
        'message'    => 'GPS data recorded',
        'timestamp'  => date('Y-m-d H:i:s'),
        'car_id'     => $carId,
        'latitude'   => $latitude,
        'longitude'  => $longitude
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
