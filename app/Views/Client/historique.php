<?= $this->extend('Client/layout/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-history"></i> Historique des transactions (V2)</span>
        <span class="badge bg-primary rounded-pill">Total : <?= $total_transactions ?? count($transactions ?? []) ?></span>
    </div>
    <div class="card-body">
        <?php if(!empty($transactions)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Réseau / Destinataire</th>
                            <th>Montant</th>
                            <th>Détail Frais (V2)</th>
                            <th>Total Impacté</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $tx): ?>
                            <?php 
                                $isDebit = ($tx->compte_source == $compte->id);
                                $isCredit = ($tx->compte_destination == $compte->id);

                                // Calcul des frais totaux V2
                                $fraisBase = $tx->frais ?? 0;
                                $fraisCommission = $tx->frais_commission_externe ?? 0;
                                $fraisRetraitInclus = $tx->frais_retrait_inclus ?? 0;
                                $fraisTotauxTx = $tx->frais_total ?? ($fraisBase + $fraisCommission + $fraisRetraitInclus);

                                // Impact sur le solde
                                if ($isDebit) {
                                    $totalTx = -($tx->montant + $fraisTotauxTx);
                                } elseif ($isCredit) {
                                    // Si l'expéditeur a inclus les frais de retrait, le destinataire reçoit le montant + frais de retrait
                                    $totalTx = $tx->montant + $fraisRetraitInclus;
                                } else {
                                    $totalTx = 0;
                                }

                                $classColor = ($totalTx > 0) ? 'text-success fw-bold' : (($totalTx < 0) ? 'text-danger fw-bold' : 'text-muted');
                                $signe = ($totalTx > 0) ? '+' : '';
                            ?>
                            <tr>
                                <td><small class="text-muted">#<?= $tx->id ?></small></td>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($tx->date_transaction)) ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $typeNom = strtolower($tx->type_operation ?? 'depot');
                                        $badgeClass = 'bg-secondary';
                                        if (strpos($typeNom, 'depot') !== false) $badgeClass = 'bg-success';
                                        elseif (strpos($typeNom, 'retrait') !== false) $badgeClass = 'bg-danger';
                                        elseif (strpos($typeNom, 'transfert') !== false) $badgeClass = 'bg-primary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= strtoupper($tx->type_operation ?? 'INCONNU') ?>
                                    </span>
                                </td>

                                <!-- Colonne Réseau & Numéro Destinataire / Source -->
                                <td>
                                    <?php if ($isDebit): ?>
                                        <i class="fas fa-arrow-right text-danger me-1"></i>
                                        <strong><?= $tx->numero_destination ?? 'N/A' ?></strong>
                                        <?php if (!empty($tx->nom_operateur_destination)): ?>
                                            <br><span class="badge bg-outline-info text-dark border"><?= $tx->nom_operateur_destination ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($tx->nom_destination)): ?>
                                            <br><small class="text-muted"><?= $tx->nom_destination ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-left text-success me-1"></i>
                                        <strong><?= $tx->numero_source ?? 'N/A' ?></strong>
                                        <?php if (!empty($tx->nom_source)): ?>
                                            <br><small class="text-muted"><?= $tx->nom_source ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <!-- Montant Brut -->
                                <td><?= number_format($tx->montant, 2, ',', ' ') ?> Ar</td>

                                <!-- Détail des frais V2 -->
                                <td>
                                    <?php if ($fraisTotauxTx > 0): ?>
                                        <small class="d-block text-muted">Base: <?= number_format($fraisBase, 2, ',', ' ') ?> Ar</small>
                                        <?php if ($fraisCommission > 0): ?>
                                            <small class="d-block text-warning">Comm. Réseau: <?= number_format($fraisCommission, 2, ',', ' ') ?> Ar</small>
                                        <?php endif; ?>
                                        <?php if ($fraisRetraitInclus > 0): ?>
                                            <small class="d-block text-info">Frais Retrait Inc.: <?= number_format($fraisRetraitInclus, 2, ',', ' ') ?> Ar</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <small class="text-muted">0,00 Ar</small>
                                    <?php endif; ?>
                                </td>

                                <!-- Impact Total -->
                                <td class="<?= $classColor ?>">
                                    <?= $signe ?><?= number_format(abs($totalTx), 2, ',', ' ') ?> Ar
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Cumul des mouvements affichés :</td>
                            <td>
                                <?php 
                                    $totalGeneral = 0;
                                    foreach($transactions as $tx) {
                                        $isDebit = ($tx->compte_source == $compte->id);
                                        $isCredit = ($tx->compte_destination == $compte->id);
                                        $fraisTotauxTx = $tx->frais_total ?? (($tx->frais ?? 0) + ($tx->frais_commission_externe ?? 0) + ($tx->frais_retrait_inclus ?? 0));
                                        
                                        if ($isDebit) {
                                            $totalGeneral -= ($tx->montant + $fraisTotauxTx);
                                        } elseif ($isCredit) {
                                            $totalGeneral += ($tx->montant + ($tx->frais_retrait_inclus ?? 0));
                                        }
                                    }
                                    $classColor = ($totalGeneral > 0) ? 'text-success' : (($totalGeneral < 0) ? 'text-danger' : 'text-dark');
                                    $signe = ($totalGeneral > 0) ? '+' : '';
                                ?>
                                <span class="<?= $classColor ?>">
                                    <?= $signe ?><?= number_format(abs($totalGeneral), 2, ',', ' ') ?> Ar
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state text-center py-5">
                <div class="display-1 text-muted">📭</div>
                <p class="h5 mt-3">Aucune transaction trouvée</p>
                <small class="text-muted">Vos dépôts, retraits et transferts s'afficheront ici.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>