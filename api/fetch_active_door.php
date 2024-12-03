<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT active_door FROM advent_calendars WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Keine aktive Tür gefunden.']);
    exit;
}

$data = $result->fetch_assoc();
echo json_encode(['success' => true, 'active_door' => $data['active_door']]);
exit;
?>
