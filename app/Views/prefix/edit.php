<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier préfixe</title>
</head>
<body>
    <?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <h1>Modifier le préfixe</h1>
    <form action="/prefix/update/<?= $prefix['id'] ?>" method="post">
        <label for="prefixe">Préfixe :</label>
        <input type="text" id="prefixe" name="prefixe" value="<?= $prefix['prefixe'] ?>" required>
        <button type="submit">Mettre à jour</button>
    </form>
    <?= $this->endSection() ?>

</body>
</html>