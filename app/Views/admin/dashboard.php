<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

    <style>
        .grid-stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 20px; 
            margin-top: 15px; 
            margin-bottom: 30px; 
        }
        .card-stat { 
            padding: 20px; 
            border-radius: 8px; 
            color: white; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.08); 
        }
        .bg-blue { background-color: #2563eb; }
        .bg-green { background-color: #16a34a; }
        .bg-purple { background-color: #9333ea; }
        .bg-orange { background-color: #ea580c; }
        
        .stat-val { 
            font-size: 22px; 
            font-weight: bold; 
            margin-top: 8px; 
        }
        .stat-sub {
            font-size: 11px;
            opacity: 0.85;
            margin-top: 4px;
        }
    </style>

    <h2>Aperçu général de l'activité (V2)</h2>
    <p style="color: #6b7280; font-size: 14px;">Indicateurs clés du réseau et situation des opérations tiers.</p>

    <!-- Grille des cartes statistiques V2 -->
    <div class="grid-stats">
        <div class="card-stat bg-blue">
            <div>Solde Total Clients</div>
            <div class="stat-val"><?= number_format($soldeTotalClients ?? 0, 2, ',', ' ') ?> AR</div>
            <div class="stat-sub">Masse monétaire en circulation</div>
        </div>
        
        <div class="card-stat bg-green">
            <div>Gains Notre Réseau</div>
            <div class="stat-val"><?= number_format($gainsVentiles['interne'] ?? 0, 2, ',', ' ') ?> AR</div>
            <div class="stat-sub">Frais réseau local</div>
        </div>

        <div class="card-stat bg-purple">
            <div>Gains Opérateurs Tiers</div>
            <div class="stat-val"><?= number_format($gainsVentiles['externe'] ?? 0, 2, ',', ' ') ?> AR</div>
            <div class="stat-sub">Commissions sur autres réseaux</div>
        </div>

        <div class="card-stat bg-orange">
            <div>À Reverser aux Opérateurs</div>
            <div class="stat-val"><?= number_format($totalAEnvoyerOperateurs[0]->total_a_envoyer ?? 0, 2, ',', ' ') ?> AR</div>
            <div class="stat-sub">Cumul dus aux autres réseaux (V2)</div>
        </div>
    </div>

    <!-- Section des 10 dernières transactions -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <h3>10 Dernières transactions transitées</h3>
        <a href="<?= site_url('admin/situation-compte') ?>" class="btn">Voir tous les rapports</a>
    </div>

    <?php if (!empty($recentTransactions)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Destinataire / Réseau</th>
                    <th>Montant</th>
                    <th>Frais Perçus</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentTransactions as $tx): ?>
                    <tr>
                        <td>#<?= esc($tx->id) ?></td>
                        <td><small><?= date('d/m/Y H:i', strtotime($tx->date_transaction)) ?></small></td>
                        <td><strong><?= esc($tx->type_operation ?? 'Inconnu') ?></strong></td>
                        <td>
                            <?= esc($tx->numero_destination ?? '-') ?>
                            <?php if (!empty($tx->nom_operateur_destination)): ?>
                                <span class="badge badge-externe"><?= esc($tx->nom_operateur_destination) ?></span>
                            <?php else: ?>
                                <span class="badge badge-interne">Notre Réseau</span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($tx->montant, 2, ',', ' ') ?> AR</td>
                        <td style="color: #16a34a; font-weight: bold;">
                            +<?= number_format($tx->frais_total ?? $tx->frais, 2, ',', ' ') ?> AR
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="margin-top: 15px; color: #6b7280;">Aucune transaction enregistrée pour le moment.</p>
    <?php endif; ?>

<?= $this->endSection() ?>