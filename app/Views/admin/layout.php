<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= $title ?? "Administration" ?></title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    display:flex;
    background:#f4f6f9;
}

/********************
SIDEBAR
********************/

.sidebar{

    width:250px;
    min-height:100vh;
    background:#1f2937;
    color:white;
    position:fixed;
}

.logo{

    text-align:center;
    padding:25px;
    background:#111827;
}

.sidebar ul{

    list-style:none;
}

.sidebar ul li{

    border-bottom:1px solid rgba(255,255,255,.1);
}

.sidebar ul li a{

    display:block;
    color:white;
    text-decoration:none;
    padding:18px;
    transition:.3s;
}

.sidebar ul li a:hover{

    background:#2563eb;
}

/********************
CONTENT
********************/

.content{

    margin-left:250px;
    width:100%;
    padding:30px;
}

.header{

    background:white;
    padding:20px;
    border-radius:8px;
    margin-bottom:20px;
    box-shadow:0 2px 5px rgba(0,0,0,.1);
}

.main{

    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,.1);
}

table{

    width:100%;
    border-collapse:collapse;
}

table th,
table td{

    border:1px solid #ddd;
    padding:10px;
}

table th{

    background:#2563eb;
    color:white;
}

.btn{

    padding:8px 15px;
    background:#2563eb;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

.btn-danger{

    background:#dc2626;
}

.btn-success{

    background:#16a34a;
}

</style>

</head>
<body>

<?= $this->include('admin/sidebar') ?>

<div class="content">

    <div class="header">
        <h2><?= $title ?? "Administration" ?></h2>
    </div>

    <div class="main">

        <?= $this->renderSection('content') ?>

    </div>

</div>

</body>
</html>