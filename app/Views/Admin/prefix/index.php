<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>📞 Liste des Préfixes Téléphoniques (V2)</h2>
    <a href="<?= site_url('Admin/prefix/form') ?>" class="btn btn-success">➕ Créer un nouveau préfixe</a>
</div>

<?php if (!empty($prefixes)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Préfixe</th>
                <th>Opérateur Associé</th>
                <th>Statut Réseau</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prefixes as $prefix): ?>
                <?php 
                    $id = is_array($prefix) ? $prefix['id'] : $prefix->id;
                    $val = is_array($prefix) ? $prefix['prefixe'] : $prefix->prefixe;
                    $op = is_array($prefix) ? ($prefix['nom_operateur'] ?? 'Non spécifié') : ($prefix->nom_operateur ?? 'Non spécifié');
                    $type = is_array($prefix) ? ($prefix['type_reseau'] ?? 'INTERNE') : ($prefix->type_reseau ?? 'INTERNE');
                ?>
                <tr>
                    <td>#<?= $id ?></td>
                    <td><strong style="font-size: 16px; color: #2563eb;"><?= esc($val) ?></strong></td>
                    <td><?= esc($op) ?></td>
                    <td>
                        <?php if ($type === 'INTERNE'): ?>
                            <span class="badge badge-interne">Notre Réseau (Compte Client OK)</span>
                        <?php else: ?>
                            <span class="badge badge-externe">Réseau Externe (Transfert seul)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= site_url('Admin/prefix/edit/' . $prefix['id']) ?>" class="btn">✏️ Modifier</a>
                        <a href="<?= site_url('Admin/prefix/delete/' . $prefix['id']) ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce préfixe ?')">🗑️ Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: #6b7280;">
        <p style="font-size: 18px;">Aucun préfixe enregistré.</p>
        <a href="<?= site_url('admin/prefix/form') ?>" class="btn btn-success" style="margin-top: 10px;">Ajouter un préfixe</a>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>