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
$calendar = $result->fetch_assoc();

if (!$calendar || !$calendar['active_door']) {
    echo json_encode(['success' => false, 'message' => 'Keine aktive Tür gefunden.']);
    exit;
}

$doorNumber = intval($calendar['active_door']);

$stmt = $db->prepare("SELECT participant_name FROM giveaway_participants WHERE user_id = ? AND door_number = ?");
$stmt->bind_param('ii', $userId, $doorNumber);
$stmt->execute();
$result = $stmt->get_result();

$participants = [];
while ($row = $result->fetch_assoc()) {
    $participants[] = $row['participant_name'];
}

echo json_encode(['success' => true, 'participants' => $participants]);
exit;
?>
