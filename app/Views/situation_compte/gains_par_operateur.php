<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Situation des Gains par Opérateur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gains par Opérateur</h1>
        <a href="<?= base_url('admin') ?>" class="btn btn-outline-secondary">Tableau de bord</a>
    </div>

    <!-- Filtre par période -->
    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">Filtrer par Période</div>
        <div class="card-body">
            <form action="<?= base_url('situation-compte/gains-par-operateur') ?>" method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_debut" class="form-label">Date Début</label>
                    <input type="date" name="date_debut" id="date_debut" class="form-control" value="<?= esc($dateDebut ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_fin" class="form-label">Date Fin</label>
                    <input type="date" name="date_fin" id="date_fin" class="form-control" value="<?= esc($dateFin ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    <a href="<?= base_url('situation-compte/gains-par-operateur') ?>" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Synthèse Globale -->
    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white p-3">
                <h5>Gains Internes</h5>
                <h3><?= number_format($totaux['frais_interne'], 2, ',', ' ') ?> Ar</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white p-3">
                <h5>Frais Externes</h5>
                <h3><?= number_format($totaux['frais_externe'], 2, ',', ' ') ?> Ar</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark p-3">
                <h5>Commissions Externes</h5>
                <h3><?= number_format($totaux['commission_externe'], 2, ',', ' ') ?> Ar</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white p-3">
                <h5>Gain Net Total</h5>
                <h3><?= number_format($totaux['gain_net_global'], 2, ',', ' ') ?> Ar</h3>
            </div>
        </div>
    </div>

    <!-- Section 1 : Notre Opérateur (Opérations Internes) -->
    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white fw-bold">
            Notre Réseau (Opérations Internes)
        </div>
        <div class="card-body">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type de réseau</th>
                        <th class="text-center">Nombre de Transactions</th>
                        <th class="text-end">Frais perçus</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Interne (Transferts, Retraits, Dépôts locaux)</strong></td>
                        <td class="text-center"><?= esc($gainsInterne['nombre_transactions']) ?></td>
                        <td class="text-end fw-bold text-success"><?= number_format($gainsInterne['frais'], 2, ',', ' ') ?> Ar</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2 : Autres Opérateurs (Opérations Externes) -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white fw-bold">
            Autres Opérateurs (Transferts Externes)
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Opérateur Externe</th>
                        <th class="text-center">Nb Transactions</th>
                        <th class="text-end">Frais de base</th>
                        <th class="text-end">Commissions</th>
                        <th class="text-end">Total Généré</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($gainsExternes)): ?>
                        <?php foreach ($gainsExternes as $ext): ?>
                            <?php $totalOp = (float)$ext['total_frais'] + (float)$ext['total_commission']; ?>
                            <tr>
                                <td><strong><?= esc($ext['operateur_nom'] ?? 'Opérateur inconnu') ?></strong></td>
                                <td class="text-center"><?= esc($ext['nombre_transactions']) ?></td>
                                <td class="text-end"><?= number_format($ext['total_frais'], 2, ',', ' ') ?> Ar</td>
                                <td class="text-end text-warning fw-bold">+ <?= number_format($ext['total_commission'], 2, ',', ' ') ?> Ar</td>
                                <td class="text-end text-primary fw-bold"><?= number_format($totalOp, 2, ',', ' ') ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucune transaction externe trouvée sur cette période.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>