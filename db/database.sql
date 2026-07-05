CREATE TABLE IF NOT EXISTS paintings (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    material VARCHAR(255) NOT NULL,
    width_cm NUMERIC(8, 2) NOT NULL,
    height_cm NUMERIC(8, 2) NOT NULL,
    year SMALLINT,
    price NUMERIC(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'EUR',
    status VARCHAR(20) NOT NULL DEFAULT 'disponible' CHECK (
        status IN ('disponible', 'vendu')
    ),
    description TEXT,
    media_file VARCHAR(255) NOT NULL,
    media_type VARCHAR(10) NOT NULL DEFAULT 'image' CHECK (
        media_type IN ('image', 'video')
    ),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS contacts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    received_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

INSERT INTO
    admin_users (username, password_hash)
VALUES (
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    )
ON CONFLICT (username) DO NOTHING;

-- ── INDEX pour meilleures performances ──────────────────────
CREATE INDEX IF NOT EXISTS idx_paintings_status ON paintings (status);

CREATE INDEX IF NOT EXISTS idx_paintings_created_at ON paintings (created_at DESC);

CREATE INDEX IF NOT EXISTS idx_contacts_is_read ON contacts (is_read);

-- ── VUE UTILE : statistiques rapides ────────────────────────
CREATE OR REPLACE VIEW v_stats AS
SELECT
    COUNT(*) AS total_oeuvres,
    COUNT(*) FILTER (
        WHERE
            status = 'disponible'
    ) AS disponibles,
    COUNT(*) FILTER (
        WHERE
            status = 'vendu'
    ) AS vendues,
    COALESCE(
        SUM(price) FILTER (
            WHERE
                status = 'vendu'
        ),
        0
    ) AS chiffre_affaires
FROM paintings;