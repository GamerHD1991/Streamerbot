<?php
require_once '../config.php';

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Benutzer-ID fehlt.']);
    exit;
}

$userId = intval($_GET['user_id']);

$stmt = $db->prepare("UPDATE users SET bot_active = 0 WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Bot wurde gestoppt.']);
exit;
