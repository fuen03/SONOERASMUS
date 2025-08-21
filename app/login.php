<?php
session_start();
require_once 'config.php'; // aquí tienes $pdo + isLoggedIn() + isAdmin()

// Si ya está logueado, vuelve a donde iba o al destino por rol
if (isLoggedIn()) {
  $back = $_GET['redirect'] ?? '';
  if ($back) { header("Location: ".$back); exit; }
  header("Location: " . (isAdmin() ? "dashboard.php" : "../index.php"));
  exit;
}

// ===== GET: pintar formulario sin navegación =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $redirect = $_GET['redirect'] ?? '';
  $error    = $_GET['error'] ?? '';
  ?>
  <!doctype html>
  <html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Accedi — SonoErasmus+</title>
    <link rel="stylesheet" href="../assets/css/login.css">
  </head>
  <body class="login-page">
    <main class="auth-wrap">
      <div class="auth-card">
        <h1>SonoErasmus+</h1>
        <h2>Accedi</h2>

        <?php if ($error): ?>
          <div class="form-errors">
            <p><strong>Errore:</strong> <?= htmlspecialchars($error) ?></p>
          </div>
        <?php endif; ?>

        <form action="login.php<?= $redirect ? ('?redirect='.urlencode($redirect)) : '' ?>" method="post" novalidate>
          <div class="form-field">
            <label for="u">username / e-mail:</label>
            <input id="u" name="username_or_email" type="text" required>
          </div>

          <div class="form-field">
            <label for="p">password:</label>
            <input id="p" name="password" type="password" required>
          </div>

          <?php if ($redirect): ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
          <?php endif; ?>

          <div class="form-actions">
            <button class="btn-primary" type="submit">Accedi</button>
          </div>
        </form>

        <div class="create-account-section">
          <p>Non sei ancora registrato?</p>
          <a class="create-account-button" href="register.php">Crea un account</a>
        </div>
      </div>
    </main>
  </body>
  </html>
  <?php
  exit;
}

// ===== POST: procesar login =====
try {
  if (!isset($_POST['username_or_email']) || !isset($_POST['password'])) {
    header("Location: login.php?error=campi_obbligatori");
    exit;
  }

  $user_or_email = trim($_POST['username_or_email']);
  $pass          = (string)$_POST['password'];

  if ($user_or_email === '' || $pass === '') {
    header("Location: login.php?error=campi_obbligatori");
    exit;
  }

  $sql = "SELECT id, nome, cognome, email, username, password, foto, role
          FROM Utente
          WHERE LOWER(username)=LOWER(:u) OR LOWER(email)=LOWER(:u)
          LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':u' => $user_or_email]);
  $utente = $stmt->fetch();

  if ($utente && password_verify($pass, $utente['password'])) {
    $_SESSION['utente_id']       = $utente['id'];
    $_SESSION['utente_nome']     = $utente['nome'];
    $_SESSION['utente_cognome']  = $utente['cognome'];
    $_SESSION['utente_email']    = $utente['email'];
    $_SESSION['utente']          = $utente['username'];
    $_SESSION['utente_username'] = $utente['username'];
    $_SESSION['utente_foto']     = $utente['foto'];
    $_SESSION['utente_role']     = $utente['role'];

    // Redirección
    $target = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
    if ($target) { header("Location: ".$target); exit; }
    header("Location: " . ($_SESSION['utente_role']==='admin' ? "dashboard.php?login=success"
                                                             : "../index.php?login=success"));
    exit;
  }

  // Fallo de login
  header("Location: login.php?error=utente_o_password_errati");
  exit;

} catch (Throwable $e) {
  error_log("Error en login: ".$e->getMessage());
  header("Location: login.php?error=errore_sistema");
  exit;
}
