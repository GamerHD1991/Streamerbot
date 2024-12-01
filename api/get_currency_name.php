<?php
require_once '../config.php';

header('Content-Type: application/json');

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Benutzer-ID fehlt']);
    exit;
}

$stmt = $db->prepare("SELECT streamelements_jwt FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || empty($user['streamelements_jwt'])) {
    echo json_encode(['success' => false, 'message' => 'StreamElements JWT fehlt']);
    exit;
}

$jwtToken = $user['streamelements_jwt'];
$apiUrl = "https://api.streamelements.com/kappa/v2/channels/me";

// StreamElements API-Request
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $jwtToken"
]);
$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($statusCode !== 200) {
    echo json_encode(['success' => false, 'message' => 'Fehler bei der StreamElements-API']);
    exit;
}

$data = json_decode($response, true);
$currencyName = $data['loyalty']['currency'] ?? 'Punkte';

echo json_encode([
    'success' => true,
    'currency_name' => 'Punkte'
]);
