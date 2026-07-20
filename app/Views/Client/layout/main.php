<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mobile Money' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/style-client.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="brand">
            <h5>
                <span class="icon">💰</span>
                <span class="text">Mobile Money</span>
            </h5>
            <small>Espace Client</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'dashboard') ? 'active' : '' ?>" href="/Client">
                    <i class="fas fa-home"></i>
                    <span class="link-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'depot') ? 'active' : '' ?>" href="/depot">
                    <i class="fas fa-arrow-down"></i>
                    <span class="link-text">Dépôt</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'retrait') ? 'active' : '' ?>" href="/retrait">
                    <i class="fas fa-arrow-up"></i>
                    <span class="link-text">Retrait</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'transfert') ? 'active' : '' ?>" href="/transfert">
                    <i class="fas fa-exchange-alt"></i>
                    <span class="link-text">Transfert</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'historique') ? 'active' : '' ?>" href="/historique">
                    <i class="fas fa-history"></i>
                    <span class="link-text">Historique</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link logout" href="/logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="link-text">Déconnexion</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            &copy; <?= date('Y') ?> Mobile Money Operator
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="main-header">
            <h1><?= $page_title ?? 'Dashboard' ?></h1>
            <div class="user-info">
                <i class="fas fa-user-circle fa-2x" style="color: #667eea;"></i>
                <span><?= session()->get('client_nom') ?? 'Client' ?></span>
                <span class="badge bg-primary rounded-pill">
                    <?= session()->get('client_numero') ?? '' ?>
                </span>
            </div>
        </div>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>