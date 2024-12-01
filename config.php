<?php
// Fehleranzeige für Debugging aktivieren (kann später deaktiviert werden)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Funktion zur Verarbeitung der .env-Datei
function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        die("Fehler: .env-Datei nicht gefunden!");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Kommentare überspringen
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Lade die .env-Datei
loadEnv(__DIR__ . '/.env');

// Verbindung zur MySQL-Datenbank herstellen
$db = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

// Überprüfen, ob die Verbindung erfolgreich war
if ($db->connect_error) {
    die("Datenbank-Verbindung fehlgeschlagen: " . $db->connect_error);
}

// Session starten, falls noch nicht gestartet
if (session_status() !== PHP_SESSION_ACTIVE) {
    die("Session ist nicht aktiv, überprüfe session_start()");
} else {
   
}

// Datenbank-Konfiguration
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);;
define('DB_NAME', $_ENV['DB_NAME']);;

// Twitch-Konstanten definieren
define('TWITCH_CLIENT_ID', $_ENV['TWITCH_CLIENT_ID']);
define('TWITCH_CLIENT_SECRET', $_ENV['TWITCH_CLIENT_SECRET']);
define('TWITCH_REDIRECT_URI', $_ENV['TWITCH_REDIRECT_URI']);
?>
