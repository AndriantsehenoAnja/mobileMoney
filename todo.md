# To do list

[] Anja|00h00|Création du projet CodeIgniter + configuration SQLite + import base.sql|app/Config/*, base.sql 
[] Anja|00h00|Créer les modèles PrefixModel, TypeOperationModel, BaremeFraisModel|app/Models/*
[] Anja|00h00|Développer la gestion des préfixes (liste, ajout, modification, suppression)|Admin/PrefixController.php, admin/prefixes/*
[] Anja|00h00|Développer la gestion des types d'opérations|Admin/TypeOperationController.php, admin/types/*
[] Anja|00h00|Développer la gestion des barèmes de frais|Admin/BaremeController.php, admin/baremes/*
[] Anja|00h00|Développer la situation des comptes clients|Admin/CompteController.php, admin/comptes/index.php
[] Anja|00h00|Développer la situation des gains (somme des frais retrait/transfert)|Admin/GainController.php, admin/gains/index.php
[] Anja|00h00|Créer le menu administrateur (/admin)|Routes.php, admin/layout.php


[] Bryan|00h00|Créer les modèles ClientModel, CompteModel, TransactionModel|app/Models/*
[] Bryan|00h00|Développer le login automatique avec numéro de téléphone|Client/AuthController.php, client/login.php
[] Bryan|00h00|Développer l'affichage du solde|Client/CompteController.php, client/solde.php
[] Bryan|00h00|Développer le dépôt automatique|Client/DepotController.php, client/depot.php
[] Bryan|00h00|Développer le retrait avec calcul des frais|Client/RetraitController.php, client/retrait.php
[] Bryan|00h00|Développer le transfert entre clients avec calcul des frais|Client/TransfertController.php, client/transfert.php
[] Bryan|00h00|Développer l'historique des transactions|Client/HistoriqueController.php, client/historique.php

Commun|00h00|Création des données de test (préfixes, barèmes, types d'opérations)|base.sql
Commun|00h00|Tests fonctionnels et correction des bugs|Tout le projet
Commun|00h00|Création du TAG v1 + mise à jour Taches.md|Git + Taches.md