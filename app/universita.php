<?php
session_start();
require_once 'config.php';

// Obtener datos del usuario si está logueado
$currentUser = getCurrentUser($pdo);

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, nome AS name, citta AS city, nome AS cover_image, 'Università storica italiana' AS short_desc FROM Universita";
$params = [];

if ($q !== '') {
    $sql .= " WHERE nome ILIKE :search OR citta ILIKE :search";
    $params[':search'] = "%{$q}%";
}
$sql .= " ORDER BY nome ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error en consulta universidades: " . $e->getMessage());
    $rows = [];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Università — SonoErasmus+</title>
  <link rel="stylesheet" href="../assets/css/universita.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="site-header" role="banner">
    <div class="header-inner">
      <!-- Botón menú (móvil/tablet vertical) -->
      <button id="menuToggle" class="menu-toggle" aria-controls="mobileMenu" aria-expanded="false" aria-label="Apri il menu">
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
      </button>

      <!-- Logo -->
      <a class="brand" href="../index.php" aria-label="Vai alla pagina iniziale">
        <svg class="brand-logo" width="164" height="28" viewBox="0 0 164 28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
          <text x="0" y="22" font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif" font-size="24" font-weight="900" fill="#c62828">Sono</text>
          <text x="60" y="22" font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif" font-size="24" font-weight="900" fill="#8e0000">Erasmus+</text>
        </svg>
        <span class="visually-hidden">SonoErasmus+</span>
      </a>

      <!-- Nav escritorio -->
      <nav class="desktop-nav" aria-label="Navigazione principale">
        <a href="../index.php"        class="<?= basename($_SERVER['PHP_SELF'])==='index.php'        ? 'is-selected' : '' ?>">Pagina Iniziale</a>
        <a href="universita.php"   class="<?= basename($_SERVER['PHP_SELF'])==='universita.php'   ? 'is-selected' : '' ?>">Università</a>
        <a href="esperienze.php"   class="<?= basename($_SERVER['PHP_SELF'])==='esperienze.php'   ? 'is-selected' : '' ?>">Esperienza Erasmus</a>
        <a href="../contatti.html"     class="<?= basename($_SERVER['PHP_SELF'])==='../contatti.html'     ? 'is-selected' : '' ?>">Contatti e link</a>

        <?php if (isAdmin()): ?>
          <a href="dashboard.php"  class="<?= basename($_SERVER['PHP_SELF'])==='dashboard.php'    ? 'is-selected' : '' ?>">Dashboard</a>
        <?php endif; ?>
      </nav>


      <!-- Sistema de usuario -->
      <div class="auth-actions">
        <?php if ($currentUser): ?>
          <div class="user-menu">
            <?php if (!empty($currentUser['foto'])): ?>
              <img src="<?= h($currentUser['foto']) ?>" alt="Foto profilo" class="user-avatar">
            <?php endif; ?>
            <span class="user-name">Ciao, <?= h($currentUser['nome']) ?>!</span>
            <div class="user-dropdown">
              <a href="profilo.php">Il mio profilo</a>
              <a href="eventi.php">I miei eventi</a>
              <a href="logout.php">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a class="btn-login" href="../login.php" aria-label="Accedi">Accedi</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- MENÚ MÓVIL -->
  <aside id="mobileMenu" class="mobile-menu" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="mobile-menu-inner">
      <button class="close-menu" id="closeMenu" aria-label="Chiudi il menu">×</button>
      
      <?php if ($currentUser): ?>
        <div class="mobile-user-info">
          <?php if (!empty($currentUser['foto'])): ?>
            <img src="<?= h($currentUser['foto']) ?>" alt="Foto profilo" class="mobile-user-avatar">
          <?php endif; ?>
          <span>Ciao, <?= h($currentUser['nome']) ?>!</span>
        </div>
      <?php endif; ?>
      
      <nav class="mobile-cards" aria-label="Menu principale (mobile)">
        <?php if ($currentUser): ?>
          <a class="card-link" href="profilo.php"><span>Il mio profilo</span><i class="card-chevron" aria-hidden="true"></i></a>
          <a class="card-link" href="eventi.php"><span>I miei eventi</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php endif; ?>
        
        <a class="card-link" href="universita.php"><span>Università</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="esperienze.php"><span>Esperienza Erasmus</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="../contatti.html"><span>Contatti e link</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php if (isAdmin()): ?>
           <a class="card-link" href="dashboard.php"><span>Dashboard</span><i class="card-chevron" aria-hidden="true"></i></a>
         <?php endif; ?>
        
        <?php if ($currentUser): ?>
          <a class="card-link logout-link" href="logout.php"><span>Logout</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php else: ?>
          <a class="card-link" href="login.php"><span>Accedi</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php endif; ?>
      </nav>
    </div>
  </aside>

<main class="section">
  <nav class="breadcrumb" aria-label="Percorso di navigazione">
    <ol>
      <li><a href="../index.php">Home</a></li>
      <li><span aria-current="page">Università</span></li>
    </ol>
  </nav>
  
  <h1>Università</h1>

  <form method="get" class="cf-search" role="search" aria-label="Cerca università">
    <input class="cf-input" type="search" name="q" value="<?=h($q)?>" placeholder="Cerca per nome o città…">
    <button class="cf-btn" type="submit">Cerca</button>
  </form>

  <?php if (!$rows): ?>
    <p>Nessun risultato.</p>
  <?php else: ?>
  <div class="cf-grid">
    <?php foreach ($rows as $u): ?>
      <article class="cf-card">
        <a class="cf-card-link" href="universita_dettaglio.php?id=<?=$u['id']?>">
          <div class="cf-card-media" style="background: linear-gradient(45deg, #c62828, #8e0000); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
            <?= strtoupper(substr($u['name'], 0, 1)) ?>
          </div>
          <div class="cf-card-body">
            <h3 class="cf-card-title"><?=h($u['name'])?></h3>
            <div class="cf-card-meta"><?=h($u['city'])?></div>
            <p class="cf-card-text"><?=h($u['short_desc'])?></p>
            <span class="cf-card-cta">Scopri di più</span>
          </div>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<footer class="site-footer" role="contentinfo">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="footer-logo" aria-hidden="true">SE+</div>
        <p class="footer-desc">
          SonoErasmus+ aiuta gli studenti a orientarsi tra università, città, eventi e vita quotidiana in Italia.
        </p>
      </div>

      <nav class="footer-links" aria-label="Collegamenti">
        <a href="../index.php">Home</a>
        <a href="universita.php">Università</a>
        <a href="esperienze.php">Esperienze</a>
        <a href="../contatti.html">Contatti</a>
      </nav>

      <div class="footer-contact">
        <h3 class="footer-title">Contatti</h3>
        <p><a href="mailto:info@sonoerasmus.it">info@sonoerasmus.it</a></p>
        <p><a href="tel:+39000000000">+39 000 000 000</a></p>
        <p>Via Università 1, 35100 Padova, Italia</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <span id="year"></span> SonoErasmus+ — Tutti i diritti riservati</p>
    </div>
  </footer>

<script src="../assets/js/main.js" defer></script>
<script src="../assets/js/user-system.js" defer></script>
<script defer src="../assets/js/user-menu.js"></script>

</body>
</html>

