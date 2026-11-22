<?php
// API endpoint: return notifications for the logged-in user
// GET — returns JSON: { notifications: [...], unread: N }

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

try {
    $stmt = db()->prepare(
        'SELECT id, title, message, type, is_read, created_at
         FROM notifications
         WHERE user_id = ?
         ORDER BY created_at DESC
         LIMIT 15'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();

    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $unread = (int)$stmt->fetchColumn();

    echo json_encode([
        'success'       => true,
        'notifications' => $notifications,
        'unread'        => $unread
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
