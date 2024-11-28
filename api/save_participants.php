<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Keine Daten empfangen.',
            'debug' => file_get_contents('php://input')
        ]);
        exit;
    }

    $user_id = isset($input['user_id']) ? intval($input['user_id']) : null;
    $door_number = isset($input['door_number']) ? intval($input['door_number']) : null;
    $participant_name = isset($input['participant_name']) ? trim($input['participant_name']) : null;

    if (!$user_id || !$door_number || !$participant_name) {
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige Eingabedaten.',
            'debug' => [
                'user_id' => $user_id,
                'door_number' => $door_number,
                'participant_name' => $participant_name,
                'input' => $input
            ]
        ]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO giveaway_participants (user_id, door_number, participant_name) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $user_id, $door_number, $participant_name);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Teilnehmer erfolgreich gespeichert.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Fehler beim Speichern: ' . $stmt->error
        ]);
    }
    exit;
}

// Fallback für ungültige Anfragen
echo json_encode(['success' => false, 'message' => 'Ungültige Anforderung.']);
exit;
?>
