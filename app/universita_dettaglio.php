<?php
session_start();
require_once 'config.php';

// Obtener datos del usuario si está logueado
$currentUser = getCurrentUser($pdo);

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { 
    http_response_code(404); 
    exit('Università non trovata'); 
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Universita WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $uni = $stmt->fetch();
    
    if (!$uni) { 
        http_response_code(404); 
        exit('Università non trovata'); 
    }
} catch (PDOException $e) {
    error_log("Error al obtener universidad: " . $e->getMessage());
    http_response_code(500);
    exit('Errore nel caricamento della università');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?=h($uni['nome'])?> — SonoErasmus+</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/unified-styles.css">
  <link rel="stylesheet" href="../assets/css/universita-dettaglio.css">
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
        <a href="../contatti.html"     class="<?= basename($_SERVER['PHP_SELF'])==='contatti.php'     ? 'is-selected' : '' ?>">Contatti e link</a>

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
          <a class="btn-login" href="login.php" aria-label="Accedi">Accedi</a>
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
      <li><a href="universita.php">Università</a></li>
      <li><span aria-current="page"><?= h($uni['nome']) ?></span></li>
    </ol>
  </nav>
  
  <article class="uni-hero">
    <div class="uni-cover" style="background: linear-gradient(45deg, #c62828, #8e0000); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: bold; min-height: 300px; border-radius: 14px;">
      <?= strtoupper(substr($uni['nome'], 0, 1)) ?>
    </div>
    <div class="uni-meta">
      <h1><?=h($uni['nome'])?></h1>
      <p class="uni-city"><?=h($uni['citta'])?>, <?=h($uni['nazione'])?></p>
      <div class="uni-links">
        <a class="btn-red" href="#" target="_blank" rel="noopener">Informazioni generali</a>
      </div>
      <p>Una delle università più prestigiose d'Italia, con una lunga tradizione accademica e numerosi programmi internazionali.</p>
    </div>
  </article>

  <section class="section">
    <h2>Informazioni sull'università</h2>
    <p>
      L'<strong><?=h($uni['nome'])?></strong> si trova a <strong><?=h($uni['citta'])?></strong>, 
      una città universitaria piena di opportunità per studenti internazionali.
    </p>
    <p>
      Gli studenti Erasmus trovano un ambiente accogliente, con numerosi servizi dedicati 
      e una vivace vita studentesca. La città offre un perfetto equilibrio tra tradizione 
      e modernità, con numerosi eventi culturali e sociali durante l'anno accademico.
    </p>
    
    <h3>Servizi per studenti internazionali</h3>
    <ul>
      <li>🏛️ Ufficio relazioni internazionali</li>
      <li>🏠 Supporto per alloggi</li>
      <li>🇮🇹 Corsi di italiano</li>
      <li>👥 Buddy program con studenti locali</li>
      <li>🎉 Eventi di benvenuto e integrazione</li>
      <li>📚 Biblioteche moderne e spazi studio</li>
      <li>🍕 Mense universitarie</li>
      <li>🚲 Servizi di mobilità urbana</li>
    </ul>

    <h3>Perché scegliere <?=h($uni['nome'])?>?</h3>
    <div class="info-boxes">
      <div class="info-box">
        <h4>📍 Posizione strategica</h4>
        <p>Nel cuore di <?=h($uni['citta'])?>, ben collegata con i principali servizi e attrazioni della città.</p>
      </div>
      
      <div class="info-box">
        <h4>🌍 Ambiente internazionale</h4>
        <p>Migliaia di studenti internazionali ogni anno, ambiente multiculturale e accogliente.</p>
      </div>
      
      <div class="info-box">
        <h4>🎓 Eccellenza accademica</h4>
        <p>Riconoscimento internazionale per la qualità dell'insegnamento e della ricerca.</p>
      </div>
    </div>

    <h3>La città di <?=h($uni['citta'])?></h3>
    <p>
      <?=h($uni['citta'])?> è una città perfetta per gli studenti Erasmus, che offre:
    </p>
    <ul>
      <li>🎨 Ricco patrimonio artistico e culturale</li>
      <li>🍝 Gastronomia tradizionale italiana</li>
      <li>🚌 Trasporti pubblici efficienti</li>
      <li>🌳 Parchi e spazi verdi per il relax</li>
      <li>🎪 Eventi e festival durante tutto l'anno</li>
      <li>💡 Vivace vita notturna studentesca</li>
    </ul>

    <div class="cta-section">
      <h3>Vuoi saperne di più?</h3>
      <p>Contatta l'ufficio relazioni internazionali per informazioni su corsi, alloggi e programmi Erasmus.</p>
      <div class="cta-buttons">
        <a href="#" class="btn-red">Contatta l'università</a>
        <a href="../contatti.html" class="btn-outline">I nostri contatti</a>
      </div>
    </div>
  </section>
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

<script>
  // Actualizar el año en el footer
  document.getElementById('year').textContent = new Date().getFullYear();
</script>
</body>
</html>
