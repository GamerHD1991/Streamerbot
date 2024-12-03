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

            <div>                
                <small>
                    (<a href="/generate_token.php" target="_blank">Erstelle deinen OAuth-Token hier</a>)
                </small>
            </div>

            <label>OAuth-Token:</label>
            <input type="text" name="oauth_token" value="<?php echo htmlspecialchars($user['oauth_token'] ?? ''); ?>" required>

            <label>Kanalname:</label>
            <input type="text" name="channel_name" value="<?php echo htmlspecialchars($user['channel_name'] ?? ''); ?>" required>

            <label for="channel_id">StreamElements Konto-ID:</label>
            <input type="text" id="channel_id" name="channel_id" value="<?php echo htmlspecialchars($user['channel_id'] ?? ''); ?>" required>

            <label>StreamElements JWT Token:</label>
            <input type="text" name="streamelements_jwt" value="<?php echo htmlspecialchars($user['streamelements_jwt'] ?? ''); ?>" required>

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
                    <form method="POST" action="save_door.php">
                        <input type="hidden" name="door_number" value="<?php echo $door['door_number']; ?>">

                        <div class="form-group">
                            <label for="prize_<?php echo $door['door_number']; ?>">Gewinn:</label>
                            <input id="prize_<?php echo $door['door_number']; ?>" type="text" name="prize" 
                                value="<?php echo htmlspecialchars($door['prize'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="duration_<?php echo $door['door_number']; ?>">Dauer (Sekunden):</label>
                            <input id="duration_<?php echo $door['door_number']; ?>" type="number" name="giveaway_duration" 
                                value="<?php echo htmlspecialchars($door['giveaway_duration'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="points_cost_<?php echo $door['door_number']; ?>">Punkte-Kosten (StreamElements):</label>
                            <input id="points_cost_<?php echo $door['door_number']; ?>" type="number" name="points_cost" 
                                value="<?php echo htmlspecialchars($door['points_cost'] ?? 0); ?>" required>
                        </div>

                        <button type="submit">Speichern</button>
                    </form>
                    <?php if ($door['is_open']): ?>
                        <form method="POST" action="close_door.php" style="margin-top: 10px;">
                            <input type="hidden" name="door_number" value="<?php echo $door['door_number']; ?>">
                            <button type="submit" style="background-color: orange; color: white;">Tür schließen</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <center>
        <!-- Ignorierte Teilnehmer -->
        <h2>Ignorierte Teilnehmer</h2>
        <ul id="ignored-participants" class="styled-list"></ul>

        <!-- Teilnehmer -->
        <h2>Teilnehmer</h2>
        <ul id="participants-list" class="styled-list"></ul>

        <!-- Aktive Tür -->
        <h2>Aktive Tür</h2>
        <p id="active-door-display">Noch keine aktive Tür</p>
    </div>

    <script>
        async function fetchIgnoredParticipants() {
            try {
                const response = await fetch('/api/fetch_ignored_participants.php');
                if (!response.ok) {
                    throw new Error(`HTTP-Fehler: ${response.status}`);
                }
                const data = await response.json();
                const ignoredList = document.getElementById('ignored-participants');
                ignoredList.innerHTML = '';

                if (data.success && data.participants.length) {
                    data.participants.forEach(participant => {
                        const listItem = document.createElement('li');
                        listItem.innerHTML = `
                            <div>
                                <span>${participant}</span>
                                <button onclick="removeIgnored('${participant}')">Entfernen</button>
                            </div>
                        `;
                        ignoredList.appendChild(listItem);
                    });
                } else {
                    ignoredList.innerHTML = '<li>Keine ignorierten Teilnehmer gefunden.</li>';
                }
            } catch (error) {
                console.error('Fehler beim Abrufen der ignorierten Teilnehmer:', error);
            }
        }

        async function fetchParticipants() {
            try {
                const response = await fetch('/api/fetch_giveaway_participants.php');
                if (!response.ok) {
                    throw new Error(`HTTP-Fehler: ${response.status}`);
                }
                const data = await response.json();
                console.log("API-Antwort für Teilnehmer:", data);

                const participantsList = document.getElementById('participants-list');
                participantsList.innerHTML = ''; // Teilnehmerliste leeren

                if (data.success && Array.isArray(data.participants) && data.participants.length > 0) {
                    data.participants.forEach(participantName => {
                        const listItem = document.createElement('li');
                        listItem.style.marginBottom = '30px'; // Abstand wie in Adventskalender-Einstellungen
                        listItem.style.padding = '10px';
                        listItem.style.border = '1px solid #666';
                        listItem.style.borderRadius = '5px';
                        listItem.style.backgroundColor = '#444';

                        const participantInfo = document.createElement('div');
                        participantInfo.style.display = 'flex';
                        participantInfo.style.justifyContent = 'space-between';
                        participantInfo.style.alignItems = 'center';
                        participantInfo.style.gap = '20px'; // Abstand zwischen Name und Button

                        // Teilnehmername
                        const participantNameElement = document.createElement('span');
                        participantNameElement.textContent = participantName;
                        participantNameElement.style.color = '#fff';
                        participantNameElement.style.fontWeight = 'bold';
                        participantNameElement.style.textAlign = 'left'; // Links ausgerichtet

                        // Ignorieren-Button
                        const ignoreButton = document.createElement('button');
                        ignoreButton.textContent = 'Ignorieren';
                        ignoreButton.style.backgroundColor = '#e63946';
                        ignoreButton.style.color = 'white';
                        ignoreButton.style.border = 'none';
                        ignoreButton.style.padding = '10px 20px';
                        ignoreButton.style.borderRadius = '5px';
                        ignoreButton.style.cursor = 'pointer';
                        ignoreButton.style.textAlign = 'right'; // Rechts ausgerichtet
                        ignoreButton.addEventListener('click', () => ignoreParticipant(participantName));

                        participantInfo.appendChild(participantNameElement);
                        participantInfo.appendChild(ignoreButton);

                        listItem.appendChild(participantInfo);
                        participantsList.appendChild(listItem);
                    });
                } else {
                    const emptyMessage = document.createElement('li');
                    emptyMessage.textContent = 'Keine Teilnehmer gefunden';
                    emptyMessage.style.color = '#888';
                    emptyMessage.style.textAlign = 'center';
                    emptyMessage.style.padding = '10px';
                    emptyMessage.style.backgroundColor = '#333';
                    emptyMessage.style.borderRadius = '5px';
                    participantsList.appendChild(emptyMessage);
                }
            } catch (error) {
                console.error('Fehler beim Abrufen der Teilnehmer:', error);
                const participantsList = document.getElementById('participants-list');
                participantsList.innerHTML = `<li style="color: red; text-align: center;">Fehler beim Laden der Teilnehmer</li>`;
            }
        }

        async function fetchActiveDoor() {
            try {
                const response = await fetch('/api/fetch_active_door.php');
                if (!response.ok) {
                    throw new Error(`HTTP-Fehler: ${response.status}`);
                }
                const data = await response.json();
                const activeDoorDisplay = document.getElementById('active-door-display');

                if (data.success && data.active_door) {
                    activeDoorDisplay.textContent = `Aktive Tür: ${data.active_door}`;
                } else {
                    activeDoorDisplay.textContent = 'Keine aktive Tür gefunden.';
                }
            } catch (error) {
                console.error('Fehler beim Abrufen der aktiven Tür:', error);
            }
        }

        async function ignoreParticipant(username) {
            try {
                const response = await fetch('/api/ignore_participant.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username })
                });

                if (!response.ok) {
                    throw new Error(`HTTP-Fehler: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    alert(data.message); // Erfolgsnachricht anzeigen
                    fetchParticipants(); // Teilnehmerliste aktualisieren
                    fetchIgnoredParticipants(); // Ignorierliste aktualisieren
                } else {
                    alert(`Fehler: ${data.message}`);
                }
            } catch (error) {
                console.error('Fehler beim Ignorieren des Teilnehmers:', error);
                alert('Es ist ein Fehler aufgetreten. Bitte versuche es später erneut.');
            }
        }

        async function removeIgnored(participantName) {
            try {
                const response = await fetch('/api/remove_from_ignore_list.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ participant_name: participantName })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message); // Erfolgsmeldung anzeigen
                    fetchIgnoredParticipants(); // Aktualisiert die Liste der ignorierten Teilnehmer
                } else {
                    alert(`Fehler: ${data.message}`);
                }
            } catch (error) {
                console.error('Fehler beim Entfernen des Teilnehmers:', error);
                alert('Fehler beim Entfernen des Teilnehmers. Bitte versuche es später erneut.');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchParticipants();
            fetchIgnoredParticipants();
            fetchActiveDoor();
        });
    </script>
</body>
</html>
