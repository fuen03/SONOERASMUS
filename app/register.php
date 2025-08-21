<?php
require_once 'config.php';

try {
    // Validar que todos los campos estén completos
    $required_fields = ['nome', 'cognome', 'email', 'username', 'password', 'conferma_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            header("Location: ../register.html?error=" . urlencode("Tutti i campi sono obbligatori."));
            exit();
        }
    }

    // Obtener y sanitizar datos
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password_pura = $_POST['password'];
    $conferma_password_pura = $_POST['conferma_password'];

    // Validación del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../register.html?error=" . urlencode("Il formato dell'indirizzo email non è valido."));
        exit();
    }

    // Validación de contraseñas
    if ($password_pura !== $conferma_password_pura) {
        header("Location: ../register.html?error=" . urlencode("Le password non corrispondono."));
        exit();
    }

    // Validación de longitud de contraseña
    if (strlen($password_pura) < 8) {
        header("Location: ../register.html?error=" . urlencode("La password deve contenere almeno 8 caratteri."));
        exit();
    }

    // Verificar si email o username ya existen
    $sql_check = "SELECT COUNT(*) as count FROM Utente WHERE email = :email OR username = :username";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':email' => $email, ':username' => $username]);
    $result = $stmt_check->fetch();
    
    if ($result['count'] > 0) {
        header("Location: ../register.html?error=" . urlencode("L'email o l'username sono già in uso. Scegli un altro."));
        exit();
    }

    // Hash de la contraseña
    $hashed = password_hash($password_pura, PASSWORD_DEFAULT);

    // Insertar usuario en la base de datos
    $sql_insert = "INSERT INTO Utente (nome, cognome, email, username, password) 
                   VALUES (:nome, :cognome, :email, :username, :password)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':nome' => $nome,
        ':cognome' => $cognome,
        ':email' => $email,
        ':username' => $username,
        ':password' => $hashed
    ]);

    // Registración exitosa - redirigir al login
    header("Location: ../login.html?success=" . urlencode("Registrazione completata con successo! Ora puoi accedere."));
    exit();

} catch (PDOException $e) {
    error_log("Error en registro: " . $e->getMessage());
    header("Location: ../register.html?error=" . urlencode("Errore durante la registrazione. Riprova più tardi."));
    exit();
}
?>
