<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_once '../db.php';

$pdo = getDB();
$paintings  = getAllPaintings();
$stats      = getStats();
$unread     = getUnreadContacts();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de Bord — Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-wrapper">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <p class="sidebar-logo">🎨 Admin</p>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="active" id="nav-dashboard">📊 Tableau de Bord</a>
      <a href="add_painting.php" id="nav-add">➕ Ajouter une Œuvre</a>
      <a href="contacts.php" id="nav-contacts">✉ Messages <?php if($unread>0): ?><span style="background:var(--gold);color:var(--dark);border-radius:20px;padding:0.1rem 0.5rem;font-size:0.65rem;margin-left:0.3rem;"><?= $unread ?></span><?php endif; ?></a>
      <a href="../index.php" target="_blank" id="nav-site">🌐 Voir le Site</a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php" id="nav-logout">⬅ Déconnexion</a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Tableau de Bord</h1>
      <a href="add_painting.php" class="btn btn-gold" id="btn-add-top">➕ Nouvelle Œuvre</a>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <p class="stat-card-num"><?= $stats['total_oeuvres'] ?></p>
        <p class="stat-card-label">Œuvres au Total</p>
      </div>
      <div class="stat-card">
        <p class="stat-card-num"><?= $stats['disponibles'] ?></p>
        <p class="stat-card-label">Disponibles</p>
      </div>
      <div class="stat-card">
        <p class="stat-card-num"><?= $stats['vendues'] ?></p>
        <p class="stat-card-label">Vendues</p>
      </div>
      <div class="stat-card">
        <p class="stat-card-num"><?= number_format($stats['chiffre_affaires'], 0, ',', ' ') ?> €</p>
        <p class="stat-card-label">Chiffre d'Affaires</p>
      </div>
      <div class="stat-card">
        <p class="stat-card-num"><?= $unread ?></p>
        <p class="stat-card-label">Messages Non Lus</p>
      </div>
    </div>

    <!-- TABLE -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success" style="margin-bottom:1.5rem;">
        <?= htmlspecialchars($_GET['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Aperçu</th>
            <th>Titre</th>
            <th>Technique</th>
            <th>Dimensions</th>
            <th>Prix</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paintings as $p):
            $mediaPath = file_exists('../uploads/' . $p['media_file'])
              ? '../uploads/' . htmlspecialchars($p['media_file'])
              : '../images/' . htmlspecialchars($p['media_file']);
          ?>
          <tr>
            <td>
              <?php if ($p['media_type'] === 'video'): ?>
                <video src="<?= $mediaPath ?>" class="thumb" muted></video>
              <?php else: ?>
                <img src="<?= $mediaPath ?>" class="thumb" alt="<?= htmlspecialchars($p['title']) ?>">
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
            <td><?= htmlspecialchars($p['material']) ?></td>
            <td><?= $p['width_cm'] ?> × <?= $p['height_cm'] ?> cm</td>
            <td><?= number_format($p['price'], 0, ',', ' ') ?> <?= htmlspecialchars($p['currency']) ?></td>
            <td><span class="badge badge-<?= $p['status'] ?>"><?= $p['status'] === 'vendu' ? 'Vendu' : 'Disponible' ?></span></td>
            <td>
              <div class="td-actions">
                <a href="edit_painting.php?id=<?= $p['id'] ?>" class="btn btn-edit">✏ Modifier</a>
                <a href="delete_painting.php?id=<?= $p['id'] ?>" class="btn btn-danger"
                   onclick="return confirm('Supprimer cette œuvre définitivement ?')">🗑 Supprimer</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($paintings)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Aucune œuvre. <a href="add_painting.php" style="color:var(--gold);">Ajouter la première</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
