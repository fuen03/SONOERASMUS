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
      <nav class="desktop-nav" aria-label="Menu principale">
        <a href="universita.php">Università</a>
        <a href="../esperienze.html">Esperienza Erasmus</a>
        <a href="../cosafare.html">Cosa Fare</a>
        <a href="../contatti.html">Contatti e link</a>
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
          <a class="btn-login" href="../login.html" aria-label="Accedi">Accedi</a>
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
        <a class="card-link" href="../esperienze.html"><span>Esperienza Erasmus</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="../cosafare.html"><span>Cosa Fare</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="../contatti.html"><span>Contatti e link</span><i class="card-chevron" aria-hidden="true"></i></a>
        
        <?php if ($currentUser): ?>
          <a class="card-link logout-link" href="logout.php"><span>Logout</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php else: ?>
          <a class="card-link" href="../login.html"><span>Accedi</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php endif; ?>
      </nav>
    </div>
  </aside>

<main class="section">
  <a href="universita.php" class="back-link">← Torna alle università</a>
  
  <article class="uni-hero">
    <div class="uni-cover" style="background: linear-gradient(45deg, #c62828, #8e0000); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: bold; min-height: 300px; border-radius: 14px;">
      <?= strtoupper(substr($uni['nome'], 0, 1)) ?>
    </div>
    <div class="uni-meta">
      <h1><?=h($uni['nome'])?></h1>
      <p class="uni-city"><?=h($uni['citta'])?>, <?=h($uni['nazione'])?></p>
      <p class="uni-links">
        <a class="btn-red" href="#" target="_blank" rel="noopener">Informazioni generali</a>
      </p>
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
      <li>Ufficio relazioni internazionali</li>
      <li>Supporto per alloggi</li>
      <li>Corsi di italiano</li>
      <li>Buddy program con studenti locali</li>
      <li>Eventi di benvenuto e integrazione</li>
    </ul>
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
        <a href="../esperienze.html">Esperienze</a>
        <a href="../cosafare.html">Cosa Fare</a>
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

<style>
.uni-hero{display:grid;grid-template-columns:minmax(0,520px) 1fr;gap:1.5rem;align-items:center; margin: 2rem 0;}
.uni-cover{width:100%;border-radius:14px;box-shadow:0 8px 18px rgba(0,0,0,.12);object-fit:cover;max-height:340px}
.uni-meta h1{margin:.2rem 0 0;color:#c62828}
.uni-city{color:#555;margin:.25rem 0 1rem}
.btn-red{display:inline-block;background:#c62828;color:#fff;padding:.6rem 1rem;border-radius:8px;text-decoration:none;font-weight:800;margin-right:.5rem}
.btn-outline{display:inline-block;border:2px solid #c62828;color:#c62828;padding:.5rem .9rem;border-radius:8px;text-decoration:none;font-weight:700;margin-right:.5rem}
@media (max-width:900px){.uni-hero{grid-template-columns:1fr}.uni-cover{max-height:280px}}
</style>
</body>
</html>
