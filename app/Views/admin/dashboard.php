
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    
        <h2>Tableau de Bord Administrateur</h2>
        <p style="color: #7f8c8d;">Statistiques globales en temps réel du système Mobile Money.</p>
    
        <!-- Grille des indicateurs (Cards) -->
        <style>
            .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; margin-bottom: 40px; }
            .card-stat { padding: 20px; border-radius: 8px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .bg-blue { background-color: #3498db; }
            .bg-green { background-color: #2ecc71; }
            .bg-purple { background-color: #9b59b6; }
            .stat-val { font-size: 24px; font-weight: bold; margin-top: 10px; }
            .table-recent { width: 100%; border-collapse: collapse; margin-top: 15px; }
            .table-recent th, .table-recent td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            .table-recent th { background-color: #f4f6f7; }
        </style>
    
        <div class="grid-stats">
            <div class="card-stat bg-blue">
                <div>Masse Monétaire Globale</div>
                <div class="stat-val"><?= number_format($masseMonetaire, 2, ',', ' ') ?> AR</div>
            </div>
            
            <div class="card-stat bg-green">
                <div>Gains de la Plateforme</div>
                <div class="stat-val"><?= number_format($gainGlobal, 2, ',', ' ') ?> AR</div>
            </div>
    
            <div class="card-stat bg-purple">
                <div>Comptes Actifs</div>
                <div class="stat-val"><?= $nombreComptes ?></div>
            </div>
        </div>
    
        <!-- Section des activités récentes -->
        <h3>Flux des 5 dernières transactions</h3>
        <?php if (!empty($dernieresTransactions)): ?>
            <table class="table-recent">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Frais perçus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieresTransactions as $tx): ?>
                        <tr>
                            <td>#<?= esc($tx->id) ?></td>
                            <td><strong><?= esc($tx->type_nom) ?></strong></td>
                            <td><?= number_format($tx->montant, 2, ',', ' ') ?> AR</td>
                            <td style="color: #27ae60; font-weight: bold;">+<?= number_format($tx->frais, 2, ',', ' ') ?> AR</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucune transaction n'a encore transité par le système.</p>
        <?php endif; ?>
    
    
</body>
</html>