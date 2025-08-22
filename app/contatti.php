<?php
// contatti.php
session_start();
require_once __DIR__ . '/config.php'; // debe exponer $pdo (si lo necesitas), isLoggedIn(), isAdmin()

// helper escape por si no lo tienes en config.php
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Contatti e link — SonoErasmus+</title>

    <link rel="stylesheet" href="../assets/css/contatti.css" />
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>
  <!-- ===== HEADER ===== -->
  <header class="site-header" id="siteHeader">
    <div class="header-inner">
      <button class="menu-toggle" id="openMenu" aria-label="Apri menu">
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
      </button>

      <!-- Marca (usa el texto habitual; si prefieres el SVG, reemplázalo) -->
      <a class="brand brand-link-erasmus" href="#home" aria-label="Vai alla pagina iniziale">
        <svg class="brand-logo" width="164" height="28" viewBox="0 0 164 28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
          <text x="0" y="22" class="brand-logo-text brand-logo-erasmus-color">SonoErasmus</text>
        </svg>
        <span class="visually-hidden">SonoErasmus+</span>
      </a>

      <nav class="desktop-nav" aria-label="Navigazione principale">
        <a href="../index.php">Pagina Iniziale</a>
        <a href="universita.php">Università</a>
        <a href="esperienze.php">Esperienza Erasmus</a>
        <a href="contatti.php" class="is-selected">Contatti e link</a>
        <?php if (isAdmin()): ?>
          <a href="dashboard.php">Dashboard</a>
        <?php endif; ?>
      </nav>

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

  <!-- ===== MENÚ MÓVIL ===== -->
  <aside class="mobile-menu" id="mobileMenu" aria-hidden="true">
    <div class="mobile-menu-inner">
      <button class="close-menu" id="closeMenu" aria-label="Chiudi menu">×</button>
      <nav class="mobile-cards" aria-label="Menu mobile">
        <a class="card-link" href="../index.php"><span>Pagina Iniziale</span><span class="card-chevron"></span></a>
        <a class="card-link" href="universita.php"><span>Università</span><span class="card-chevron"></span></a>
        <a class="card-link" href="esperienze.php"><span>Esperienza Erasmus</span><span class="card-chevron"></span></a>
        <a class="card-link is-selected" href="contatti.php" aria-current="page"><span>Contatti e link</span><span class="card-chevron"></span></a>
        <?php if (isAdmin()): ?>
          <a class="card-link" href="dashboard.php"><span>Dashboard</span><span class="card-chevron"></span></a>
        <?php endif; ?>
      </nav>
    </div>
  </aside>

  <!-- ===== BREADCRUMB ===== -->
  <nav class="breadcrumb" aria-label="breadcrumb">
    <ol>
      <li><a href="../index.php">Home</a></li>
      <li aria-current="page">Contatti e link</li>
    </ol>
  </nav>

  <!-- ===== HERO ===== -->
  <section class="section contact-hero">
    <h1 class="hero-title">Contatti e link utili</h1>
  </section>

  <!-- ===== UNIVERSITÀ (SOLO VISITA) ===== -->
  <section class="section" aria-labelledby="titUni">
    <h2 id="titUni" class="group-title">Università</h2>

    <div class="cards-grid">
      <!-- 1-3 ya estaban -->
      <article class="card" data-text="università di padova padova unipd veneto">
        <h3 class="card-title">Università di Padova</h3>
        <p class="card-meta">International Desk</p>
        <a class="card-link" href="https://www.unipd.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di firenze firenze unifi toscana">
        <h3 class="card-title">Università di Firenze</h3>
        <p class="card-meta">Ufficio Relazioni Internazionali</p>
        <a class="card-link" href="https://www.unifi.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="sapienza università di roma roma lazio uniroma1">
        <h3 class="card-title">Sapienza – Università di Roma</h3>
        <p class="card-meta">International Office</p>
        <a class="card-link" href="https://www.uniroma1.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <!-- +15 nuevas (solo visita: link ufficiale) -->
      <article class="card" data-text="università di bologna alma mater bologna emilia-romagna unibo">
        <h3 class="card-title">Alma Mater Studiorum — Università di Bologna</h3>
        <p class="card-meta">Welcome/International</p>
        <a class="card-link" href="https://www.unibo.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di milano statale milano lombardia unimi">
        <h3 class="card-title">Università degli Studi di Milano</h3>
        <p class="card-meta">International Students</p>
        <a class="card-link" href="https://www.unimi.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="politecnico di milano polimi milano ingegneria">
        <h3 class="card-title">Politecnico di Milano</h3>
        <p class="card-meta">International Admissions</p>
        <a class="card-link" href="https://www.polimi.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di torino torino piemonte unito">
        <h3 class="card-title">Università degli Studi di Torino</h3>
        <p class="card-meta">International Mobility</p>
        <a class="card-link" href="https://www.unito.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="politecnico di torino politecnico torino piemonte polito">
        <h3 class="card-title">Politecnico di Torino</h3>
        <p class="card-meta">Incoming Students</p>
        <a class="card-link" href="https://www.polito.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di pisa pisa toscana unipi">
        <h3 class="card-title">Università di Pisa</h3>
        <p class="card-meta">International</p>
        <a class="card-link" href="https://www.unipi.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="scuola normale superiore pisa sns">
        <h3 class="card-title">Scuola Normale Superiore</h3>
        <p class="card-meta">Welcome</p>
        <a class="card-link" href="https://www.sns.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di napoli federico ii napoli campania unina">
        <h3 class="card-title">Università degli Studi di Napoli Federico II</h3>
        <p class="card-meta">International Relations</p>
        <a class="card-link" href="https://www.unina.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di bari aldo moro bari puglia uniba">
        <h3 class="card-title">Università degli Studi di Bari “Aldo Moro”</h3>
        <p class="card-meta">International Office</p>
        <a class="card-link" href="https://www.uniba.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di trento trento trentino unitn">
        <h3 class="card-title">Università degli Studi di Trento</h3>
        <p class="card-meta">International</p>
        <a class="card-link" href="https://www.unitn.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di trieste trieste friuli unts">
        <h3 class="card-title">Università degli Studi di Trieste</h3>
        <p class="card-meta">Incoming</p>
        <a class="card-link" href="https://www.units.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di genova genova liguria unige">
        <h3 class="card-title">Università degli Studi di Genova</h3>
        <p class="card-meta">International Students</p>
        <a class="card-link" href="https://unige.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di verona verona veneto univr">
        <h3 class="card-title">Università degli Studi di Verona</h3>
        <p class="card-meta">International</p>
        <a class="card-link" href="https://www.univr.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di pavia pavia lombardia unipv">
        <h3 class="card-title">Università degli Studi di Pavia</h3>
        <p class="card-meta">Welcome/International</p>
        <a class="card-link" href="https://web.unipv.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di siena siena toscana unisi">
        <h3 class="card-title">Università degli Studi di Siena</h3>
        <p class="card-meta">International</p>
        <a class="card-link" href="https://www.unisi.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di parma parma emilia unipr">
        <h3 class="card-title">Università degli Studi di Parma</h3>
        <p class="card-meta">International</p>
        <a class="card-link" href="https://www.unipr.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di catania catania sicilia unict">
        <h3 class="card-title">Università degli Studi di Catania</h3>
        <p class="card-meta">International</p>
        <a class="card-link" href="https://www.unict.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>

      <article class="card" data-text="università di palermo palermo sicilia unipa">
        <h3 class="card-title">Università degli Studi di Palermo</h3>
        <p class="card-meta">International</p>
        <a class="card-link" href="https://www.unipa.it/" target="_blank" rel="noopener">Sito ufficiale</a>
      </article>
    </div>
  </section>

  <!-- ===== ESN / ASSOCIAZIONI ===== -->
  <section class="section" aria-labelledby="titAss">
    <h2 id="titAss" class="group-title">ESN / Associazioni</h2>
    <div class="cards-grid">
      <article class="card" data-text="esn padova instagram esn padova">
        <h3 class="card-title">ESN Padova</h3>
        <div class="social-row">
          <a class="social" href="https://instagram.com/esnpadova" target="_blank" rel="noopener" aria-label="Instagram ESN Padova">IG</a>
          <a class="card-link" href="https://esnpadova.it" target="_blank" rel="noopener">Sito ufficiale</a>
        </div>
      </article>

      <article class="card" data-text="esn firenze facebook esn firenze">
        <h3 class="card-title">ESN Firenze</h3>
        <div class="social-row">
          <a class="social" href="#" target="_blank" rel="noopener" aria-label="Facebook ESN Firenze">FB</a>
          <a class="card-link" href="#" target="_blank" rel="noopener">Sito ufficiale</a>
        </div>
      </article>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer class="site-footer">
    <div class="footer-inner">
      <div>
        <div class="footer-logo">SE+</div>
        <p class="footer-desc">SonoErasmus+ aiuta gli studenti a orientarsi tra università, città, eventi e vita quotidiana in Italia.</p>
      </div>
      <nav class="footer-links" aria-label="Mappa">
        <strong class="footer-title">Mappa</strong>
        <a href="../index.php">Home</a>
        <a href="universita.php">Università</a>
        <a href="esperienze.php">Esperienze</a>
        <a aria-current="page" href="contatti.php">Contatti</a>
      </nav>
      <div class="footer-contact">
        <strong class="footer-title">Contatti</strong>
        <p><a href="mailto:info@sonoerasmus.it">info@sonoerasmus.it</a><br/>+39 000 000 000</p>
        <div class="social-row">
          <a class="social" href="#" aria-label="Instagram">IG</a>
          <a class="social" href="#" aria-label="Facebook">FB</a>
          <a class="social" href="#" aria-label="X">X</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">© 2025 SonoErasmus+ — Tutti i diritti riservati</div>
  </footer>

  <script src="assets/js/main.js"></script>
  <script src="assets/js/contatti.js"></script>

  <!-- Menú usuario “pegajoso” (si usas hover con retardo) -->
  <script>
  (function(){
    const menu = document.querySelector('[data-usermenu]');
    if (!menu) return;
    const list = menu.querySelector('.user-dropdown');
    let hideTimer = null;
    function open(){ if (hideTimer) { clearTimeout(hideTimer); hideTimer=null; } list.hidden=false; list.style.display='block'; }
    function scheduleClose(){ if (hideTimer) clearTimeout(hideTimer); hideTimer=setTimeout(()=>{ list.style.display='none'; list.hidden=true; }, 220); }
    menu.addEventListener('mouseenter', open);
    menu.addEventListener('mouseleave', scheduleClose);
    menu.addEventListener('focusin', open);
    menu.addEventListener('focusout', scheduleClose);
  })();
  </script>
</body>
</html>
