<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Zugriff verweigert.");
}

$userId = $_SESSION['user_id'];
$doorNumber = $_POST['door_number'] ?? null;

if (!$doorNumber || !is_numeric($doorNumber)) {
    die("Ungültige Türnummer.");
}

// Tür schließen
$stmt = $db->prepare("UPDATE advent_calendars SET is_open = 0 WHERE user_id = ? AND door_number = ?");
$stmt->bind_param('ii', $userId, $doorNumber);

if ($stmt->execute()) {
    header('Location: dashboard.php');
    exit;
} else {
    die("Fehler beim Schließen der Tür.");
}
?>
