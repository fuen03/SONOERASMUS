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

// ---- WHERE dinámico (insensible a mayúsculas) ----
$conditions = [];
$params     = [];

if ($search !== '') {
  // Usamos LOWER(col) LIKE LOWER(:search) para que no dependa de la collation
  $conditions[] = "(LOWER(u.nome) LIKE LOWER(:search)
                 OR LOWER(u.cognome) LIKE LOWER(:search)
                 OR LOWER(uni.nome) LIKE LOWER(:search)
                 OR LOWER(uni.citta) LIKE LOWER(:search))";
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
$experienciasQuery = "
  SELECT
    ee.id, ee.periodo, ee.titolo, ee.descrizione, ee.image,
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
  <title>Esperienze Erasmus — SonoErasmus+</title>

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

      <a class="brand" href="index.php" aria-label="SonoErasmus+ Home">
        <svg class="brand-logo" width="164" height="28" viewBox="0 0 164 28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
          <text x="0" y="22" font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif" font-size="24" font-weight="900" fill="#c62828">Sono</text>
          <text x="60" y="22" font-family="system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif" font-size="24" font-weight="900" fill="#8e0000">Erasmus+</text>
        </svg>
      </a>

      <nav class="desktop-nav" aria-label="Navigazione principale">
        <a href="index.php">Pagina Iniziale</a>
        <a href="universita.php">Università</a>
        <a href="esperienze.php" aria-current="page">Esperienza Erasmus</a>
        <a href="cosafare.php">Cosa Fare</a>
        <a href="contatti.php">Contatti e link</a>
      </nav>

      <div class="auth-actions">
        <a class="btn-login" href="login.php">Accedi</a>
      </div>
    </div>
  </header>

  <!-- ===== MENÚ MÓVIL ===== -->
  <aside class="mobile-menu" id="mobileMenu" aria-hidden="true">
    <div class="mobile-menu-inner">
      <button class="close-menu" id="closeMenu" aria-label="Chiudi menu">×</button>
      <nav class="mobile-cards" aria-label="Menu mobile">
        <a class="card-link" href="index.php"><span>Pagina Iniziale</span><span class="card-chevron"></span></a>
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
      <li><a href="index.php">Home</a></li>
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
        <?php if ($search !== ''): ?>“<?=h($search)?>”<?php endif; ?>
        <?php if ($user_filter > 0): ?> (utente #<?=h($user_filter)?>)<?php endif; ?>
      </p>
      <p><a href="esperienze.php">Cancella filtro</a></p>
    <?php else: ?>
      <p class="exp-lead">Scopri le università visitate dai nostri utenti</p>
    <?php endif; ?>
  </section>

  <!-- ===== “STORIE” (círculos) ===== -->
  <section class="section" aria-labelledby="storieTit">
    <h2 id="storieTit" class="exp-subtitle">Gli utenti più attivi</h2>
    <div class="stories-row" role="list">
      <?php if ($usuariosConExperiencias): ?>
        <?php foreach ($usuariosConExperiencias as $u): ?>
          <a class="story" role="listitem" href="esperienze.php?user_id=<?= (int)$u['id'] ?>">
            <?php if (!empty($u['foto'])): ?>
              <span class="story-avatar" style="background-image:url('<?=h($u['foto'])?>')"></span>
            <?php else: ?>
              <span class="story-avatar story-initials"><?=h(initials($u['nome'] ?? '', $u['cognome'] ?? ''))?></span>
            <?php endif; ?>
            <span class="story-name"><?=h($u['nome'])?></span>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Nessun utente trovato.</p>
      <?php endif; ?>
    </div>
  </section>

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
                   <?php endif; ?>></div>
              <div>
                <div class="exp-user"><?=h(trim($e['utente_nome'].' '.$e['utente_cognome']))?></div>
                <div class="exp-university"><?=h($e['universita_nome'])?> — <?=h($e['universita_citta'])?></div>
              </div>
            </header>

            <?php if (!empty($e['image'])): ?>
              <a class="exp-photo" href="esperienza.php?id=<?= (int)$e['id'] ?>"
                 style="background-image:url('<?=h($e['image'])?>')"
                 aria-label="Apri esperienza"></a>
            <?php else: ?>
              <a class="exp-photo exp-photo--placeholder" href="esperienza.php?id=<?= (int)$e['id'] ?>"></a>
            <?php endif; ?>

            <?php if (!empty($e['titolo'])): ?>
              <h3 class="exp-card-title"><?=h($e['titolo'])?></h3>
            <?php endif; ?>

            <?php if (!empty($e['descrizione'])): ?>
              <p class="exp-text"><?=h($e['descrizione'])?></p>
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
        <a href="index.php">Home</a>
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
    <div class="footer-bottom">© 2025 SonoErasmus+ — Tutti i diritti riservati</div>
  </footer>

  <!-- JS -->
  <script>
    const openBtn = document.getElementById('openMenu');
    const closeBtn = document.getElementById('closeMenu');
    const mm = document.getElementById('mobileMenu');
    openBtn?.addEventListener('click', ()=>{ mm.classList.add('open'); mm.setAttribute('aria-hidden','false'); });
    closeBtn?.addEventListener('click', ()=>{ mm.classList.remove('open'); mm.setAttribute('aria-hidden','true'); });
    const header = document.getElementById('siteHeader');
    window.addEventListener('scroll', ()=> header.classList.toggle('is-scrolled', window.scrollY>4));
  </script>
</body>
</html>