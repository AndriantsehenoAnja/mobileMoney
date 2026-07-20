<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>📊 Configuration des Barèmes de Frais (V2)</h2>
    <a href="<?= site_url('admin/bareme/addbareme') ?>" class="btn btn-success">➕ Nouveau Barème</a>
</div>

<?php if (!empty($baremes)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type d'opération</th>
                <th>Destination</th>
                <th>Tranche de Montant</th>
                <th>Frais Réseau</th>
                <th>Comm. Externe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($baremes as $bareme): ?>
                <?php 
                    $id = is_array($bareme) ? $bareme['id'] : $bareme->id;
                    $opNom = is_array($bareme) ? ($bareme['type_nom'] ?? 'N/A') : ($bareme->type_nom ?? 'N/A');
                    $min = is_array($bareme) ? $bareme['montant_min'] : $bareme->montant_min;
                    $max = is_array($bareme) ? $bareme['montant_max'] : $bareme->montant_max;
                    $frais = is_array($bareme) ? $bareme['frais'] : $bareme->frais;
                    $comm = is_array($bareme) ? ($bareme['commission_externe'] ?? 0) : ($bareme->commission_externe ?? 0);
                    $reseau = is_array($bareme) ? ($bareme['reseau_cible'] ?? 'INTERNE') : ($bareme->reseau_cible ?? 'INTERNE');
                ?>
                <tr>
                    <td>#<?= $id ?></td>
                    <td><strong><?= esc($opNom) ?></strong></td>
                    <td>
                        <?php if ($reseau === 'EXTERNE'): ?>
                            <span class="badge badge-externe">Opérateurs Tiers</span>
                        <?php else: ?>
                            <span class="badge badge-interne">Notre Réseau</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= number_format($min, 2, ',', ' ') ?> AR &rarr; <?= number_format($max, 2, ',', ' ') ?> AR
                    </td>
                    <td><?= number_format($frais, 2, ',', ' ') ?> AR</td>
                    <td>
                        <?php if ($comm > 0): ?>
                            <span style="color: #b45309; font-weight: bold;">+<?= number_format($comm, 2, ',', ' ') ?> AR</span>
                        <?php else: ?>
                            <span style="color: #9ca3af;">0,00 AR</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= site_url('admin/bareme/edit/' . $id) ?>" class="btn">✏️ Modifier</a>
                        <a href="<?= site_url('admin/bareme/delete/' . $id) ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce barème ?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: #6b7280;">
        <p style="font-size: 18px;">Aucun barème de frais configuré.</p>
        <a href="<?= site_url('admin/bareme/addbareme') ?>" class="btn btn-success" style="margin-top: 10px;">Créer le premier barème</a>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>