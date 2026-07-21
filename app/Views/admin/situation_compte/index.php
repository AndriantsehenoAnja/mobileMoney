<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>👥 Situation Récapitulative des Comptes Clients</h2>
    <a href="<?= site_url('Admin/situation-compte/gain-total') ?>" class="btn btn-success">💰 Voir rapport des gains</a>
</div>

<?php if (!empty($clientsSituation)): ?>
    <table>
        <thead>
            <tr>
                <th>N° Compte</th>
                <th>Nom du Client</th>
                <th>Numéro Mobile</th>
                <th>Opérateur / Préfixe</th>
                <th>Date d'Inscription</th>
                <th style="text-align: right;">Solde Actuel</th>
            </tr>
        </thead>
        <tbody>
            <?php $masseMonetaire = 0; ?>
            <?php foreach ($clientsSituation as $row): ?>
                <?php 
                    $cId = is_array($row) ? ($row['compte_id'] ?? 'N/A') : ($row->compte_id ?? 'N/A');
                    $nom = is_array($row) ? $row['nom'] : $row->nom;
                    $pref = is_array($row) ? ($row['prefixe'] ?? '') : ($row->prefixe ?? '');
                    $num = is_array($row) ? $row['numero'] : $row->numero;
                    $opNom = is_array($row) ? ($row['nom_operateur'] ?? 'Notre Réseau') : ($row->nom_operateur ?? 'Notre Réseau');
                    $dateC = is_array($row) ? $row['date_creation'] : $row->date_creation;
                    $solde = is_array($row) ? ($row['solde'] ?? 0) : ($row->solde ?? 0);
                    
                    $masseMonetaire += $solde;
                ?>
                <tr>
                    <td>#<?= esc($cId) ?></td>
                    <td><strong><?= esc($nom) ?></strong></td>
                    <td><?= esc($pref) ?> <?= esc($num) ?></td>
                    <td>
                        <span class="badge badge-interne"><?= esc($opNom) ?></span>
                    </td>
                    <td><small><?= date('d/m/Y H:i', strtotime($dateC)) ?></small></td>
                    <td style="text-align: right; font-weight: bold; color: #16a34a;">
                        <?= number_format($solde, 2, ',', ' ') ?> AR
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background: #f8fafc; font-weight: bold;">
            <tr>
                <td colspan="5" style="text-align: right;">Masse Monétaire Totale (Soldes Clients) :</td>
                <td style="text-align: right; color: #2563eb; font-size: 16px;">
                    <?= number_format($masseMonetaire, 2, ',', ' ') ?> AR
                </td>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 40px; color: #6b7280;">
        <p style="font-size: 16px;">Aucun compte client enregistré pour le moment.</p>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>