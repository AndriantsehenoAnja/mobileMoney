<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Types d'Opérations</h2>
    <div>
        <a href="<?= site_url('admin/bareme/addbareme') ?>" class="btn btn-success">➕ Nouveau Barème</a>
    </div>
</div>

<p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">
    Liste des catégories d'opérations financières supportées par la plateforme (Transferts locaux, Transferts vers opérateurs tiers, Retraits, etc.).
</p>

<?php if (!empty($type_operations)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Type d'Opération</th>
                <th>Barèmes Associés</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($type_operations as $type_operation): ?>
                <?php 
                    $id = is_array($type_operation) ? $type_operation['id'] : $type_operation->id;
                    $nom = is_array($type_operation) ? $type_operation['nom'] : $type_operation->nom;
                ?>
                <tr>
                    <td>#<?= $id ?></td>
                    <td><strong style="font-size: 15px; color: #1e293b;"><?= esc($nom) ?></strong></td>
                    <td>
                        <a href="<?= site_url('admin/bareme/showbytypeoperation/' . $id) ?>" class="btn" style="background-color: #e2e8f0; color: #1e293b;">
                            📋 Voir les barèmes de cette opération
                        </a>
                    </td>
                    <td>
                        <!-- Si vous permettez l'édition des types d'opérations -->
                        <a href="<?= site_url('admin/type-operation/edit/' . $id) ?>" class="btn">✏️ Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: #6b7280;">
        <p style="font-size: 16px;">Aucun type d'opération configuré.</p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>