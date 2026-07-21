<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container mt-4" style="max-width: 600px;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Modifier la commission : <?= esc($operateur['nom']) ?></h4>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('admin/operateur/update/' . $operateur['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="nom" class="form-label">Nom de l'opérateur</label>
                    <input type="text" class="form-disabled form-control" id="nom" value="<?= esc($operateur['nom']) ?>" disabled>
                </div>

                <div class="mb-3">
                    <label for="commission" class="form-label">Pourcentage de commission (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="commission" name="commission" value="<?= esc($operateur['commission']) ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Ce pourcentage s'appliquera en sus sur les transferts vers cet opérateur.</div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('admin/operateur') ?>" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>