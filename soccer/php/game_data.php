<?php
    if (!isset($_POST['json'])) {
        exit();
    }
    const DATA_FILE = __DIR__ . '/../data/games.json';
    $data = $_POST['json'];
    $data = str_replace("'", '', trim($data));
    $ok = file_put_contents(DATA_FILE, $data);
?>