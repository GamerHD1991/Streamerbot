<?php
require_once 'config.php';

header('Content-Type: text/html');

// Benutzer-ID aus der URL
$user_id = intval($_GET['user_id'] ?? 0);

if (!$user_id) {
    die("Fehler: Benutzer-ID fehlt.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adventskalender</title>
    <link rel="stylesheet" href="calendar.css">
</head>
<body>
    <h1>🎄 Adventskalender</h1>
    <div id="calendar">
        <!-- Dynamische Inhalte -->
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            const userId = <?php echo json_encode($user_id); ?>;
            const calendar = document.getElementById("calendar");

            // Funktion: Türdaten abrufen
            async function fetchDoors() {
                const response = await fetch(`/api/get_calendar_status.php?user_id=${userId}`);
                if (!response.ok) throw new Error("Fehler beim Laden der Kalendertüren.");
                return response.json();
            }

            // Funktion: Kalender rendern
            function renderCalendar(doors) {
                calendar.innerHTML = ""; // Alte Türchen entfernen

                for (let i = 1; i <= 24; i++) {
                    const door = doors.find(d => d.door_number === i);
                    const isOpen = door ? door.is_open : false;

                    const doorElement = document.createElement("div");
                    doorElement.classList.add("door");
                    if (isOpen) doorElement.classList.add("open");

                    const number = document.createElement("div");
                    number.classList.add("door-number");
                    number.textContent = i;

                    doorElement.appendChild(number);
                    calendar.appendChild(doorElement);
                }
            }

            try {
                const data = await fetchDoors();
                renderCalendar(data.doors);
            } catch (error) {
                console.error("Fehler beim Rendern des Kalenders:", error);
            }

            // Aktualisierung alle 10 Sekunden
            setInterval(async () => {
                try {
                    const data = await fetchDoors();
                    renderCalendar(data.doors);
                } catch (error) {
                    console.error("Fehler beim Aktualisieren des Kalenders:", error);
                }
            }, 10000);
        });
    </script>
</body>
</html>
