<?php
$host = "localhost";
$port = "5432";
$dbname = "sonoerasmus";
$user = "postgres";
$password = "diana"; 

try {
    // Stabilire la connessione usando PDO
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validare che i campi non siano vuoti.
    if (empty($_POST['nome']) || empty($_POST['cognome']) || empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['conferma_password']))  {
        header("Location: register.html?error=Tutti i campi sono obbligatori.");
        exit();
    }

    // Ottenere e sanificare i dati del form 
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password_pura = $_POST['password'];
    $conferma_password_pura = $_POST['conferma_password'];


    // Validazione dell'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Il formato dell'indrizzo email non è valido.");
    }

    // Validazione password == conferma password
    if ($password_pura !== $conferma_password_pura) {
        header("Location: register.html?error=password_non_coincidono");
        exit();
    }

    // Verificare se l'email o l'username essitono già nel databasae
    $sql_check = "SELECT COUNT(*) FROM Utente WHERE email = :email OR username = :username";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt->execute([':email' => $email, ':username' => $username]);
    
    if ($stmt->fetchCount() > 0) {
        header("Location: register.html?error=L'email o l'username sono già in uso. Scegli un altro.");
    }

    // Hashing della password
    $hashed = password_hash($password_pura, PASSWORD_DEFAULT);

    // Prepare la query SQL per inserire i dati dell'utente
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

    // Sucesso della registrazione
    echo "Registrazione completata con successo!";
} catch (PDOException $e) {
    // Gestione degli errori di connessione o query
    echo "Errore durante la registrazione: " . $e->getMessage();
}
?>
