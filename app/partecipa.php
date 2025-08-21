<?php
session_start();
require_once 'config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    header("Location: ../login.html?error=Devi essere loggato per partecipare agli eventi");
    exit();
}

// Verificar que se haya enviado un ID evento
if (!isset($_POST['evento_id']) || empty($_POST['evento_id'])) {
    header("Location: eventi.php?status=errore&message=" . urlencode("ID evento non valido"));
    exit();
}

$evento_id = (int)$_POST['evento_id'];
$utente_id = $_SESSION['utente_id'];

try {
    // Verificar que el evento exista y esté disponible
    $sql_evento = "SELECT e.*, 
                   (SELECT COUNT(*) FROM Partecipazioni p WHERE p.evento_id = e.id) as partecipanti_attuali,
                   (SELECT COUNT(*) FROM Partecipazioni p WHERE p.evento_id = e.id AND p.utente_id = :utente_id) as gia_iscritto
                   FROM Evento e WHERE e.id = :evento_id AND e.data_evento >= NOW()";
    
    $stmt_evento = $pdo->prepare($sql_evento);
    $stmt_evento->execute([':evento_id' => $evento_id, ':utente_id' => $utente_id]);
    $evento = $stmt_evento->fetch();
    
    if (!$evento) {
        header("Location: eventi.php?status=errore&message=" . urlencode("Evento non trovato o già terminato"));
        exit();
    }
    
    // Verificar que el usuario no esté ya inscrito
    if ($evento['gia_iscritto'] > 0) {
        header("Location: eventi.php?status=errore&message=" . urlencode("Ti sei già iscritto a questo evento"));
        exit();
    }
    
    // Verificar capacidad máxima
    if ($evento['max_partecipanti'] && $evento['partecipanti_attuali'] >= $evento['max_partecipanti']) {
        header("Location: eventi.php?status=errore&message=" . urlencode("L'evento ha raggiunto la capacità massima"));
        exit();
    }
    
    // Insertar participación
    $sql_insert = "INSERT INTO Partecipazioni (utente_id, evento_id, data_iscrizione) VALUES (:utente_id, :evento_id, NOW())";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([':utente_id' => $utente_id, ':evento_id' => $evento_id]);
    
    // Redirigir con éxito
    header("Location: eventi.php?status=successo");
    exit();
    
} catch (PDOException $e) {
    error_log("Error en partecipazione: " . $e->getMessage());
    
    // Verificar si es error de clave duplicada
    if ($e->getCode() == '23505' || strpos($e->getMessage(), 'duplicate key') !== false) {
        header("Location: eventi.php?status=errore&message=" . urlencode("Ti sei già iscritto a questo evento!"));
    } else {
        header("Location: eventi.php?status=errore&message=" . urlencode("Si è verificato un errore. Riprova più tardi."));
    }
    exit();
}
?>
