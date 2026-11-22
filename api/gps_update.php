<?php
// API endpoint: receive GPS data from tracking devices
// POST — expects JSON: { car_id, latitude, longitude, speed, heading, device_id, api_key }

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Simple API key auth (for device simulators / external integrations)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
$validKey = 'gps_tracker_key_2024'; // In production, store per-device keys in DB

if ($apiKey !== $validKey) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing API key']);
    exit;
}

// Accept JSON or form data
$rawInput = file_get_contents('php://input');
if ($rawInput) {
    $data = json_decode($rawInput, true);
} else {
    $data = $_POST;
}

$carId    = (int)($data['car_id'] ?? 0);
$latitude = (float)($data['latitude'] ?? 0);
$longitude = (float)($data['longitude'] ?? 0);
$speed    = (float)($data['speed'] ?? 0);
$heading  = (float)($data['heading'] ?? 0);
$deviceId = trim($data['device_id'] ?? '');

if ($carId <= 0 || $latitude == 0 || $longitude == 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields: car_id, latitude, longitude']);
    exit;
}

$stmt = db()->prepare(
    'INSERT INTO gps_logs (car_id, latitude, longitude, speed, heading, device_id)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$carId, $latitude, $longitude, $speed, $heading, $deviceId]);

// Update car status to rented if it was available and speed > 0
if ($speed > 0) {
    db()->prepare('UPDATE cars SET status = "rented" WHERE id = ? AND status = "available"')
        ->execute([$carId]);
}

echo json_encode([
    'success' => true,
    'message' => 'GPS data recorded',
    'car_id' => $carId,
    'timestamp' => date('Y-m-d H:i:s')
]);
