<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página Inicial</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #E0E0E0;
            text-align: center;
            padding-top: 50px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #1F1F1F;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        .container h2 {
            margin-bottom: 20px;
            color: #BB86FC;
        }

        .container a {
            display: block;
            margin-bottom: 15px;
            padding: 12px 25px;
            background-color: #BB86FC;
            color: #121212;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }

        .container a:hover {
            background-color: #9B63D7;
        }

        .container a:active {
            background-color: #7A49B8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bem-vindo!</h2>
        <a href="login.php">Login</a>
        <a href="register.php">Registrar</a>
    </div>
</body>
</html>