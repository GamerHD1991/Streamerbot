<?php
require_once '../config.php';

header('Content-Type: application/json');

// Überprüfen, ob die Benutzer-ID übergeben wurde
if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Benutzer-ID fehlt.']);
    exit;
}

$userId = intval($_GET['user_id']);

// Benutzer-Daten abrufen
$stmt = $db->prepare("
    SELECT 
        bot_username, 
        oauth_token, 
        channel_name, 
        streamelements_jwt, 
        channel_id 
    FROM users 
    WHERE id = ?
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$userSettings = $stmt->get_result()->fetch_assoc();

if (!$userSettings) {
    echo json_encode(['success' => false, 'message' => 'Benutzerdaten konnten nicht geladen werden.']);
    exit;
}

// Türdaten abrufen
$stmt = $db->prepare("
    SELECT 
        door_number, 
        is_open, 
        prize, 
        giveaway_duration, 
        min_follower_hours, 
        points_cost 
    FROM advent_calendars 
    WHERE user_id = ?
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$doorSettings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Erfolgreiche Rückgabe der Daten
echo json_encode([
    'success' => true,
    'bot_username' => $userSettings['bot_username'] ?? '',
    'oauth_token' => $userSettings['oauth_token'] ?? '',
    'channel_name' => $userSettings['channel_name'] ?? '',
    'streamelements_jwt' => $userSettings['streamelements_jwt'] ?? '',
    'channel_id' => $userSettings['channel_id'] ?? '', // Neuer Key
    'doors' => $doorSettings
]);
?>
