<?php
session_start();
include("connessione.php");

// 1. Controlla se l'utente è loggato
if (!isset($_SESSION['utente_id'])) {
    header("Location: index.php?sezione=login");
    exit();
}

// 2. Controlla se è stato inviato un ID evento tramite POST
if (isset($_POST['evento_id']) && !empty($_POST['evento_id'])) {
    $evento_id = $_POST['evento_id'];
    $utente_id = $_SESSION['utente_id'];

    try {
        // Prepara la query per inserire la partecipazione nel database
        $sql = "INSERT INTO Partecipazioni (utente_id, evento_id) VALUES (:utente_id, :evento_id)";
        $stmt = $pdo->prepare($sql);
        
        // Collega i parametri e esegui la query
        $stmt->bindParam(':utente_id', $utente_id, PDO::PARAM_INT);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->execute();

        // Reindirizza l'utente alla pagina eventi con un messaggio di successo
        header("Location: index.php?sezione=eventi&status=successo");
        exit();

    } catch (PDOException $e) {
        if ($e->getCode() == '23505') {
        header("Location: index.php?sezione=eventi&status=errore&message=" . urlencode("Ti sei già iscritto a questo evento!"));
    } else {
        // Per altri tipi di errori, mostra un messaggio generico
        header("Location: index.php?sezione=eventi&status=errore&message=" . urlencode("Si è verificato un errore: " . $e->getMessage()));
    }
    exit();
    }

} else {
    // Se non viene inviato un ID evento valido, reindirizza alla pagina eventi
    header("Location: index.php?sezione=eventi&status=errore&message=ID evento non valido.");
    exit();
}
?>
