<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Barème</title>
</head>
<body>
    <form action="/bareme/update/<?= $bareme['id'] ?>" method="post">
        <label for="type_operation_id">Type d'opération:</label>
        <select name="type_operation_id" id="type_operation_id">
            <?php foreach ($type_operations as $type_operation): ?>
                <option value="<?= $type_operation['id'] ?>" <?= $bareme['type_operation_id'] == $type_operation['id'] ? 'selected' : '' ?>>
                    <?= $type_operation['nom'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="montant_min">Montant minimum:</label>
        <input type="number" name="montant_min" id="montant_min" value="<?= $bareme['montant_min'] ?>" required>
        <br>

        <label for="montant_max">Montant maximum:</label>
        <input type="number" name="montant_max" id="montant_max" value="<?= $bareme['montant_max'] ?>" required>
        <br>

        <label for="frais">Frais:</label>
        <input type="number" name="frais" id="frais" value="<?= $bareme['frais'] ?>" required>
        <br>

        <button type="submit">Modifier</button>
    </form>
</body>
</html>