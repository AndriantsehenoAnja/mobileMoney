<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Administration Mobile Money" ?></title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background: #f4f6f9;
            color: #333;
        }

        /********************
        SIDEBAR
        ********************/
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #1f2937;
            color: white;
            position: fixed;
        }

        .logo {
            text-align: center;
            padding: 20px 15px;
            background: #111827;
            font-size: 20px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar ul li a {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            padding: 15px 20px;
            transition: .3s;
            font-size: 14px;
        }

        .sidebar ul li a:hover {
            background: #2563eb;
            color: white;
        }

        /********************
        CONTENT
        ********************/
        .content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,.05);
        }

        /* Helper Classes */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        table th {
            background: #2563eb;
            color: white;
            font-weight: 600;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .btn {
            padding: 8px 15px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            font-size: 13px;
        }

        .btn-danger { background: #dc2626; }
        .btn-success { background: #16a34a; }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-interne { background: #dcfce7; color: #15803d; }
        .badge-externe { background: #fef3c7; color: #b45309; }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <?= $this->include('admin/sidebar') ?>

    <div class="content">
        <div class="header">
            <h2><?= $page_title ?? $title ?? "Administration" ?></h2>
            <span style="font-size: 13px; color: #6b7280;">Espace Administrateur V2</span>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                ✅ <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                ❌ <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="main">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

</body>
</html>