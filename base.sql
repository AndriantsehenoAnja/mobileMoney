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
