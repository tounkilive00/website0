<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_once '../db.php';

$id = intval($_GET['id'] ?? 0);
$painting = getPainting($id);
if (!$painting) { header('Location: dashboard.php'); exit; }

$error   = '';
$allowedImages = ['image/jpeg','image/png','image/webp','image/gif'];
$allowedVideos = ['video/mp4','video/webm','video/ogg'];
$maxSize = 50 * 1024 * 1024;

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
    } else {
        $mediaFile = $painting['media_file'];
        $mediaType = $painting['media_type'];

        // Handle new file upload if provided
        if (!empty($_FILES['media_file']['name'])) {
            if ($_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
                if ($_FILES['media_file']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['media_file']['error'] === UPLOAD_ERR_FORM_SIZE) {
                    $error = 'Fichier trop volumineux. Maximum 50 MB.';
                } else {
                    $error = 'Erreur lors du téléchargement du fichier (code ' . $_FILES['media_file']['error'] . ').';
                }
            } else {
                $file    = $_FILES['media_file'];
                if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                    $error = 'Fichier téléchargé invalide.';
                } else {
                    $mime    = mime_content_type($file['tmp_name']);
                    $isImage = in_array($mime, $allowedImages);
                    $isVideo = in_array($mime, $allowedVideos);

                    if (!$isImage && !$isVideo) {
                        $error = 'Format non supporté.';
                    } elseif ($file['size'] > $maxSize) {
                        $error = 'Fichier trop volumineux (max 50 MB).';
                    } else {
                        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = uniqid('oeuvre_', true) . '.' . strtolower($ext);
                        $dest     = '../uploads/' . $filename;
                        if (!is_dir('../uploads')) mkdir('../uploads', 0755, true);
                        if (move_uploaded_file($file['tmp_name'], $dest)) {
                            // Delete old file if it's in uploads
                            $oldPath = '../uploads/' . $painting['media_file'];
                            if (file_exists($oldPath)) unlink($oldPath);
                            $mediaFile = $filename;
                            $mediaType = $isVideo ? 'video' : 'image';
                        } else {
                            $error = 'Erreur lors de l\'enregistrement du fichier.';
                        }
                    }
                }
            }
        }

        if (!$error) {
            $stmt = getDB()->prepare("
                UPDATE paintings SET title=?,material=?,width_cm=?,height_cm=?,year=?,price=?,currency=?,status=?,description=?,media_file=?,media_type=?
                WHERE id=?
            ");
            $stmt->execute([$title,$material,$width_cm,$height_cm,$year,$price,$currency,$status,$description,$mediaFile,$mediaType,$id]);
            header('Location: dashboard.php?msg=' . urlencode('✓ Œuvre modifiée avec succès.'));
            exit;
        }
    }
    // Repopulate from POST on error
    $painting = array_merge($painting, compact('title','material','width_cm','height_cm','year','price','currency','status','description'));
}

