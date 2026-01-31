DROP TABLE IF EXISTS utenti CASCADE;

-- 1. TABELLA UTENTI
CREATE TABLE utenti (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    domanda_sicurezza VARCHAR(255) NOT NULL,
    risposta_sicurezza VARCHAR(255) NOT NULL,
    telegram_id VARCHAR(50),
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);