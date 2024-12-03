<?php
require_once '../config.php'; // Datenbankkonfiguration laden

header('Content-Type: application/json');

// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt.']);
    exit;
}

// Nur POST-Anfragen zulassen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Eingaben validieren
    if (!isset($input['user_id']) || !isset($input['door_number'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige Daten übergeben.',
            'debug' => $input // Debugging-Hilfe
        ]);
        exit;
    }

    $userId = intval($input['user_id']);
    $doorNumber = intval($input['door_number']);

    // Überprüfen, ob der Benutzer existiert
    $userCheckStmt = $db->prepare("SELECT id FROM advent_calendars WHERE user_id = ?");
    $userCheckStmt->bind_param('i', $userId);
    $userCheckStmt->execute();
    $userResult = $userCheckStmt->get_result();

    if ($userResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Benutzer oder Kalender nicht gefunden.']);
        exit;
    }

    $db->begin_transaction(); // Transaktion starten

    try {
        // Schritt 1: Alle aktiven Türen für diesen Benutzer zurücksetzen
        $resetStmt = $db->prepare("UPDATE advent_calendars SET active_door = NULL WHERE user_id = ?");
        $resetStmt->bind_param('i', $userId);
        if (!$resetStmt->execute()) {
            throw new Exception("Fehler beim Zurücksetzen der aktiven Tür: " . $resetStmt->error);
        }

        // Schritt 2: Nur die angegebene Tür aktiv setzen, falls gültig
        $updateStmt = $db->prepare("UPDATE advent_calendars SET active_door = ? WHERE user_id = ? AND door_number = ?");
        $updateStmt->bind_param('iii', $doorNumber, $userId, $doorNumber); // Türnummer validieren
        if (!$updateStmt->execute()) {
            throw new Exception("Fehler beim Setzen der aktiven Tür: " . $updateStmt->error);
        }

        if ($updateStmt->affected_rows === 0) {
            throw new Exception("Die Tür konnte nicht als aktiv gesetzt werden. Bitte überprüfe die Eingaben.");
        }

        $db->commit(); // Transaktion abschließen
        echo json_encode(['success' => true, 'message' => "Aktive Tür erfolgreich gesetzt: Tür $doorNumber für Benutzer-ID $userId."]);
    } catch (Exception $e) {
        $db->rollback(); // Transaktion zurücksetzen
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    exit;
}

// Fehler bei ungültiger Methode
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage-Methode.']);
exit;
