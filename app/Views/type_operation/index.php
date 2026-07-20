<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>liste type operation</title>
</head>
<body>
    <a href="/bareme/addbareme">Ajouter un Bareme</a>
    <h1>Liste des types d'opérations</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type d'opération</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($type_operations as $type_operation): ?>
                <tr>
                    <td><?= $type_operation['id'] ?></td>
                    <td><?= $type_operation['nom'] ?></td>
                    <td>
                        <a href="/bareme/showbytypeoperation/<?= $type_operation['id'] ?>">voir bareme</a> |
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>