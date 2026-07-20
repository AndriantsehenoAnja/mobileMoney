<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Préfixe Externe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <h1 class="mb-4">Nouveau Préfixe Externe</h1>

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

    <form action="<?= base_url('admin/prefixes-externes/add') ?>" method="post" class="card card-body col-md-6">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="operateur_id" class="form-label">Opérateur Externe <span class="text-danger">*</span></label>
            <select name="operateur_id" id="operateur_id" class="form-select" required>
                <option value="">-- Sélectionner un opérateur --</option>
                <?php if (!empty($operateurs)): ?>
                    <?php foreach ($operateurs as $op): ?>
                        <option value="<?= esc($op['id']) ?>" <?= old('operateur_id') == $op['id'] ? 'selected' : '' ?>>
                            <?= esc($op['nom']) ?> (Commission: <?= esc($op['commission']) ?>%)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="prefixe" class="form-label">Préfixe Téléphonique <span class="text-danger">*</span></label>
            <input type="text" 
                   name="prefixe" 
                   id="prefixe" 
                   class="form-control" 
                   placeholder="Ex: 032, 034, +261..." 
                   value="<?= old('prefixe') ?>" 
                   required>
            <div class="form-text">Ce préfixe sera utilisé pour identifier si un numéro appartient à cet opérateur.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Enregistrer</button>
            <a href="<?= base_url('admin/prefixes-externes') ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>

</body>
</html>