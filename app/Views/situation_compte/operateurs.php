<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>🔄 Montants à reverser aux Opérateurs Tiers (V2)</h2>
    <a href="<?= site_url('admin/situation-compte') ?>" class="btn">🔙 Retour situation comptes</a>
</div>

<p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">
    Récapitulatif des sommes collectées lors de transferts vers des numéros externes (Orange, Telma, Airtel) et devant être reversées aux opérateurs partenaires.
</p>

<?php if (!empty($operateursDu)): ?>
    <table>
        <thead>
            <tr>
                <th>Opérateur Destination</th>
                <th>Nombre de Transactions</th>
                <th style="text-align: right;">Montants Transférés Brut</th>
                <th style="text-align: right;">Commissions Collectées</th>
                <th style="text-align: right;">Net à Reverser</th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotalReverser = 0; ?>
            <?php foreach ($operateursDu as $op): ?>
                <?php 
                    $nomOp = is_array($op) ? $op['nom_operateur'] : $op->nom_operateur;
                    $nbTx = is_array($op) ? $op['nb_transactions'] : $op->nb_transactions;
                    $brut = is_array($op) ? $op['total_brut'] : $op->total_brut;
                    $comm = is_array($op) ? $op['total_commissions'] : $op->total_commissions;
                    $net = is_array($op) ? $op['net_a_reverser'] : $op->net_a_reverser;
                    
                    $grandTotalReverser += $net;
                ?>
                <tr>
                    <td><strong style="font-size: 15px; color: #ea580c;"><?= esc($nomOp) ?></strong></td>
                    <td><?= $nbTx ?> transfert(s)</td>
                    <td style="text-align: right;"><?= number_format($brut, 2, ',', ' ') ?> AR</td>
                    <td style="text-align: right; color: #b45309;"><?= number_format($comm, 2, ',', ' ') ?> AR</td>
                    <td style="text-align: right; font-weight: bold; color: #dc2626;">
                        <?= number_format($net, 2, ',', ' ') ?> AR
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background: #f8fafc; font-weight: bold;">
            <tr>
                <td colspan="4" style="text-align: right;">Cumul Général Dû aux Opérateurs Externe :</td>
                <td style="text-align: right; color: #dc2626; font-size: 16px;">
                    <?= number_format($grandTotalReverser, 2, ',', ' ') ?> AR
                </td>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: #6b7280;">
        <p style="font-size: 16px;">Aucun versement en attente pour un opérateur tiers.</p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>