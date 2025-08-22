<?php
try {
    $pdo = new PDO("pgsql:host=localhost;dbname=postgres", "postgres", "mariafu03");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connessione riuscita!";
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?>
