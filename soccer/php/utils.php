<?php
    if (isset($_GET['url'])) {
        $url = $_GET['url'];
        $httpcode = 0;
        
        for ($i = 0; $i < 5 && $httpcode != 200; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }
        echo htmlentities($data);
    }
?>