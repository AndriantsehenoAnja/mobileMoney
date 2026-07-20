<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Transfert - Mobile Money' ?></title>
</head>
<body>
    <div class="container">
        <h1> Effectuer un transfert</h1>
        
        <!-- Messages Flash -->
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-error">❌ <?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success">✅ <?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        
        <!-- Solde -->
        <div class="info-solde">
            <div class="label"> Solde disponible</div>
            <div class="solde"><?= number_format($solde ?? 0, 2) ?> Ar</div>
            <div class="votre-numero"> Votre numéro : <?= $client->numero ?? '' ?></div>
        </div>

        <!-- Formulaire -->
        <form action="/transfert/effectuer" method="POST" id="transfertForm">
            <?= csrf_field() ?>
            
            <!-- Numéro du destinataire -->
            <div class="form-group">
                <label for="numero_destinataire"> Numéro du destinataire</label>
                <input type="text" 
                       id="numero_destinataire" 
                       name="numero_destinataire" 
                       placeholder="0325610052" 
                       maxlength="10"
                       pattern="[0-9]{10}"
                       value="<?= old('numero_destinataire') ?>"
                       required>
                <small>Entrez le numéro à 10 chiffres du destinataire</small>
                
                <!-- Message de chargement -->
                <div id="loadingMessage" class="loading"> Vérification du destinataire...</div>
                
                <!-- Info destinataire trouvé -->
                <div id="infoDestinataire" class="info-destinataire">
                    ✅ Destinataire : <strong id="nomDestinataire"></strong> (<span id="numeroDestinataire"></span>)
                </div>
                
                <!-- Erreur destinataire -->
                <div id="errorDestinataire" class="error-destinataire">
                    ❌ <span id="messageErreur"></span>
                </div>
            </div>

            <!-- Montant -->
            <div class="form-group">
                <label for="montant"> Montant à transférer</label>
                <input type="number" 
                       id="montant" 
                       name="montant" 
                       placeholder="Ex: 5000" 
                       min="100"
                       step="100"
                       value="<?= old('montant') ?>"
                       required>
                <small>Montant minimum : 100 Ar</small>
                <br>
                <small style="color: #888;">⚠️ Des frais peuvent s'appliquer selon le montant</small>
            </div>

            <!-- Boutons rapides -->
            <div class="btn-group">
                <button type="button" onclick="setMontant(500)">500 Ar</button>
                <button type="button" onclick="setMontant(1000)">1 000 Ar</button>
                <button type="button" onclick="setMontant(5000)">5 000 Ar</button>
                <button type="button" onclick="setMontant(10000)">10 000 Ar</button>
                <button type="button" onclick="setMontant(25000)">25 000 Ar</button>
                <button type="button" onclick="setMontant(50000)">50 000 Ar</button>
            </div>

            <button type="submit" class="btn-submit" id="transfertBtn">Effectuer le transfert</button>
        </form>

        <a href="/client" class="back-link">← Retour au dashboard</a>
    </div>

    <script>
        // Éléments DOM
        const numeroInput = document.getElementById('numero_destinataire');
        const montantInput = document.getElementById('montant');
        const transfertBtn = document.getElementById('transfertBtn');
        const infoDestinataire = document.getElementById('infoDestinataire');
        const errorDestinataire = document.getElementById('errorDestinataire');
        const loadingMessage = document.getElementById('loadingMessage');
        const nomDestinataire = document.getElementById('nomDestinataire');
        const numeroDestinataire = document.getElementById('numeroDestinataire');
        const messageErreur = document.getElementById('messageErreur');

        let timeoutId = null;
        let destinataireValide = false;

        function setMontant(montant) {
            document.getElementById('montant').value = montant;
        }

        // Vérification du destinataire avec délai (debounce)
        numeroInput.addEventListener('input', function() {
            const numero = this.value.replace(/\D/g, '');
            this.value = numero;
            
            // Cacher les messages précédents
            infoDestinataire.classList.remove('visible');
            errorDestinataire.classList.remove('visible');
            loadingMessage.classList.remove('visible');
            destinataireValide = false;
            
            // Annuler la vérification précédente
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Vérifier seulement si 10 chiffres
            if (numero.length === 10) {
                timeoutId = setTimeout(function() {
                    verifierDestinataire(numero);
                }, 300);
            }
        });

        function verifierDestinataire(numero) {
            // ✅ Utiliser le bon chemin
            const url = '/verifier-destinataire?numero=' + encodeURIComponent(numero);
            
            console.log('🔍 Vérification du destinataire:', url);
            
            loadingMessage.classList.add('visible');
            
            fetch(url)
                .then(function(response) {
                    console.log(' Réponse reçue, status:', response.status);
                    return response.json();
                })
                .then(function(data) {
                    loadingMessage.classList.remove('visible');
                    
                    console.log(' Données reçues:', data);
                    
                    if (data.success) {
                        nomDestinataire.textContent = data.client.nom;
                        numeroDestinataire.textContent = data.client.numero;
                        infoDestinataire.classList.add('visible');
                        errorDestinataire.classList.remove('visible');
                        destinataireValide = true;
                    } else {
                        messageErreur.textContent = data.message;
                        errorDestinataire.classList.add('visible');
                        infoDestinataire.classList.remove('visible');
                        destinataireValide = false;
                    }
                })
                .catch(function(error) {
                    loadingMessage.classList.remove('visible');
                    console.error('❌ Erreur AJAX:', error);
                    messageErreur.textContent = 'Erreur de connexion au serveur';
                    errorDestinataire.classList.add('visible');
                    infoDestinataire.classList.remove('visible');
                    destinataireValide = false;
                });
        }

        // Validation avant soumission
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
            
            transfertBtn.disabled = true;
            transfertBtn.textContent = 'Traitement en cours...';
        });

        // Forcer la vérification si le numéro est déjà saisi au chargement
        window.addEventListener('load', function() {
            const numero = numeroInput.value.replace(/\D/g, '');
            if (numero.length === 10) {
                verifierDestinataire(numero);
            }
        });
    </script>
</body>
</html>