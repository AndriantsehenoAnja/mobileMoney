<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>➕ Ajouter un nouveau Barème (V2)</h2>
    <a href="<?= site_url('Admin/type_operation') ?>">🔙 Retour</a>
</div>

<form action="<?= site_url('Admin/bareme/addbareme') ?>" method="post">
    <?= csrf_field() ?>

    <div style="margin-bottom: 15px;">
        <label for="type_operation_id" style="font-weight: bold; display: block; margin-bottom: 5px;">Type d'opération :</label>
        <select name="type_operation_id" id="type_operation_id" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <option value="">-- Sélectionner un type d'opération --</option>
            <?php foreach ($type_operations as $type_operation): ?>
                <option value="<?= is_array($type_operation) ? $type_operation['id'] : $type_operation->id ?>">
                    <?= is_array($type_operation) ? $type_operation['nom'] : $type_operation->nom ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Nouveauté V2 : Réseau Cible / Opérateur -->
    <div style="margin-bottom: 15px;">
        <label for="reseau_cible" style="font-weight: bold; display: block; margin-bottom: 5px;">Réseau / Destination :</label>
        <select name="reseau_cible" id="reseau_cible" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <option value="INTERNE">Notre Réseau (Interne)</option>
            <option value="EXTERNE">Opérateurs Tiers / Externe (Telma, Orange, Airtel)</option>
        </select>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
        <div style="flex: 1;">
            <label for="montant_min" style="font-weight: bold; display: block; margin-bottom: 5px;">Montant Minimum (AR) :</label>
            <input type="number" step="0.01" min="0" name="montant_min" id="montant_min" placeholder="Ex: 100" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
        </div>
        <div style="flex: 1;">
            <label for="montant_max" style="font-weight: bold; display: block; margin-bottom: 5px;">Montant Maximum (AR) :</label>
            <input type="number" step="0.01" min="0" name="montant_max" id="montant_max" placeholder="Ex: 50000" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
        </div>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div style="flex: 1;">
            <label for="frais" style="font-weight: bold; display: block; margin-bottom: 5px;">Frais Réseau De Base (AR) :</label>
            <input type="number" step="0.01" min="0" name="frais" id="frais" placeholder="Ex: 500" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
        </div>
        <!-- Nouveauté V2 : Commission réseau externe -->
        <div style="flex: 1;">
            <label for="commission_externe" style="font-weight: bold; display: block; margin-bottom: 5px;">Commission Opérateur Tiers (AR) :</label>
            <input type="number" step="0.01" min="0" name="commission_externe" id="commission_externe" value="0" placeholder="Ex: 200" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <small style="color: #6b7280;">S'applique uniquement si la destination est un réseau tiers.</small>
        </div>
    </div>

    <button type="submit" class="btn btn-success" style="padding: 12px 25px; cursor: pointer; font-size: 15px;">
        ➕ Enregistrer le barème
    </button>
</form>

<?= $this->endSection() ?>