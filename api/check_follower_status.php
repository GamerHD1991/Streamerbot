<?php
require_once '../config.php';

header('Content-Type: application/json');

// Überprüfen, ob die erforderlichen Parameter übergeben wurden
if (!isset($_GET['user_id']) || !isset($_GET['channel_name'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit;
}

$userId = $_GET['user_id'];
$channelName = $_GET['channel_name'];

// Benutzer-Daten abrufen
$stmt = $db->prepare("SELECT oauth_token, bot_username AS client_id FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$userSettings = $stmt->get_result()->fetch_assoc();

if (!$userSettings || !$userSettings['oauth_token'] || !$userSettings['client_id']) {
    echo json_encode(['success' => false, 'message' => 'Fehlende API-Daten.']);
    exit;
}

$oauthToken = $userSettings['oauth_token'];
$clientId = $userSettings['client_id'];

// Twitch-API-Anfrage
$url = "https://api.twitch.tv/helix/users/follows?from_id=$userId&to_name=$channelName";
$headers = [
    "Authorization: Bearer $oauthToken",
    "Client-Id: $clientId"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($statusCode !== 200) {
    echo json_encode([
        'success' => false,
        'message' => 'Fehler bei der Twitch-API',
        'status_code' => $statusCode,
        'response' => $response
    ]);
    exit;
}

$data = json_decode($response, true);
if (count($data['data']) > 0) {
    $followedAt = $data['data'][0]['followed_at'];
    echo json_encode(['success' => true, 'followed_at' => $followedAt]);
} else {
    echo json_encode(['success' => false, 'message' => 'Benutzer ist kein Follower']);
}
?>
