<?php
// racconta.php
session_start();
require_once __DIR__ . '/config.php';   // $pdo + helpers (isLoggedIn(), etc.)

// 1) Acceso: solo usuarios logueados
if (!isLoggedIn()) {
  // FIXED: Since racconta.php seems to be in app/ directory, redirect to login.php in same directory
  header('Location: login.php?redirect=' . urlencode('racconta.php'));
  exit;
}

$errors  = [];
$success = false;

// 2) Cargar universidades (para el <select>)
$universita = [];
try {
  $stmt = $pdo->query("SELECT id, nome FROM Universita ORDER BY nome ASC");
  $universita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $errors[] = 'Errore nel caricamento delle università.';
  error_log('Error loading universities: ' . $e->getMessage());
}

// 3) Procesar envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $uid   = (int)($_SESSION['utente_id'] ?? 0);
  $uniId = (int)($_POST['universita'] ?? 0);
  $peri  = trim((string)($_POST['periodo'] ?? ''));
  $tit   = trim((string)($_POST['titolo'] ?? ''));
  $desc  = trim((string)($_POST['descrizione'] ?? ''));

  // Validación mejorada
  if ($uid <= 0) {
    $errors[] = 'Utente non valido. ID utente: ' . $uid;
    error_log('Invalid user ID: ' . $uid);
  } else {
    // Verify user exists in database
    try {
      $stmt = $pdo->prepare("SELECT id FROM Utente WHERE id = :id");
      $stmt->execute([':id' => $uid]);
      if (!$stmt->fetch()) {
        $errors[] = 'L\'utente con ID ' . $uid . ' non esiste nel database.';
        error_log('User ID ' . $uid . ' not found in database');
      }
    } catch (Exception $e) {
      $errors[] = 'Errore nella verifica utente: ' . $e->getMessage();
      error_log('Error verifying user: ' . $e->getMessage());
    }
  }
  
  if ($uniId <= 0) {
    $errors[] = 'Seleziona una università.';
  } else {
    // Verify university exists in database
    try {
      $stmt = $pdo->prepare("SELECT id FROM Universita WHERE id = :id");
      $stmt->execute([':id' => $uniId]);
      if (!$stmt->fetch()) {
        $errors[] = 'L\'università selezionata non esiste.';
        error_log('University ID ' . $uniId . ' not found in database');
      }
    } catch (Exception $e) {
      $errors[] = 'Errore nella verifica università: ' . $e->getMessage();
      error_log('Error verifying university: ' . $e->getMessage());
    }
  }
  
  if (!in_array($peri, ['Fatta','In corso','Futura','Curioso'], true)) {
    $errors[] = 'Seleziona un periodo valido.';
  }
  if ($desc === '') {
    $errors[] = 'Scrivi il tuo racconto.';
  }

  // Subida de imagen (opcional)
  $imagePath = null;
  if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed, true)) {
      $errors[] = 'Formato immagine non valido (usa jpg, png, gif, webp).';
    } else {
      $dir = __DIR__ . '/uploads/esperienze';
      if (!is_dir($dir)) mkdir($dir, 0775, true);
      $name = 'exp_'.$uid.'_'.time().'.'.$ext;
      if (move_uploaded_file($_FILES['image']['tmp_name'], $dir.'/'.$name)) {
        $imagePath = 'uploads/esperienze/'.$name;
      } else {
        $errors[] = 'Errore nel caricamento della foto.';
      }
    }
  }

  // Guardar solo si no hay errores
  if (!$errors) {
    try {
      $sql = "INSERT INTO EsperienzaErasmus
                (utente_id, universita_id, periodo, titolo, testo, foto)
              VALUES
                (:uid, :uni, :per, :tit, :desc, :img)";
      $stmt = $pdo->prepare($sql);
      $result = $stmt->execute([
        ':uid'  => $uid,
        ':uni'  => $uniId,
        ':per'  => $peri,
        ':tit'  => ($tit !== '' ? $tit : null),
        ':desc' => $desc,
        ':img'  => $imagePath
      ]);

      if ($result) {
        $success = true;
        error_log('Experience inserted successfully for user ID: ' . $uid);
        
        // Redirige a tus experiencias
        header('Location: ../esperienze.php?user_id='.$uid);
        exit;
      } else {
        $errors[] = 'Errore di salvataggio. Riprova più tardi.';
      }
    } catch (Throwable $e) {
      $errors[] = 'Errore di salvataggio: ' . $e->getMessage();
      error_log('Error saving experience: ' . $e->getMessage());
    }
  }
}

// Helper h() si no viene de config.php
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Racconta la tua esperienza – SonoErasmus+</title>

  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/esperienze.css" />
