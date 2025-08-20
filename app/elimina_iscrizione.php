<?php
session_start();
include("connessione.php");

// 1. Verifica che l'utente sia loggato
if (!isset($_SESSION['utente_id'])) {
    header("Location: index.php?sezione=login");
    exit();
}

// 2. Verifica che l'ID dell'evento sia stato inviato
if (isset($_POST['evento_id']) && !empty($_POST['evento_id'])) {
    $evento_id = $_POST['evento_id'];
    $utente_id = $_SESSION['utente_id'];

    try {
        // Prepara la query DELETE per eliminare l'iscrizione
        $sql = "DELETE FROM Partecipazioni WHERE utente_id = :utente_id AND evento_id = :evento_id";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':utente_id', $utente_id, PDO::PARAM_INT);
        $stmt->bindParam(':evento_id', $evento_id, PDO::PARAM_INT);
        $stmt->execute();

        // Reindirizza l'utente al profilo con un messaggio di successo
        header("Location: index.php?sezione=profilo&status=cancellato");
        exit();

    } catch (PDOException $e) {
        // In caso di errore, reindirizza con un messaggio
        header("Location: index.php?sezione=profilo&status=errore&message=" . urlencode("Errore durante l'eliminazione."));
        exit();
    }
} else {
    // Se l'ID evento non è valido, reindirizza al profilo
    header("Location: index.php?sezione=profilo&status=errore&message=" . urlencode("ID evento non valido."));
    exit();
}
?>
