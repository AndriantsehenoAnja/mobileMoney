<?= $this->extend('Client/layout/main') ?>

<?= $this->section('content') ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <i class="fas fa-arrow-down text-success"></i> Effectuer un dépôt
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>💰 Solde disponible :</strong> <?= number_format($solde ?? 0, 2) ?> Ar
        </div>

        <form action="/depot/effectuer" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="montant"><i class="fas fa-coins"></i> Montant à déposer</label>
                <input type="number" 
                       class="form-control form-control-lg" 
                       id="montant" 
                       name="montant" 
                       placeholder="Ex: 10000" 
                       min="100"
                       step="100"
                       value="<?= old('montant') ?>"
                       required>
                <div class="form-text">Montant minimum : 100 Ar</div>
            </div>

            <div class="quick-amounts">
                <button type="button" class="quick-amount-btn" onclick="setMontant(500)">500 Ar</button>
                <button type="button" class="quick-amount-btn" onclick="setMontant(1000)">1 000 Ar</button>
                <button type="button" class="quick-amount-btn" onclick="setMontant(5000)">5 000 Ar</button>
                <button type="button" class="quick-amount-btn" onclick="setMontant(10000)">10 000 Ar</button>
                <button type="button" class="quick-amount-btn" onclick="setMontant(25000)">25 000 Ar</button>
                <button type="button" class="quick-amount-btn" onclick="setMontant(50000)">50 000 Ar</button>
            </div>

            <button type="submit" class="btn btn-success btn-block" id="depotBtn">
                <i class="fas fa-arrow-down"></i> Effectuer le dépôt
            </button>
        </form>
    </div>
</div>

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
        document.getElementById('depotBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
    });
</script>

<?= $this->endSection() ?>