# To do list

## V1

[x] Anja|00h00|Création du projet CodeIgniter + configuration SQLite + import base.sql|app/Config/*, base.sql|09:30 
[x] Anja|00h00|Créer les modèles PrefixModel, TypeOperationModel, BaremeFraisModel|app/Models/*|10:00
[x] Anja|00h00|Développer la gestion des préfixes (liste, ajout, modification, suppression)|Admin/PrefixController.php, admin/prefixes/*|11:00
[x] Anja|00h00|Développer la gestion des types d'opérations|Admin/TypeOperationController.php, admin/types/*|12:00
[x] Anja|00h00|Développer la gestion des barèmes de frais|Admin/BaremeController.php, admin/baremes/*|13:30
[x] Anja|00h00|Développer la situation des comptes clients|Admin/CompteController.php, admin/comptes/index.php|14:30
[x] Anja|00h00|Développer la situation des gains (somme des frais retrait/transfert)|Admin/GainController.php, admin/gains/index.php|16:00
[] Anja|00h00|Créer le menu administrateur (/admin)|Routes.php, admin/layout.php|16:30


[x] Bryan|00h00|Créer les modèles ClientModel, CompteModel, TransactionModel|app/Models/*
[x] Bryan|00h00|Développer le login automatique avec numéro de téléphone|Client/AuthController.php, client/login.php
[x] Bryan|00h00|Développer l'affichage du solde|Client/ClientController.php, client/solde.php
[x] Bryan|00h00|Développer le dépôt automatique|Client/DepotController.php, client/depot.php
[x] Bryan|00h00|Développer le retrait avec calcul des frais|Client/RetraitController.php, client/retrait.php
[x] Bryan|00h00|Développer le transfert entre clients avec calcul des frais|Client/TransfertController.php, client/transfert.php
[x] Bryan|00h00|Développer l'historique des transactions|Client/HistoriqueController.php, client/historique.php

Commun|00h00|Création des données de test (préfixes, barèmes, types d'opérations)|base.sql
Commun|00h00|Tests fonctionnels et correction des bugs|Tout le projet
Commun|00h00|Création du TAG v1 + mise à jour Taches.md|Git + Taches.md



### 🛠️ Étudiant 1 - Partie Administrateur

#### 1. Migration base de données V2
**Fichiers :** `base.sql`

- [ ] Ajouter table `operateurs`
- [ ] Ajouter table `prefixesOperateurExterne`
- [ ] Ajouter table `redevances_operateurs`
- [ ] Ajouter table `groupes_transferts`
- [ ] Modifier table `clients` (ajout `prefixe_operateur_externe_id`)
- [ ] Modifier table `transactions` (ajout champs V2)
- [ ] Ajouter données de test

#### 2. Créer les modèles
**Fichiers :** `app/Models/`

- [ ] Créer `OperateurModel.php`
- [ ] Créer `PrefixeExterneModel.php`
- [ ] Créer `RedevanceModel.php`
- [ ] Créer `GroupeTransfertModel.php`

#### 3. Gestion des opérateurs externes (CRUD)
**Fichiers :** `AdminController.php`, `app/Views/admin/operateurs/`

- [ ] Créer méthode `operateurs()` dans `AdminController`
- [ ] Créer méthode `operateurAdd()` dans `AdminController`
- [ ] Créer méthode `operateurEdit($id)` dans `AdminController`
- [ ] Créer méthode `operateurDelete($id)` dans `AdminController`
- [ ] Créer vue `admin/operateurs/index.php` (liste)
- [ ] Créer vue `admin/operateurs/add.php` (formulaire)
- [ ] Créer vue `admin/operateurs/edit.php` (formulaire)

#### 4. Gestion des préfixes externes
**Fichiers :** `AdminController.php`, `app/Views/admin/prefixes_externes/`

- [ ] Créer méthode `prefixesExternes()` dans `AdminController`
- [ ] Créer méthode `prefixeExterneAdd()` dans `AdminController`
- [ ] Créer méthode `prefixeExterneDelete($id)` dans `AdminController`
- [ ] Créer vue `admin/prefixes_externes/index.php`
- [ ] Créer vue `admin/prefixes_externes/add.php`

#### 5. Page "Situation gains par opérateur"
**Fichiers :** `SituationCompteController.php`, `app/Views/situation_compte/`

- [ ] Modifier `SituationCompteController::getGainTotalParType()` pour séparer par opérateur
- [ ] Créer méthode `gainsParOperateur()` dans `SituationCompteController`
- [ ] Créer vue `situation_compte/gains_par_operateur.php`
- [ ] Afficher : Gains NOTRE opérateur vs Autres opérateurs
- [ ] Ajouter filtres par période

#### 6. Page "Montants à envoyer aux opérateurs"
**Fichiers :** `AdminController.php`, `app/Views/admin/redevances/`

- [ ] Créer méthode `redevances()` dans `AdminController`
- [ ] Créer méthode `redevanceMarquerPaye($id)` dans `AdminController`
- [ ] Créer vue `admin/redevances/index.php`
- [ ] Afficher : dû, payé, solde par opérateur
- [ ] Ajouter bouton "Marquer comme payé"

#### 7. Routes Admin
**Fichiers :** `Routes.php`

```php
// Ajouter dans le group admin
$routes->group('admin', function($routes) {
    // ... routes existantes ...
    
    // Opérateurs
    $routes->get('operateurs', 'AdminController::operateurs');
    $routes->get('operateurs/add', 'AdminController::operateurAdd');
    $routes->post('operateurs/create', 'AdminController::operateurCreate');
    $routes->get('operateurs/edit/(:num)', 'AdminController::operateurEdit/$1');
    $routes->post('operateurs/update/(:num)', 'AdminController::operateurUpdate/$1');
    $routes->get('operateurs/delete/(:num)', 'AdminController::operateurDelete/$1');
    
    // Préfixes externes
    $routes->get('prefixes-externes', 'AdminController::prefixesExternes');
    $routes->get('prefixes-externes/add', 'AdminController::prefixeExterneAdd');
    $routes->post('prefixes-externes/create', 'AdminController::prefixeExterneCreate');
    $routes->get('prefixes-externes/delete/(:num)', 'AdminController::prefixeExterneDelete/$1');
    
    // Redevances
    $routes->get('redevances', 'AdminController::redevances');
    $routes->get('redevances/payer/(:num)', 'AdminController::redevanceMarquerPaye/$1');
    
    // Gains par opérateur
    $routes->get('gains-operateurs', 'SituationCompteController::gainsParOperateur');
});
```



## Clients

| # | Tâche | Statut |
|---|-------|--------|
| 1 | Option "Frais inclus" pour retrait | ⬜ |
| 2 | Transfert multiple vers plusieurs numéros | ⬜ |

---

## 🔨 Tâches détaillées

### 1. Option "Frais inclus" pour retrait

#### 📁 Fichiers concernés
- `app/Controllers/ClientController.php`
- `app/Models/TransactionModel.php`
- `app/Views/Client/retrait.php`
- `app/Views/Client/historique.php`

#### 📝 Étapes

**1.1 Modifier `TransactionModel.php`**
- [ ] Ajouter `frais_inclus` dans `$allowedFields`
```php
protected $allowedFields = [
    'type_operation_id',
    'compte_source',
    'compte_destination',
    'montant',
    'frais',
    'date_transaction',
    'frais_inclus'  // ✅ AJOUTER
];