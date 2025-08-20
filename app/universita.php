<?php
require __DIR__.'/config.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id,name,city,cover_image,short_desc FROM universities";
$params = [];
if ($q !== '') {
  $sql .= " WHERE name LIKE ? OR city LIKE ? ";
  $like = "%{$q}%";
  $params = [$like, $like];
}
$sql .= " ORDER BY name ASC";

$stmt = $mysqli->prepare(
  $q !== '' ? $sql : "SELECT id,name,city,cover_image,short_desc FROM universities ORDER BY name ASC"
);
if ($q !== '') $stmt->bind_param("ss", ...$params);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Università — SonoErasmus+</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php /* Header reutilizable si lo tienes como include; aquí lo dejamos simple */ ?>
<header class="site-header"><div class="header-inner">
  <a class="brand" href="../index.html">SonoErasmus+</a>
  <nav class="desktop-nav"><a href="../index.html#home">Home</a></nav>
</div></header>

<main class="section">
  <h1>Università</h1>

  <form method="get" class="cf-search" role="search" aria-label="Cerca università">
    <input class="cf-input" type="search" name="q" value="<?=h($q)?>" placeholder="Cerca per nome o città…">
    <button class="cf-btn" type="submit">Cerca</button>
  </form>

  <?php if (!$rows): ?>
    <p>Nessun risultato.</p>
  <?php else: ?>
  <div class="cf-grid">
    <?php foreach ($rows as $u): ?>
      <article class="cf-card">
        <a class="cf-card-link" href="universita_dettaglio.php?id=<?=$u['id']?>">
          <div class="cf-card-media" style="background-image:url('<?=h($u['cover_image'])?>');"></div>
          <div class="cf-card-body">
            <h3 class="cf-card-title"><?=h($u['name'])?></h3>
            <div class="cf-card-meta"><?=h($u['city'])?></div>
            <p class="cf-card-text"><?=h($u['short_desc'])?></p>
            <span class="cf-card-cta">Scopri di più</span>
          </div>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<footer class="site-footer"><div class="footer-bottom">© <?=date('Y')?> SonoErasmus+</div></footer>
</body>
</html>
