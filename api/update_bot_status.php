<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Nur POST-Anfragen erlaubt.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['user_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Benutzer-ID oder Status fehlt.']);
    exit;
}

$userId = intval($data['user_id']);
$status = $data['status'] ? 1 : 0;

$stmt = $db->prepare("UPDATE users SET bot_status = ? WHERE id = ?");
$stmt->bind_param('ii', $status, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Datenbankaktualisierung fehlgeschlagen.']);
}
?>
