<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Opérateurs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Opérateurs Externes</h1>
        <a href="<?= base_url('admin/operateurs/add') ?>" class="btn btn-primary">+ Ajouter un opérateur</a>
    </div>

    <!-- Alertes -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Commission (%)</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($operateurs) && is_array($operateurs)): ?>
                <?php foreach ($operateurs as $op): ?>
                    <tr>
                        <td><?= esc($op['id']) ?></td>
                        <td><strong><?= esc($op['nom']) ?></strong></td>
                        <td><?= esc($op['commission']) ?> %</td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/operateurs/edit/' . $op['id']) ?>" class="btn btn-sm btn-warning">Éditer</a>
                            <a href="<?= base_url('admin/operateurs/delete/' . $op['id']) ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet opérateur ?');">
                               Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">Aucun opérateur trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="<?= base_url('admin') ?>" class="btn btn-secondary">Retour au tableau de bord</a>

</body>
</html>