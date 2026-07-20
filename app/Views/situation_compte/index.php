<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Situation des Comptes</title>
    <style>
        .table-situation { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-situation th, .table-situation td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .table-situation th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .badge-solde { font-weight: bold; color: #1e7e34; }
    </style>
</head>
<body>

    <h1>Situation Récapitulative des Comptes Clients</h1>

    <?php if (!empty($clientsSituation)): ?>
        <table class="table-situation">
            <thead>
                <tr>
                    <th>N° Compte</th>
                    <th>Nom du Client</th>
                    <th>Numéro Mobile</th>
                    <th>Date d'Inscription</th>
                    <th class="text-right">Solde Actuel</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientsSituation as $row): ?>
                    <tr>
                        <!-- Syntaxe Objet (->) utilisée ici -->
                        <td>#<?= esc($row->compte_id ?? 'N/A') ?></td>
                        <td><?= esc($row->nom) ?></td>
                        <td>
                            <?= esc($row->prefixe ?? '') ?> <?= esc($row->numero) ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($row->date_creation)) ?></td>
                        <td class="text-right badge-solde">
                            <!-- Gestion du solde si NULL (au cas où le client n'a pas encore de compte créé) -->
                            <?= number_format($row->solde ?? 0, 2, ',', ' ') ?> AR
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun compte ou client enregistré pour le moment.</p>
    <?php endif; ?>

</body>
</html>