<?php
require_once '../config.php';

header('Content-Type: application/json');

// Überprüfung der Anfragemethode
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Abrufen der ignorierten Teilnehmer aus der Tabelle `giveaway_ignored_participants`
    $stmt = $db->prepare("SELECT participant_name FROM giveaway_ignored_participants WHERE user_id = ?");
    $userId = $_SESSION['user_id']; // Benutzer-ID aus der Session
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $participants = [];
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row['participant_name'];
    }

    echo json_encode(['success' => true, 'participants' => $participants]);
    exit;
}

// Fehler bei ungültigen Anfragen
echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
exit;
