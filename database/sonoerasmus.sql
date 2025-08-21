--DROP TABLE IF EXISTS Partecipazione CASCADE;
--DROP TABLE IF EXISTS Evento CASCADE;
--DROP TABLE IF EXISTS EsperienzaErasmus CASCADE;
--DROP TABLE IF EXISTS Universita CASCADE;
--DROP TABLE IF EXISTS Utente CASCADE;

CREATE TABLE Utente (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50),
    cognome VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    username VARCHAR(50) UNIQUE,
    data_nascita DATE,
    nazionalita VARCHAR(50),
    foto VARCHAR(255),
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

-- Tabla principal de universidades
CREATE TABLE IF NOT EXISTS universities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  city VARCHAR(100) NOT NULL,
  website VARCHAR(200),
  email VARCHAR(200),
  phone VARCHAR(40),
  cover_image VARCHAR(200),      
  short_desc VARCHAR(300),      
  long_desc MEDIUMTEXT,         
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO universities (name, slug, city, website, email, phone, cover_image, short_desc, long_desc) VALUES
('Università degli Studi di Padova','unipd','Padova','https://www.unipd.it','urp@unipd.it','+39 049 827 5111','/assets/img/calendar.jpg',
 'Ateneo storico del 1222, cuore della vita Erasmus in Veneto.',
 '<p>L’Università di Padova è uno dei più antichi atenei del mondo. Ampia offerta formativa, ricerca di eccellenza e una città a misura di studente.</p>'),
('Università di Firenze','unifi','Firenze','https://www.unifi.it','info@unifi.it','+39 055 27571','/assets/img/calendar.jpg',
 'Vivere l’Erasmus tra arte, cultura e innovazione.',
 '<p>L’Università di Firenze offre numerosi corsi internazionali e un campus diffuso nel centro storico.</p>'),
('Sapienza – Università di Roma','sapienza','Roma','https://www.uniroma1.it','segreteria@sapienza.it','+39 06 49911','/assets/calendar.jpg',
 'Il più grande ateneo europeo nel cuore della capitale.',
 '<p>Sapienza è un polo di eccellenza con migliaia di studenti internazionali e servizi dedicati all’accoglienza.</p>'),
('Università di Udine','uniud','Udine','https://www.uniud.it','info@uniud.it','+39 0432 556111','/assets/img/calendar.jpg',
 'Qualità della vita e didattica su misura nel Friuli.',
 '<p>UniUD è giovane e dinamica, perfetta per chi cerca community e contatto con il territorio.</p>');

CREATE TABLE IF NOT EXISTS cosa_fare (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(160) NOT NULL,
  city         VARCHAR(100),
  category     VARCHAR(60),
  period       VARCHAR(60),
  image        VARCHAR(255) NOT NULL, -- p.ej. /assets/img/museo.jpg
  short_desc   VARCHAR(300),
  long_desc    MEDIUMTEXT,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cosa_fare (title,city,category,period,image,short_desc,long_desc) VALUES
('Visita ai musei civici','Padova','musei','Tutto l’anno','/assets/img/museo.jpg',
 'Scopri la collezione storica cittadina con ingresso scontato.',
 '<p>I musei civici offrono collezioni d’arte e storia con riduzioni per studenti Erasmus.</p>'),
('Gita sui Colli Euganei','Veneto','gite','Stagionale','/assets/img/gite.jpg',
 'Una giornata tra sentieri, borghi e degustazioni.',
 '<p>Percorsi facili, cantine locali e paesaggi verdi perfetti in primavera.</p>'),
('Aperitivo tipico','Centro storico','cibo','Ogni settimana','/assets/img/streetfood.jpg',
 'Spritz e cicchetti: rito serale veneto.',
 '<p>Ideale per socializzare: prova i bacari e i cicchetti tradizionali.</p>');

 -- Primero, agregar columna 'role' a la tabla Utente si no existe
ALTER TABLE Utente ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'user';

-- Crear el usuario administrador
-- Nota: La contraseña 'admin' será hasheada con password_hash() de PHP
INSERT INTO Utente (nome, cognome, email, username, password, role) 
VALUES (
    'Admin', 
    'Sistema', 
    'admin@sonoerasmus.it', 
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    'admin'
) ON CONFLICT (username) DO UPDATE SET
    password = EXCLUDED.password,
    role = EXCLUDED.role;
