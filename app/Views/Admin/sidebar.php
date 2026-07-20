<div class="sidebar">
    <h2 class="logo">📱 Mobile Money</h2>

    <ul>
        <li>
            <a href="<?= site_url('admin') ?>">
                🏠 Dashboard
            </a>
        </li>
        <li>
            <a href="<?= site_url('Admin/prefix') ?>">
                📞 Gestion Préfixes
            </a>
        </li>
        <li>
            <a href="<?= site_url('Admin/type-operation') ?>">
                💳 Types d'opérations
            </a>
        </li>
        <li>
            <a href="<?= site_url('Admin/bareme/show') ?>">
                📊 Barèmes de Frais
            </a>
        </li>
        <!-- Nouveauté V2 : Situation Globale & Opérateurs -->
        <li>
            <a href="<?= site_url('Admin/situation-compte') ?>">
                📈 Situation & Gains (V2)
            </a>
        </li>
        <li>
            <a href="<?= site_url('Admin/situation-compte/operateurs') ?>">
                🔄 Montants aux Opérateurs
            </a>
        </li>
        <li style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.2);">
            <a href="<?= site_url('logout') ?>" style="color: #f87171;">
                🚪 Déconnexion
            </a>
        </li>
    </ul>
</div>