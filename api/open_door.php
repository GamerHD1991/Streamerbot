<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = intval($input['user_id']);
    $door_number = intval($input['door_number']);

    if (!$user_id || !$door_number) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Eingabedaten.']);
        exit;
    }

    // Tür als geöffnet markieren
    $stmt = $db->prepare("UPDATE advent_calendars SET is_open = 1 WHERE user_id = ? AND door_number = ?");
    $stmt->bind_param('ii', $user_id, $door_number);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Tür $door_number geöffnet."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fehler beim Aktualisieren der Tür: ' . $stmt->error]);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anforderung.']);
    exit;
}
?>
