<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>✏️ Modifier le Barème #<?= is_array($bareme) ? $bareme['id'] : $bareme->id ?></h2>
    <a href="<?= site_url('admin/bareme') ?>" class="btn">🔙 Annuler</a>
</div>

<?php 
    $bId = is_array($bareme) ? $bareme['id'] : $bareme->id;
    $bOpId = is_array($bareme) ? $bareme['type_operation_id'] : $bareme->type_operation_id;
    $bMin = is_array($bareme) ? $bareme['montant_min'] : $bareme->montant_min;
    $bMax = is_array($bareme) ? $bareme['montant_max'] : $bareme->montant_max;
    $bFrais = is_array($bareme) ? $bareme['frais'] : $bareme->frais;
    $bComm = is_array($bareme) ? ($bareme['commission_externe'] ?? 0) : ($bareme->commission_externe ?? 0);
    $bReseau = is_array($bareme) ? ($bareme['reseau_cible'] ?? 'INTERNE') : ($bareme->reseau_cible ?? 'INTERNE');
?>

<form action="<?= site_url('admin/bareme/update/' . $bId) ?>" method="post">
    <?= csrf_field() ?>

    <div style="margin-bottom: 15px;">
        <label for="type_operation_id" style="font-weight: bold; display: block; margin-bottom: 5px;">Type d'opération :</label>
        <select name="type_operation_id" id="type_operation_id" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <?php foreach ($type_operations as $type_operation): ?>
                <?php $opId = is_array($type_operation) ? $type_operation['id'] : $type_operation->id; ?>
                <?php $opNom = is_array($type_operation) ? $type_operation['nom'] : $type_operation->nom; ?>
                <option value="<?= $opId ?>" <?= $bOpId == $opId ? 'selected' : '' ?>>
                    <?= $opNom ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="reseau_cible" style="font-weight: bold; display: block; margin-bottom: 5px;">Réseau / Destination :</label>
        <select name="reseau_cible" id="reseau_cible" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <option value="INTERNE" <?= $bReseau === 'INTERNE' ? 'selected' : '' ?>>Notre Réseau (Interne)</option>
            <option value="EXTERNE" <?= $bReseau === 'EXTERNE' ? 'selected' : '' ?>>Opérateurs Tiers / Externe</option>
        </select>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
        <div style="flex: 1;">
            <label for="montant_min" style="font-weight: bold; display: block; margin-bottom: 5px;">Montant Minimum (AR) :</label>
            <input type="number" step="0.01" min="0" name="montant_min" id="montant_min" value="<?= $bMin ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
        </div>
        <div style="flex: 1;">
            <label for="montant_max" style="font-weight: bold; display: block; margin-bottom: 5px;">Montant Maximum (AR) :</label>
            <input type="number" step="0.01" min="0" name="montant_max" id="montant_max" value="<?= $bMax ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
        </div>
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div style="flex: 1;">
            <label for="frais" style="font-weight: bold; display: block; margin-bottom: 5px;">Frais Réseau (AR) :</label>
            <input type="number" step="0.01" min="0" name="frais" id="frais" value="<?= $bFrais ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
        </div>
        <div style="flex: 1;">
            <label for="commission_externe" style="font-weight: bold; display: block; margin-bottom: 5px;">Commission Opérateur Tiers (AR) :</label>
            <input type="number" step="0.01" min="0" name="commission_externe" id="commission_externe" value="<?= $bComm ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
    </div>

    <button type="submit" class="btn" style="padding: 12px 25px; cursor: pointer; font-size: 15px;">
        💾 Mettre à jour le barème
    </button>
</form>

<?= $this->endSection() ?>