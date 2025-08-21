<?php
session_start();
require_once 'config.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Obtener estadísticas
try {
    // Contar usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Utente");
    $total_usuarios = $stmt->fetch()['count'];
    
    // Contar universidades
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Universita");
    $total_universitas = $stmt->fetch()['count'];
    
    // Contar eventos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Evento");
    $total_eventi = $stmt->fetch()['count'];
    
    // Contar participaciones
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Partecipazioni");
    $total_partecipazioni = $stmt->fetch()['count'];
    
    // Obtener usuarios recientes
    $stmt = $pdo->query("SELECT nome, cognome, username, email FROM Utente ORDER BY id DESC LIMIT 5");
    $usuarios_recientes = $stmt->fetchAll();
    
    // Obtener eventos próximos
    $stmt = $pdo->query("SELECT e.titolo, e.data_evento, e.luogo, u.nome as universita 
                         FROM Evento e 
                         LEFT JOIN Universita u ON e.universita_id = u.id 
                         WHERE e.data_evento >= NOW() 
                         ORDER BY e.data_evento ASC LIMIT 5");
    $eventos_proximos = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $total_usuarios = $total_universitas = $total_eventi = $total_partecipazioni = 0;
    $usuarios_recientes = $eventos_proximos = [];
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
            <a href="universitas.php">🎓 Gestisci Università</a>
            <a href="eventi.php">📅 Gestisci Eventi</a>
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