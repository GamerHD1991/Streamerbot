<?php
require_once '../config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$user_id = intval($input['user_id']);
$door_number = intval($input['door_number']);

if (!$user_id || !$door_number) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Eingabedaten.']);
    exit;
}

$stmt = $db->prepare("DELETE FROM giveaway_participants WHERE user_id = ? AND door_number = ?");
$stmt->bind_param('ii', $user_id, $door_number);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Teilnehmerliste erfolgreich gelöscht.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Löschen der Teilnehmerliste.']);
}
