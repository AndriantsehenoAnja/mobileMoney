
CREATE TABLE prefixes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe TEXT UNIQUE NOT NULL
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
    solde DECIMAL(12,2) DEFAULT 0,

    FOREIGN KEY(client_id) REFERENCES clients(id)
);

CREATE TABLE types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL
);

CREATE TABLE baremes_frais (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    type_operation_id INTEGER,

    montant_min DECIMAL(12,2),
    montant_max DECIMAL(12,2),

    frais DECIMAL(12,2),

    FOREIGN KEY(type_operation_id)
        REFERENCES types_operations(id)
);

CREATE TABLE transactions (

    id INTEGER PRIMARY KEY AUTOINCREMENT,

    type_operation_id INTEGER,

    compte_source INTEGER,

    compte_destination INTEGER,

    montant DECIMAL(12,2),

    frais DECIMAL(12,2),

    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(type_operation_id)
        REFERENCES types_operations(id),

    FOREIGN KEY(compte_source)
        REFERENCES comptes(id),

    FOREIGN KEY(compte_destination)
        REFERENCES comptes(id)
);