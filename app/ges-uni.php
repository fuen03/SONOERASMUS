<?php
// admin_universita.php
session_start();
require_once __DIR__ . '/config.php'; // $pdo, isLoggedIn(), isAdmin()

if (!isAdmin()) {
  header('Location: index.php');
  exit;
}

$flash_ok = [];
$flash_err = [];

// CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// --- Eliminar universidad ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_universita') {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $csrf)) {
    $flash_err[] = 'Token non valido. Riprova.';
  } else {
    $uniId = (int)($_POST['universita_id'] ?? 0);
    if ($uniId <= 0) {
      $flash_err[] = 'ID università non valido.';
    } else {
      // ¿Existe?
      $stmt = $pdo->prepare("SELECT id, nome, citta FROM Universita WHERE id = :id");
      $stmt->execute([':id' => $uniId]);
      $uni = $stmt->fetch();

      if (!$uni) {
        $flash_err[] = 'Università non trovata.';
      } else {
        try {
          $pdo->beginTransaction();

          // Borra dependencias antes (ajusta si tienes más tablas con FK)
          // 1) Partecipazione -> depende de Evento
          $pdo->prepare("
            DELETE FROM Partecipazione 
             WHERE evento_id IN (SELECT id FROM Evento WHERE universita_id = :id)
          ")->execute([':id' => $uniId]);

          // 2) Eventi della università
          $pdo->prepare("DELETE FROM Evento WHERE universita_id = :id")
              ->execute([':id' => $uniId]);

          // 3) Esperienze legate alla università
          $pdo->prepare("DELETE FROM EsperienzaErasmus WHERE universita_id = :id")
              ->execute([':id' => $uniId]);

          // 4) Finalmente la università
          $pdo->prepare("DELETE FROM Universita WHERE id = :id")
              ->execute([':id' => $uniId]);

          $pdo->commit();
          $flash_ok[] = "Università «".htmlspecialchars($uni['nome'])."» eliminata.";
        } catch (Throwable $e) {
          if ($pdo->inTransaction()) $pdo->rollBack();
          error_log("Errore eliminazione università: ".$e->getMessage());
          $flash_err[] = "Errore durante l'eliminazione. Riprova più tardi.";
        }
      }
    }
  }
}

// --- Listar universidades ---
$universita = [];
try {
  $q = "
    SELECT u.id, u.nome, u.citta, u.nazione,
           COALESCE(e.cnt,0)  AS esperienze,
           COALESCE(ev.cnt,0) AS eventi
      FROM Universita u
      LEFT JOIN (
        SELECT universita_id, COUNT(*) cnt
          FROM EsperienzaErasmus
         GROUP BY universita_id
      ) e ON e.universita_id = u.id
      LEFT JOIN (
        SELECT universita_id, COUNT(*) cnt
          FROM Evento
         GROUP BY universita_id
      ) ev ON ev.universita_id = u.id
     ORDER BY u.nome ASC, u.citta ASC
  ";
  $universita = $pdo->query($q)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $flash_err[] = 'Errore nel caricamento delle università.';
}

// helper escape
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestisci Università — Dashboard</title>
  <link rel="stylesheet" href="../assets/css/ges-uni.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="dash-wrap">
    <div class="toolbar">
      <h1 class="h3">Gestisci Università</h1>
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

    <div class="search">
      <input type="search" id="q" placeholder="Cerca per nome o città…">
    </div>

    <div class="table-wrap">
      <table class="table" id="uniTable">
        <thead>
          <tr>
            <th style="width:60px">ID</th>
            <th>Nome</th>
            <th>Città</th>
            <th>Nazione</th>
            <th style="width:140px">Esperienze</th>
            <th style="width:120px">Eventi</th>
            <th style="width:1%">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($universita as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= h($u['nome']) ?></td>
              <td><?= h($u['citta']) ?></td>
              <td><?= h($u['nazione']) ?></td>
              <td><span class="badge"><?= (int)$u['esperienze'] ?></span></td>
              <td><span class="badge"><?= (int)$u['eventi'] ?></span></td>
              <td>
                <form method="post" action="" data-del-uni style="display:inline;">
                  <input type="hidden" name="action"         value="delete_universita">
                  <input type="hidden" name="csrf"           value="<?= h($_SESSION['csrf']) ?>">
                  <input type="hidden" name="universita_id"  value="<?= (int)$u['id'] ?>">
                  <button class="btn-danger" type="submit"
                          data-nome="<?= h($u['nome']) ?>">Elimina</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    // Confirmación de borrado
    document.querySelectorAll('form[data-del-uni]').forEach(form => {
      form.addEventListener('submit', (e) => {
        const n = form.querySelector('button[data-nome]').dataset.nome || 'università';
        if (!confirm(`Sei sicuro di eliminare “${n}”? Verranno eliminati anche eventi ed esperienze collegati.`)) {
          e.preventDefault();
        }
      });
    });

    // Filtro rápido
    const q = document.getElementById('q');
    const rows = Array.from(document.querySelectorAll('#uniTable tbody tr'));
    q.addEventListener('input', () => {
      const v = q.value.trim().toLowerCase();
      rows.forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(v) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
