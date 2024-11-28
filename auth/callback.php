<?php
require_once '../config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Tausche Code gegen Access-Token
    $tokenUrl = "https://id.twitch.tv/oauth2/token";
    $data = [
        'client_id' => TWITCH_CLIENT_ID,
        'client_secret' => TWITCH_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => TWITCH_REDIRECT_URI,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['access_token'])) {
        $accessToken = $response['access_token'];

        // Benutzerdaten abrufen
        $userUrl = "https://api.twitch.tv/helix/users";
        $headers = [
            "Authorization: Bearer $accessToken",
            "Client-Id: " . TWITCH_CLIENT_ID,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $userData = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($userData['data'][0]['id'])) {
            $twitchId = $userData['data'][0]['id'];
            $username = $userData['data'][0]['display_name'];

            // Überprüfen, ob der Benutzer bereits existiert
            $stmt = $db->prepare("SELECT id FROM users WHERE twitch_id = ?");
            $stmt->bind_param('s', $twitchId);
            $stmt->execute();
            $result = $stmt->get_result();
            $existingUser = $result->fetch_assoc();

            if ($existingUser) {
                // Benutzer existiert, aktualisieren
                $stmt = $db->prepare("
                    UPDATE users 
                    SET username = ?, updated_at = NOW() 
                    WHERE twitch_id = ?
                ");
                $stmt->bind_param('ss', $username, $twitchId);
                $stmt->execute();

                // Setze die Session-ID auf den bestehenden Benutzer
                $_SESSION['user_id'] = $existingUser['id'];
            } else {
                // Benutzer existiert nicht, einfügen
                $stmt = $db->prepare("
                    INSERT INTO users (twitch_id, username, bot_username, oauth_token, channel_name, advent_command) 
                    VALUES (?, ?, ?, '', ?, '!advent')
                ");
                $botUsername = $username . "_bot";
                $channelName = $username; // Standardmäßig Username als Channel-Name
                $stmt->bind_param('ssss', $twitchId, $username, $botUsername, $channelName);
                $stmt->execute();

                // Setze die Session-ID auf den neuen Benutzer
                $_SESSION['user_id'] = $stmt->insert_id;
            }

            // Weiterleitung zum Dashboard
            header('Location: ../dashboard.php');
            exit;
        }
    }
}

die("Fehler: Twitch-Authentifizierung fehlgeschlagen.");
?>
