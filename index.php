<?php
require_once 'db.php';
$paintings = getAllPaintings();

include 'header.php';
?>

<!-- ══ HERO ══ -->
<section id="hero">
  <div class="hero-bg"></div>
  <div class="hero-content">
    <div class="hero-tag">Galerie d'Art Contemporain</div>
    <h1 class="hero-title">L'Art comme<br><em>Langage de l'Âme</em></h1>
    <p class="hero-subtitle">Peintures originales — chaque œuvre, une histoire unique à suspendre chez vous.</p>
    <a href="#galerie" class="btn-primary">Explorer la Galerie</a>
  </div>
  <div class="scroll-indicator">
    <span>Défiler</span>
    <div class="scroll-line"></div>
  </div>
</section>

<?php
// Inclure la section Biographie/Présentation de l'artiste
include 'biography.php';
?>

<section id="galerie">
  <div class="section-header">
    <span class="section-tag">Œuvres Originales</span>
    <h2 class="section-title">La Galerie</h2>
    <div class="section-divider"></div>
  </div>

  <div class="gallery-filters">
    <button class="filter-btn active" data-filter="all">Tout</button>
    <button class="filter-btn" data-filter="disponible">Disponible</button>
    <button class="filter-btn" data-filter="vendu">Vendu</button>
  </div>

  <div class="gallery-grid">
    <?php foreach ($paintings as $p): ?>
    <?php
      $mediaPath = file_exists('uploads/' . $p['media_file'])
        ? 'uploads/' . htmlspecialchars($p['media_file'])
        : 'images/' . htmlspecialchars($p['media_file']);
    ?>
    <article class="painting-card" data-status="<?= htmlspecialchars($p['status']) ?>"
      data-id="<?= $p['id'] ?>"
      data-title="<?= htmlspecialchars($p['title']) ?>"
      data-material="<?= htmlspecialchars($p['material']) ?>"
      data-width="<?= $p['width_cm'] ?>"
      data-height="<?= $p['height_cm'] ?>"
      data-year="<?= $p['year'] ?>"
      data-price="<?= number_format($p['price'],0,',',' ') ?>"
      data-currency="<?= htmlspecialchars($p['currency']) ?>"
      data-desc="<?= htmlspecialchars($p['description']) ?>"
      data-media="<?= $mediaPath ?>"
      data-type="<?= htmlspecialchars($p['media_type']) ?>"
      data-status-val="<?= htmlspecialchars($p['status']) ?>"
      onclick="openModal(this)">
      <div class="card-media">
        <?php if ($p['media_type'] === 'video'): ?>
          <video src="<?= $mediaPath ?>" muted loop playsinline></video>
        <?php else: ?>
          <img src="<?= $mediaPath ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
        <?php endif; ?>
        <span class="card-badge badge-<?= $p['status'] ?>">
          <?= $p['status'] === 'vendu' ? 'Vendu' : 'Disponible' ?>
        </span>
      </div>
      <div class="card-body">
        <h3 class="card-title"><?= htmlspecialchars($p['title']) ?></h3>
        <p class="card-meta">Technique : <span><?= htmlspecialchars($p['material']) ?></span></p>
        <p class="card-meta">Format : <span><?= $p['width_cm'] ?> × <?= $p['height_cm'] ?> cm</span></p>
        <?php if ($p['year']): ?><p class="card-meta">Année : <span><?= $p['year'] ?></span></p><?php endif; ?>
        <p class="card-desc"><?= htmlspecialchars(mb_substr($p['description'], 0, 100)) ?>…</p>
        <div class="card-footer">
          <span class="card-price"><?= number_format($p['price'],0,',',' ') ?> <?= htmlspecialchars($p['currency']) ?></span>
          <?php if ($p['status'] === 'disponible'): ?>
            <button class="btn-buy" onclick="event.stopPropagation();openModal(this.closest('.painting-card'))">Acquérir</button>
          <?php else: ?>
            <span class="btn-buy btn-sold">Vendu</span>
          <?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══ MODAL DETAIL ══ -->
<div class="modal-overlay" id="paintingModal" onclick="closeModalOnBg(event)">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()" aria-label="Fermer">✕</button>
    <div class="modal-img" id="modalMedia"></div>
    <div class="modal-info">
      <h2 class="modal-title" id="modalTitle"></h2>
      <p class="modal-price" id="modalPrice"></p>
      <p class="modal-detail"><strong>Technique :</strong> <span id="modalMaterial"></span></p>
      <p class="modal-detail"><strong>Dimensions :</strong> <span id="modalSize"></span></p>
      <p class="modal-detail"><strong>Année :</strong> <span id="modalYear"></span></p>
      <p class="modal-detail"><strong>Statut :</strong> <span id="modalStatus"></span></p>
      <p class="modal-desc" id="modalDesc"></p>
      <div class="modal-actions" id="modalActions"></div>
    </div>
  </div>
</div>

<?php
// Inclure les sections Paiement, Contact et Pied de page
include 'payment.php';
include 'contact.php';
include 'footer.php';
?>
