<!-- Vue du formulaire de Transfert Simple -->
<div class="card shadow-sm col-md-8 mx-auto mt-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Effectuer un Transfert</h4>
    </div>
    <div class="card-body">
        <form action="<?= base_url('client/transfert/effectuer') ?>" method="post" id="transfertForm">
            <?= csrf_field() ?>

            <!-- Numéro du destinataire -->
            <div class="mb-3">
                <label for="numero_destination" class="form-label">Numéro du destinataire</label>
                <input type="text" class="form-control" id="numero_destination" name="numero_destination" placeholder="Ex: 0341234567" required>
                <div id="opInfo" class="form-text fw-bold mt-1"></div>
            </div>

            <!-- Montant -->
            <div class="mb-3">
                <label for="montant" class="form-label">Montant (Ar)</label>
                <input type="number" class="form-control" id="montant" name="montant" min="1000" step="100" required>
            </div>

            <!-- Option Frais de retrait inclus -->
            <div class="form-check mb-4" id="fraisRetraitContainer">
                <input class="form-check-input" type="checkbox" value="1" id="inclure_frais_retrait" name="inclure_frais_retrait">
                <label class="form-check-label" for="inclure_frais_retrait" id="labelFraisRetrait">
                    Inclure les frais de retrait pour le destinataire
                </label>
                <small id="fraisRetraitHelp" class="d-block text-muted"></small>
            </div>

            <button type="submit" class="btn btn-success w-100">Valider le transfert</button>
        </form>
    </div>
</div>

<script>
document.getElementById('numero_destination').addEventListener('input', function() {
    let numero = this.value.trim();
    let opInfo = document.getElementById('opInfo');
    let checkbox = document.getElementById('inclure_frais_retrait');
    let helpText = document.getElementById('fraisRetraitHelp');

    if (numero.length >= 3) {
        fetch('<?= base_url('client/verifier-destinataire') ?>?numero=' + encodeURIComponent(numero))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    opInfo.textContent = "Opérateur : " + data.operateur_nom;
                    
                    // Si ce n'est PAS notre réseau
                    if (!data.est_notre_operateur) {
                        opInfo.className = "form-text text-warning fw-bold mt-1";
                        checkbox.checked = false;
                        checkbox.disabled = true;
                        helpText.textContent = "Les frais de retrait inclus ne sont pas applicables aux autres opérateurs.";
                        helpText.className = "d-block text-danger";
                    } else {
                        opInfo.className = "form-text text-success fw-bold mt-1";
                        checkbox.disabled = false;
                        helpText.textContent = "";
                    }
                } else {
                    opInfo.textContent = "Numéro invalide ou opérateur non reconnu.";
                    opInfo.className = "form-text text-danger fw-bold mt-1";
                    checkbox.disabled = false;
                    helpText.textContent = "";
                }
            });
    }
});
</script>