<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['perfil'] != 'admin') {
    header('Location: dashboard_public.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
            text-align: center;
        }

        .dashboard-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-container h1 {
            margin-bottom: 20px;
        }

        .dashboard-container p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .dashboard-container a {
            display: block;
            width: calc(100% - 20px);
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin-bottom: 10px;
        }

        .dashboard-container a:hover {
            background-color: #45a049;
        }

        .dashboard-container .logout-link {
            background-color: #d9534f;
        }

        .dashboard-container .logout-link:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Bem-vindo ao Painel de Administração, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p>Aqui você pode gerenciar o sistema.</p>

        <a href="criar_backup.php">Criar Backup</a>
        <a href="logout.php" class="logout-link">Sair</a>
    </div>
</body>
</html>
