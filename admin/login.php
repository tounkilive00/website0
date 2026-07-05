<?php
session_start();


if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administration — Galerie d'Art</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">
    <p class="login-logo">⚙ Admin</p>
    <p class="login-sub">Galerie d'Art — Espace Administration</p>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" id="login-form">
      <input type="password" name="password" id="admin-password" placeholder="Mot de passe" required autofocus>
      <button type="submit" class="login-btn" id="login-submit">Se Connecter</button>
    </form>
    <p style="margin-top:1.5rem;font-size:0.75rem;color:var(--text-muted);">
      <a href="../index.php" style="color:var(--gold);text-decoration:none;">← Retour au site</a>
    </p>
  </div>
</div>
</body>
</html>
