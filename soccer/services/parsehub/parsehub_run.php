<?php

require_once __DIR__ . '/parsehub_utils.php';

$runData = getRunTokenSeries();
if (empty($runData['ok'])) {
    echo "Project run failed";
    die();
}
echo date("d-m-Y H:i:s");
echo "<br /><br />";
echo $runData['run'];
echo "<br /><br />";
echo "Attempts: " . $runData['attempts'];
echo "<br /><br />";
echo $runData['logged'] ? "Log successful" : "Log failed" ;

?>