<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <h2><i class="bi bi-graph-up-arrow me-2"></i>Situation des Gains via les Frais (V2)</h2>
    <p class="text-muted">Analyse détaillée de la ventilation des gains générés par les transactions.</p>

    <!-- Résumé rapide -->
    <div class="row my-4">
        <div class="col-md-4">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Gains Notre Réseau</h5>
                    <h3><?= number_format($totalNotreReseau, 2) ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Gains Autres Opérateurs</h5>
                    <h3><?= number_format($totalAutresOperateurs, 2) ?> Ar</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Grand Total Gains</h5>
                    <h3><?= number_format($grandTotal, 2) ?> Ar</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1 : Notre Réseau -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">1. Gains générés sur notre réseau</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Type d'Opération</th>
                        <th>Total Frais de Base</th>
                        <th>Frais de Retrait Inclus</th>
                        <th>Gain Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($gainsNotreReseau)): ?>
                        <?php foreach ($gainsNotreReseau as $g): ?>
                            <tr>
                                <td><strong><?= esc($g['type_nom']) ?></strong></td>
                                <td><?= number_format($g['total_frais_base'], 2) ?> Ar</td>
                                <td><?= number_format($g['total_frais_retrait_inclus'], 2) ?> Ar</td>
                                <td class="fw-bold text-success"><?= number_format($g['gain_total'], 2) ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Aucune transaction enregistrée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2 : Autres Opérateurs -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">2. Gains générés sur les autres opérateurs</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Opérateur Destinataire</th>
                        <th>Frais de Base Collectés</th>
                        <th>Commission Externe (%) Collectée</th>
                        <th>Gain Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($gainsAutresOperateurs)): ?>
                        <?php foreach ($gainsAutresOperateurs as $ga): ?>
                            <tr>
                                <td><strong><?= esc($ga['operateur_nom']) ?></strong></td>
                                <td><?= number_format($ga['total_frais_base'], 2) ?> Ar</td>
                                <td><?= number_format($ga['total_commission_externe'], 2) ?> Ar</td>
                                <td class="fw-bold text-warning"><?= number_format($ga['gain_total'], 2) ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Aucun transfert inter-opérateur réalisé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>