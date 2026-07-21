<div class="sidebar">
    <h2 class="logo">📱 Mobile Money</h2>

    <ul>
        <li>
            <a href="<?= site_url('admin') ?>">
                🏠 Dashboard
            </a>
        </li>
        <li>
            <a href="<?= site_url('admin/prefix') ?>">
                📞 Gestion Préfixes
            </a>
        </li>
        <li>
            <a href="<?= site_url('admin/type-operation') ?>">
                💳 Types d'opérations
            </a>
        </li>
        <!-- Nouveauté V2 : Situation Globale & Opérateurs -->
        <li>
            <a href="<?= site_url('admin/situation-compte') ?>">
                📈 Situation & Gains (V2)
            </a>
        </li>
        <li>
            <a href="<?= site_url('admin/situation-compte/operateurs') ?>">
                🔄 Montants aux Opérateurs
            </a>
        </li>
        <li class="nav-item">
    <a class="nav-link" href="<?= base_url('admin/operateur') ?>">
        <i class="bi bi-percent"></i> Commissions Opérateurs
    </a>
</li>
    </ul>
</div>