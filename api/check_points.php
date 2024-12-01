<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['username']) || !isset($_GET['channel_name']) || !isset($_GET['points'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
    exit;
}

$username = trim($_GET['username']); // Benutzer, der !join eingegeben hat
$channelName = trim($_GET['channel_name']); // Kanalname aus der Bot-Einstellungen
$requiredPoints = intval($_GET['points']);

// Kanalname in der Datenbank überprüfen
$stmt = $db->prepare("SELECT streamelements_jwt FROM users WHERE channel_name = ?");
$stmt->bind_param('s', $channelName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Kanal nicht gefunden.']);
    exit;
}

$row = $result->fetch_assoc();
$jwtToken = $row['streamelements_jwt'];

if (!$jwtToken) {
    echo json_encode(['success' => false, 'message' => 'Kein JWT-Token gefunden.']);
    exit;
}

// StreamElements API für Punkte prüfen
$ch = curl_init("https://api.streamelements.com/kappa/v2/points/$username");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $jwtToken"
]);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($statusCode === 401) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges JWT-Token. Bitte aktualisieren.']);
    exit;
} elseif ($statusCode === 404) {
    echo json_encode(['success' => false, 'message' => 'Benutzer nicht bei StreamElements gefunden.']);
    exit;
} elseif ($statusCode !== 200) {
    echo json_encode(['success' => false, 'message' => 'Fehler bei der StreamElements-API.', 'statusCode' => $statusCode]);
    exit;
}

$data = json_decode($response, true);
$currentPoints = (int) ($data['points'] ?? 0);

if ($currentPoints < $requiredPoints) {
    echo json_encode([
        'success' => false,
        'message' => 'Nicht genügend Punkte.',
        'currentPoints' => $currentPoints,
        'requiredPoints' => $requiredPoints
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Genügend Punkte vorhanden.',
    'currentPoints' => $currentPoints
]);
?>
