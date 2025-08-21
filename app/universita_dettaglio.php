<?php
require _DIR_ . '/config.php'; 

if (!function_exists('h')) { function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(404); exit('Università non trovata'); }

$stmt = $mysqli->prepare("SELECT * FROM universities WHERE id=?");

$stmt->bind_param("i", $id);
$stmt->execute();
$uni = $stmt->get_result()->fetch_assoc();
if (!$uni) { http_response_code(404); exit('Università non trovata'); }
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= h($uni['name']) ?> — SonoErasmus+</title>


  <base href="../">

  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

  <!-- ===== HEADER  ===== -->
  <header class="site-header" role="banner" id="siteHeader">
    <div class="header-inner">
      <!-- Botón menú  -->
      <button id="menuToggle" class="menu-toggle" aria-controls="mobileMenu" aria-expanded="false" aria-label="Apri il menu">
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
      </button>

      <!-- Logo -->
      <a class="brand" href="index.html#home" aria-label="Vai alla pagina iniziale">
        <img class="brand-logo" src="assets/img/logo-se.svg" alt="SonoErasmus+">
      </a>

      <!-- Nav escritorio  -->
      <nav class="desktop-nav" aria-label="Menu principale">
        <a href="universita.html">Università</a>
        <a href="esperienze.html">Esperienza Erasmus</a>
        <a href="cosafare.html">Cosa Fare</a>
        <a href="contatti.html">Contatti e link</a>
      </nav>

      <!-- Accedi -->
      <div class="auth-actions">
        <a class="btn-login" href="app/login.php" aria-label="Accedi">Accedi</a>
      </div>
    </div>
  </header>

  <!-- ===== MENÚ MÓVIL  ===== -->
  <aside id="mobileMenu" class="mobile-menu" role="dialog" aria-modal="true" aria-label="Menu" hidden>
    <div class="mobile-menu-inner">
      <button class="close-menu" id="closeMenu" aria-label="Chiudi il menu">×</button>
      <nav class="mobile-cards" aria-label="Menu principale (mobile)">
        <a class="card-link" href="index.html#home"><span>Pagina Iniziale</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link is-selected" href="universita.html"><span>Università</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="esperienze.html"><span>Esperienza Erasmus</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="cosafare.html"><span>Cosa Fare</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="contatti.html"><span>Contatti e link</span><i class="card-chevron" aria-hidden="true"></i></a>
      </nav>
    </div>
  </aside>

  <!-- ===== CONTENIDO ===== -->
  <main role="main">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="briciole di pane">
      <ol>
        <li><a href="index.html#home">Home</a></li>
        <li><a href="universita.html">Università</a></li>
        <li aria-current="page"><?= h($uni['name']) ?></li>
      </ol>
    </nav>


    <section class="section">
      <article class="uni-hero">
        <figure class="uni-cover-wrap">
          <img class="uni-cover" src="<?= h($uni['cover_image']) ?>" alt="<?= h($uni['name']) ?>">
        </figure>
        <div class="uni-meta">
          <h1 class="hero-title" style="text-align:left"><?= h($uni['name']) ?></h1>
          <?php if (!empty($uni['city'])): ?>
            <p class="hero-subtitle" style="margin-top:.25rem"><?= h($uni['city']) ?></p>
          <?php endif; ?>

          <div class="uni-links">
            <?php if (!empty($uni['website'])): ?>
              <a class="btn-red" href="<?= h($uni['website']) ?>" target="_blank" rel="noopener">Sito ufficiale</a>
            <?php endif; ?>
            <?php if (!empty($uni['email'])): ?>
              <a class="btn-outline" href="mailto:<?= h($uni['email']) ?>">E‑mail</a>
            <?php endif; ?>
            <?php if (!empty($uni['phone'])): ?>
              <a class="btn-outline" href="tel:<?= h($uni['phone']) ?>">Telefono</a>
            <?php endif; ?>
          </div>

          <?php if (!empty($uni['short_desc'])): ?>
            <p style="margin-top:.75rem"><?= h($uni['short_desc']) ?></p>
          <?php endif; ?>
        </div>
      </article>
    </section>


    <?php if (!empty($uni['long_desc'])): ?>
      <section class="section">
        <?= $uni['long_desc'] ?>
      </section>
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
        <a href="index.html#home">Home</a>
        <a href="universita.html">Università</a>
        <a href="esperienze.html">Esperienze</a>
        <a href="cosafare.html">Cosa Fare</a>
        <a href="contatti.html">Contatti</a>
      </nav>

      <div class="footer-contact">
        <h3 class="footer-title">Contatti</h3>
        <p><a href="mailto:info@sonoerasmus.it">info@sonoerasmus.it</a></p>
        <p><a href="tel:+39000000000">+39 000 000 000</a></p>
        <p>Via Università 1, 35100 Padova, Italia</p>
      </div>
    </div>
    <div class="footer-bottom">© <?= date('Y') ?> SonoErasmus+ — Tutti i diritti riservati</div>
  </footer>


  <script src="assets/js/main.js" defer></script>


  <style>
    .uni-hero{
      display:grid; grid-template-columns:minmax(0,520px) 1fr; gap:1.5rem; align-items:center;
    }
    .uni-cover-wrap{margin:0}
    .uni-cover{
      width:100%; border-radius:14px; box-shadow:0 8px 18px rgba(0,0,0,.12); object-fit:cover; max-height:360px;
    }
    .uni-meta .btn-red{
      display:inline-block; background:var(--red-main); color:#fff; padding:.6rem 1rem; border-radius:12px; text-decoration:none; font-weight:800; margin:.4rem .5rem .4rem 0;
    }
    .uni-meta .btn-outline{
      display:inline-block; border:2px solid var(--red-main); color:var(--red-main); padding:.5rem .9rem; border-radius:12px; text-decoration:none; font-weight:800; margin:.4rem .5rem .4rem 0;
    }
    @media (max-width:900px){
      .uni-hero{ grid-template-columns:1fr; }
      .uni-cover{ max-height:280px; }
    }
  </style>
</body>
</html>