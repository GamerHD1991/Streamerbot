<?php
require_once '../config.php';

header('Content-Type: application/json');

// CORS-Header hinzufügen, falls erforderlich
header('Access-Control-Allow-Origin: https://adventskalender.bestefreundecommunity.de');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Überprüfe die Anfrage-Methode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage-Methode.']);
    exit;
}

// Eingabedaten prüfen
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || empty(trim($input['username']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Benutzername fehlt oder ist ungültig.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
    exit;
}

$username = trim($input['username']);
$userId = $_SESSION['user_id'];

// Teilnehmer ignorieren
$stmt = $db->prepare("INSERT IGNORE INTO giveaway_ignored_participants (user_id, participant_name, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param('is', $userId, $username);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => "Teilnehmer $username wurde erfolgreich ignoriert."]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Fehler beim Ignorieren des Teilnehmers.', 'error' => $stmt->error]);
}
exit;
