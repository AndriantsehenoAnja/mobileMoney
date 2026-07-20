<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <i class="fas fa-exchange-alt text-primary"></i> Effectuer un transfert
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>💰 Solde disponible :</strong> <?= number_format($solde ?? 0, 2) ?> Ar
            <br>
            <strong>📱 Votre numéro :</strong> <?= $client->numero ?? '' ?>
        </div>
        <div class="alert alert-warning">
            <i class="fas fa-info-circle"></i> Des frais peuvent s'appliquer selon le montant
        </div>

        <form action="/transfert/effectuer" method="POST" id="transfertForm">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="numero_destinataire"><i class="fas fa-phone"></i> Numéro du destinataire</label>
                <input type="text" 
                       class="form-control" 
                       id="numero_destinataire" 
                       name="numero_destinataire" 
                       placeholder="0325610052" 
                       maxlength="10"
                       value="<?= old('numero_destinataire') ?>"
                       required>
                <div class="form-text">Entrez le numéro à 10 chiffres du destinataire</div>
                
                <div id="loadingMessage" style="color: #3498db; display: none;">⏳ Vérification du destinataire...</div>
                <div id="infoDestinataire" style="background: #e8f5e9; padding: 10px; border-left: 4px solid #27ae60; margin: 10px 0; display: none;">
                    ✅ Destinataire : <strong id="nomDestinataire"></strong> (<span id="numeroDestinataire"></span>)
                </div>
                <div id="errorDestinataire" style="background: #fde8e8; padding: 10px; border-left: 4px solid #e74c3c; margin: 10px 0; display: none; color: #c0392b;">
                    ❌ <span id="messageErreur"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="montant"><i class="fas fa-coins"></i> Montant à transférer</label>
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

            <button type="submit" class="btn btn-primary btn-block" id="transfertBtn">
                <i class="fas fa-exchange-alt"></i> Effectuer le transfert
            </button>
        </form>
    </div>
</div>

<script>
    const numeroInput = document.getElementById('numero_destinataire');
    let timeoutId = null;
    let destinataireValide = false;

    function setMontant(montant) {
        document.getElementById('montant').value = montant;
    }

    numeroInput.addEventListener('input', function() {
        const numero = this.value.replace(/\D/g, '');
        this.value = numero;
        
        document.getElementById('infoDestinataire').style.display = 'none';
        document.getElementById('errorDestinataire').style.display = 'none';
        document.getElementById('loadingMessage').style.display = 'none';
        destinataireValide = false;
        
        if (timeoutId) clearTimeout(timeoutId);
        
        if (numero.length === 10) {
            timeoutId = setTimeout(function() {
                verifierDestinataire(numero);
            }, 300);
        }
    });

    function verifierDestinataire(numero) {
        document.getElementById('loadingMessage').style.display = 'block';
        
        fetch('/verifier-destinataire?numero=' + encodeURIComponent(numero))
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingMessage').style.display = 'none';
                
                if (data.success) {
                    document.getElementById('nomDestinataire').textContent = data.client.nom;
                    document.getElementById('numeroDestinataire').textContent = data.client.numero;
                    document.getElementById('infoDestinataire').style.display = 'block';
                    destinataireValide = true;
                } else {
                    document.getElementById('messageErreur').textContent = data.message;
                    document.getElementById('errorDestinataire').style.display = 'block';
                    destinataireValide = false;
                }
            })
            .catch(error => {
                document.getElementById('loadingMessage').style.display = 'none';
                document.getElementById('messageErreur').textContent = 'Erreur de connexion';
                document.getElementById('errorDestinataire').style.display = 'block';
                destinataireValide = false;
            });
    }

    document.getElementById('transfertForm').addEventListener('submit', function(e) {
        const numero = document.getElementById('numero_destinataire').value;
        const montant = parseFloat(document.getElementById('montant').value);
        const solde = <?= $solde ?? 0 ?>;
        
        if (numero.length !== 10) {
            e.preventDefault();
            alert('❌ Veuillez saisir un numéro de destinataire valide (10 chiffres)');
            return false;
        }
        
        if (!destinataireValide) {
            e.preventDefault();
            alert('❌ Destinataire non valide. Veuillez vérifier le numéro.');
            return false;
        }
        
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
        
        if (!confirm(`Confirmer le transfert de ${montant.toLocaleString()} Ar vers le numéro ${numero} ?`)) {
            e.preventDefault();
            return false;
        }
        
        document.getElementById('transfertBtn').disabled = true;
        document.getElementById('transfertBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
    });
</script>

<?= $this->endSection() ?>