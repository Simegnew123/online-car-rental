<?php
// API endpoint: mark a notification as read (or all if id=all)
// GET ?id=N | ?id=all

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$id = $_GET['id'] ?? '';

try {
    if ($id === 'all') {
        db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')
            ->execute([$_SESSION['user_id']]);
    } else {
        $nid = (int)$id;
        if ($nid > 0) {
            db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')
                ->execute([$nid, $_SESSION['user_id']]);
        }
    }
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
