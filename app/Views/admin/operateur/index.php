<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <h2>Gestion des Commissions Opérateurs</h2>
    <p class="text-muted">Configurez les pourcentages de commission pour les transferts inter-opérateurs.</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom de l'Opérateur</th>
                <th>Type</th>
                <th>Commission (%)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($operateurs as $op): ?>
                <tr>
                    <td><?= $op['id'] ?></td>
                    <td><strong><?= esc($op['nom']) ?></strong></td>
                    <td>
                        <?php if ($op['est_notre_operateur']): ?>
                            <span class="badge bg-primary">Notre Réseau</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Opérateur Externe</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($op['commission'], 2) ?> %</td>
                    <td>
                        <?php if (!$op['est_notre_operateur']): ?>
                            <a href="<?= base_url('admin/operateur/edit/' . $op['id']) ?>" class="btn btn-sm btn-warning">
                                Modifier %
                            </a>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>