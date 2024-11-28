<?php
require_once '../config.php';

header('Content-Type: application/json');

// Benutzer-ID überprüfen
if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Benutzer-ID fehlt.']);
    exit;
}

$user_id = intval($_GET['user_id']);

// Aktive Türnummer ermitteln
$stmt = $db->prepare("SELECT door_number FROM giveaway_participants WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$active_door = null;
if ($result->num_rows > 0) {
    $active_door = $result->fetch_assoc()['door_number'];
}

// Teilnehmer abrufen
$stmt = $db->prepare("SELECT participant_name FROM giveaway_participants WHERE user_id = ? AND door_number = ?");
$stmt->bind_param('ii', $user_id, $active_door);
$stmt->execute();
$participants_result = $stmt->get_result();

$participants = [];
while ($row = $participants_result->fetch_assoc()) {
    $participants[] = $row['participant_name'];
}

echo json_encode([
    'success' => true,
    'participants' => $participants,
    'active_door' => $active_door
]);
exit;
?>
