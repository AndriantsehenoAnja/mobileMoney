-- ==========================================
-- SCRIPT DATABASE V2 : Mobile Money App
-- ==========================================

DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS baremes_frais;
DROP TABLE IF EXISTS types_operations;
DROP TABLE IF EXISTS comptes;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS prefixes;
DROP TABLE IF EXISTS operateurs;

-- 1. Table Operateurs
-- Le champ 'commission' exprime le pourcentage de commission pour les transferts vers les autres operateurs (ex: 2.00 = 2%)
CREATE TABLE operateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(50) NOT NULL,
    commission NUMERIC DEFAULT 0.00, -- Commission en pourcentage (%)
    est_notre_operateur INTEGER DEFAULT 0 
);

-- 2. Table Prefixes
CREATE TABLE prefixes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe TEXT UNIQUE NOT NULL,
    operateur_id INTEGER NOT NULL,
    FOREIGN KEY(operateur_id) REFERENCES operateurs(id) ON DELETE CASCADE
);

-- 3. Table Clients
CREATE TABLE clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    numero TEXT UNIQUE NOT NULL,
    prefixe_id INTEGER,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(prefixe_id) REFERENCES prefixes(id)
);

-- 4. Table Comptes
CREATE TABLE comptes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER UNIQUE,
    solde NUMERIC DEFAULT 0,
    FOREIGN KEY(client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- 5. Table Types d'Operations
CREATE TABLE types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL
);

-- 6. Table Baremes de Frais
CREATE TABLE baremes_frais (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER,
    montant_min NUMERIC,
    montant_max NUMERIC,
    frais NUMERIC,
    FOREIGN KEY(type_operation_id) REFERENCES types_operations(id)
);

-- 7. Table Transactions
CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER,
    compte_source INTEGER,
    compte_destination INTEGER NULL,       -- NULL si numero externe
    numero_destination TEXT NULL,          -- Numéro cible
    operateur_destination_id INTEGER NULL, -- Pour la situation des montants par operateur
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

-- ==========================================
-- INSERTION DES DONNEES DE TEST (V2)
-- ==========================================

-- Operateurs (% de commission explicite pour les autres operateurs)
INSERT INTO operateurs (id, nom, commission, est_notre_operateur) VALUES
(1, 'Notre Reseau', 0.00, 1),
(2, 'Orange', 2.00, 0),   -- 2.00% de commission
(3, 'Telma', 2.50, 0),    -- 2.50% de commission
(4, 'Airtel', 2.00, 0);   -- 2.00% de commission

-- Prefixes
INSERT INTO prefixes (id, prefixe, operateur_id) VALUES
(1, '034', 1), -- Notre réseau
(2, '038', 1), -- Notre réseau
(3, '032', 2), -- Orange
(4, '033', 3), -- Telma
(5, '037', 4); -- Airtel

-- Clients
INSERT INTO clients (id, nom, numero, prefixe_id, date_creation) VALUES
(1, 'Rabe Jean', '0341234567', 1, '2026-01-10 09:00:00'),
(2, 'Rakoto Marie', '0389876543', 2, '2026-01-15 10:30:00'),
(3, 'Rakoto Bob', '0389876542', 2, '2026-01-15 10:30:00'),
(4, 'Rakoto Samuel', '0389876541', 2, '2026-01-15 10:30:00');

-- Comptes
INSERT INTO comptes (id, client_id, solde) VALUES
(1, 1, 250000.00),
(2, 2, 120000.00),
(3, 3, 50000.00),
(4, 4, 75000.00);

-- Types d'operations
INSERT INTO types_operations (id, nom) VALUES
(1, 'Depot'),
(2, 'Retrait'),
(3, 'Transfert Interne'),
(4, 'Transfert Inter-operateur');

-- Baremes de frais
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES
-- Retrait
(2, 1000.00, 50000.00, 500.00),
(2, 50001.00, 500000.00, 1500.00),

-- Transfert Interne
(3, 1000.00, 100000.00, 200.00),
(3, 100001.00, 1000000.00, 1000.00),

-- Transfert Inter-opérateur
(4, 1000.00, 100000.00, 300.00),
(4, 100001.00, 1000000.00, 1500.00);

-- Transactions de test
INSERT INTO transactions 
(type_operation_id, compte_source, compte_destination, numero_destination, operateur_destination_id, montant, frais_base, frais_commission_externe, frais_retrait_inclus, frais_total, date_transaction) 
VALUES
(1, 1, 1, '0341234567', 1, 300000.00, 0.00, 0.00, 0.00, 0.00, '2026-02-01 08:00:00'),
(3, 1, 2, '0389876543', 1, 50000.00, 200.00, 0.00, 0.00, 200.00, '2026-02-02 14:15:00'),
(4, 1, NULL, '0331122334', 3, 20000.00, 300.00, 500.00, 0.00, 800.00, '2026-02-03 11:00:00'),
(4, 2, NULL, '0329988776', 2, 150000.00, 1500.00, 3000.00, 0.00, 4500.00, '2026-02-04 16:45:00'),
(2, 1, NULL, '0341234567', 1, 30000.00, 500.00, 0.00, 0.00, 500.00, '2026-02-05 09:20:00');