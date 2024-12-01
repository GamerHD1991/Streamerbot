<?php
require_once '../config.php';

// Twitch-API-Konstanten
define('TWITCH_TOKEN_URL', 'https://id.twitch.tv/oauth2/token');

// Funktion: Gültiges Token abrufen
function getValidAccessToken($channelName) {
    global $db;

    // Token und Ablaufdatum für den angegebenen Kanal abrufen
    $query = $db->prepare("SELECT oauth_token, expires_at FROM users WHERE channel_name = ?");
    $query->bind_param("s", $channelName);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();

        // Überprüfen, ob das Token gültig ist
        if ($data['expires_at'] && strtotime($data['expires_at']) > time()) {
            return $data['oauth_token']; // Token ist gültig
        }

        // Token ist abgelaufen, generiere ein neues
        return generateAccessToken($channelName);
    }

    return null; // Kein gültiges Token gefunden
}

// Funktion: Neues Token generieren
function generateAccessToken($channelName) {
    global $db;

    $postFields = [
        'client_id' => TWITCH_CLIENT_ID,
        'client_secret' => TWITCH_CLIENT_SECRET,
        'grant_type' => 'client_credentials',
        'scope' => 'user:read:follows chat:read chat:edit'
    ];

    $ch = curl_init(TWITCH_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        $expiresAt = date('Y-m-d H:i:s', time() + $data['expires_in']);
        $oauthToken = "oauth:" . $data['access_token'];

        // Neues Token in der Datenbank speichern
        $updateQuery = $db->prepare("UPDATE users SET oauth_token = ?, expires_at = ? WHERE channel_name = ?");
        $updateQuery->bind_param("sss", $oauthToken, $expiresAt, $channelName);
        $updateQuery->execute();

        return $oauthToken;
    }

    return null; // Fehler beim Generieren des Tokens
}
?>
