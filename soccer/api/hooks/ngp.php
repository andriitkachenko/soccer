<?php

    $ok = $_SERVER['REQUEST_METHOD'] === 'POST';
    if (!$ok) {
        echo 'Wrong request';
        die();
    }
    if (!empty($_POST['debug'])) {
        echo 'Run hook';
    }
   
    require_once __DIR__ . '/../../sources/nowgoalpro/ngp_parsehub_hook.php';
?>