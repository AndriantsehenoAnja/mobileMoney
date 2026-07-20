<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Opérateur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <h1 class="mb-4">Modifier l'Opérateur : <?= esc($operateur['nom']) ?></h1>

    <!-- Affichage des erreurs de validation -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('admin/operateurs/edit/' . $operateur['id']) ?>" method="post" class="card card-body col-md-6">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="nom" class="form-label">Nom de l'opérateur <span class="text-danger">*</span></label>
            <input type="text" 
                   name="nom" 
                   id="nom" 
                   class="form-control" 
                   value="<?= old('nom', $operateur['nom']) ?>" 
                   required>
        </div>

        <div class="mb-3">
            <label for="commission" class="form-label">Commission (%)</label>
            <input type="number" 
                   step="0.01" 
                   min="0" 
                   name="commission" 
                   id="commission" 
                   class="form-control" 
                   value="<?= old('commission', $operateur['commission']) ?>">
            <div class="form-text">Pourcentage supplémentaire appliqué lors des transferts externes.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="<?= base_url('admin/operateurs') ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>

</body>
</html>