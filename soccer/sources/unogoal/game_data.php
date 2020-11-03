<?php
    $version = time();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Games data</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="css/game_data.css?v=<?=$version?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
        function cleanUp() {
            $('body > div').remove();
            $('body > script').remove();
        }
        function onLoad() {
            cleanUp();
        }
    </script>
</head>
    <body onload="onLoad()">
        <form action="php/game_data.php" name="game_data_form" method="post">
            <div>
                <input type="submit" value="Accept" class="submitButton">
            </div>
            <div>
            <textarea id="dataJson" class="text" rows ="30" name="json"></textarea>
            </div>
        </form> 
    </body>
</html>