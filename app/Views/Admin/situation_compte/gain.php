<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>💰 Rapport des Gains et Commissions (V2)</h2>
    <a href="<?= site_url('Admin/situation-compte/operateurs') ?>" class="btn">🔄 Voir montants dus aux opérateurs</a>
</div>

<p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">
    Analyse détaillée de la ventilation des frais perçus (frais de base réseau vs commissions reversées aux opérateurs externes).
</p>

<?php if (!empty($gainTotal)): ?>
    <table>
        <thead>
            <tr>
                <th>Type d'Opération</th>
                <th>Destination</th>
                <th style="text-align: right;">Gains Réseau Local</th>
                <th style="text-align: right;">Commissions Opérateurs Tiers</th>
                <th style="text-align: right;">Total Généré</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $cumulInterne = 0;
                $cumulExterne = 0;
            ?>
            <?php foreach ($gainTotal as $row): ?>
                <?php 
                    $opType = is_array($row) ? ($row['type_operation'] ?? $row['type_nom'] ?? 'N/A') : ($row->type_operation ?? $row->type_nom ?? 'N/A');
                    $gainLocal = is_array($row) ? ($row['gain_interne'] ?? $row['total_gain'] ?? 0) : ($row->gain_interne ?? $row->total_gain ?? 0);
                    $gainComm = is_array($row) ? ($row['gain_commission_externe'] ?? 0) : ($row->gain_commission_externe ?? 0);
                    $reseau = is_array($row) ? ($row['reseau_cible'] ?? 'INTERNE') : ($row->reseau_cible ?? 'INTERNE');
                    
                    $totalLigne = $gainLocal + $gainComm;
                    $cumulInterne += $gainLocal;
                    $cumulExterne += $gainComm;
                ?>
                <tr>
                    <td><strong><?= esc($opType) ?></strong></td>
                    <td>
                        <?php if ($reseau === 'EXTERNE'): ?>
                            <span class="badge badge-externe">Réseaux Tiers</span>
                        <?php else: ?>
                            <span class="badge badge-interne">Notre Réseau</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; color: #16a34a; font-weight: bold;">
                        <?= number_format($gainLocal, 2, ',', ' ') ?> AR
                    </td>
                    <td style="text-align: right; color: #b45309; font-weight: bold;">
                        <?= number_format($gainComm, 2, ',', ' ') ?> AR
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?= number_format($totalLigne, 2, ',', ' ') ?> AR
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background: #f8fafc; font-weight: bold;">
            <tr>
                <td colspan="2" style="text-align: right;">Totaux Cumulés :</td>
                <td style="text-align: right; color: #16a34a;"><?= number_format($cumulInterne, 2, ',', ' ') ?> AR</td>
                <td style="text-align: right; color: #b45309;"><?= number_format($cumulExterne, 2, ',', ' ') ?> AR</td>
                <td style="text-align: right; color: #2563eb;"><?= number_format($cumulInterne + $cumulExterne, 2, ',', ' ') ?> AR</td>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: #6b7280;">
        <p style="font-size: 16px;">Aucun gain enregistré pour le moment.</p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>