$mediaPath = file_exists('../uploads/' . $painting['media_file'])
  ? '../uploads/' . htmlspecialchars($painting['media_file'])
  : '../images/' . htmlspecialchars($painting['media_file']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier l'Œuvre — Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-wrapper">
  <aside class="sidebar">
    <p class="sidebar-logo">🎨 Admin</p>
    <nav class="sidebar-nav">
      <a href="dashboard.php" id="nav-dash">📊 Tableau de Bord</a>
      <a href="add_painting.php" id="nav-add">➕ Ajouter une Œuvre</a>
      <a href="../index.php" target="_blank" id="nav-site">🌐 Voir le Site</a>
    </nav>
    <div class="sidebar-footer"><a href="logout.php" id="nav-logout">⬅ Déconnexion</a></div>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Modifier : <?= htmlspecialchars($painting['title']) ?></h1>
      <a href="dashboard.php" class="btn btn-edit" id="btn-back-edit">← Retour</a>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST" enctype="multipart/form-data" id="edit-painting-form">
        <div class="form-grid">
          <div class="form-group full">
            <label for="title">Titre de l'Œuvre *</label>
            <input type="text" id="title" name="title" required value="<?= htmlspecialchars($painting['title']) ?>">
          </div>
          <div class="form-group">
            <label for="material">Technique / Matière *</label>
            <input type="text" id="material" name="material" required value="<?= htmlspecialchars($painting['material']) ?>">
          </div>
          <div class="form-group">
            <label for="year">Année</label>
            <input type="number" id="year" name="year" min="1900" max="2099" value="<?= htmlspecialchars($painting['year'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="width_cm">Largeur (cm) *</label>
            <input type="number" id="width_cm" name="width_cm" step="0.1" min="1" required value="<?= htmlspecialchars($painting['width_cm']) ?>">
          </div>
          <div class="form-group">
            <label for="height_cm">Hauteur (cm) *</label>
            <input type="number" id="height_cm" name="height_cm" step="0.1" min="1" required value="<?= htmlspecialchars($painting['height_cm']) ?>">
          </div>
          <div class="form-group">
            <label for="price">Prix *</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required value="<?= htmlspecialchars($painting['price']) ?>">
          </div>
          <div class="form-group">
            <label for="currency">Devise</label>
            <select id="currency" name="currency">
              <option value="EUR" <?= $painting['currency'] === 'EUR' ? 'selected' : '' ?>>EUR €</option>
              <option value="USD" <?= $painting['currency'] === 'USD' ? 'selected' : '' ?>>USD $</option>
              <option value="GBP" <?= $painting['currency'] === 'GBP' ? 'selected' : '' ?>>GBP £</option>
              <option value="XOF" <?= $painting['currency'] === 'XOF' ? 'selected' : '' ?>>XOF CFA</option>
            </select>
          </div>
          <div class="form-group">
            <label for="status">Statut</label>
            <select id="status" name="status">
              <option value="disponible" <?= $painting['status'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
              <option value="vendu" <?= $painting['status'] === 'vendu' ? 'selected' : '' ?>>Vendu</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= htmlspecialchars($painting['description'] ?? '') ?></textarea>
          </div>
          <div class="form-group full">
            <label>Remplacer l'image/vidéo (laisser vide pour conserver l'actuelle)</label>
            <div class="current-media" style="margin-bottom:1rem;">
              <p style="font-size:0.78rem;color:var(--text-muted);margin-bottom:0.5rem;">Média actuel :</p>
              <?php if ($painting['media_type'] === 'video'): ?>
                <video src="<?= $mediaPath ?>" controls style="max-height:160px;border-radius:4px;"></video>
              <?php else: ?>
                <img src="<?= $mediaPath ?>" style="max-height:160px;border-radius:4px;" alt="Média actuel">
              <?php endif; ?>
            </div>
            <div class="upload-area">
              <input type="file" name="media_file" id="new_media" accept="image/*,video/*" onchange="previewNew(this)">
              <p style="color:var(--text-muted);font-size:1.2rem;">📁</p>
              <p style="color:var(--text);font-size:0.88rem;margin-top:0.4rem;">Choisir un nouveau fichier (optionnel)</p>
            </div>
            <div id="new-preview"></div>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-gold" id="submit-edit">✓ Enregistrer les Modifications</button>
          <a href="dashboard.php" class="btn btn-edit">Annuler</a>
        </div>
      </form>
    </div>
  </main>
</div>
<script>
function previewNew(input) {
  const area = document.getElementById('new-preview');
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  const url  = URL.createObjectURL(file);
  if (file.type.startsWith('video')) {
    area.innerHTML = `<p style="font-size:0.78rem;color:var(--text-muted);margin-top:0.8rem;">Nouveau média :</p><video src="${url}" controls style="max-height:160px;border-radius:4px;margin-top:0.4rem;"></video>`;
  } else {
    area.innerHTML = `<p style="font-size:0.78rem;color:var(--text-muted);margin-top:0.8rem;">Nouveau média :</p><img src="${url}" style="max-height:160px;border-radius:4px;margin-top:0.4rem;">`;
  }
}
</script>
</body>
</html>
