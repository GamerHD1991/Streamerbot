<?php
// Absolute Fehleranzeige aktivieren (nur für Debugging, später entfernen)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verbindung zur Datenbank herstellen
$mysqli = new mysqli('DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME');
if ($mysqli->connect_error) {
    die('Fehler bei der Datenbankverbindung: ' . $mysqli->connect_error);
}

// Twitch-API-Konstanten
define('TWITCH_CLIENT_ID', 'DEINE_CLIENT_ID');
define('TWITCH_CLIENT_SECRET', 'DEIN_CLIENT_SECRET');
define('TWITCH_TOKEN_URL', 'https://id.twitch.tv/oauth2/token');

// Neues Token generieren
function generateAccessToken($mysqli) {
    $postFields = [
        'client_id' => TWITCH_CLIENT_ID,
        'client_secret' => TWITCH_CLIENT_SECRET,
        'grant_type' => 'client_credentials',
        'scope' => 'chat:read chat:edit'
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

        // Token in der Datenbank speichern
        $stmt = $mysqli->prepare("UPDATE users SET oauth_token = ?, expires_at = ? WHERE channel_name = ?");
        $channelName = 'GamerHD1991'; // Ersetze mit deinem Kanalnamen
        $stmt->bind_param("sss", $oauthToken, $expiresAt, $channelName);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Token erfolgreich generiert: $oauthToken";
        } else {
            echo "Fehler beim Speichern des Tokens in der Datenbank.";
        }
        $stmt->close();
    } else {
        echo "Fehler beim Generieren des Tokens: " . json_encode($data);
    }
}

// Token generieren
generateAccessToken($mysqli);
?>
