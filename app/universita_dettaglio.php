<?php
require __DIR__.'/config.php';

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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?=h($uni['name'])?> — SonoErasmus+</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="site-header"><div class="header-inner">
  <a class="brand" href="../index.html">SonoErasmus+</a>
  <nav class="desktop-nav">
    <a href="universita.php">Tutte le università</a>
  </nav>
</div></header>

<main class="section">
  <article class="uni-hero">
    <img class="uni-cover" src="<?=h($uni['cover_image'])?>" alt="<?=h($uni['name'])?>">
    <div class="uni-meta">
      <h1><?=h($uni['name'])?></h1>
      <p class="uni-city"><?=$uni['city']?></p>
      <p class="uni-links">
        <?php if($uni['website']): ?><a class="btn-red" href="<?=h($uni['website'])?>" target="_blank" rel="noopener">Sito ufficiale</a><?php endif; ?>
        <?php if($uni['email']): ?><a class="btn-outline" href="mailto:<?=h($uni['email'])?>">E‑mail</a><?php endif; ?>
        <?php if($uni['phone']): ?><a class="btn-outline" href="tel:<?=h($uni['phone'])?>">Telefono</a><?php endif; ?>
      </p>
      <?="<p>".h($uni['short_desc'])."</p>"?>
    </div>
  </article>

  <section class="section">
    <?=$uni['long_desc'] /* ya guardada como HTML seguro; si no, sanear */?>
  </section>
</main>

<footer class="site-footer"><div class="footer-bottom">© <?=date('Y')?> SonoErasmus+</div></footer>

<style>
.uni-hero{display:grid;grid-template-columns:minmax(0,520px) 1fr;gap:1.5rem;align-items:center}
.uni-cover{width:100%;border-radius:14px;box-shadow:0 8px 18px rgba(0,0,0,.12);object-fit:cover;max-height:340px}
.uni-meta h1{margin:.2rem 0 0;color:#c62828}
.uni-city{color:#555;margin:.25rem 0 1rem}
.btn-red{display:inline-block;background:#c62828;color:#fff;padding:.6rem 1rem;border-radius:8px;text-decoration:none;font-weight:800;margin-right:.5rem}
.btn-outline{display:inline-block;border:2px solid #c62828;color:#c62828;padding:.5rem .9rem;border-radius:8px;text-decoration:none;font-weight:700;margin-right:.5rem}
@media (max-width:900px){.uni-hero{grid-template-columns:1fr}.uni-cover{max-height:280px}}
</style>
</body>
</html>
