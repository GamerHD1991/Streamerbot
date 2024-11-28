<?php
require_once 'config.php';

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Prüfen, ob bereits ein Adventskalender existiert
$stmt = $db->prepare("SELECT COUNT(*) as count FROM advent_calendars WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    die("Du hast bereits einen Adventskalender erstellt.");
}

// Adventskalender erstellen (24 Türen)
$stmt = $db->prepare("INSERT INTO advent_calendars (user_id, door_number, is_open, prize, giveaway_duration) VALUES (?, ?, 0, '', 300)");
for ($door = 1; $door <= 24; $door++) {
    $stmt->bind_param('ii', $_SESSION['user_id'], $door);
    $stmt->execute();
}

// Weiterleitung zurück zum Dashboard
header('Location: dashboard.php');
exit;
