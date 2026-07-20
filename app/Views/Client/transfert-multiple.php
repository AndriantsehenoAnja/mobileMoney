<?= $this->extend('Client/layout/main') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-users"></i> Envoi Multiple (Montant Partagé)</h5>
        <a href="<?= site_url('client/transfert') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Transfert simple
        </a>
    </div>
    <div class="card-body">
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= site_url('client/transfert-multiple/effectuer') ?>" method="post" id="multiTransferForm">
            <?= csrf_field() ?>

            <!-- Montant Global Unique -->
            <div class="mb-4 p-3 bg-light rounded border">
                <label for="montant_total" class="form-label fw-bold">Montant Total à Partager (Ar)</label>
                <div class="input-group input-group-lg">
                    <input type="number" step="0.01" min="100" id="montant_total" name="montant_total" class="form-control" placeholder="Ex: 100000" required>
                    <span class="input-group-text">Ar</span>
                </div>
                <div class="form-text text-muted mt-2">
                    Part par destinataire : <strong id="partIndividuelle" class="text-primary">0.00 Ar</strong>
                </div>
            </div>

            <!-- Liste des Numéros -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Numéros des Destinataires <small class="text-muted">(Même opérateur uniquement)</small></th>
                            <th style="width: 10%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="numerosContainer">
                        <tr class="numero-row">
                            <td>
                                <input type="text" name="numeros[]" class="form-control numero-input" placeholder="Ex: 0341234567" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row-btn" disabled>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="numero-row">
                            <td>
                                <input type="text" name="numeros[]" class="form-control numero-input" placeholder="Ex: 0349876543" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center my-3">
                <button type="button" class="btn btn-success btn-sm" id="addNumeroBtn">
                    <i class="fas fa-plus"></i> Ajouter un numéro
                </button>
                <div class="fw-bold fs-6">
                    Nombre de destinataires : <span id="nbDestinataires" class="badge bg-secondary">2</span>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> Répartir et exécuter les transferts
                </button>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('numerosContainer');
    const addBtn = document.getElementById('addNumeroBtn');
    const montantInput = document.getElementById('montant_total');
    const partDisplay = document.getElementById('partIndividuelle');
    const nbDisplay = document.getElementById('nbDestinataires');

    function updateCalculations() {
        const rows = container.querySelectorAll('.numero-row');
        const nb = rows.length;
        const total = parseFloat(montantInput.value) || 0;
        const part = nb > 0 ? (total / nb) : 0;

        nbDisplay.textContent = nb;
        partDisplay.textContent = part.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Ar';

        rows.forEach(row => {
            const btn = row.querySelector('.remove-row-btn');
            btn.disabled = nb === 1;
        });
    }

    addBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.className = 'numero-row';
        newRow.innerHTML = `
            <td>
                <input type="text" name="numeros[]" class="form-control numero-input" placeholder="Ex: 034..." required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-row-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        container.appendChild(newRow);
        updateCalculations();
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row-btn')) {
            const rows = container.querySelectorAll('.numero-row');
            if (rows.length > 1) {
                e.target.closest('.numero-row').remove();
                updateCalculations();
            }
        }
    });

    montantInput.addEventListener('input', updateCalculations);
});
</script>

<?= $this->endSection() ?>