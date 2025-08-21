<?php
session_start();
require_once 'config.php'; // Debe definir $pdo (PDO) y getConfig()

// ---- Utilidades de salida segura ----
if (!function_exists('h')) {
  function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
function initials($name, $surname) {
  $i1 = mb_substr(trim((string)$name), 0, 1, 'UTF-8');
  $i2 = mb_substr(trim((string)$surname), 0, 1, 'UTF-8');
  return mb_strtoupper($i1.$i2, 'UTF-8');
}

// ---- Entrada ----
$search      = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// ---- Resolver búsqueda directa de usuario (username o "Nombre Apellido") ----
// Si el usuario ha escrito algo en la búsqueda, intentamos identificar un usuario único
if ($search !== '' && $user_filter === 0) {
  try {
    // 1) @username o username "limpio"
    if (preg_match('/^@?([a-z0-9_.-]{3,})$/i', $search, $m)) {
      $stmt = $pdo->prepare("
        SELECT id FROM Utente
        WHERE LOWER(username) = LOWER(:u)
        LIMIT 2
      ");
      $stmt->execute([':u' => $m[1]]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($rows) === 1) {
        $user_filter = (int)$rows[0]['id'];
        $search = ''; // anulamos el resto del filtro para que solo aparezca ese perfil
      }
    }

    // 2) "Nombre Apellido" exacto (o nombre + apellido)
    if ($user_filter === 0 && preg_match('/\S+\s+\S+/', $search)) {
      $parts = preg_split('/\s+/', $search, 2);
      $nome = $parts[0] ?? '';
      $cognome = $parts[1] ?? '';
      $stmt = $pdo->prepare("
        SELECT id FROM Utente
        WHERE LOWER(CONCAT(nome,' ',cognome)) = LOWER(:full)
           OR (LOWER(nome) = LOWER(:n) AND LOWER(cognome) = LOWER(:c))
        LIMIT 2
      ");
      $stmt->execute([
        ':full' => $search,
        ':n'    => $nome,
        ':c'    => $cognome
      ]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($rows) === 1) {
        $user_filter = (int)$rows[0]['id'];
        $search = ''; // anulamos el filtro general
      }
    }
  } catch (PDOException $e) {
    // Si falla, seguimos con la búsqueda general sin romper la página
    error_log('[búsqueda usuario] '.$e->getMessage());
  }
}

// ---- WHERE dinámico (insensible a mayúsculas) ----
$conditions = [];
$params     = [];

if ($search !== '') {
  // Usamos LOWER(col) LIKE LOWER(:search) para que no dependa de la collation
  $conditions[] = "(LOWER(u.nome) LIKE LOWER(:search)
                 OR LOWER(u.cognome) LIKE LOWER(:search)
                 OR LOWER(CONCAT(u.nome, ' ', u.cognome)) LIKE LOWER(:search)
                 OR LOWER(uni.nome) LIKE LOWER(:search)
                 OR LOWER(uni.citta) LIKE LOWER(:search)
                 OR LOWER(ee.titolo) LIKE LOWER(:search)
                 OR LOWER(ee.testo) LIKE LOWER(:search))";
  $params[':search'] = '%'.$search.'%';
}
if ($user_filter > 0) {
  $conditions[] = "u.id = :user_id";
  $params[':user_id'] = $user_filter;
}

$whereClause = $conditions ? ('WHERE '.implode(' AND ', $conditions)) : '';

// ---- Config límites ----
$maxUsuarios     = (int) getConfig('max_usuarios_activos', '5');
$maxExperiencias = (int) getConfig('max_experiencias_mostrar', '20');

// ---- Usuarios más activos (para historias) ----
$storyQuery = "
  SELECT u.id, u.nome, u.cognome, u.foto, COUNT(ee.id) AS experiencias_count
  FROM Utente u
  LEFT JOIN EsperienzaErasmus ee ON ee.utente_id = u.id
  GROUP BY u.id, u.nome, u.cognome, u.foto
  HAVING experiencias_count > 0
  ORDER BY experiencias_count DESC
  LIMIT :limit
";
$usuariosConExperiencias = [];
try {
  $stmt = $pdo->prepare($storyQuery);
  $stmt->bindValue(':limit', $maxUsuarios, PDO::PARAM_INT);
  $stmt->execute();
  $usuariosConExperiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log('[storie] '.$e->getMessage());
}

// ---- Experiencias (lista principal) ----
// CORREGIDO: Cambié 'descrizione' por 'testo' que es el campo correcto
$experienciasQuery = "
  SELECT
    ee.id, ee.periodo, ee.titolo, ee.testo, ee.foto,
    u.id AS utente_id, u.nome AS utente_nome, u.cognome AS utente_cognome, u.foto AS utente_foto,
    uni.nome AS universita_nome, uni.citta AS universita_citta
  FROM EsperienzaErasmus ee
  JOIN Utente u   ON u.id   = ee.utente_id
  JOIN Universita uni ON uni.id = ee.universita_id
  $whereClause
  ORDER BY ee.id DESC
  LIMIT :limit
";
$experiencias = [];
try {
  $stmt = $pdo->prepare($experienciasQuery);
  foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
  }
  $stmt->bindValue(':limit', $maxExperiencias, PDO::PARAM_INT);
  $stmt->execute();
  $experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log('[experiencias] '.$e->getMessage());
  $experiencias = [];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Esperienze Erasmus – SonoErasmus+</title>

  <!-- Tu CSS específico primero para permitir overrides por el global si quieres -->
  <link rel="stylesheet" href="../assets/css/esperienze.css" />
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

      <a class="brand" href="../index.php" aria-label="SonoErasmus+ Home">
        <svg class="brand-logo" width="164" height="28" viewBox="0 0 164 28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
          <text x="0" y="22" font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif" font-size="24" font-weight="900" fill="#c62828">Sono</text>
          <text x="60" y="22" font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif" font-size="24" font-weight="900" fill="#8e0000">Erasmus+</text>
        </svg>
      </a>

      <nav class="desktop-nav" aria-label="Navigazione principale">
        <a href="../index.php">Pagina Iniziale</a>
        <a href="universita.php">Università</a>
        <a href="esperienze.php" aria-current="page">Esperienza Erasmus</a>
        <a href="cosafare.php">Cosa Fare</a>
        <a href="contatti.php">Contatti e link</a>
      </nav>

      <div class="auth-actions">
        <?php if (isLoggedIn()): ?>
          <div class="user-menu" data-usermenu>
            <button class="user-trigger user-trigger--text" aria-haspopup="menu" aria-expanded="false">
              Ciao, <?= h($_SESSION['utente_nome'] ?? ($_SESSION['utente_nome'] ?? 'utente')) ?>!
            </button>
            <ul class="user-dropdown" role="menu" hidden>
              <li><a href="profilo.php" role="menuitem">Il mio profilo</a></li>
              <li><a href="partecipa.php" role="menuitem">I miei eventi</a></li>
              <li><a href="logout.php" role="menuitem">Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a class="btn-login"
            href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Accedi</a>
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
        <a class="card-link is-selected" href="esperienze.php"><span>Esperienza Erasmus</span><span class="card-chevron"></span></a>
        <a class="card-link" href="cosafare.php"><span>Cosa Fare</span><span class="card-chevron"></span></a>
        <a class="card-link" href="contatti.php"><span>Contatti e link</span><span class="card-chevron"></span></a>
      </nav>
    </div>
  </aside>

  <!-- ===== BREADCRUMB ===== -->
  <nav class="breadcrumb" aria-label="breadcrumb">
    <ol>
      <li><a href="../index.php">Home</a></li>
      <li aria-current="page">Esperienze Erasmus</li>
    </ol>
  </nav>

  <!-- ===== TÍTULO + BUSCADOR ===== -->
  <section class="section exp-hero">
    <h1 class="exp-title">Scopri le esperienze dei nostri utenti</h1>

    <form class="search-row" action="esperienze.php" method="get" role="search" aria-label="Cerca esperienze">
      <input
        class="search-input"
        type="search"
        name="q"
        placeholder="Cerca per città, università, utente…"
        value="<?=h($search)?>">
      <?php if ($user_filter > 0): ?>
        <input type="hidden" name="user_id" value="<?=$user_filter?>">
      <?php endif; ?>
      <button class="search-btn" type="submit">Cerca</button>
    </form>

    <?php if ($search !== '' || $user_filter > 0): ?>
      <p class="exp-lead">
        Risultati per
        <?php if ($search !== ''): ?>"<?=h($search)?>"<?php endif; ?>
        <?php if ($user_filter > 0): ?> (utente #<?=h($user_filter)?>)<?php endif; ?>
      </p>
      <p><a href="esperienze.php">Cancella filtro</a></p>
    <?php else: ?>
      <p class="exp-lead">Scopri le università visitate dai nostri utenti</p>
    <?php endif; ?>
    
    <!-- BOTÓN SOLO VISIBLE SI ESTÁ LOGUEADO -->
    <?php if (isLoggedIn()): ?>
      <a class="btn-primary exp-cta" href="racconta.php">Racconta la tua esperienza</a>
    <?php else: ?>
      <a class="btn-primary exp-cta" href="login.php?redirect=racconta.php">Accedi per raccontare</a>
    <?php endif; ?>
  </section>

  <!-- ===== USUARIOS MÁS ACTIVOS (HISTORIAS) ===== -->
  <?php if (!empty($usuariosConExperiencias) && ($search === '' && $user_filter === 0)): ?>
  <section class="section" aria-labelledby="storiesTit">
    <h2 id="storiesTit">Utenti più attivi</h2>
    <div class="stories-container">
      <?php foreach ($usuariosConExperiencias as $usuario): ?>
        <a href="esperienze.php?user_id=<?= (int)$usuario['id'] ?>" class="story-item">
          <div class="story-avatar"
               <?php if (!empty($usuario['foto'])): ?>
                 style="background-image:url('<?=h($usuario['foto'])?>')"
               <?php else: ?>
                 data-initials="<?=h(initials($usuario['nome'], $usuario['cognome']))?>"
               <?php endif; ?>></div>
          <span class="story-name"><?=h(trim($usuario['nome'].' '.$usuario['cognome']))?></span>
          <small class="story-count"><?= (int)$usuario['experiencias_count'] ?> esperienze</small>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===== LISTA DE EXPERIENCIAS ===== -->
  <section class="section" aria-labelledby="listaExpTit">
    <h2 id="listaExpTit">Esperienze recenti</h2>

    <?php if (!$experiencias): ?>
      <p>Nessuna esperienza trovata per i criteri selezionati.</p>
    <?php else: ?>
      <div class="exp-grid">
        <?php foreach ($experiencias as $e): ?>
          <article class="exp-card">
            <header class="exp-card-head">
              <div class="exp-avatar"
                   <?php if (!empty($e['utente_foto'])): ?>
                     style="background-image:url('<?=h($e['utente_foto'])?>')"
                   <?php else: ?>
                     data-initials="<?=h(initials($e['utente_nome'], $e['utente_cognome']))?>"
                   <?php endif; ?>></div>
              <div>
                <div class="exp-user"><?=h(trim($e['utente_nome'].' '.$e['utente_cognome']))?></div>
                <div class="exp-university"><?=h($e['universita_nome'])?> – <?=h($e['universita_citta'])?></div>
              </div>
            </header>

            <?php if (!empty($e['foto'])): ?>
              <a class="exp-photo" href="esperienza.php?id=<?= (int)$e['id'] ?>"
                 style="background-image:url('<?=h($e['foto'])?>')"
                 aria-label="Apri esperienza"></a>
            <?php else: ?>
              <a class="exp-photo exp-photo--placeholder" href="esperienza.php?id=<?= (int)$e['id'] ?>"></a>
            <?php endif; ?>

            <?php if (!empty($e['titolo'])): ?>
              <h3 class="exp-card-title"><?=h($e['titolo'])?></h3>
            <?php endif; ?>

            <?php if (!empty($e['testo'])): ?>
              <p class="exp-text"><?=h(mb_substr($e['testo'], 0, 150))?>...</p>
            <?php endif; ?>

            <footer class="exp-meta">
              <?= $e['periodo'] ? h($e['periodo']) : 'Senza periodo' ?>
            </footer>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
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
        <a href="esperienze.php" aria-current="page">Esperienze</a>
        <a href="cosafare.php">Cosa Fare</a>
        <a href="contatti.php">Contatti</a>
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
    <div class="footer-bottom">© 2025 SonoErasmus+ – Tutti i diritti riservati</div>
  </footer>

  <!-- JS -->
  <script>
    (function(){
      const menu = document.querySelector('[data-usermenu]');
      if (!menu) return;
      const list = menu.querySelector('.user-dropdown');
      let hideTimer = null;

      function open() {
        if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
        list.hidden = false;
        list.style.display = 'block';
      }
      function scheduleClose() {
        if (hideTimer) clearTimeout(hideTimer);
        hideTimer = setTimeout(() => {
          list.style.display = 'none';
          list.hidden = true;
        }, 220); // <- retardo de cierre (ajusta 180–300ms a tu gusto)
      }

      // Abrir/cerrar con hover “pegajoso”
      menu.addEventListener('mouseenter', open);
      menu.addEventListener('mouseleave', scheduleClose);

      // Accesible con teclado
      menu.addEventListener('focusin', open);
      menu.addEventListener('focusout', scheduleClose);
    })();
    </script>
</body>
</html>
