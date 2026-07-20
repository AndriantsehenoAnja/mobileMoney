<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-history"></i> Historique des transactions</span>
        <span class="badge bg-primary rounded-pill">Total : <?= $total_transactions ?? 0 ?></span>
    </div>
    <div class="card-body">
        <?php if(!empty($transactions)): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Frais</th>
                            <th>Source</th>
                            <th>Destination</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $tx): ?>
                            <?php 
                                $isDebit = ($tx->compte_source == $compte->id);
                                $isCredit = ($tx->compte_destination == $compte->id);
                                $totalTx = ($isDebit) ? -($tx->montant + $tx->frais) : $tx->montant;
                                $classColor = ($totalTx > 0) ? 'amount-positive' : (($totalTx < 0) ? 'amount-negative' : 'amount-neutral');
                                $signe = ($totalTx > 0) ? '+' : '';
                            ?>
                            <tr>
                                <td>#<?= $tx->id ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($tx->date_transaction)) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($tx->type_operation ?? 'depot') ?>">
                                        <?= $tx->type_operation ?? 'Inconnu' ?>
                                    </span>
                                </td>
                                <td><?= number_format($tx->montant, 2) ?> Ar</td>
                                <td class="frais-text"><?= number_format($tx->frais ?? 0, 2) ?> Ar</td>
                                <td>
                                    <?php if($tx->compte_source): ?>
                                        <?= $tx->numero_source ?? 'N/A' ?>
                                        <br><small><?= $tx->nom_source ?? '' ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($tx->compte_destination): ?>
                                        <?= $tx->numero_destination ?? 'N/A' ?>
                                        <br><small><?= $tx->nom_destination ?? '' ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $classColor ?>">
                                    <?= $signe ?><?= number_format(abs($totalTx), 2) ?> Ar
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background: #f8f9fa;">
                            <td colspan="7" style="text-align: right;">Totaux :</td>
                            <td>
                                <?php 
                                    $totalGeneral = 0;
                                    foreach($transactions as $tx) {
                                        $isDebit = ($tx->compte_source == $compte->id);
                                        $totalGeneral += ($isDebit) ? -($tx->montant + $tx->frais) : $tx->montant;
                                    }
                                    $classColor = ($totalGeneral > 0) ? 'amount-positive' : (($totalGeneral < 0) ? 'amount-negative' : 'amount-neutral');
                                    $signe = ($totalGeneral > 0) ? '+' : '';
                                ?>
                                <span class="<?= $classColor ?>">
                                    <?= $signe ?><?= number_format(abs($totalGeneral), 2) ?> Ar
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="icon">📭</span>
                <p>Aucune transaction trouvée</p>
                <small>Effectuez votre première opération</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>