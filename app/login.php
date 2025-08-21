<?php
session_start();
require_once 'config.php';

// Si el usuario ya está logueado, redirigir
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: dashboard.php");
    }  
    if (!isAdmin()) {
        header("Location: ../index.php");
    }
    exit();
}

try {
    // Verificar que se enviaron los datos del formulario
    if (!isset($_POST['username_or_email']) || !isset($_POST['password'])) {
        header("Location: ../login.html?error=datos_incompletos");
        exit();
    }

    // Obtener y sanitizar los datos del formulario
    $username_or_email = trim($_POST['username_or_email']);
    $password_inserita = $_POST['password'];

    // Validación: verificar que los campos no estén vacíos
    if (empty($username_or_email) || empty($password_inserita)) {
        header("Location: ../login.html?error=campi_obbligatori");
        exit();
    }

    // Query para buscar el usuario por username o email
    $sql = "SELECT id, nome, cognome, email, username, password, foto, role
            FROM Utente 
            WHERE username = :login_id OR email = :login_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':login_id' => $username_or_email]);
    $utente = $stmt->fetch();

    if ($utente && password_verify($password_inserita, $utente['password'])) {
        // Login exitoso - crear todas las variables de sesión necesarias
        $_SESSION['utente_id'] = $utente['id'];
        $_SESSION['utente_nome'] = $utente['nome'];
        $_SESSION['utente_cognome'] = $utente['cognome'];
        $_SESSION['utente_email'] = $utente['email'];
        $_SESSION['utente'] = $utente['username']; // Para compatibilidad con código existente
        $_SESSION['utente_username'] = $utente['username'];
        $_SESSION['utente_foto'] = $utente['foto'];
        $_SESSION['utente_role'] = $utente['role'];
        
        // Log de debug para verificar sesión
        error_log("Login exitoso para usuario: " . $utente['username'] . " (ID: " . $utente['id'] . ")");
        
        // Redirigir según el rol del usuario
        if ($_SESSION['utente_role'] !== 'admin') {
            header("Location: ../index.php?login=success");
        } 
        if ($_SESSION['utente_role'] === 'admin') {
            header("Location: dashboard.php?login=success");
        }
        exit();
        
    } else {
        // Login fallido
        error_log("Login fallido para: " . $username_or_email);
        
        if (!$utente) {
            header("Location: ../login.html?error=utente_non_trovato");
        } else {
            header("Location: ../login.html?error=password_errata");
        }
        exit();
    }

} catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    header("Location: ../login.html?error=errore_sistema");
    exit();
} catch (Exception $e) {
    error_log("Error general en login: " . $e->getMessage());
    header("Location: ../login.html?error=errore_generale");
    exit();
}
?>
