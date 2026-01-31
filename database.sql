DROP TABLE IF EXISTS codici_backup CASCADE;

CREATE TABLE utenti (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, 
    domanda_sicurezza VARCHAR(255) NOT NULL,
    risposta_sicurezza VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS utenti CASCADE;

CREATE TABLE codici_backup (
    id SERIAL PRIMARY KEY,
    utente_id INTEGER REFERENCES utenti(id) ON DELETE CASCADE,
    codice_hash VARCHAR(255) NOT NULL, 
    usato BOOLEAN DEFAULT FALSE       
);

DROP TABLE IF EXISTS preferiti;

CREATE TABLE preferiti (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES utente(id) ON DELETE CASCADE,
    content_id INTEGER NOT NULL,
    tipo_content VARCHAR(20) NOT NULL CHECK (tipo_content IN ('movie', 'tv')),
    titolo VARCHAR(255) NOT NULL,   
    poster_path VARCHAR(255),      
    data_aggiunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(user_id, content_id, tipo_content)
);

DROP TABLE IF EXISTS watchlist;

CREATE TABLE watchlist (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES utente(id) ON DELETE CASCADE,
    content_id INTEGER NOT NULL,
    tipo_content VARCHAR(20) NOT NULL CHECK (tipo_content IN ('movie', 'tv')),
    titolo VARCHAR(255) NOT NULL, 
    poster_path VARCHAR(255),    
    data_aggiunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(user_id, content_id, tipo_content)
);

DROP TABLE IF EXISTS commenti;

CREATE TABLE commenti (
    id SERIAL PRIMARY KEY,
    id_utente INT NOT NULL,
    id_contenuto INT NOT NULL,
    tipo_contenuto VARCHAR(10) CHECK (tipo_contenuto IN ('movie', 'tv')),
    titolo VARCHAR(255) NOT NULL, 
    testo TEXT NOT NULL,
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_utente FOREIGN KEY (id_utente) REFERENCES utente(id) ON DELETE CASCADE
);

GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "www";
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "www";
