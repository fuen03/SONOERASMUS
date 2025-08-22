<?php
session_start();
require_once __DIR__ . '/config.php';

// (opcional pero recomendado)
if (!isAdmin()) {
  header('Location: index.php');
  exit;
}

$total_usuarios = $total_universitas = $total_eventi = $total_partecipazioni = 0;
$usuarios_recientes = $eventos_proximos = [];

try {
    // Totale utenti
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM Utente");
    $total_usuarios = (int)$stmt->fetch()['count'];

    // Totale università
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM Universita");
    $total_universitas = (int)$stmt->fetch()['count'];

    // Totale eventi
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM Evento");
    $total_eventi = (int)$stmt->fetch()['count'];

    // Totale partecipazioni  (¡tabla correcta!)
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM Partecipazione");
    $total_partecipazioni = (int)$stmt->fetch()['count'];

    // Utenti recenti
    $stmt = $pdo->query("
        SELECT id, username, email, created_at
        FROM Utente
        ORDER BY id DESC
        LIMIT 5
    ");
    $usuarios_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Eventi prossimi (soporta distintos nombres de columna con COALESCE)
    $stmt = $pdo->query("
        SELECT e.id,
               COALESCE(e.titolo, e.nome, CONCAT('Evento #', e.id)) AS titolo,
               COALESCE(e.data_evento, e.data, e.dataora)           AS data_evento,
               u.nome                                               AS universita_nome
        FROM Evento e
        LEFT JOIN Universita u ON u.id = e.universita_id
        WHERE COALESCE(e.data_evento, e.data, e.dataora) >= CURRENT_DATE
        ORDER BY COALESCE(e.data_evento, e.data, e.dataora) ASC
        LIMIT 5
    ");
    $eventos_proximos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Error en dashboard: '.$e->getMessage());
    // valores por defecto SOLO si no existen (no pisamos los ya calculados)
    if (!isset($total_usuarios))       $total_usuarios = 0;
    if (!isset($total_universitas))    $total_universitas = 0;
    if (!isset($total_eventi))         $total_eventi = 0;
    if (!isset($total_partecipazioni)) $total_partecipazioni = 0;
    if (!isset($usuarios_recientes))   $usuarios_recientes = [];
    if (!isset($eventos_proximos))     $eventos_proximos = [];
}
?>

<!DOCTYPE php>
<php lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SonoErasmus+</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/unified-styles.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>🛠️ Dashboard Amministratore</h1>
            <p>Benvenuto, <?= h($_SESSION['utente_nome']) ?>! Da qui puoi gestire tutti i contenuti del sito.</p>
        </header>
        
        <nav class="admin-nav">
            <a href="users.php">👥 Gestisci Utenti</a>
            <a href="ges-uni.php">🎓 Gestisci Università</a>
            <a href="ges-ev.php">📅 Gestisci Eventi</a>
            <a href="logout.php">🚪 Logout</a>
            <a href="../index.php">🏠 Torna al Sito</a>
        </nav>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_usuarios ?></div>
                <div class="stat-label">Utenti Totali</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_universitas ?></div>
                <div class="stat-label">Università</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_eventi ?></div>
                <div class="stat-label">Eventi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_partecipazioni ?></div>
                <div class="stat-label">Partecipazioni</div>
            </div>
        </div>
        
        <div class="content-grid">
            <div class="content-card">
                <h3>👥 Utenti Recenti</h3>
                <?php if (empty($usuarios_recientes)): ?>
                    <p>Nessun utente registrato.</p>
                <?php else: ?>
                    <ul class="recent-list">
                        <?php foreach ($usuarios_recientes as $user): ?>
                            <li class="recent-item">
                                <div class="user-info">
                                    <div class="user-name"><?= h($user['nome'] . ' ' . $user['cognome']) ?></div>
                                    <div class="user-email">@<?= h($user['username']) ?> • <?= h($user['email']) ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <div class="content-card">
                <h3>📅 Eventi Prossimi</h3>
                <?php if (empty($eventos_proximos)): ?>
                    <p>Nessun evento programmato.</p>
                <?php else: ?>
                    <ul class="recent-list">
                        <?php foreach ($eventos_proximos as $evento): ?>
                            <li class="recent-item">
                                <div class="user-info">
                                    <div class="user-name"><?= h($evento['titolo']) ?></div>
                                    <div class="user-email">
                                        📍 <?= h($evento['luogo']) ?>
                                        <?php if ($evento['universita']): ?>
                                            • 🎓 <?= h($evento['universita']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="event-date">
                                    <?= date('d/m/Y', strtotime($evento['data_evento'])) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</php>
