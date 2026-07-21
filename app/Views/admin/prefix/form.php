<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>📞 Ajouter un Préfixe Téléphonique (V2)</h2>
    <a href="<?= site_url('admin/prefix') ?>" class="btn">🔙 Retour à la liste</a>
</div>

<!-- Message d'erreur -->
<?php if (session()->getFlashdata('error')) : ?>
    <div style="color: red; margin-bottom: 15px; padding: 10px; border: 1px solid red; border-radius: 4px;">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<form action="<?= site_url('admin/prefix/create') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Champ Préfixe -->
    <div style="margin-bottom: 15px;">
        <label for="prefixe" style="font-weight: bold; display: block; margin-bottom: 5px;">Préfixe (Ex: 034, 032, 033) :</label>
        <input type="text" 
               id="prefixe" 
               name="prefixe" 
               maxlength="5" 
               placeholder="Ex: 034" 
               value="<?= old('prefixe') ?>"
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" 
               required>
    </div>

    <!-- Nom de l'Opérateur -->
    <div style="margin-bottom: 15px;">
        <label for="nom_operateur" style="font-weight: bold; display: block; margin-bottom: 5px;">Nom de l'Opérateur :</label>
        <input type="text" 
               id="nom_operateur" 
               name="nom_operateur" 
               placeholder="Ex: Orange, Telma, Airtel" 
               value="<?= old('nom_operateur') ?>"
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" 
               required>
    </div>

    <!-- Type de Réseau -->
    <div style="margin-bottom: 15px;">
        <label for="est_notre_operateur" style="font-weight: bold; display: block; margin-bottom: 5px;">Type de Réseau :</label>
        <select name="est_notre_operateur" id="est_notre_operateur" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <option value="1">Notre Réseau (Interne - Connexion & Comptes autorisés)</option>
            <option value="0">Opérateur Tiers (Externe - Transfert sortant uniquement)</option>
        </select>
    </div>

    <!-- Commission pour opérateur tiers -->
    <div style="margin-bottom: 20px;">
        <label for="commission" style="font-weight: bold; display: block; margin-bottom: 5px;">Commission (%) :</label>
        <input type="number" 
               step="0.01" 
               min="0" 
               id="commission" 
               name="commission" 
               placeholder="Ex: 2.5 (Laisser 0 si réseau interne)" 
               value="<?= old('commission', 0) ?>"
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
    </div>

    <button type="submit" class="btn btn-success" style="padding: 12px 25px; cursor: pointer; font-size: 15px;">
        ➕ Enregistrer le préfixe
    </button>
</form>

<?= $this->endSection() ?>