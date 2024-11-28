<?php
require_once '../config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login mit Twitch</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="container">
        <h1>Login mit Twitch</h1>
        <p>Melde dich an, um auf deinen Adventskalender zuzugreifen.</p>
        <a href="https://id.twitch.tv/oauth2/authorize?response_type=code&client_id=<?php echo TWITCH_CLIENT_ID; ?>&redirect_uri=<?php echo urlencode(TWITCH_REDIRECT_URI); ?>&scope=user:read:email">
            Mit Twitch einloggen
        </a>
        <div class="footer">
            <p>Powered by <a href="https://bestefreundecommunity.de">Beste Freunde Community</a></p>
        </div>
    </div>
</body>
</html>
