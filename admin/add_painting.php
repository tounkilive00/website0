<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_once '../db.php';

$error   = '';
$success = '';

// ── ALLOWED TYPES ──
$allowedImages = ['image/jpeg','image/png','image/webp','image/gif'];
$allowedVideos = ['video/mp4','video/webm','video/ogg'];
$maxSize = 50 * 1024 * 1024; // 50 MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $material    = trim($_POST['material'] ?? '');
    $width_cm    = floatval($_POST['width_cm'] ?? 0);
    $height_cm   = floatval($_POST['height_cm'] ?? 0);
    $year        = intval($_POST['year'] ?? 0) ?: null;
    $price       = floatval($_POST['price'] ?? 0);
    $currency    = $_POST['currency'] ?? 'EUR';
    $status      = $_POST['status'] ?? 'disponible';
    $description = trim($_POST['description'] ?? '');

    if (!$title || !$material || !$width_cm || !$height_cm || !$price) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (empty($_FILES['media_file']['name'])) {
        $error = 'Veuillez sélectionner une image ou une vidéo.';
    } else {
        $file     = $_FILES['media_file'];
        $mime     = mime_content_type($file['tmp_name']);
        $isImage  = in_array($mime, $allowedImages);
        $isVideo  = in_array($mime, $allowedVideos);

        if (!$isImage && !$isVideo) {
            $error = 'Format non supporté. Images: JPG, PNG, WEBP, GIF. Vidéos: MP4, WEBM, OGG.';
        } elseif ($file['size'] > $maxSize) {
            $error = 'Fichier trop volumineux. Maximum 50 MB.';
        } else {
            $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename  = uniqid('oeuvre_', true) . '.' . strtolower($ext);
            $dest      = '../uploads/' . $filename;
            if (!is_dir('../uploads')) mkdir('../uploads', 0755, true);

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $mediaType = $isVideo ? 'video' : 'image';
                $stmt = getDB()->prepare("
                    INSERT INTO paintings (title,material,width_cm,height_cm,year,price,currency,status,description,media_file,media_type)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)
                ");
                $stmt->execute([$title,$material,$width_cm,$height_cm,$year,$price,$currency,$status,$description,$filename,$mediaType]);
                header('Location: dashboard.php?msg=' . urlencode('✓ Œuvre ajoutée avec succès.'));
                exit;
            } else {
                $error = 'Erreur lors de l\'enregistrement du fichier. Vérifiez les permissions du dossier uploads/.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajouter une Œuvre — Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-wrapper">
  <aside class="sidebar">
    <p class="sidebar-logo">🎨 Admin</p>
    <nav class="sidebar-nav">
      <a href="dashboard.php" id="nav-dash">📊 Tableau de Bord</a>
      <a href="add_painting.php" class="active" id="nav-add">➕ Ajouter une Œuvre</a>
      <a href="../index.php" target="_blank" id="nav-site">🌐 Voir le Site</a>
    </nav>
    <div class="sidebar-footer"><a href="logout.php" id="nav-logout">⬅ Déconnexion</a></div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Ajouter une Œuvre</h1>
      <a href="dashboard.php" class="btn btn-edit" id="btn-back">← Retour</a>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST" enctype="multipart/form-data" id="add-painting-form">
        <div class="form-grid">
          <div class="form-group full">
            <label for="title">Titre de l'Œuvre *</label>
            <input type="text" id="title" name="title" placeholder="ex: Lumières d'Automne" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="material">Technique / Matière *</label>
            <input type="text" id="material" name="material" placeholder="ex: Huile sur toile" required value="<?= htmlspecialchars($_POST['material'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="year">Année</label>
            <input type="number" id="year" name="year" placeholder="ex: 2024" min="1900" max="2099" value="<?= htmlspecialchars($_POST['year'] ?? date('Y')) ?>">
          </div>
          <div class="form-group">
            <label for="width_cm">Largeur (cm) *</label>
            <input type="number" id="width_cm" name="width_cm" placeholder="ex: 80" step="0.1" min="1" required value="<?= htmlspecialchars($_POST['width_cm'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="height_cm">Hauteur (cm) *</label>
            <input type="number" id="height_cm" name="height_cm" placeholder="ex: 60" step="0.1" min="1" required value="<?= htmlspecialchars($_POST['height_cm'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="price">Prix *</label>
            <input type="number" id="price" name="price" placeholder="ex: 1200" step="0.01" min="0" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="currency">Devise</label>
            <select id="currency" name="currency">
              <option value="EUR" <?= ($_POST['currency'] ?? 'EUR') === 'EUR' ? 'selected' : '' ?>>EUR €</option>
              <option value="USD" <?= ($_POST['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD $</option>
              <option value="GBP" <?= ($_POST['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP £</option>
              <option value="XOF" <?= ($_POST['currency'] ?? '') === 'XOF' ? 'selected' : '' ?>>XOF CFA</option>
            </select>
          </div>
          <div class="form-group">
            <label for="status">Statut</label>
            <select id="status" name="status">
              <option value="disponible" <?= ($_POST['status'] ?? 'disponible') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
              <option value="vendu" <?= ($_POST['status'] ?? '') === 'vendu' ? 'selected' : '' ?>>Vendu</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Décrivez l'inspiration, la technique, l'histoire de cette œuvre…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>
          <div class="form-group full">
            <label>Image ou Vidéo * (JPG, PNG, WEBP, MP4, WEBM — max 50 MB)</label>
            <div class="upload-area" id="upload-area">
              <input type="file" name="media_file" id="media_file" accept="image/*,video/*" onchange="previewMedia(this)" required>
              <p style="color:var(--text-muted);font-size:1.5rem;">📁</p>
              <p style="color:var(--text);font-size:0.9rem;margin-top:0.5rem;">Glissez un fichier ici ou cliquez pour choisir</p>
              <p class="upload-hint">Images ou vidéos acceptées</p>
            </div>
            <div class="current-media" id="preview-area"></div>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-gold" id="submit-add">✓ Enregistrer l'Œuvre</button>
          <a href="dashboard.php" class="btn btn-edit">Annuler</a>
        </div>
      </form>
    </div>
  </main>
</div>
<script>
function previewMedia(input) {
  const area = document.getElementById('preview-area');
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const url  = URL.createObjectURL(file);
  const label = document.querySelector('#upload-area p:nth-child(2)');
  label.textContent = file.name;
  if (file.type.startsWith('video')) {
    area.innerHTML = `<video src="${url}" controls style="max-height:180px;border-radius:4px;margin-top:1rem;"></video>`;
  } else {
    area.innerHTML = `<img src="${url}" style="max-height:180px;border-radius:4px;margin-top:1rem;">`;
  }
}
</script>
</body>
</html>
