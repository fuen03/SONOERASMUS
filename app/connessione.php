<?php
try {
    $pdo = new PDO("pgsql:host=localhost;dbname=sonoerasmus", "postgres", "diana");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connessione riuscita!";
} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?>
