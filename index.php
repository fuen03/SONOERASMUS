<?php
session_start();
require_once 'app/config.php';

// Obtener datos del usuario si está logueado
$currentUser = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SonoErasmus+ — Pagina Iniziale</title>
  <meta name="description" content="SonoErasmus+ — Esperienze, università e attività per studenti Erasmus.">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <!-- HEADER -->
  <header class="site-header" role="banner">
    <div class="header-inner">
      <!-- Botón menú (móvil/tablet vertical) -->
      <button id="menuToggle" class="menu-toggle" aria-controls="mobileMenu" aria-expanded="false" aria-label="Apri il menu">
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
        <span class="menu-toggle-bar"></span>
      </button>

      <!-- Logo -->
      <a class="brand brand-link-erasmus" href="#home" aria-label="Vai alla pagina iniziale">
        <svg class="brand-logo" width="164" height="28" viewBox="0 0 164 28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
          <text x="0" y="22" class="brand-logo-text brand-logo-erasmus-color">SonoErasmus</text>
        </svg>
        <span class="visually-hidden">SonoErasmus+</span>
      </a>

      <!-- Nav escritorio -->
      <nav class="desktop-nav" aria-label="Navigazione principale">
        <a href="index.php"        class="<?= basename($_SERVER['PHP_SELF'])==='index.php'        ? 'is-selected' : '' ?>">Pagina Iniziale</a>
        <a href="app/universita.php"   class="<?= basename($_SERVER['PHP_SELF'])==='app/universita.php'   ? 'is-selected' : '' ?>">Università</a>
        <a href="app/esperienze.php"   class="<?= basename($_SERVER['PHP_SELF'])==='app/esperienze.php'   ? 'is-selected' : '' ?>">Esperienza Erasmus</a>
        <a href="app/contatti.php"     class="<?= basename($_SERVER['PHP_SELF'])==='app/contatti.php'     ? 'is-selected' : '' ?>">Contatti e link</a>

        <?php if (isAdmin()): ?>
          <a href="app/dashboard.php"  class="<?= basename($_SERVER['PHP_SELF'])==='../dashboard.php'    ? 'is-selected' : '' ?>">Dashboard</a>
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
              <a href="app/profilo.php">Il mio profilo</a>
              <a href="app/eventi.php">I miei eventi</a>
              <a href="app/logout.php">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a class="btn-login" href="app/login.php" aria-label="Accedi">Accedi</a>
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
          <a class="card-link" href="app/profilo.php"><span>Il mio profilo</span><i class="card-chevron" aria-hidden="true"></i></a>
          <a class="card-link" href="app/eventi.php"><span>I miei eventi</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php endif; ?>
        
        <a class="card-link" href="app/universita.php"><span>Università</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="app/esperienze.php"><span>Esperienza Erasmus</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="app/cosafare.php"><span>Cosa Fare</span><i class="card-chevron" aria-hidden="true"></i></a>
        <a class="card-link" href="app/contatti.php"><span>Contatti e link</span><i class="card-chevron" aria-hidden="true"></i></a>
         <?php if (isAdmin()): ?>
           <a class="card-link" href="app/dashboard.php"><span>Dashboard</span><i class="card-chevron" aria-hidden="true"></i></a>
         <?php endif; ?>
        
        <?php if ($currentUser): ?>
          <a class="card-link logout-link" href="app/logout.php"><span>Logout</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php else: ?>
          <a class="card-link" href="login.php"><span>Accedi</span><i class="card-chevron" aria-hidden="true"></i></a>
        <?php endif; ?>
      </nav>
    </div>
  </aside>

  <!-- CONTENIDO -->
  <main id="home" role="main">
    
    <!-- Mensaje de bienvenida para usuarios logueados -->
    <?php if ($currentUser): ?>
      <div class="welcome-banner">
        <p>Benvenuto, <?= h($currentUser['nome']) ?>! Esplora le università e partecipa agli eventi.</p>
      </div>
    <?php endif; ?>

    <!-- Mensaje de éxito de login -->
    <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
      <div class="success-banner">
        <p>Login effettuato con successo!</p>
      </div>
    <?php endif; ?>
    
    <section class="hero">
      <!-- HERO SLIDER -->
      <section class="hero-slider" aria-label="Galleria immagini principali">
        <button class="hero-nav prev" aria-label="Immagine precedente" tabindex="0">‹</button>

        <div class="hero-viewport">
          <div class="hero-track">
             <img src="assets/img/image1.png" alt="Erasmus a Padova">
            <img src="assets/img/image2.jpg" alt="Studente in Italia">
            <img src="assets/img/image3.jpg" alt="Erasmus a Roma">
            <img src="assets/img/benvenuto.png" alt="Image Città di Padova">
          </div>
        </div>

        <button class="hero-nav next" aria-label="Immagine successiva" tabindex="0">›</button>
      </section>

      <h1 class="hero-title">Benvenuto a <span>SonoErasmus+</span></h1>
      <p class="hero-subtitle">
        La piattaforma dove gli studenti internazionali condividono <strong>esperienze reali</strong>,
        scoprono <strong>università e città italiane</strong> e trovano <strong>eventi e consigli pratici</strong>
        per vivere al meglio l'Erasmus a Padova e in tutta Italia.
      </p>
    </section>

    <!-- BANDA ROJA UNIVERSITÀ -->
    <section class="logo-rail" aria-label="Università in evidenza">
      <div class="rail-mask">
        <div class="rail-track" id="railTrack">
          <figure class="rail-item">
            <a href="app/universita_dettaglio.php?id=1">
              <div class="logo-placeholder" role="img" aria-label="Università di Padova, logo"></div>
              <figcaption>Università di Padova</figcaption>
            </a>
          </figure>

          <figure class="rail-item">
            <a href="app/universita_dettaglio.php?id=2">
              <div class="logo-placeholder" role="img" aria-label="Universitat de Barcelona, logo"></div>
              <figcaption>Universitat de Barcelona</figcaption>
            </a>
          </figure>

          <figure class="rail-item">
            <a href="app/universita_dettaglio.php?id=3">
              <div class="logo-placeholder" role="img" aria-label="UUniversità di Bologna, logo"></div>
              <figcaption>Università di Bologna</figcaption>
            </a>
          </figure>

          <figure class="rail-item">
            <a href="app/universita_dettaglio.php?id=4">
              <div class="logo-placeholder" role="img" aria-label="Sapienza – Università di Roma, logo"></div>
              <figcaption>Sapienza – Università di Roma</figcaption>
            </a>
          </figure>
        </div>
      </div>
    </section>

    <section id="universita" class="section">
      <section class="universita-intro">
        <div class="universita-box">
          <figure class="universita-img">
            <img src="assets/img/universita.png" alt="Università in il mondo" loading="lazy">
          </figure>
          <div class="universita-content">
            <h2>Università in Italia</h2>
            <p>
              Scopri le <strong>migliori università italiane</strong>, le loro città, 
              la vita accademica e le esperienze Erasmus raccontate dagli studenti.
            </p>
            <a href="app/universita.php" class="btn-red">Scopri di più</a>
          </div>
        </div>
      </section>
    </section>
    
    <section id="esperienza" class="section">
      <section class="esperienza-intro">
        <div class="esperienza-box">
          <figure class="esperienza-img">
            <img src="assets/img/esperenza.png" alt="Esperienza Erasmus in Italia" loading="lazy">
          </figure>
          <div class="esperienza-content">
            <h2>Esperienza Erasmus</h2>
            <p>
              Leggi le <strong>storie reali degli studenti Erasmus</strong>, scopri come hanno vissuto 
              la loro avventura in Italia e lasciati ispirare per la tua esperienza.
            </p>
            <a href="app/esperienze.php" class="btn-red">Scopri di più</a>
          </div>
        </div>
      </section>
    </section>
    
    <section id="cosafare" class="section">
      <section class="cosafare-intro">
        <div class="cosafare-box">
          <div class="cosafare-gallery">
            <figure class="cosafare-img wide">
              <img src="assets/img/cosafare.png" alt="Attività per studenti Erasmus" loading="lazy">
            </figure>
            <figure class="cosafare-img square">
              <img src="assets/img/calendar.jpg" alt="Calendario Erasmus" loading="lazy">
            </figure>
          </div>
          <div class="cosafare-content">
            <h2>Cosa Fare</h2>
            <p>
              Scopri <strong>eventi, attività e consigli pratici</strong> per vivere al meglio il tuo Erasmus in Italia: 
              dal divertimento alla cultura, dalla vita sociale alle escursioni.
            </p>
            <a href="cosafare.php" class="btn-red">Scopri di più</a>
          </div>
        </div>
      </section>
    </section>
    
    <section id="contatti" class="section">
      <section class="contatti-intro">
        <div class="contatti-box">
          <div class="contatti-content">
            <h2>Contatti e link</h2>
            <p>
              Qui trovi tutte le <strong>informazioni utili per metterti in contatto</strong> con noi 
              e i link alle principali risorse per studenti Erasmus in Italia.
            </p>
            <a href="contatti.php" class="btn-red">Scopri di più</a>
          </div>
          <figure class="contatti-img">
            <img src="assets/img/contatti.png" alt="Numero di telefono Erasmus" loading="lazy">
          </figure>
        </div>
      </section>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="site-footer" role="contentinfo">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="footer-logo" aria-hidden="true">SE+</div>
        <p class="footer-desc">
          SonoErasmus+ aiuta gli studenti a orientarsi tra università, città, eventi e vita quotidiana in Italia.
        </p>
      </div>

      <nav class="footer-links" aria-label="Collegamenti">
        <a href="#home">Home</a>
        <a href="app/universita.php">Università</a>
        <a href="app/esperienze.php">Esperienze</a>
        <a href="app/cosafare.php">Cosa Fare</a>
        <a href="app/contatti.php">Contatti</a>
      </nav>

      <div class="footer-contact">
        <h3 class="footer-title">Contatti</h3>
        <p><a href="mailto:info@sonoerasmus.it">info@sonoerasmus.it</a></p>
        <p><a href="tel:+39000000000">+39 000 000 000</a></p>
        <p>Via Università 1, 35100 Padova, Italia</p>

        <div class="social-row" aria-label="Social">
          <a class="social" href="#" aria-label="Instagram">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 5a5 5 0 1 0 .001 10.001A5 5 0 0 0 12 7zm0 2.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zm6-2.75a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5z"/></svg>
          </a>
          <a class="social" href="#" aria-label="Facebook">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M22 12a10 10 0 1 0-11.5 9.9v-7H7.9V12h2.6V9.7c0-2.6 1.5-4 3.8-4 1.1 0 2.3.2 2.3.2v2.6H15c-1.3 0-1.7.8-1.7 1.6V12h2.9l-.5 2.9h-2.4v7A10 10 0 0 0 22 12z"/></svg>
          </a>
          <a class="social" href="#" aria-label="X/Twitter">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M20.9 3H17l-4 5.3L9.2 3H3l6.8 9.6L3.3 21h3.8l4.5-6 3.2 6h6.2l-7-10.2L20.9 3z"/></svg>
          </a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <span id="year"></span> SonoErasmus+ — Tutti i diritti riservati</p>
    </div>
  </footer>

  <script src="assets/js/main.js" defer></script>
  <script src="assets/js/user-system.js" defer></script>
</body>
</html>
