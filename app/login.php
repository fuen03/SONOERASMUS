<?php
// Avvia la sessione per memorizzare le informazioni dell'utente
session_start();

// Configurazione del database
$host = "localhost";
$port = "5432";
$dbname = "sonoerasmus";
$user = "postgres";
$db_password = "diana"; 

try {
    // Connessione al database PostgreSQL
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ottieni e sanifica i dati del form
    $username_or_email = htmlspecialchars(trim($_POST['username_or_email']));
    $password_inserita = $_POST['password'];

    // Validazione: controlla che i campi non siano vuoti
    if (empty($username_or_email) || empty($password_inserita)) {
        header("Location: login.html?error=campi_obbligatori");
        exit();
    }

    // Query per cercare l'utente per username o email
    $sql = "SELECT id, password FROM Utente WHERE username = :login_id OR email = :login_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':login_id' => $username_or_email]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utente) {
        // Verifica la password utilizzando password_verify()
        if (password_verify($password_inserita, $utente['password'])) {
            // Password corretta: avvia la sessione
            $_SESSION['user_id'] = $utente['id'];
            $_SESSION['username'] = $username_or_email;
            
            // Reindirizza l'utente alla pagina di successo
            header("Location: dashboard.php");
            exit();
        } else {
            // Password errata: reindirizza con un messaggio di errore
            header("Location: login.html?error=password_errata");
            exit();
        }
    } else {
        // Utente non trovato: reindirizza con un messaggio di errore
        header("Location: login.html?error=utente_non_trovato");
        exit();
    }

} catch (PDOException $e) {
    // Gestione degli errori del database
    header("Location: login.html?error=db_error");
    exit();
}
?>
