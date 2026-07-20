<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Historique' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .header-info {
            display: flex;
            justify-content: space-between;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .header-info .solde {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60;
        }
        .header-info .total {
            color: #2c3e50;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .stats .stat-item {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            flex: 1;
            min-width: 100px;
        }
        .stats .stat-item .label {
            font-size: 12px;
            color: #888;
        }
        .stats .stat-item .value {
            font-size: 18px;
            font-weight: bold;
        }
        .stats .stat-item .value.depot { color: #27ae60; }
        .stats .stat-item .value.retrait { color: #e74c3c; }
        .stats .stat-item .value.transfert { color: #3498db; }
        .stats .stat-item .value.frais { color: #f39c12; }
        
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-depot {
            background: #d4edda;
            color: #155724;
        }
        .badge-retrait {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-transfert {
            background: #cce5ff;
            color: #004085;
        }
        
        .amount-positive {
            color: #27ae60;
            font-weight: bold;
        }
        .amount-negative {
            color: #e74c3c;
            font-weight: bold;
        }
        .amount-neutral {
            color: #2c3e50;
            font-weight: bold;
        }
        
        .frais-text {
            color: #888;
            font-size: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        .empty-state .icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-error {
            background: #fde8e8;
            color: #c0392b;
            border-left: 4px solid #c0392b;
        }
        .alert-success {
            background: #e8f5e9;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }
        
        @media (max-width: 768px) {
            .header-info {
                flex-direction: column;
                text-align: center;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 6px 8px;
            }
            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Historique des transactions</h1>
        
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-error">❌ <?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success">✅ <?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        
        <div class="header-info">
            <div>
                <strong>💰 Solde actuel :</strong>
                <span class="solde"><?= number_format($solde ?? 0, 2) ?> Ar</span>
            </div>
            <div>
                <span class="total">📋 Total : <strong><?= $total_transactions ?? 0 ?></strong> transactions</span>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <div class="label">💰 Dépôts</div>
                <div class="value depot"><?= number_format($total_depots ?? 0, 2) ?> Ar</div>
            </div>
            <div class="stat-item">
                <div class="label">💸 Retraits</div>
                <div class="value retrait"><?= number_format($total_retraits ?? 0, 2) ?> Ar</div>
            </div>
            <div class="stat-item">
                <div class="label">📤 Transferts</div>
                <div class="value transfert"><?= number_format($total_transferts ?? 0, 2) ?> Ar</div>
            </div>
            <div class="stat-item">
                <div class="label">💳 Frais</div>
                <div class="value frais"><?= number_format($total_frais ?? 0, 2) ?> Ar</div>
            </div>
        </div>
        
        <div class="table-container">
            <?php if(!empty($transactions)): ?>
                <table>
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
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <span class="icon">📭</span>
                    <p>Aucune transaction trouvée</p>
                    <small>Effectuez votre première opération</small>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="/client" class="back-link">← Retour au dashboard</a>
    </div>
</body>
</html>