</head>
<body>
  <!-- HEADER -->
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
        <a href="../index.php"        class="<?= basename($_SERVER['PHP_SELF'])==='index.php'        ? 'is-selected' : '' ?>">Pagina Iniziale</a>
        <a href="universita.php"   class="<?= basename($_SERVER['PHP_SELF'])==='universita.php'   ? 'is-selected' : '' ?>">Università</a>
        <a href="esperienze.php"   class="<?= basename($_SERVER['PHP_SELF'])==='esperienze.php'   ? 'is-selected' : '' ?>">Esperienza Erasmus</a>
        <a href="../contatti.html"     class="<?= basename($_SERVER['PHP_SELF'])==='contatti.php'     ? 'is-selected' : '' ?>">Contatti e link</a>

        <?php if (isAdmin()): ?>
          <a href="dashboard.php"  class="<?= basename($_SERVER['PHP_SELF'])==='dashboard.php'    ? 'is-selected' : '' ?>">Dashboard</a>
        <?php endif; ?>
      </nav>


      <div class="auth-actions">
        <?php if (isLoggedIn()): ?>
          <div class="user-menu" data-usermenu>
            <button class="user-trigger" aria-haspopup="menu" aria-expanded="false">
              Ciao, <?= h($_SESSION['utente_username'] ?? ($_SESSION['utente_nome'] ?? 'utente')) ?>!
            </button>
            <ul class="user-dropdown" role="menu" hidden>
              <li><a href="profilo.php" role="menuitem">Il mio profilo</a></li>
              <li><a href="partecipa.php" role="menuitem">I miei eventi</a></li>
              <li><a href="logout.php" role="menuitem">Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a class="btn-login" href="app/login.php?redirect=racconta.php">Accedi</a>
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

  <!-- BREADCRUMB -->
  <nav class="breadcrumb" aria-label="breadcrumb">
    <ol>
      <li><a href="../index.php">Home</a></li>
      <li><a href="../esperienze.php">Esperienze Erasmus</a></li>
      <li aria-current="page">Racconta la tua esperienza</li>
    </ol>
  </nav>

  <!-- CONTENIDO -->
  <section class="section racconta-wrap" aria-labelledby="raccontaTit">
    <h1 id="raccontaTit" class="exp-title">Racconta la tua esperienza</h1>
    <p class="exp-subtitle">Condividi consigli, foto e università in cui sei stato.</p>

    <?php if (!isLoggedIn()): ?>
      <!-- Show this message if somehow they reach this page without being logged in -->
      <div class="form-errors" style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px 0; border: 1px solid #f5c6cb; text-align: center;">
        <h3>Accesso Richiesto</h3>
        <p>Devi effettuare l'accesso per raccontare la tua esperienza.</p>
        <a href="app/login.php?redirect=racconta.php" class="btn-primary">Accedi ora</a>
      </div>
    <?php else: ?>

    <div class="form-container">
      <?php if ($success): ?>
        <div class="form-success" style="background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;">
          <p>Esperienza salvata con successo!</p>
        </div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="form-errors" style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;">
          <ul style="margin: 0; padding-left: 20px;">
            <?php foreach($errors as $er): ?>
              <li><?= h($er) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form class="racconta-form" id="raccontaForm" action="" method="post" enctype="multipart/form-data" novalidate>
        <div class="form-row">
          <div class="form-field">
            <label for="nome">Il tuo nome</label>
            <input id="nome" name="nome" type="text"
                   value="<?= h($_SESSION['utente_username'] ?? ($_SESSION['utente_nome'] ?? '')) ?>"
                   readonly>
          </div>

          <div class="form-field">
            <label for="universita">Università</label>
            <select id="universita" name="universita" required>
              <option value="" disabled <?= empty($_POST['universita'])?'selected':''; ?>>Seleziona</option>
              <?php foreach ($universita as $u): ?>
                <option value="<?= (int)$u['id'] ?>"
                  <?= (isset($_POST['universita']) && (int)$_POST['universita']===(int)$u['id'])?'selected':''; ?>>
                  <?= h($u['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-field">
            <label for="periodo">Periodo</label>
            <select id="periodo" name="periodo" required>
              <?php
                $opts = ['Seleziona','Fatta','In corso','Futura','Curioso'];
                $sel  = $_POST['periodo'] ?? '';
                foreach ($opts as $o){
                  if ($o === 'Seleziona') {
                    $s = ($sel==='' || $sel==='Seleziona') ? 'selected' : '';
                    echo "<option value=\"\" disabled $s>Seleziona</option>";
                  } else {
                    $s = ($sel === $o) ? 'selected' : '';
                    echo "<option value=\"".h($o)."\" $s>".h($o)."</option>";
                  }
                }
              ?>
            </select>
          </div>

          <div class="form-field">
            <label for="titolo">Titolo esperienza</label>
            <input id="titolo" name="titolo" type="text" placeholder="Es. Semestre a Padova"
                   value="<?= h($_POST['titolo'] ?? '') ?>">
          </div>
        </div>

        <div class="form-field">
          <label for="descrizione">Racconto</label>
          <textarea id="descrizione" name="descrizione" rows="6"
                    placeholder="Consigli, alloggio, trasporti, vita universitaria…" required><?= h($_POST['descrizione'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-field">
            <label for="image">Foto (opzionale)</label>
            <input id="image" name="image" type="file" accept="image/*">
          </div>
        </div>

        <div class="form-actions">
          <button class="btn-primary" type="submit">Invia esperienza</button>
          <a class="btn-secondary" href="../esperienze.php">Annulla</a>
        </div>
      </form>
    </div>

    <?php endif; ?>
  </section>

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
  <script defer src="../assets/js/user-menu.js"></script>

</body>
</html>
