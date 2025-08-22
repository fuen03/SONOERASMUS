<?php
// admin_eventi.php
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

// --- Eliminar evento ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_evento') {
  $csrf = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $csrf)) {
    $flash_err[] = 'Token non valido. Riprova.';
  } else {
    $eventId = (int)($_POST['evento_id'] ?? 0);
    if ($eventId <= 0) {
      $flash_err[] = 'ID evento non valido.';
    } else {
      // ¿Existe?
      $stmt = $pdo->prepare("
        SELECT e.id, COALESCE(e.titolo, e.nome, CONCAT('Evento #', e.id)) AS titolo
          FROM Evento e
         WHERE e.id = :id
      ");
      $stmt->execute([':id' => $eventId]);
      $ev = $stmt->fetch();

      if (!$ev) {
        $flash_err[] = 'Evento non trovato.';
      } else {
        try {
          $pdo->beginTransaction();

          // 1) Borrar partecipazioni ligadas al evento
          $pdo->prepare("DELETE FROM Partecipazione WHERE evento_id = :id")
              ->execute([':id' => $eventId]);

          // 2) Borrar el propio evento
          $pdo->prepare("DELETE FROM Evento WHERE id = :id")
              ->execute([':id' => $eventId]);

          $pdo->commit();
          $flash_ok[] = "Evento «" . htmlspecialchars($ev['titolo']) . "» eliminato.";
        } catch (Throwable $e) {
          if ($pdo->inTransaction()) $pdo->rollBack();
          error_log("Errore eliminazione evento: ".$e->getMessage());
          $flash_err[] = "Errore durante l'eliminazione. Riprova più tardi.";
        }
      }
    }
  }
}

// --- Listar eventos ---
$eventi = [];
try {
  // Soporta distintos nombres de columnas con COALESCE (data, data_evento, dataora / luogo, location / titolo, nome)
  $q = "
    SELECT
      e.id,
      COALESCE(e.titolo, e.nome, CONCAT('Evento #', e.id))               AS titolo,
      COALESCE(e.data_evento, e.data, e.dataora)                          AS data_evento,
      COALESCE(e.luogo, e.location)                                       AS luogo,
      u.nome                                                              AS universita_nome,
      COALESCE(p.cnt,0)                                                   AS partecipanti
    FROM Evento e
    LEFT JOIN Universita u ON u.id = e.universita_id
    LEFT JOIN (
      SELECT evento_id, COUNT(*) cnt
        FROM Partecipazione
       GROUP BY evento_id
    ) p ON p.evento_id = e.id
    ORDER BY e.id DESC
  ";
  $eventi = $pdo->query($q)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $flash_err[] = 'Errore nel caricamento degli eventi.';
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
  <title>Gestisci Eventi — Dashboard</title>
  <link rel="stylesheet" href="../assets/css/ges-ev.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="dash-wrap">
    <div class="toolbar">
      <h1 class="h3">Gestisci Eventi</h1>
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
      <input type="search" id="q" placeholder="Cerca per titolo, università, città…">
    </div>

    <div class="table-wrap">
      <table class="table" id="evTable">
        <thead>
          <tr>
            <th style="width:60px">ID</th>
            <th>Titolo</th>
            <th>Università</th>
            <th class="col-small">Data</th>
            <th class="col-small">Luogo</th>
            <th style="width:120px">Partecipanti</th>
            <th style="width:1%">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($eventi as $e): ?>
            <tr>
              <td><?= (int)$e['id'] ?></td>
              <td><?= h($e['titolo']) ?></td>
              <td><?= h($e['universita_nome']) ?></td>
              <td><?= h($e['data_evento']) ?></td>
              <td><?= h($e['luogo']) ?></td>
              <td><span class="badge"><?= (int)$e['partecipanti'] ?></span></td>
              <td>
                <form method="post" action="" data-del-ev style="display:inline;">
                  <input type="hidden" name="action"     value="delete_evento">
                  <input type="hidden" name="csrf"       value="<?= h($_SESSION['csrf']) ?>">
                  <input type="hidden" name="evento_id"  value="<?= (int)$e['id'] ?>">
                  <button class="btn-danger" type="submit"
                          data-titolo="<?= h($e['titolo']) ?>">Elimina</button>
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
    document.querySelectorAll('form[data-del-ev]').forEach(form => {
      form.addEventListener('submit', (e) => {
        const t = form.querySelector('button[data-titolo]').dataset.titolo || 'evento';
        if (!confirm(`Sei sicuro di eliminare “${t}”? Verranno rimosse anche le partecipazioni.`)) {
          e.preventDefault();
        }
      });
    });

    // Filtro rápido
    const q = document.getElementById('q');
    const rows = Array.from(document.querySelectorAll('#evTable tbody tr'));
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
