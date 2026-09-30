-- 01-extensions.sql
-- Extensions PostgreSQL nécessaires à Nujum Al-Huda Center

-- Extension pour la recherche plein texte
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- Extension pour les UUID (bien que nous utilisions ULID)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Extension pour les données non structurées
CREATE EXTENSION IF NOT EXISTS hstore;

-- Extension pour les types géométriques (carte de localisation)
CREATE EXTENSION IF NOT EXISTS postgis;

-- Collation française pour le tri correct des chaînes accentuées
CREATE COLLATION IF NOT EXISTS fr_FR (
    LOCALE = 'fr_FR.UTF-8'
);

-- Collation arabe
CREATE COLLATION IF NOT EXISTS ar_SA (
    LOCALE = 'ar_SA.UTF-8'
);

-- Fonction helper pour convertir ULID en timestamp
CREATE OR REPLACE FUNCTION ulid_to_timestamp(ulid TEXT)
RETURNS TIMESTAMPTZ AS $$
DECLARE
    timestamp_part BIGINT;
BEGIN
    -- Les 10 premiers caractères d'un ULID encodent le timestamp
    timestamp_part := (
        SELECT sum(
            power(32, 9 - pos) * 
            position(substring(ulid from pos + 1 for 1) in '0123456789ABCDEFGHJKMNPQRSTVWXYZ') - 1
        )
        FROM generate_series(0, 9) AS pos
    );
    
    RETURN to_timestamp(timestamp_part / 1000.0);
END;
$$ LANGUAGE plpgsql IMMUTABLE;

COMMENT ON FUNCTION ulid_to_timestamp IS 
'Extrait le timestamp UTC d''un ULID (26 caractères)';
