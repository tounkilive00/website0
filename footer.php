<!-- ══ FOOTER ══ -->
<footer>
  <p class="footer-logo">Art Galerie</p>
  <p class="footer-tagline">Peintures originales • Livraison mondiale</p>
  <div class="footer-links">
    <a href="#about">Biographie</a>
    <a href="#galerie">Galerie</a>
    <a href="#paiement">Paiement</a>
    <a href="#contact">Contact</a>
    <a href="admin/login.php">Admin</a>
  </div>
  <p class="footer-copy">© <?= date('Y') ?> Art Galerie — Tous droits réservés. Œuvres protégées par le droit d'auteur.</p>
</footer>

<script>
// ── NAV SCROLL ──
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 60);
});

// ── GALLERY FILTER ──
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    document.querySelectorAll('.painting-card').forEach(card => {
      const show = filter === 'all' || card.dataset.status === filter;
      card.style.display = show ? '' : 'none';
    });
  });
});

// ── MODAL ──
function openModal(card) {
  const m = document.getElementById('paintingModal');
  const type = card.dataset.type;
  const media = card.dataset.media;
  const status = card.dataset.statusVal;

  document.getElementById('modalMedia').innerHTML =
    type === 'video'
      ? `<video src="${media}" controls autoplay muted style="width:100%;height:100%;object-fit:cover;"></video>`
      : `<img src="${media}" alt="${card.dataset.title}" style="width:100%;height:100%;object-fit:cover;">`;

  document.getElementById('modalTitle').textContent = card.dataset.title;
  document.getElementById('modalPrice').textContent = card.dataset.price + ' ' + card.dataset.currency;
  document.getElementById('modalMaterial').textContent = card.dataset.material;
  document.getElementById('modalSize').textContent = card.dataset.width + ' × ' + card.dataset.height + ' cm';
  document.getElementById('modalYear').textContent = card.dataset.year || '—';
  document.getElementById('modalStatus').textContent = status === 'vendu' ? '🔴 Vendu' : '🟢 Disponible';
  document.getElementById('modalDesc').textContent = card.dataset.desc;

  const actions = document.getElementById('modalActions');
  if (status === 'disponible') {
    actions.innerHTML = `
      <a href="https://paypal.me/votrecompte" target="_blank" class="btn-paypal">🅿 PayPal</a>
      <a href="https://payoneer.com" target="_blank" class="btn-payoneer">💳 Payoneer</a>
      <a href="#contact" class="btn-contact-buy" onclick="closeModal()">✉ Me Contacter</a>`;
  } else {
    actions.innerHTML = `<p style="color:var(--text-muted);font-size:0.85rem;">Cette œuvre a déjà été vendue. Contactez-moi pour une commande similaire.</p>`;
  }
  m.classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('paintingModal').classList.remove('active');
  document.body.style.overflow = '';
}
function closeModalOnBg(e) {
  if (e.target === document.getElementById('paintingModal')) closeModal();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>
