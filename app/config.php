<?php

$DB_HOST='localhost'; $DB_NAME='sonoerasmus'; $DB_USER='root'; $DB_PASS='';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
$mysqli->set_charset('utf8mb4');

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Helpers de auth
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){
  if (!current_user()){ header('Location: /login.html?next='.urlencode($_SERVER['REQUEST_URI'])); exit; }
}
function is_admin(){ return (current_user()['role'] ?? '') === 'admin'; }
