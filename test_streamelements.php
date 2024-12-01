<?php
$channelId = "5fb49a2d73fa232f0d6cba4b"; // Deine Kanal-ID
$jwtToken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJjaXRhZGVsIiwiZXhwIjoxNzQ4NTYyMDU2LCJqdGkiOiIwN2M1ZWQxMC04YTMwLTRmZTQtYWU2ZC00NWRmOTRjNDE1YWQiLCJjaGFubmVsIjoiNWZiNDlhMmQ3M2ZhMjMyZjBkNmNiYTRiIiwicm9sZSI6Im93bmVyIiwiYXV0aFRva2VuIjoiNDhTTGE5Y2k1MzVEU2xpU1NqQXZGYWVHUXEwQTVLaVU2SmZBUVpEV2tsUUpjeGNBIiwidXNlciI6IjVmYjQ5YTJkNzNmYTIzZmRkODZjYmE0YSIsInVzZXJfaWQiOiIyMTBjNTZmYi05OGEwLTRmMzctOWViYS1jMmY3ZTFmMjNmNzYiLCJ1c2VyX3JvbGUiOiJjcmVhdG9yIiwicHJvdmlkZXIiOiJ0d2l0Y2giLCJwcm92aWRlcl9pZCI6IjczNTczOTA1IiwiY2hhbm5lbF9pZCI6IjY4MDg5ZWQyLWE0YTUtNDljMS05N2E4LWJhZWMxMzFlY2IxMSIsImNyZWF0b3JfaWQiOiI2YjU1ZWNmNS00MjU3LTRjZTItOGE5YS0yZDBkZmQ0MzhlZmUifQ.bJOy3ah8vv_cLX2GbjVS0sdnp667dA32HLOiZwFzTfA"; // Dein gültiges JWT-Token

$username = "bmwsepp"; // Benutzername

$apiUrl = "https://api.streamelements.com/kappa/v2/points/$channelId";

$data = [
    "users" => [
        ["username" => $username]
    ],
    "mode" => "get" // Modus prüfen (falls nötig)
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $jwtToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');
if ($statusCode === 200) {
    echo json_encode([
        'success' => true,
        'message' => "Punkte erfolgreich abgerufen.",
        'response' => json_decode($response, true)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Fehler beim Abrufen der Punkte.",
        'statusCode' => $statusCode,
        'response' => $response
    ]);
}
?>
