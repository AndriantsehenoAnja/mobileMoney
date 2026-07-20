<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<?php 
    $pId = is_array($prefix) ? $prefix['id'] : $prefix->id;
    $pVal = is_array($prefix) ? $prefix['prefixe'] : $prefix->prefixe;
    $pOp = is_array($prefix) ? ($prefix['nom_operateur'] ?? '') : ($prefix->nom_operateur ?? '');
    $pType = is_array($prefix) ? ($prefix['type_reseau'] ?? 'EXTERNE') : ($prefix->type_reseau ?? 'EXTERNE');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>✏️ Modifier le Préfixe #<?= $pId ?></h2>
    <a href="<?= site_url('admin/prefix') ?>" class="btn">🔙 Annuler</a>
</div>

<form action="<?= site_url('admin/prefix/update/' . $pId) ?>" method="post">
    <?= csrf_field() ?>

    <div style="margin-bottom: 15px;">
        <label for="prefixe" style="font-weight: bold; display: block; margin-bottom: 5px;">Préfixe :</label>
        <input type="text" 
               id="prefixe" 
               name="prefixe" 
               value="<?= esc($pVal) ?>" 
               maxlength="5" 
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" 
               required>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="nom_operateur" style="font-weight: bold; display: block; margin-bottom: 5px;">Nom de l'Opérateur :</label>
        <input type="text" 
               id="nom_operateur" 
               name="nom_operateur" 
               value="<?= esc($pOp) ?>" 
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" 
               required>
    </div>

    <div style="margin-bottom: 20px;">
        <label for="type_reseau" style="font-weight: bold; display: block; margin-bottom: 5px;">Type de Réseau :</label>
        <select name="type_reseau" id="type_reseau" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <option value="INTERNE" <?= $pType === 'INTERNE' ? 'selected' : '' ?>>Notre Réseau (Interne)</option>
            <option value="EXTERNE" <?= $pType === 'EXTERNE' ? 'selected' : '' ?>>Opérateur Tiers (Externe)</option>
        </select>
    </div>

    <button type="submit" class="btn" style="padding: 12px 25px; cursor: pointer; font-size: 15px;">
        💾 Mettre à jour
    </button>
</form>

<?= $this->endSection() ?>