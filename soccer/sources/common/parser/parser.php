<?php
declare(strict_types=1);

interface iParser {
    public static function parseStat($html);
    public static function parseGame($html);
}

?>