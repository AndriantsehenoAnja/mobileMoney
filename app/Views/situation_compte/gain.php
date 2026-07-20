<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>gain total</title>
    <style>
        .table-situation { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-situation th, .table-situation td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .table-situation th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .badge-solde { font-weight: bold; color: #1e7e34; }
    </style>
</head>
<body>


    <?php if (!empty($gainTotal)): ?>
        <table class="table-situation">
            <thead>
                <tr>
                    <th>type de gain</th>
                    <th>montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gainTotal as $row): ?>
                    <tr>
                       <td><?= esc($row->type_operation) ?></td>
                       <td class="text-right"><?= number_format($row->total_gain, 2, ',', ' ') ?> AR</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>pas de gain</p>
    <?php endif; ?>

</body>
</html>