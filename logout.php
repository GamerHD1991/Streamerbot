<?php
// Session nur starten, wenn noch keine läuft
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session-Daten löschen
session_unset();
session_destroy();

// Weiterleitung zur Login-Seite
header('Location: auth/login.php');
exit;
?>
