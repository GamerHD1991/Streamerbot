<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['active_door'])) {
        echo json_encode(['success' => false, 'message' => 'Aktive Tür fehlt.']);
        exit;
    }

    $activeDoor = intval($input['active_door']);
    $userId = $_SESSION['user_id'];

    $stmt = $db->prepare("UPDATE advent_calendars SET active_door = ? WHERE user_id = ?");
    $stmt->bind_param('ii', $activeDoor, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Aktive Tür aktualisiert.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fehler beim Aktualisieren der aktiven Tür.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
exit;
