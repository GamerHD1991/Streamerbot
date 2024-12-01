<?php
// Fehleranzeige aktivieren (nur für Debugging, später entfernen)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Session starten, falls noch nicht aktiv
}

// Umgebungsvariablen laden
require_once './config.php'; // Sicherstellen, dass diese Datei existiert und korrekt eingebunden wird
if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
    die("Fehler: Datenbank-Konstanten sind nicht definiert.");
}

if (!defined('TWITCH_CLIENT_ID') || !defined('TWITCH_CLIENT_SECRET') || !defined('TWITCH_REDIRECT_URI')) {
    die("Fehler: Twitch-API-Konstanten sind nicht definiert.");
}

// Twitch-API-URLs
define('TWITCH_TOKEN_URL', 'https://id.twitch.tv/oauth2/token');
define('TWITCH_AUTHORIZE_URL', 'https://id.twitch.tv/oauth2/authorize');

// Datenbankverbindung herstellen
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Datenbankverbindungsfehler: " . $mysqli->connect_error);
}

// Schritt 1: Weiterleitung zu Twitch, wenn kein Code vorhanden ist
if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(16)); // CSRF-Schutz
    $_SESSION['twitch_state'] = $state;

    $authUrl = TWITCH_AUTHORIZE_URL . '?' . http_build_query([
        'client_id' => TWITCH_CLIENT_ID,
        'redirect_uri' => TWITCH_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'chat:read chat:edit user:read:email',
        'state' => $state
    ]);

    header("Location: $authUrl");
    exit;
}

// Schritt 2: Token generieren und Benutzerinformationen abrufen
if (isset($_GET['code']) && isset($_GET['state']) && $_GET['state'] === $_SESSION['twitch_state']) {
    $code = $_GET['code'];

    // Tausche Authorization Code gegen Access Token
    $postFields = [
        'client_id' => TWITCH_CLIENT_ID,
        'client_secret' => TWITCH_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => TWITCH_REDIRECT_URI,
    ];

    $ch = curl_init(TWITCH_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!isset($response['access_token'])) {
        die("Fehler: Token-Austausch fehlgeschlagen. Antwort: " . json_encode($response));
    }

    $accessToken = $response['access_token'];

    // Benutzerinformationen abrufen
    $ch = curl_init('https://api.twitch.tv/helix/users');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Client-Id: " . TWITCH_CLIENT_ID
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userData = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!isset($userData['data'][0]['id'])) {
        die("Fehler: Benutzerinformationen konnten nicht abgerufen werden.");
    }

    $twitchId = $userData['data'][0]['id'];
    $channelName = $userData['data'][0]['display_name'];
    $botUsername = $channelName; // Verwende den Kanalnamen als Bot-Username

    // Token in der Datenbank speichern
    if ($stmt = $mysqli->prepare("
    INSERT INTO users (bot_username, oauth_token) 
    VALUES (?, ?) 
    ON DUPLICATE KEY UPDATE oauth_token = VALUES(oauth_token)
")) {
    $stmt->bind_param("ss", $botUsername, $oauthToken);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Daten erfolgreich gespeichert.";
    } else {
        echo "Keine Änderungen vorgenommen.";
    }

    $stmt->close();
} else {
    echo "Fehler beim Vorbereiten der SQL-Abfrage: " . $mysqli->error;
}
}
?>
