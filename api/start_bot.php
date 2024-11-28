<?php
require_once '../config.php';

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Benutzer-ID fehlt.']);
    exit;
}

$userId = intval($_GET['user_id']);

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Benutzerdaten konnten nicht geladen werden.']);
    exit;
}

// Update bot_active-Status
$stmt = $db->prepare("UPDATE users SET bot_active = 1 WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Bot wurde gestartet.']);
exit;
