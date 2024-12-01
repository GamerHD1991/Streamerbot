<?php
require_once '../config.php';
require_once 'token_manager.php';

// Setze den Header für JSON-Antworten
header('Content-Type: application/json');

// Bereinige den Ausgabe-Buffer
if (ob_get_length()) {
    ob_clean();
}

// Überprüfen, ob die benötigten Parameter gesendet wurden
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['username']) || !isset($data['channelName'])) {
    echo json_encode(['success' => false, 'message' => 'Fehlende Parameter']);
    exit;
}

$username = $data['username'];
$channelName = $data['channelName'];

error_log("Empfangene Parameter: Username = $username, ChannelName = $channelName");

// Abrufen eines gültigen Tokens
$oauthToken = getValidAccessToken($channelName);
if (!$oauthToken) {
    echo json_encode(['success' => false, 'message' => 'Token konnte nicht generiert oder erneuert werden']);
    exit;
}

// Funktion für API-Aufrufe
function twitchApiRequest($url, $oauthToken) {
    $response = @file_get_contents($url, false, stream_context_create([
        'http' => [
            'header' => [
                "Authorization: Bearer {$oauthToken}",
            ],
        ],
    ]));

    if ($response === false) {
        error_log("API-Fehler bei URL: $url");
        return null;
    }

    return json_decode($response, true);
}

$username = $data['username'];
if (!$username) {
    echo json_encode(['success' => false, 'message' => 'Benutzername fehlt']);
    exit;
}


// Benutzer-ID abrufen
$userDataApi = twitchApiRequest("https://api.twitch.tv/helix/users?login={$username}", $oauthToken);
if (!$userDataApi || empty($userDataApi['data'])) {
    echo json_encode(['success' => false, 'message' => 'Benutzer-ID konnte nicht abgerufen werden']);
    exit;
}

$userId = $userDataApi['data'][0]['id'];

// Kanal-ID abrufen
$channelDataApi = twitchApiRequest("https://api.twitch.tv/helix/users?login={$channelName}", $oauthToken);
if (!$channelDataApi || empty($channelDataApi['data'])) {
    echo json_encode(['success' => false, 'message' => 'Kanal-ID konnte nicht abgerufen werden']);
    exit;
}

$channelId = $channelDataApi['data'][0]['id'];

// Follower-Status prüfen
$followData = twitchApiRequest("https://api.twitch.tv/helix/users/follows?from_id={$userId}&to_id={$channelId}", $oauthToken);
if (!$followData || empty($followData['data'])) {
    echo json_encode(['success' => false, 'message' => 'Benutzer ist kein Follower']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Benutzer ist ein Follower']);
?>
