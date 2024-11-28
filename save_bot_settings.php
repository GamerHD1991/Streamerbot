<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bot_username = $_POST['bot_username'] ?? '';
    $oauth_token = $_POST['oauth_token'] ?? '';
    $channel_name = $_POST['channel_name'] ?? '';

    if ($bot_username && $oauth_token && $channel_name) {
        $stmt = $db->prepare("UPDATE users SET bot_username = ?, oauth_token = ?, channel_name = ? WHERE id = ?");
        $stmt->bind_param('sssi', $bot_username, $oauth_token, $channel_name, $_SESSION['user_id']);
        $stmt->execute();

        header('Location: dashboard.php?success=bot-settings-saved');
        exit;
    }
}

header('Location: dashboard.php?error=missing-fields');
exit;
?>
