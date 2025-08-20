<?php
// Avvia la sessione se non è già stata avviata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica che l'utente sia loggato
if (!isset($_SESSION['utente_id'])) {
    header('Location: index.php?sezione=login');
    exit();
}

// Connessione al database
$host = "localhost";
$port = "5432";
$dbname = "sonoerasmus";
$user = "postgres";
$password = "diana";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_SESSION['utente_id'];

    // Se il form è stato inviato
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password_pura = $_POST['password'];

        // Se è stata caricata una nuova foto
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploads_dir = 'uploads';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }
            $foto = $uploads_dir . '/' . basename($_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        }

        // Prepara la query di aggiornamento
        $sql = "UPDATE Utente SET nome=:nome, cognome=:cognome, email=:email, username=:username";
        $params = [
            ':nome' => $nome,
            ':cognome' => $cognome,
            ':email' => $email,
            ':username' => $username,
            ':id' => $id
        ];

        if (!empty($password_pura)) {
            $hashed = password_hash($password_pura, PASSWORD_DEFAULT);
            $sql .= ", password=:password";
            $params[':password'] = $hashed;
        }

        if ($foto) {
            $sql .= ", foto=:foto";
            $params[':foto'] = $foto;
        }

        $sql .= " WHERE id=:id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['utente_nome'] = $nome;
        $_SESSION['utente'] = $username;

        echo "<p style='color:green'>✅ Dati aggiornati con successo!</p>";
    }

    // Carica i dati aggiornati dell’utente
    $stmt = $pdo->prepare("SELECT * FROM Utente WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Carica gli eventi a cui l'utente si è iscritto
    $sql_eventi = "SELECT e.id, e.titolo, e.descrizione, e.data_evento, e.luogo, u.nome AS universita
                   FROM Evento e
                   JOIN Partecipazioni p ON e.id = p.evento_id
                   JOIN Universita u ON e.universita_id = u.id
                   WHERE p.utente_id = :id
                   ORDER BY e.data_evento ASC";
    $stmt_eventi = $pdo->prepare($sql_eventi);
    $stmt_eventi->execute([':id' => $id]);
    $eventi_iscritti = $stmt_eventi->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Errore: " . $e->getMessage() . "</p>";
}
?>

<h2>Profilo utente</h2>

<hr>

<h3>I tuoi dati</h3>
<?php if (!empty($utente['foto'])): ?>
    <img src="<?php echo htmlspecialchars($utente['foto']); ?>" width="150" style="border-radius: 50px;"><br><br>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    Nome:<br>
    <input type="text" name="nome" value="<?= htmlspecialchars($utente['nome']) ?>"><br><br>

    Cognome:<br>
    <input type="text" name="cognome" value="<?= htmlspecialchars($utente['cognome']) ?>"><br><br>

    Email:<br>
    <input type="email" name="email" value="<?= htmlspecialchars($utente['email']) ?>"><br><br>

    Username:<br>
    <input type="text" name="username" value="<?= htmlspecialchars($utente['username']) ?>"><br><br>

    Nuova password (lascia vuoto se non vuoi cambiarla):<br>
    <input type="password" name="password"><br><br>

    Cambia foto profilo:<br>
    <input type="file" name="foto"><br><br>

    <button type="submit">💾 Salva modifiche</button>
</form>

<br>
<hr>

<h3>Eventi a cui sei iscritto</h3>
<?php if ($eventi_iscritti): ?>
    <ul>
        <?php foreach ($eventi_iscritti as $evento): ?>
            <li style="margin-bottom: 20px;">
                <strong><?= htmlspecialchars($evento['titolo']) ?></strong><br>
                <?= htmlspecialchars($evento['descrizione']) ?><br>
                📍 <?= htmlspecialchars($evento['luogo']) ?> — 📅 <?= htmlspecialchars($evento['data_evento']) ?><br>
                🎓 Università: <?= htmlspecialchars($evento['universita']) ?><br>
                
                <?php
                // Check if 'id' exists in the event data before trying to use it
                if (isset($evento['id'])): ?>
                    <form action="elimina_iscrizione.php" method="POST" style="display:inline;">
                        <input type="hidden" name="evento_id" value="<?= htmlspecialchars($evento['id']) ?>">
                        <button type="submit" style="color:red; background:none; border:none; cursor:pointer;">❌ Elimina iscrizione</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Non sei ancora iscritto a nessun evento.</p>
<?php endif; ?>

<br><br>
<a href="index.php">🔙 Torna alla home</a>
