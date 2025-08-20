<?php
$DB_HOST = 'localhost';
$DB_NAME = 'sonoerasmus';
$DB_USER = 'root';
$DB_PASS = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$mysqli->set_charset('utf8mb4');

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
