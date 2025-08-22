<?php
// admin_utenti.php
session_start();
require_once __DIR__ . '/config.php'; // -> aquí ya tienes $pdo, isLoggedIn(), isAdmin()

// Solo admin
if (!isAdmin()) {
  header('Location: ../index.php');
  exit;
}

$flash_ok = [];
$flash_err = [];

// CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Eliminar usuario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $csrf)) {
    $flash_err[] = 'Token non valido. Riprova.';
  } else {
    $userId = (int)($_POST['user_id'] ?? 0);
    if ($userId <= 0) {
      $flash_err[] = 'ID utente non valido.';
    } elseif ($userId === (int)($_SESSION['utente_id'] ?? 0)) {
      $flash_err[] = 'Non puoi eliminare te stesso.';
    } else {
      // Verificar existencia y rol
      $stmt = $pdo->prepare("SELECT id, username, role FROM Utente WHERE id = :id");
      $stmt->execute([':id' => $userId]);
      $u = $stmt->fetch();

      if (!$u) {
        $flash_err[] = 'Utente non trovato.';
      // Si NO quieres permitir borrar admins, deja esta comprobación:
      } elseif (strtolower((string)$u['role']) === 'admin') {
        $flash_err[] = 'Non puoi eliminare un amministratore.';
      } else {
        try {
          $pdo->beginTransaction();

          // Borra primero dependencias (ajusta si tienes más tablas FK)
          $pdo->prepare("DELETE FROM EsperienzaErasmus WHERE utente_id = :id")->execute([':id' => $userId]);
          // Si tienes estas tablas, descomenta:
          // $pdo->prepare("DELETE FROM Partecipazione WHERE utente_id = :id")->execute([':id' => $userId]);
          // $pdo->prepare("DELETE FROM Commento WHERE utente_id = :id")->execute([':id' => $userId]);

          // Finalmente borra el usuario
          $pdo->prepare("DELETE FROM Utente WHERE id = :id")->execute([':id' => $userId]);

          $pdo->commit();
          $flash_ok[] = "Utente «" . htmlspecialchars($u['username']) . "» eliminato.";
        } catch (Throwable $e) {
          if ($pdo->inTransaction()) $pdo->rollBack();
          error_log("Errore eliminazione utente: ".$e->getMessage());
          $flash_err[] = "Errore durante l'eliminazione. Riprova più tardi.";
        }
      }
    }
  }
}

// Cargar usuarios
$users = [];
try {
  $q = "SELECT id, username, nome, cognome, email, role FROM Utente ORDER BY id DESC";
  $users = $pdo->query($q)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $flash_err[] = 'Errore nel caricamento degli utenti.';
}

// helper escape si no existe
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestisci Utenti — Dashboard</title>
  <link rel="stylesheet" href="../assets/css/users.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="dash-wrap">
    <div class="toolbar">
      <h1 class="h3">Gestisci Utenti</h1>
      <a class="btn-back" href="dashboard.php">← Torna al dashboard</a>
    </div>

    <?php if ($flash_ok): ?>
      <div class="notice success">
        <?php foreach ($flash_ok as $m): ?><p><?= h($m) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($flash_err): ?>
      <div class="notice error">
        <?php foreach ($flash_err as $m): ?><p><?= h($m) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- filtro simple en cliente -->
    <div class="search">
      <input type="search" id="q" placeholder="Cerca per username, nome, email…">
    </div>

    <div class="table-wrap">
      <table class="table" id="usersTable">
        <thead>
          <tr>
            <th style="width:60px">ID</th>
            <th>Username</th>
            <th>Nome</th>
            <th>Email</th>
            <th style="width:120px">Ruolo</th>
            <th style="width:1%">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= h($r['username']) ?></td>
              <td><?= h(trim(($r['nome'] ?? '').' '.($r['cognome'] ?? ''))) ?></td>
              <td><?= h($r['email']) ?></td>
              <td><?= h($r['role']) ?></td>
              <td>
                <?php
                  $isAdminRow = strtolower((string)$r['role']) === 'admin';
                  $isSelf     = (int)$r['id'] === (int)($_SESSION['utente_id'] ?? 0);
                ?>
                <?php if (!$isAdminRow && !$isSelf): ?>
                  <form method="post" action="" data-del-user style="display:inline;">
                    <input type="hidden" name="action"  value="delete_user">
                    <input type="hidden" name="csrf"    value="<?= h($_SESSION['csrf']) ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>">
                    <button class="btn-danger" type="submit"
                            data-username="<?= h($r['username']) ?>">Elimina</button>
                  </form>
                <?php else: ?>
                  <span class="muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    // Confirmación y filtro en cliente
    document.querySelectorAll('form[data-del-user]').forEach(form => {
      form.addEventListener('submit', (e) => {
        const u = form.querySelector('button[data-username]').dataset.username || 'utente';
        if (!confirm(`Sei sicuro di eliminare l'utente “${u}”? Questa azione è irreversibile.`)) {
          e.preventDefault();
        }
      });
    });

    // Filtro rápido
