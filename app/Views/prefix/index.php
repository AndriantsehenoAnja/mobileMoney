<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>liste prefixes</title>
</head>
<body>
    <h1>Liste des préfixes</h1>
    <a href="/prefix/form">Créer un nouveau préfixe</a>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Préfixe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prefixes as $prefix): ?>
                <tr>
                    <td><?= $prefix['id'] ?></td>
                    <td><?= $prefix['prefixe'] ?></td>
                    <td>
                        <a href="/prefix/edit/<?= $prefix['id'] ?>">Modifier</a> |
                        <a href="/prefix/delete/<?= $prefix['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce préfixe ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>