<?php
require_once '../config.php';

header('Content-Type: application/json');

// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
    exit;
}

// Nur POST-Anfragen erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage-Methode.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validieren der Eingabedaten
if (!isset($input['participant_name']) || empty(trim($input['participant_name']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Teilnehmername fehlt oder ist ungültig.']);
    exit;
}

$participantName = trim($input['participant_name']);
$userId = $_SESSION['user_id'];

// Teilnehmer aus der Ignore-Liste entfernen
$stmt = $db->prepare("DELETE FROM giveaway_ignored_participants WHERE user_id = ? AND participant_name = ?");
$stmt->bind_param('is', $userId, $participantName);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => "Teilnehmer {$participantName} wurde erfolgreich entfernt."]);
    } else {
        echo json_encode(['success' => false, 'message' => "Teilnehmer {$participantName} wurde nicht gefunden."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Entfernen des Teilnehmers.']);
}
$stmt->close();
?>
