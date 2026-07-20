<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépôt - Mobile Money</title>
</head>
<body>
    <!-- Messages Flash -->
    <?php if(session()->getFlashdata('error')): ?>
        <div style="color: red; background: #fde8e8; padding: 10px; border-left: 4px solid red; margin-bottom: 15px;">
            ❌ <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('success')): ?>
        <div style="color: green; background: #e8f5e9; padding: 10px; border-left: 4px solid green; margin-bottom: 15px;">
            ✅ <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <h1>Effectuer un dépôt</h1>
    
    <p><strong>Solde disponible :</strong> <?= number_format($solde ?? 0, 2) ?> Ar</p>

    <form action="/depot/effectuer" method="POST">
        <?= csrf_field() ?>
        
        <label for="montant">Montant à déposer :</label>
        <input type="number" 
               id="montant" 
               name="montant" 
               placeholder="Ex: 10000" 
               min="100"
               step="100"
               value="<?= old('montant') ?>"
               required>
        <br>
        <small>Montant minimum : 100 Ar</small>
        <br><br>

        <!-- Boutons rapides -->
        <div>
            <button type="button" onclick="setMontant(500)">500 Ar</button>
            <button type="button" onclick="setMontant(1000)">1 000 Ar</button>
            <button type="button" onclick="setMontant(5000)">5 000 Ar</button>
            <button type="button" onclick="setMontant(10000)">10 000 Ar</button>
            <button type="button" onclick="setMontant(25000)">25 000 Ar</button>
            <button type="button" onclick="setMontant(50000)">50 000 Ar</button>
        </div>
        <br><br>

        <button type="submit" id="depotBtn">Effectuer le dépôt</button>
    </form>

    <br>
    <a href="/client">← Retour au dashboard</a>

    <script>
        function setMontant(montant) {
            document.getElementById('montant').value = montant;
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const montant = parseFloat(document.getElementById('montant').value);
            
            if (isNaN(montant) || montant < 100) {
                e.preventDefault();
                alert('❌ Veuillez saisir un montant valide (minimum 100 Ar)');
                return false;
            }
            
            if (!confirm(`Confirmer le dépôt de ${montant.toLocaleString()} Ar ?`)) {
                e.preventDefault();
                return false;
            }
            
            document.getElementById('depotBtn').disabled = true;
            document.getElementById('depotBtn').textContent = 'Traitement en cours...';
        });
    </script>
</body>
</html>