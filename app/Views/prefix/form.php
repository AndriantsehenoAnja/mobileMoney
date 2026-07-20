<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un préfixe</title>
</head>
<body>
    <?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <h1>Créer un préfixe</h1>
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;">Erreur lors de la création du préfixe. Veuillez réessayer.<?= $_GET['error'] ?></p>
    <?php endif; ?>
    <form action="/prefix/create" method="post">
        <label for="prefixe">Préfixe :</label>
        <input type="text" id="prefixe" name="prefixe" required>
        <button type="submit">Créer</button>
    </form>
    <?= $this->endSection() ?>
</body>
</html>