document.addEventListener("DOMContentLoaded", async () => {
    const calendar = document.getElementById("calendar");

    // Funktion: Türdaten vom Server abrufen
    async function fetchDoors() {
        const response = await fetch('/api/get_calendar_status.php');
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
