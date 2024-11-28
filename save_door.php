<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingabewerte validieren
    $door_number = isset($_POST['door_number']) ? intval($_POST['door_number']) : null;
    $prize = isset($_POST['prize']) ? trim($_POST['prize']) : null;
    $giveaway_duration = isset($_POST['giveaway_duration']) ? intval($_POST['giveaway_duration']) : null;
    $min_follower_hours = isset($_POST['min_follower_hours']) ? intval($_POST['min_follower_hours']) : 0;

    if ($door_number === null || $giveaway_duration === null || $min_follower_hours < 0) {
        echo "Fehler: Ungültige Eingabedaten.";
        exit;
    }

    // SQL-Update vorbereiten
    $stmt = $db->prepare("UPDATE advent_calendars SET prize = ?, giveaway_duration = ?, min_follower_hours = ? WHERE user_id = ? AND door_number = ?");
    $stmt->bind_param('siiii', $prize, $giveaway_duration, $min_follower_hours, $_SESSION['user_id'], $door_number);

    if ($stmt->execute()) {
        // Erfolgreich gespeichert, zurück zur Dashboard-Seite
        header('Location: dashboard.php');
        exit;
    } else {
        // Fehler anzeigen, wenn das Update fehlschlägt
        echo "Fehler beim Speichern: " . $stmt->error;
        exit;
    }
} else {
    echo "Ungültige Anforderung.";
    exit;
}
