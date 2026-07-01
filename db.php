<?php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'galerie_art_db');
define('DB_USER', 'postgres');
define('DB_PASS', 'Tounkarababa201');
define('ADMIN_PASSWORD_HASH', '$2y$12$DANDskQB4bWO8m.WO0dPwuXnKPImmS4TzRfWHxa3Um8PsKoS4Wrhi');

define('UPLOAD_DIR', __DIR__ . '/uploads/');

// ── Connexion PDO PostgreSQL ─────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;options=--client_encoding=UTF8',
            DB_HOST, DB_PORT, DB_NAME
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Affiche un message d'erreur lisible en cas de mauvaise configuration
            die('<div style="font-family:sans-serif;background:#1e1e1e;color:#e74c3c;padding:2rem;margin:2rem;border:1px solid #c0392b;border-radius:4px;">
                <h2>❌ Erreur de connexion PostgreSQL</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p style="color:#8a8078;font-size:0.85rem;">Vérifiez les paramètres dans <code>db.php</code> (DB_HOST, DB_NAME, DB_USER, DB_PASS)</p>
            </div>');
        }
    }
    return $pdo;
}

// ── Fonctions utilitaires ────────────────────────────────────

function getAllPaintings(): array {
    return getDB()
        ->query("SELECT * FROM paintings ORDER BY created_at DESC")
        ->fetchAll();
}

function getPainting(int $id): ?array {
    $stmt = getDB()->prepare("SELECT * FROM paintings WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function saveContact(string $name, string $email, string $subject, string $message): bool {
    try {
        $stmt = getDB()->prepare(
            "INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$name, $email, $subject, $message]);
    } catch (PDOException $e) {
        return false;
    }
}

function getStats(): array {
    return getDB()->query("SELECT * FROM v_stats")->fetch() ?: [
        'total_oeuvres'    => 0,
        'disponibles'      => 0,
        'vendues'          => 0,
        'chiffre_affaires' => 0,
    ];
}

function getUnreadContacts(): int {
    return (int) getDB()->query("SELECT COUNT(*) FROM contacts WHERE is_read = FALSE")->fetchColumn();
}
?>
