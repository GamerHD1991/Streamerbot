<?php
require_once 'config.php';

// Überprüfung, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Benutzer-Daten abrufen
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Fehler: Benutzerdaten konnten nicht geladen werden.");
}

// Adventskalender-Daten abrufen
$stmt = $db->prepare("SELECT * FROM advent_calendars WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$calendar = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adventskalender Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="container">
        <h1>🎄 Dein Adventskalender Dashboard</h1>

        <h2>Benutzerverwaltung</h2>
        <p>
            Eingeloggt als: <?php echo htmlspecialchars($user['username']); ?>
        </p>
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit">Ausloggen</button>
        </form>

        <h2>Bot-Einstellungen</h2>
        <form method="POST" action="save_bot_settings.php">
            <label>Bot-Name:</label>
            <input type="text" name="bot_username" value="<?php echo htmlspecialchars($user['bot_username'] ?? ''); ?>" required>
            <label>OAuth-Token:</label>
            <div>
                <input type="text" name="oauth_token" value="<?php echo htmlspecialchars($user['oauth_token'] ?? ''); ?>" required>
                <small>(<a href="https://twitchapps.com/tmi/" target="_blank">Erstelle deinen OAuth-Token hier</a>)</small>
            </div>
            <label>Kanalname:</label>
            <input type="text" name="channel_name" value="<?php echo htmlspecialchars($user['channel_name'] ?? ''); ?>" required>
            <button type="submit">Speichern</button>
        </form>

        <h2>Bot Steuerung</h2>
        <form onsubmit="startBot(event)">
            <button type="submit" style="background-color: blue; color: white;">Bot starten</button>
        </form>
        <script>
            function startBot(event) {
                event.preventDefault(); // Verhindert das Standard-Formular-Submit
                const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
                const botUrl = `/public/twitch_bot.html?user_id=${userId}`;
                const botWindow = window.open(botUrl, '_blank');
                if (!botWindow) {
                    alert('Fehler: Bot konnte nicht gestartet werden. Bitte Pop-ups erlauben.');
                }
            }
        </script>

        <h2>OBS-Integration</h2>
        <p>
            Dein individueller OBS-Link: 
            <p>Kalender</p>
            <code>https://adventskalender.bestefreundecommunity.de/calendar.php?user_id=<?php echo $_SESSION['user_id']; ?></code>
            <p>Lostrommel</p>
            <code>https://adventskalender.bestefreundecommunity.de/giveaway_visual.html?user_id=<?php echo $_SESSION['user_id']; ?></code>
        </p>
        <p>Kopiere diesen Link und füge ihn in OBS als Browser-Quelle ein.</p>

        <h2>Adventskalender</h2>
        <p style="display: flex; align-items: center; gap: 10px;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <form method="POST" action="create_calendar.php" onsubmit="return confirm('Bist du sicher, dass du einen Adventskalender erstellen möchtest?');">
                    <button type="submit" style="background-color: green; color: white;">Adventskalender erstellen</button>
                </form>
                <form method="POST" action="delete_calendar.php" onsubmit="return confirm('Bist du sicher, dass du den gesamten Kalender löschen möchtest?');">
                    <button type="submit" style="background-color: red; color: white;">Kalender löschen</button>
                </form>
            </div>
            <div class="info-box">
            <h3>ℹ️ Anleitung für Twitch-Befehle:</h3>
                <p>
                    Um ein Giveaway für eine Tür zu starten, gebe im Twitch-Chat den Befehl ein:
                </p>
                <pre><code>!advent [Türnummer]</code></pre>
                <p>Beispiel: <code>!advent 1</code>, um das Giveaway für Tür 1 zu starten.</p>
            </div>
        </p>

        <h2>Adventskalender-Einstellungen</h2>        
        <ul>
            <?php while ($door = $calendar->fetch_assoc()): ?>
                <li>
                    Tür <?php echo $door['door_number']; ?>:
                    <?php echo $door['is_open'] ? "Geöffnet 🎉" : "Geschlossen 🔒"; ?>
                    <form method="POST" action="save_door.php">
                        <input type="hidden" name="door_number" value="<?php echo $door['door_number']; ?>">
                        <label>Gewinn:</label>
                        <input type="text" name="prize" value="<?php echo htmlspecialchars($door['prize'] ?? ''); ?>">
                        <label>Dauer (Sekunden):</label>
                        <input type="number" name="giveaway_duration" value="<?php echo htmlspecialchars($door['giveaway_duration'] ?? ''); ?>" required>
                        <label>Min. Follower-Stunden:</label>
                        <input type="number" name="min_follower_hours" value="<?php echo htmlspecialchars($door['min_follower_hours'] ?? 0); ?>" required>
                        <button type="submit">Speichern</button>
                    </form>
                    <?php if ($door['is_open']): ?>
                        <form method="POST" action="close_door.php" style="display:inline;">
                            <input type="hidden" name="door_number" value="<?php echo $door['door_number']; ?>">
                            <button type="submit" style="background-color: orange; color: white;">Tür schließen</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
