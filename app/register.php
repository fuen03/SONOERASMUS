<?php
session_start();
require_once 'config.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
  header("Location: " . (isAdmin() ? "admin/dashboard.php" : "../index.php"));
  exit;
}

// ===== GET: mostrar formulario de registro =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $error = $_GET['error'] ?? '';
  $success = $_GET['success'] ?? '';
  ?>
  <!doctype html>
  <html lang="it">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registrati — SonoErasmus+</title>
    <link rel="stylesheet" href="../assets/css/register.css">
  </head>
  <body class="login-page">
    <main class="auth-wrap">
      <div class="auth-card">
        <h1>SonoErasmus+</h1>
        <h2>Registrati</h2>

        <?php if ($error): ?>
          <div class="form-errors">
            <p><strong>Errore:</strong> <?= htmlspecialchars($error) ?></p>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="form-success">
            <p><?= htmlspecialchars($success) ?></p>
          </div>
        <?php endif; ?>

        <form action="register.php" method="post" novalidate>
          <!-- Fila de Nome y Cognome -->
          <div class="form-row">
            <div class="form-field">
              <label for="nome">nome:</label>
              <input id="nome" name="nome" type="text" required value="<?= htmlspecialchars($_GET['nome'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label for="cognome">cognome:</label>
              <input id="cognome" name="cognome" type="text" required value="<?= htmlspecialchars($_GET['cognome'] ?? '') ?>">
            </div>
          </div>

          <!-- Email - ancho completo -->
          <div class="form-field">
            <label for="email">email:</label>
            <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
          </div>

          <!-- Username - ancho completo -->
          <div class="form-field">
            <label for="username">username:</label>
            <input id="username" name="username" type="text" required value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
          </div>

          <!-- Fila de Password y Conferma Password -->
          <div class="form-row">
            <div class="form-field">
              <label for="password">password:</label>
              <input id="password" name="password" type="password" required>
            </div>
            <div class="form-field">
              <label for="conferma_password">conferma password:</label>
              <input id="conferma_password" name="conferma_password" type="password" required>
            </div>
          </div>

          <div class="form-actions">
            <button class="btn-primary" type="submit">Registrati</button>
          </div>
        </form>

        <div class="create-account-section">
          <p>Hai già un account?</p>
          <a class="create-account-button" href="login.php">Accedi</a>
        </div>
      </div>
    </main>
  </body>
  </html>
  <?php
  exit;
}

// ===== POST: procesar registro =====
try {
    // Validar que todos los campos estén completos
    $required_fields = ['nome', 'cognome', 'email', 'username', 'password', 'conferma_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            header("Location: register.php?error=" . urlencode("Tutti i campi sono obbligatori."));
            exit();
        }
    }

    // Obtener y sanitizar datos
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password_pura = $_POST['password'];
    $conferma_password_pura = $_POST['conferma_password'];

    // Validación del email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $params = http_build_query([
            'error' => "Il formato dell'indirizzo email non è valido.",
            'nome' => $nome,
            'cognome' => $cognome,
            'username' => $username
        ]);
        header("Location: register.php?" . $params);
        exit();
    }

    // Validación de contraseñas
    if ($password_pura !== $conferma_password_pura) {
        $params = http_build_query([
            'error' => "Le password non corrispondono.",
            'nome' => $nome,
            'cognome' => $cognome,
            'email' => $email,
            'username' => $username
        ]);
        header("Location: register.php?" . $params);
        exit();
    }

    // Validación de longitud de contraseña
    if (strlen($password_pura) < 8) {
        $params = http_build_query([
            'error' => "La password deve contenere almeno 8 caratteri.",
            'nome' => $nome,
            'cognome' => $cognome,
            'email' => $email,
            'username' => $username
        ]);
        header("Location: register.php?" . $params);
        exit();
    }

    // Verificar si email o username ya existen
    $sql_check = "SELECT COUNT(*) as count FROM Utente WHERE email = :email OR username = :username";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':email' => $email, ':username' => $username]);
    $result = $stmt_check->fetch();
    
    if ($result['count'] > 0) {
        $params = http_build_query([
            'error' => "L'email o l'username sono già in uso. Scegli un altro.",
            'nome' => $nome,
            'cognome' => $cognome
        ]);
        header("Location: register.php?" . $params);
        exit();
    }

    // Hash de la contraseña
    $hashed = password_hash($password_pura, PASSWORD_DEFAULT);

    // Insertar usuario en la base de datos
    $sql_insert = "INSERT INTO Utente (nome, cognome, email, username, password) 
                   VALUES (:nome, :cognome, :email, :username, :password)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':nome' => $nome,
        ':cognome' => $cognome,
        ':email' => $email,
        ':username' => $username,
        ':password' => $hashed
    ]);

    // Registración exitosa - redirigir al login
    header("Location: login.php?success=" . urlencode("Registrazione completata con successo! Ora puoi accedere."));
    exit();

} catch (PDOException $e) {
    error_log("Error en registro: " . $e->getMessage());
    header("Location: register.php?error=" . urlencode("Errore durante la registrazione. Riprova più tardi."));
    exit();
}
