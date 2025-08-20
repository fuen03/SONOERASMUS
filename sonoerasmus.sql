DROP TABLE IF EXISTS Partecipazione CASCADE;
DROP TABLE IF EXISTS Evento CASCADE;
DROP TABLE IF EXISTS EsperienzaErasmus CASCADE;
DROP TABLE IF EXISTS Universita CASCADE;
DROP TABLE IF EXISTS Utente CASCADE;

CREATE TABLE Utente (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50),
    cognome VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    username VARCHAR(50) UNIQUE,
    data_nascita DATE,
    nazionalita VARCHAR(50),
    descrizione TEXT
);

CREATE TABLE Universita (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100),
    citta VARCHAR(100),
    nazione VARCHAR(100)
);

CREATE TABLE EsperienzaErasmus (
    id SERIAL PRIMARY KEY,
    utente_id INT REFERENCES Utente(id),
    universita_id INT REFERENCES Universita(id),
    periodo VARCHAR(20) CHECK (periodo IN ('Fatta', 'In corso', 'Futura', 'Curioso'))
);

CREATE TABLE Evento (
    id SERIAL PRIMARY KEY,
    titolo VARCHAR(100),
    descrizione TEXT,
    data_evento DATE,
    luogo VARCHAR(100),
    universita_id INT REFERENCES Universita(id)
);

CREATE TABLE Partecipazione (
    utente_id INT REFERENCES Utente(id),
    evento_id INT REFERENCES Evento(id),
    PRIMARY KEY (utente_id, evento_id)
);

-- Università
INSERT INTO Universita (nome, citta, nazione)
VALUES ('Università di Padova', 'Padova', 'Italia'),
       ('Universitat de Barcelona', 'Barcellona', 'Spagna');

-- Utente
INSERT INTO Utente (nome, cognome, email, password, username, data_nascita, nazionalita)
VALUES ('Maria', 'Trigueros', 'mariatrigueros@gmail.com', '1234', 'maria1', '2000-07-12', 'Spagna');

-- Esperienza Erasmus
INSERT INTO EsperienzaErasmus (utente_id, universita_id, periodo)
VALUES (1, 1, 'In corso');

-- Evento
INSERT INTO Evento (titolo, descrizione, data_evento, luogo, universita_id)
VALUES ('Festa di benvenuto', 'Grande party per nuovi studenti Erasmus', '2025-10-15', 'Campus Padova', 1);
