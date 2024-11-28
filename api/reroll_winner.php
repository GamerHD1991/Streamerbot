<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = intval($_GET['user_id']);
    $door_number = intval($_GET['door_number']);

    if (!$user_id || !$door_number) {
        echo json_encode(['success' => false, 'message' => 'Fehlende Parameter: user_id oder door_number.']);
        exit;
    }

    $stmt = $db->prepare("SELECT participant_name FROM giveaway_participants WHERE user_id = ? AND door_number = ?");
    $stmt->bind_param('ii', $user_id, $door_number);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Fehler bei der SQL-Ausführung: ' . $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();
    $participants = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($participants)) {
        echo json_encode(['success' => false, 'message' => 'Keine Teilnehmer gefunden.']);
        exit;
    }

    $winner = $participants[array_rand($participants)]['participant_name'];

    echo json_encode([
        'success' => true,
        'winner_name' => $winner,
        'participants' => array_column($participants, 'participant_name') // Teilnehmerliste hinzufügen
    ]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anforderung.']);
    exit;
}
?>
