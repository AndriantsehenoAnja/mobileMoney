CREATE TABLE operateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(50) NOT NULL,
    commission NUMERIC DEFAULT 0, 
    est_notre_operateur INTEGER DEFAULT 0 
);

CREATE TABLE prefixes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe TEXT UNIQUE NOT NULL,
    operateur_id INTEGER NOT NULL,
    FOREIGN KEY(operateur_id) REFERENCES operateurs(id)
);

CREATE TABLE clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    numero TEXT UNIQUE NOT NULL,
    prefixe_id INTEGER,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(prefixe_id) REFERENCES prefixes(id)
);

CREATE TABLE comptes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER UNIQUE,
    solde NUMERIC DEFAULT 0, -- Remplacé DECIMAL par NUMERIC pour SQLite
    FOREIGN KEY(client_id) REFERENCES clients(id)
);

CREATE TABLE types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL
);

CREATE TABLE baremes_frais (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER,
    montant_min NUMERIC,
    montant_max NUMERIC,
    frais NUMERIC,
    FOREIGN KEY(type_operation_id) REFERENCES types_operations(id)
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER,
    compte_source INTEGER,
    compte_destination INTEGER NULL, -- NULL si numéro externe
    numero_destination TEXT NULL,     -- Utile pour l'envoi vers un autre opérateur
    operateur_destination_id INTEGER NULL, -- Pour la situation des montants par opérateur
    montant NUMERIC,
    frais_base NUMERIC DEFAULT 0,
    frais_commission_externe NUMERIC DEFAULT 0,
    frais_retrait_inclus NUMERIC DEFAULT 0,
    frais_total NUMERIC,
    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(type_operation_id) REFERENCES types_operations(id),
    FOREIGN KEY(compte_source) REFERENCES comptes(id),
    FOREIGN KEY(compte_destination) REFERENCES comptes(id),
    FOREIGN KEY(operateur_destination_id) REFERENCES operateurs(id)
);

-- ========================================================
-- 1. OPERATEURS & PREFIXES
-- Notre opérateur : Telma (033, 037)
-- Autres opérateurs : Orange (032), Airtel (034)
-- ========================================================
INSERT INTO operateurs (nom, commission, est_notre_operateur) VALUES 
('Telma (Nous)', 0.0, 1),      -- id: 1 (Notre opérateur)
('Orange', 2.5, 0),            -- id: 2 (2.5% de commission)
('Airtel', 3.0, 0);            -- id: 3 (3.0% de commission)

INSERT INTO prefixes (prefixe, operateur_id) VALUES 
('033', 1),
('037', 1),
('032', 2),
('034', 3);

-- ========================================================
-- 2. TYPES D'OPERATIONS
-- ========================================================
INSERT INTO types_operations (id, nom) VALUES 
(1, 'Dépôt'),
(2, 'Retrait'),
(3, 'Transfert');

-- ========================================================
-- 3. BAREME DES FRAIS (Selon le tableau de l'énoncé)
-- ========================================================
-- Frais pour les Retraits / Transferts de base
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES 
(3, 100, 1000, 50),
(3, 1001, 5000, 50),
(3, 5001, 10000, 100),
(3, 10001, 25000, 200),
(3, 25001, 50000, 400),
(3, 50001, 100000, 800),
(3, 100010, 250000, 1500),
(3, 250001, 500000, 1500),
(3, 500001, 1000000, 2500),
(3, 1000001, 2000000, 3000);

-- Même barème appliqué pour les retraits (type_operation_id = 2)
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES 
(2, 100, 1000, 50),
(2, 1001, 5000, 50),
(2, 5001, 10000, 100),
(2, 10001, 25000, 200),
(2, 25001, 50000, 400),
(2, 50001, 100000, 800),
(2, 100010, 250000, 1500),
(2, 250001, 500000, 1500),
(2, 500001, 1000000, 2500),
(2, 1000001, 2000000, 3000);

insert into baremes_frais (type_operation_id, montant_min, montant_max, frais) values 

(1, 0, 2000000, 0);
-- ========================================================
-- 4. CLIENTS & COMPTES
-- ========================================================
-- Clients internes (Telma)
INSERT INTO clients (nom, numero, prefixe_id) VALUES 
('Rakoto Jean', '0331122233', 1),    -- id: 1
('Rabe Paul', '0379988877', 2),      -- id: 2
('Rasoa Marie', '0334455566', 1);    -- id: 3

-- Comptes clients correspondants
INSERT INTO comptes (client_id, solde) VALUES 
(1, 150000), -- Compte Rakoto
(2, 50000),  -- Compte Rabe
(3, 10000);  -- Compte Rasoa

-- ========================================================
-- 5. EXEMPLES DE TRANSACTIONS (Pour tester les vues/dashboards)
-- ========================================================

-- Transaction 1: Transfert interne classique (Rakoto -> Rabe : 20,000 Ar)
-- Frais de base = 200 Ar | Commission externe = 0 | Retrait inclus = 0 | Total = 200 Ar
INSERT INTO transactions (
    type_operation_id, compte_source, compte_destination, numero_destination, 
    operateur_destination_id, montant, frais_base, frais_commission_externe, 
    frais_retrait_inclus, frais_total
) VALUES (
    3, 1, 2, '0379988877', 
    1, 20000, 200, 0, 
    0, 200
);

-- Transaction 2: Transfert interne AVEC option retrait inclus (Rakoto -> Rasoa : 15,000 Ar)
-- Frais de base = 200 Ar | Commission externe = 0 | Frais retrait (15k Ar) = 200 Ar | Total = 400 Ar
INSERT INTO transactions (
    type_operation_id, compte_source, compte_destination, numero_destination, 
    operateur_destination_id, montant, frais_base, frais_commission_externe, 
    frais_retrait_inclus, frais_total
) VALUES (
    3, 1, 3, '0334455566', 
    1, 15000, 200, 0, 
    200, 400
);

-- Transaction 3: Transfert externe vers Orange (Rakoto -> 0321234567 : 50,000 Ar)
-- Frais de base = 400 Ar | Commission Orange (2.5% de 50,000) = 1,250 Ar | Total = 1,650 Ar
INSERT INTO transactions (
    type_operation_id, compte_source, compte_destination, numero_destination, 
    operateur_destination_id, montant, frais_base, frais_commission_externe, 
    frais_retrait_inclus, frais_total
) VALUES (
    3, 1, NULL, '0321234567', 
    2, 50000, 400, 1250, 
    0, 1650
);

-- Transaction 4: Transfert externe vers Airtel (Rabe -> 0340011122 : 10,000 Ar)
-- Frais de base = 100 Ar | Commission Airtel (3% de 10,000) = 300 Ar | Total = 400 Ar
INSERT INTO transactions (
    type_operation_id, compte_source, compte_destination, numero_destination, 
    operateur_destination_id, montant, frais_base, frais_commission_externe, 
    frais_retrait_inclus, frais_total
) VALUES (
    3, 2, NULL, '0340011122', 
    3, 10000, 100, 300, 
    0, 400
);
