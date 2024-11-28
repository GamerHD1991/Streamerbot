$url = 'https://adventskalender.bestefreundecommunity.de/api/save_participants.php';

$data = [
    'user_id' => 1,
    'door_number' => 1,
    'participant_name' => 'TestBenutzer'
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo $result;
