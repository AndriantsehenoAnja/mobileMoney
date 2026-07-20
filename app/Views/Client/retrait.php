<?= $this->extend('Client/layout/main') ?>

<?= $this->section('content') ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <i class="fas fa-arrow-up text-danger"></i> Effectuer un retrait
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>💰 Solde disponible :</strong> <?= number_format($solde ?? 0, 2) ?> Ar
        </div>
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> Des frais peuvent s'appliquer selon le montant
        </div>

        <form action="/retrait/effectuer" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="montant"><i class="fas fa-coins"></i> Montant à retirer</label>
                <input type="number" 
                       class="form-control form-control-lg" 
                       id="montant" 
                       name="montant" 
                       placeholder="Ex: 5000" 
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

            <button type="submit" class="btn btn-danger btn-block" id="retraitBtn">
                <i class="fas fa-arrow-up"></i> Effectuer le retrait
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
        const solde = <?= $solde ?? 0 ?>;
        
        if (isNaN(montant) || montant < 100) {
            e.preventDefault();
            alert('❌ Veuillez saisir un montant valide (minimum 100 Ar)');
            return false;
        }
        
        if (montant > solde) {
            e.preventDefault();
            alert('❌ Solde insuffisant. Solde disponible : ' + solde.toLocaleString() + ' Ar');
            return false;
        }
        
        if (!confirm(`Confirmer le retrait de ${montant.toLocaleString()} Ar ?`)) {
            e.preventDefault();
            return false;
        }
        
        document.getElementById('retraitBtn').disabled = true;
        document.getElementById('retraitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
    });
</script>

<?= $this->endSection() ?>