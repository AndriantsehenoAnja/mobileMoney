<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>📞 Ajouter un Préfixe Téléphonique (V2)</h2>
    <a href="<?= site_url('admin/prefix') ?>" class="btn">🔙 Retour à la liste</a>
</div>

<form action="<?= site_url('Admin/prefix/create') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Champ Préfixe -->
    <div style="margin-bottom: 15px;">
        <label for="prefixe" style="font-weight: bold; display: block; margin-bottom: 5px;">Préfixe (Ex: 034, 032, 033) :</label>
        <input type="text" 
               id="prefixe" 
               name="prefixe" 
               maxlength="5" 
               placeholder="Ex: 034" 
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" 
               required>
    </div>

    <!-- Nouveauté V2 : Nom de l'Opérateur associé -->
    <div style="margin-bottom: 15px;">
        <label for="nom_operateur" style="font-weight: bold; display: block; margin-bottom: 5px;">Nom de l'Opérateur :</label>
        <input type="text" 
               id="nom_operateur" 
               name="nom_operateur" 
               placeholder="Ex: Orange, Telma, Airtel, Notre Réseau" 
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" 
               required>
    </div>

    <!-- Nouveauté V2 : Type de Réseau (Interne vs Externe) -->
    <div style="margin-bottom: 20px;">
        <label for="type_reseau" style="font-weight: bold; display: block; margin-bottom: 5px;">Type de Réseau :</label>
        <select name="type_reseau" id="type_reseau" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required>
            <option value="INTERNE">Notre Réseau (Autorise la connexion & compte client)</option>
            <option value="EXTERNE">Opérateur Tiers (Transfert sortant uniquement)</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success" style="padding: 12px 25px; cursor: pointer; font-size: 15px;">
        ➕ Enregistrer le préfixe
    </button>
</form>

<?= $this->endSection() ?>