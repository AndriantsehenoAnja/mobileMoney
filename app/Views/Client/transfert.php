<?= $this->extend('Client/layout/main') ?>

<?= $this->section('content') ?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <i class="fas fa-exchange-alt text-primary"></i> Effectuer un transfert (Simple ou Multiple)
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>💰 Solde disponible :</strong> <?= number_format($solde ?? 0, 2) ?> Ar
            <br>
            <strong>📱 Votre numéro :</strong> <?= $client->numero ?? '' ?>
        </div>
        
        <form action="/transfert/effectuer" method="POST" id="transfertForm">
            <?= csrf_field() ?>
            
            <!-- Champ numéros destinataires -->
            <div class="form-group mb-3">
                <label for="numeros_destinataires" class="form-label">
                    <i class="fas fa-phone"></i> Numéro(s) du/des destinataire(s)
                </label>
                <textarea 
                       class="form-control" 
                       id="numeros_destinataires" 
                       name="numero_destinataire" 
                       rows="2"
                       placeholder="Ex simple: 0341234567" 
                       required><?= old('numeros_destinataires') ?></textarea>
                <div class="form-text">Entrez un ou plusieurs numéros à 10 chiffres.</div>
            </div>

            <!-- Option V2 : Inclure les frais de retrait -->
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="inclure_frais_retrait" name="inclure_frais_retrait" value="1">
                <label class="form-check-label fw-bold" for="inclure_frais_retrait">
                    <i class="fas fa-hand-holding-usd text-success"></i> Inclure les frais de retrait lors de l'envoi
                </label>
                <div class="form-text text-muted">
                    Le destinataire recevra le montant exact + les frais nécessaires pour qu'il puisse retirer sans frais (Valable pour notre réseau uniquement).
                </div>
            </div>

            <!-- Champ Montant -->
            <div class="form-group mb-3">
                <label for="montant" class="form-label"><i class="fas fa-coins"></i> Montant total à transférer</label>
                <input type="number" 
                       class="form-control form-control-lg" 
                       id="montant" 
                       name="montant" 
                       placeholder="Ex: 10000" 
                       min="100"
                       step="100"
                       value="<?= old('montant') ?>"
                       required>
                <div class="form-text">Si vous saisissez plusieurs numéros, le montant sera divisé équitablement.</div>
            </div>

            <!-- Boutons de montant rapide -->
            <div class="quick-amounts mb-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setMontant(1000)">1 000 Ar</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setMontant(5000)">5 000 Ar</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setMontant(10000)">10 000 Ar</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setMontant(25000)">25 000 Ar</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setMontant(50000)">50 000 Ar</button>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg" id="transfertBtn">
                <i class="fas fa-paper-plane"></i> Confirmer le transfert
            </button>
        </form>
    </div>
</div>

<script>
    function setMontant(montant) {
        document.getElementById('montant').value = montant;
    }

    document.getElementById('transfertForm').addEventListener('submit', function(e) {
        const inputNumeros = document.getElementById('numeros_destinataires').value.trim();
        const montant = parseFloat(document.getElementById('montant').value);
        const solde = <?= $solde ?? 0 ?>;
        
        // Extraction des numéros
        const listeNumeros = inputNumeros.split(/[\s,;]+/).filter(Boolean);
        
        if (listeNumeros.length === 0) {
            e.preventDefault();
            alert('❌ Veuillez saisir au moins un numéro destinataire.');
            return false;
        }

        if (isNaN(montant) || montant < 100) {
            e.preventDefault();
            alert('❌ Veuillez saisir un montant valide (minimum 100 Ar).');
            return false;
        }

        if (montant > solde) {
            e.preventDefault();
            alert('❌ Solde insuffisant pour couvrir le montant saisi.');
            return false;
        }

        let messageConfirmation = `Confirmer le transfert d'un montant global de ${montant.toLocaleString()} Ar vers ${listeNumeros.length} destinataire(s) ?`;
        if (listeNumeros.length > 1) {
            const parPersonne = montant / listeNumeros.length;
            messageConfirmation += `\n(Chaque destinataire recevra ${parPersonne.toLocaleString()} Ar)`;
        }

        if (!confirm(messageConfirmation)) {
            e.preventDefault();
            return false;
        }

        document.getElementById('transfertBtn').disabled = true;
        document.getElementById('transfertBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
    });
</script>

<?= $this->endSection() ?>