CREATE TABLE operateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    commission NUMERIC DEFAULT 0     -- % supplémentaire pour transfert externe
);

CREATE TABLE prefixes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe TEXT UNIQUE NOT NULL
);

CREATE TABLE prefixesOperateurExterne(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operateur_id INTEGER,
    prefixe TEXT UNIQUE NOT NULL,
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
    compte_destination INTEGER,
    montant NUMERIC,
    frais NUMERIC,
    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- NOUVEAU CHAMP POUR ENVOI MULTIPLE
    groupe_transfert_id INTEGER DEFAULT NULL,  -- NULL si transfert simple
    
    -- AUTRES CHAMPS V2
    frais_inclus INTEGER DEFAULT 0,
    operateur_destination_id INTEGER DEFAULT NULL,
    commission_inter_operateur NUMERIC DEFAULT 0,
    est_inter_operateur INTEGER DEFAULT 0,
    
    FOREIGN KEY(type_operation_id) REFERENCES types_operations(id),
    FOREIGN KEY(compte_source) REFERENCES comptes(id),
    FOREIGN KEY(compte_destination) REFERENCES comptes(id),
    FOREIGN KEY(groupe_transfert_id) REFERENCES groupes_transferts(id),
    FOREIGN KEY(operateur_destination_id) REFERENCES operateurs(id)
);

CREATE TABLE groupes_transferts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    compte_source INTEGER NOT NULL,          -- Compte de l'expéditeur
    montant_total NUMERIC NOT NULL,          -- Montant total envoyé
    nombre_destinataires INTEGER NOT NULL,   -- Nombre de destinataires
    frais_total NUMERIC DEFAULT 0,           -- Frais totaux prélevés
    commission_total NUMERIC DEFAULT 0,      -- Commission inter-opérateur totale
    date_transfert DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(compte_source) REFERENCES comptes(id)
);

-- 1. Insertion des préfixes téléphoniques
INSERT INTO prefixes (prefixe) VALUES 
('032'), -- Exemple Opérateur A
('033'), -- Exemple Opérateur B
('034'), -- Exemple Opérateur C
('036');    -- International / Autre

-- 2. Insertion des types d'opérations fondamentales
INSERT INTO types_operations (nom) VALUES 
('Depot'),
('Retrait'),
('Transfert');

-- 3. Insertion des clients de test
-- (Associez bien les numéros aux préfixes logiques pour le réalisme)
INSERT INTO clients (nom, numero, prefixe_id) VALUES 
('Jean Dupont', '0321122334', 1),
('Alice Ranoro', '0345566778', 3),
('Marc Smith', '0339988776', 2),
('Fanja Rakoto', '0324455667', 1);

-- 4. Création des comptes (Liés aux clients par client_id)
-- Note : Les soldes sont ici au format NUMERIC. Si vous passez en centimes plus tard, multipliez par 100.
INSERT INTO comptes (client_id, solde) VALUES 
(1, 150000.00), -- Compte de Jean
(2, 25000.50),  -- Compte d'Alice
(3, 0.00),      -- Compte de Marc (Vide)
(4, 500000.00); -- Compte de Fanja

-- 5. Configuration des barèmes de frais (Exemples)
-- Pour les Dépôts (type_operation_id = 1) : Généralement gratuit (0)
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES 
(1, 0.00, 1000000.00, 0.00);

-- Pour les Retraits (type_operation_id = 2) : Frais progressifs ou fixes
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES 
(2, 100.00, 5000.00, 100.00),
(2, 5001.00, 20000.00, 300.00),
(2, 20001.00, 100000.00, 800.00),
(2, 100001.00, 500000.00, 1500.00);

-- Pour les Transferts (type_operation_id = 3) : Frais souvent fixes ou % légers
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES 
(3, 100.00, 10000.00, 50.00),
(3, 10001.00, 500000.00, 200.00);

-- 6. Insertion de transactions passées (Historique de test)
INSERT INTO transactions (type_operation_id, compte_source, compte_destination, montant, frais) VALUES 
-- Exemple 1 : Jean (compte 1) dépose de l'argent sur son propre compte (Pas de destination externe, frais 0)
(1, NULL, 1, 50000.00, 0.00),

-- Exemple 2 : Fanja (compte 4) transfère à Alice (compte 2) un montant de 15 000. Frais de 200 appliqué au source.
(3, 4, 2, 15000.00, 200.00),

-- Exemple 3 : Alice (compte 2) effectue un retrait de 5 000. Frais de 100.
(2, 2, NULL, 5000.00, 100.00);


