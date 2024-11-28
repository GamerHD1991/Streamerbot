<?php
require_once '../config.php';

header('Content-Type: application/json');

// Benutzer-ID aus der Anfrage
$user_id = intval($_GET['user_id'] ?? 0);

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Benutzer-ID fehlt.']);
    exit;
}

// Türstatus abrufen
$stmt = $db->prepare("SELECT door_number, is_open FROM advent_calendars WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$doors = [];
while ($row = $result->fetch_assoc()) {
    $doors[] = $row;
}

echo json_encode(['success' => true, 'doors' => $doors]);
?>
