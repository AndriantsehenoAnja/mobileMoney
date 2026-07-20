<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Balance -->
<div class="balance-card">
    <div class="row align-items-center">
        <div class="col">
            <div class="balance-label">
                <i class="fas fa-wallet"></i> Solde disponible
            </div>
            <div class="balance-amount">
                <?= number_format($solde ?? 0, 2) ?> <small>Ar</small>
            </div>
            <div class="balance-sub">
                <i class="fas fa-phone"></i> <?= $client->numero ?? 'N/A' ?>
            </div>
        </div>
        <div class="col-auto">
            <div class="balance-icon">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <a href="/depot" class="quick-action-btn depot">
        <span class="qa-icon"><i class="fas fa-arrow-down"></i></span>
        <span class="qa-label">Dépôt</span>
    </a>
    <a href="/retrait" class="quick-action-btn retrait">
        <span class="qa-icon"><i class="fas fa-arrow-up"></i></span>
        <span class="qa-label">Retrait</span>
    </a>
    <a href="/transfert" class="quick-action-btn transfert">
        <span class="qa-icon"><i class="fas fa-exchange-alt"></i></span>
        <span class="qa-label">Transfert</span>
    </a>
    <a href="/historique" class="quick-action-btn historique">
        <span class="qa-icon"><i class="fas fa-history"></i></span>
        <span class="qa-label">Historique</span>
    </a>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="fas fa-exchange-alt"></i></div>
        <div class="stat-number"><?= $total_transactions ?? 0 ?></div>
        <div class="stat-label">Transactions</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-success"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-number"><?= number_format($total_depots ?? 0, 0) ?> Ar</div>
        <div class="stat-label">Dépôts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-number"><?= number_format($total_retraits ?? 0, 0) ?> Ar</div>
        <div class="stat-label">Retraits</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-info"><i class="fas fa-share"></i></div>
        <div class="stat-number"><?= number_format($total_transferts ?? 0, 0) ?> Ar</div>
        <div class="stat-label">Transferts</div>
    </div>
</div>

<!-- Dernières transactions -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-clock"></i> Dernières transactions</span>
        <a href="/client/historique" class="btn btn-primary btn-sm">Voir tout</a>
    </div>
    <div class="card-body">
        <?php if(!empty($transactions)): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $tx): ?>
                            <?php 
                                $isDebit = ($tx->compte_source == $compte->id);
                                $totalTx = ($isDebit) ? -($tx->montant + $tx->frais) : $tx->montant;
                                $classColor = ($totalTx > 0) ? 'amount-positive' : (($totalTx < 0) ? 'amount-negative' : 'amount-neutral');
                                $signe = ($totalTx > 0) ? '+' : '';
                            ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($tx->date_transaction)) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($tx->type_operation ?? 'depot') ?>">
                                        <?= $tx->type_operation ?? 'Inconnu' ?>
                                    </span>
                                </td>
                                <td><?= number_format($tx->montant, 2) ?> Ar</td>
                                <td class="<?= $classColor ?>">
                                    <?= $signe ?><?= number_format(abs($totalTx), 2) ?> Ar
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="icon">📭</span>
                <p>Aucune transaction</p>
                <small>Effectuez votre première opération</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>