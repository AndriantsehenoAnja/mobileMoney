<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mobile Money</title>
</head>
<body>
    <?php if (session()->getFlashdata('error')): ?>
             <?= session()->getFlashdata('error') ?>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
            <?= session()->getFlashdata('success') ?>
    <?php endif; ?>

    <form action="/login" method="POST">
        <?= csrf_field() ?>
        
        <label for="numero">Numéro de téléphone :</label>
        <input type="text" 
               id="numero" 
               name="numero" 
               placeholder="0325610052" 
               maxlength="10"
               value="<?= old('numero') ?>"
               required>
        <br><br>
        
        <button type="submit">Se connecter</button>
    </form>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const numero = document.getElementById('numero').value;
            
            if (numero.length !== 10) {
                e.preventDefault();
                alert('❌ Le numéro doit contenir exactement 10 chiffres');
                return false;
            }
            
            if (!/^\d+$/.test(numero)) {
                e.preventDefault();
                alert('❌ Le numéro ne doit contenir que des chiffres');
                return false;
            }
            
            if (!numero.startsWith('0')) {
                e.preventDefault();
                alert('❌ Le numéro doit commencer par 0');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>