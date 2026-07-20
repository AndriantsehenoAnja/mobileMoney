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

INSERT INTO operateurs (id, nom, commission, est_notre_operateur) VALUES
(1, 'Notre Reseau', 0.00, 1),
(2, 'Orange', 2.00, 0),   -- Ex: 2% ou montant fixe
(3, 'Telma', 2.50, 0),
(4, 'Airtel', 2.00, 0);

INSERT INTO prefixes (id, prefixe, operateur_id) VALUES
(1, '034', 1), -- Notre réseau
(2, '038', 1), -- Notre réseau
(3, '032', 2), -- Orange
(4, '033', 3), -- Telma
(5, '037', 4); -- Airtel

INSERT INTO clients (id, nom, numero, prefixe_id, date_creation) VALUES
(1, 'Rabe Jean', '0341234567', 1, '2026-01-10 09:00:00'),
(2, 'Rakoto Marie', '0389876543', 2, '2026-01-15 10:30:00'),
(3, 'Rakoto bob', '0389876542', 2, '2026-01-15 10:30:00'),
(4, 'Rakoto Samuel', '0389876541', 2, '2026-01-15 10:30:00');


INSERT INTO comptes (id, client_id, solde) VALUES
(1, 1, 250000.00),
(2, 2, 120000.00);

INSERT INTO comptes (id, client_id, solde) VALUES
(3, 3, 50000.00),
(4, 4, 75000.00);

INSERT INTO types_operations (id, nom) VALUES
(1, 'Depot'),
(2, 'Retrait'),
(3, 'Transfert Interne'),
(4, 'Transfert Inter-operateur');

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

INSERT INTO transactions 
(type_operation_id, compte_source, compte_destination, numero_destination, operateur_destination_id, montant, frais_base, frais_commission_externe, frais_retrait_inclus, frais_total, date_transaction) 
VALUES
-- 1. Dépôt initial sur le compte 1
(1, 1, 1, '0341234567', 1, 300000.00, 0.00, 0.00, 0.00, 0.00, '2026-02-01 08:00:00'),

-- 2. Transfert interne : Client 1 (Rabe) -> Client 2 (Rakoto)
(3, 1, 2, '0389876543', 1, 50000.00, 200.00, 0.00, 0.00, 200.00, '2026-02-02 14:15:00'),

-- 3. Transfert externe : Client 1 -> Numéro Telma (compte_destination NULL)
(4, 1, NULL, '0331122334', 3, 20000.00, 300.00, 500.00, 0.00, 800.00, '2026-02-03 11:00:00'),

-- 4. Transfert externe : Client 2 -> Numéro Orange (compte_destination NULL)
(4, 2, NULL, '0329988776', 2, 150000.00, 1500.00, 2000.00, 0.00, 3500.00, '2026-02-04 16:45:00'),

-- 5. Retrait : Client 1
(2, 1, NULL, '0341234567', 1, 30000.00, 500.00, 0.00, 0.00, 500.00, '2026-02-05 09:20:00');