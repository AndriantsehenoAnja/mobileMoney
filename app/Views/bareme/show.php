<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>liste bareme pour <?= $type_operation['nom'] ?></title>
</head>
<body>
    <h1>Liste des barèmes pour <?= $type_operation['nom'] ?></h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type d'opération</th>
                <th>Montant minimum</th>
                <th>Montant maximum</th>
                <th>Frais fixe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($baremes as $bareme): ?>
                <tr>
                    <td><?= $bareme['id'] ?></td>
                    <td><?= $type_operation['nom'] ?></td>
                    <td><?= $bareme['montant_min'] ?></td>
                    <td><?= $bareme['montant_max'] ?></td>
                    <td><?= $bareme['frais'] ?></td>
                    <td>
                        <a href="/bareme/edit/<?= $bareme['id'] ?>">Modifier</a> |
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
