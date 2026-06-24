<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_once '../db.php';

// Mark a message as read
if (isset($_GET['read'])) {
    $stmt = getDB()->prepare("UPDATE contacts SET is_read = TRUE WHERE id = $1");
    $stmt->execute([intval($_GET['read'])]);
    header('Location: contacts.php');
    exit;
}

// Delete a message
if (isset($_GET['delete'])) {
    $stmt = getDB()->prepare("DELETE FROM contacts WHERE id = $1");
    $stmt->execute([intval($_GET['delete'])]);
    header('Location: contacts.php?msg=' . urlencode('Message supprimé.'));
    exit;
}

$contacts = getDB()->query("SELECT * FROM contacts ORDER BY received_at DESC")->fetchAll();
$unread   = getUnreadContacts();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages — Admin</title>
  <link rel="stylesheet" href="admin.css">
  <style>
    .msg-card { background: var(--dark3); border: 1px solid var(--dark4); padding: 1.5rem; margin-bottom: 1rem; border-radius: var(--radius); transition: border-color var(--transition); }
    .msg-card.unread { border-left: 3px solid var(--gold); }
    .msg-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.8rem; }
    .msg-sender strong { color: var(--white); font-size: 0.95rem; }
    .msg-sender a { color: var(--gold); font-size: 0.82rem; text-decoration: none; }
    .msg-meta { font-size: 0.75rem; color: var(--text-muted); text-align: right; }
    .msg-subject { font-size: 0.78rem; letter-spacing: 0.08em; color: var(--gold); text-transform: uppercase; margin-bottom: 0.5rem; }
    .msg-body { font-size: 0.88rem; color: var(--text-muted); line-height: 1.7; white-space: pre-wrap; }
    .msg-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
  </style>
</head>
<body>
<div class="admin-wrapper">
  <aside class="sidebar">
    <p class="sidebar-logo">🎨 Admin</p>
    <nav class="sidebar-nav">
      <a href="dashboard.php" id="nav-dash">📊 Tableau de Bord</a>
      <a href="add_painting.php" id="nav-add">➕ Ajouter une Œuvre</a>
      <a href="contacts.php" class="active" id="nav-contacts">✉ Messages
        <?php if($unread > 0): ?>
          <span style="background:var(--gold);color:var(--dark);border-radius:20px;padding:0.1rem 0.5rem;font-size:0.65rem;margin-left:0.3rem;"><?= $unread ?></span>
        <?php endif; ?>
      </a>
      <a href="../index.php" target="_blank" id="nav-site">🌐 Voir le Site</a>
    </nav>
    <div class="sidebar-footer"><a href="logout.php" id="nav-logout">⬅ Déconnexion</a></div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Messages Reçus</h1>
      <span style="color:var(--text-muted);font-size:0.85rem;"><?= count($contacts) ?> message(s) — <?= $unread ?> non lu(s)</span>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success" style="margin-bottom:1.5rem;"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if (empty($contacts)): ?>
      <p style="color:var(--text-muted);text-align:center;padding:3rem;">Aucun message reçu pour le moment.</p>
    <?php endif; ?>

    <?php foreach ($contacts as $c): ?>
    <div class="msg-card <?= !$c['is_read'] ? 'unread' : '' ?>">
      <div class="msg-header">
        <div class="msg-sender">
          <strong><?= htmlspecialchars($c['name']) ?></strong>
          <?php if (!$c['is_read']): ?><span class="badge badge-disponible" style="margin-left:0.5rem;font-size:0.6rem;">Nouveau</span><?php endif; ?>
          <br><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a>
        </div>
        <div class="msg-meta">
          <?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['received_at']))) ?>
        </div>
      </div>
      <p class="msg-subject">📌 <?= htmlspecialchars($c['subject'] ?? 'Sans sujet') ?></p>
      <p class="msg-body"><?= htmlspecialchars($c['message']) ?></p>
      <div class="msg-actions">
        <a href="mailto:<?= htmlspecialchars($c['email']) ?>?subject=Re: <?= urlencode($c['subject'] ?? '') ?>" class="btn btn-gold">✉ Répondre</a>
        <?php if (!$c['is_read']): ?>
          <a href="contacts.php?read=<?= $c['id'] ?>" class="btn btn-edit">✓ Marquer lu</a>
        <?php endif; ?>
        <a href="contacts.php?delete=<?= $c['id'] ?>" class="btn btn-danger"
           onclick="return confirm('Supprimer ce message ?')">🗑 Supprimer</a>
      </div>
    </div>
    <?php endforeach; ?>
  </main>
</div>
</body>
</html>
