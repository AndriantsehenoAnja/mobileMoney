<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Préfixes Operateurs Externes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Préfixes des Opérateurs Externes</h1>
        <a href="<?= base_url('admin/prefixes-externes/add') ?>" class="btn btn-primary">+ Ajouter un préfixe</a>
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
                <th>Préfixe</th>
                <th>Opérateur Associé</th>
                <th>Commission (%)</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($prefixes) && is_array($prefixes)): ?>
                <?php foreach ($prefixes as $p): ?>
                    <tr>
                        <td><?= esc($p['id']) ?></td>
                        <td><span class="badge bg-secondary fs-6"><?= esc($p['prefixe']) ?></span></td>
                        <td><strong><?= esc($p['operateur_nom']) ?></strong></td>
                        <td><?= esc($p['commission']) ?> %</td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/prefixes-externes/delete/' . $p['id']) ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce préfixe ?');">
                               Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">Aucun préfixe externe enregistré.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="<?= base_url('admin') ?>" class="btn btn-secondary">Retour au tableau de bord</a>

</body>
</html>