<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Zugriff verweigert.");
}

$userId = $_SESSION['user_id'];

// Kalender löschen
$stmt = $db->prepare("DELETE FROM advent_calendars WHERE user_id = ?");
$stmt->bind_param('i', $userId);

if ($stmt->execute()) {
    header('Location: dashboard.php');
    exit;
} else {
    die("Fehler beim Löschen des Kalenders.");
}
?>
