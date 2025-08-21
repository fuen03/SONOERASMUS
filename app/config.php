<?php
<<<<<<< HEAD

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
=======
$DB_HOST = 'localhost';
$DB_NAME = 'sonoerasmus';
$DB_USER = 'postgres';
$DB_PASS = ' ';

try {
    // Conexión usando PDO para PostgreSQL
    $pdo = new PDO("pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Log de conexión exitosa (solo para debug)
    // error_log("Conexión a PostgreSQL establecida correctamente");
    
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    // En producción, mostrar un mensaje genérico
    die("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
}

// Función para escapar HTML (prevenir XSS)
function h($s) { 
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); 
}

// Función para generar avatar por defecto
function getDefaultAvatar($name, $size = 40) {
    $name = trim($name);
    if (empty($name)) {
        $name = 'User';
    }
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=c62828&color=ffffff&size=" . $size . "&font-size=0.5";
}

// Función para obtener la foto del usuario (con fallback a avatar por defecto)
function getUserAvatar($user, $size = 40) {
    if (!empty($user['foto'])) {
        return $user['foto'];
    }
    
    $fullName = trim(($user['nome'] ?? '') . ' ' . ($user['cognome'] ?? ''));
    if (empty($fullName)) {
        $fullName = $user['username'] ?? 'User';
    }
    
    return getDefaultAvatar($fullName, $size);
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['utente_id']) && !empty($_SESSION['utente_id']);
}

// Función para obtener datos completos del usuario logueado
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        // Usar los nombres de columnas que existen en tu BD actual
        $stmt = $pdo->prepare("SELECT id, nome, cognome, email, username, foto FROM Utente WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['utente_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Si el usuario no existe en DB pero hay sesión, limpiar sesión
            session_unset();
            session_destroy();
            return null;
        }
        
        return $user;
        
    } catch (PDOException $e) {
        error_log("Error al obtener usuario actual: " . $e->getMessage());
        return null;
    }
}

// Función para requerir login (usar en páginas protegidas)
function requireLogin($redirect_to = '../login.html') {
    if (!isLoggedIn()) {
        header("Location: $redirect_to?error=login_required");
        exit();
    }
}

// Función para limpiar sesión completamente
function clearUserSession() {
    // Limpiar todas las variables de sesión de usuario
    unset($_SESSION['utente_id']);
    unset($_SESSION['utente_nome']);
    unset($_SESSION['utente_cognome']);
    unset($_SESSION['utente_email']);
    unset($_SESSION['utente']);
    unset($_SESSION['utente_username']);
    unset($_SESSION['utente_foto']);
}

// Función para logging de eventos de usuario (opcional)
function logUserAction($action, $details = '', $user_id = null) {
    if (!$user_id && isLoggedIn()) {
        $user_id = $_SESSION['utente_id'];
    }
    
    $log_entry = "[" . date('Y-m-d H:i:s') . "] User $user_id: $action";
    if ($details) {
        $log_entry .= " - $details";
    }
    
    error_log($log_entry);
}

// Configuración de zona horaria
date_default_timezone_set('Europe/Rome');

// Configuración de errores (desactivar en producción)
if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
?>
>>>>>>> 28199591dbbcaf8ed640a6713e53c12088ce2550
