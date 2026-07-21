<?= $this->extend('Client/layout/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="card shadow-sm col-md-8 mx-auto">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i>Transfert Multiple</h4>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                <strong><i class="bi bi-info-circle-fill me-1"></i> Règle V2 :</strong> 
                Le montant total saisi sera divisé équitablement par le nombre de destinataires. 
                <br>
                <span class="text-danger fw-bold">* Tous les numéros saisis doivent obligatoirement appartenir à notre réseau uniquement.</span>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('client/transfert-multiple/effectuer') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="numeros" class="form-label font-weight-bold">Numéros des destinataires</label>
                    <textarea class="form-control" id="numeros" name="numeros" rows="4" placeholder="Entrez les numéros séparés par une virgule ou une nouvelle ligne (ex: 0341234567, 0389876543)" required></textarea>
                    <div class="form-text">Seuls les préfixes de notre réseau (ex: 034, 038) sont acceptés.</div>
                </div>

                <div class="mb-3">
                    <label for="montant_total" class="form-label font-weight-bold">Montant global à répartir (Ar)</label>
                    <input type="number" class="form-control" id="montant_total" name="montant_total" min="1000" step="100" placeholder="Ex: 50000" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="<?= base_url('client') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send-fill me-1"></i> Diviser et Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>