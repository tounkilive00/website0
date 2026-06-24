<?php
// Gérer la soumission du formulaire de contact
$formSuccess = false;
$formError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    if ($name && $email && $message) {
        // Sauvegarde dans la base PostgreSQL
        $saved = saveContact($name, $email, $subject, $message);
        // Envoi email (optionnel — nécessite un serveur mail configuré)
        $to      = 'votre@email.com'; // ← remplacez par votre email
        $headers = "From: $email\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";
        $body    = "Nom: $name\nEmail: $email\nSujet: $subject\n\nMessage:\n$message";
        @mail($to, "Contact Galerie — $subject", $body, $headers);
        if ($saved) {
            $formSuccess = true;
        } else {
            $formError = 'Erreur lors de l\'enregistrement. Contactez-nous directement par email.';
        }
    } else {
        $formError = 'Veuillez remplir tous les champs obligatoires.';
    }
}
?>
<!-- ══ CONTACT ══ -->
<section id="contact">
  <div class="section-header">
    <span class="section-tag">Restons en Contact</span>
    <h2 class="section-title">Me Contacter</h2>
    <div class="section-divider"></div>
  </div>
  <div class="contact-wrap">
    <div class="contact-info">
      <h3>Discutons de votre projet</h3>
      <div class="contact-item">
        <div class="contact-item-icon">✉</div>
        <div class="contact-item-text">
          <strong>Email</strong>
          <a href="mailto:votre@email.com">votre@email.com</a>
        </div>
      </div>
      <div class="contact-item">
        <div class="contact-item-icon">📍</div>
        <div class="contact-item-text">
          <strong>Localisation</strong>
          <span>Paris, France</span>
        </div>
      </div>
      <div class="contact-item">
        <div class="contact-item-icon">⏰</div>
        <div class="contact-item-text">
          <strong>Disponibilité</strong>
          <span>Lun–Ven, 9h–18h</span>
        </div>
      </div>

      <div class="social-links">
        <a href="#" class="social-link" id="social-instagram">📸 Instagram</a>
        <a href="#" class="social-link" id="social-facebook">👤 Facebook</a>
        <a href="#" class="social-link" id="social-twitter">🐦 Twitter / X</a>
        <a href="#" class="social-link" id="social-youtube">▶ YouTube</a>
        <a href="#" class="social-link" id="social-tiktok">🎵 TikTok</a>
      </div>
    </div>

    <div class="contact-form">
      <?php if ($formSuccess): ?>
        <div class="form-success" style="display:block;">✓ Votre message a bien été envoyé. Je vous répondrai sous 48h.</div>
      <?php endif; ?>
      <?php if ($formError): ?>
        <div class="form-success" style="display:block;border-color:#c0392b;color:#c0392b;"><?= $formError ?></div>
      <?php endif; ?>
      <form method="POST" action="#contact" id="contactForm">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Nom complet *</label>
            <input type="text" id="name" name="name" placeholder="Jean Dupont" required>
          </div>
          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" placeholder="jean@exemple.com" required>
          </div>
        </div>
        <div class="form-group">
          <label for="subject">Sujet</label>
          <select id="subject" name="subject">
            <option value="Demande d'achat">Demande d'achat</option>
            <option value="Commande personnalisée">Commande personnalisée</option>
            <option value="Information sur une œuvre">Information sur une œuvre</option>
            <option value="Collaboration / Exposition">Collaboration / Exposition</option>
            <option value="Autre">Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label for="message">Message *</label>
          <textarea id="message" name="message" placeholder="Décrivez votre demande…" required></textarea>
        </div>
        <button type="submit" name="contact_submit" class="btn-submit" id="submit-contact">Envoyer le Message</button>
      </form>
    </div>
  </div>
</section>
