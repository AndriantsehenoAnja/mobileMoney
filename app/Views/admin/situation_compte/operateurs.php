<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <h2><i class="bi bi-building me-2"></i>Situation des Montants par Opérateur</h2>
    <p class="text-muted">Récapitulatif des flux financiers et montants cumulés à envoyer aux opérateurs partenaires.</p>

    <div class="card shadow-sm mt-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Montants à envoyer par opérateur tiers</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Opérateur</th>
                        <th>Commission (%)</th>
                        <th>Nombre de Transferts</th>
                        <th>Cumul Montants Transférés</th>
                        <th>Commissions Externe Perçues</th>
                        <th>Total Global Transmis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($situationOperateurs)): ?>
                        <?php 
                            $totalEnvoyeGlobal = 0;
                            $totalCommissionGlobal = 0;
                        ?>
                        <?php foreach ($situationOperateurs as $op): ?>
                            <?php 
                                $totalEnvoyeGlobal += $op['total_montant_envoye'];
                                $totalCommissionGlobal += $op['total_commission'];
                            ?>
                            <tr>
                                <td><strong><?= esc($op['operateur_nom']) ?></strong></td>
                                <td><span class="badge bg-info text-dark"><?= number_format($op['commission'], 2) ?> %</span></td>
                                <td><?= $op['nombre_transactions'] ?></td>
                                <td class="fw-bold"><?= number_format($op['total_montant_envoye'], 2) ?> Ar</td>
                                <td class="text-success"><?= number_format($op['total_commission'], 2) ?> Ar</td>
                                <td class="fw-bold text-primary">
                                    <?= number_format($op['total_montant_envoye'] + $op['total_commission'], 2) ?> Ar
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">TOTAL :</td>
                            <td><?= number_format($totalEnvoyeGlobal, 2) ?> Ar</td>
                            <td class="text-success"><?= number_format($totalCommissionGlobal, 2) ?> Ar</td>
                            <td class="text-primary"><?= number_format($totalEnvoyeGlobal + $totalCommissionGlobal, 2) ?> Ar</td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Aucun opérateur partenaire configuré.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>