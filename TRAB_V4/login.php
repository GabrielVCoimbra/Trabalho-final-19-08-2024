<?php
session_start();
require_once 'db.php';
require_once 'utils.php'; // Inclua a função de log

// Gera o token CSRF se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($mysqli, $_POST['username']);
    $password = sanitize_input($mysqli, $_POST['senha']);
    $csrf_token = $_POST['csrf_token'];

    // Verifica o token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error'] = "Token CSRF inválido.";
        log_activity("Tentativa de login falhou: CSRF inválido para usuário '$username'.");
        header('Location: login.php');
        exit();
    }

    // Prepara a query usando prepared statements
    $stmt = $mysqli->prepare("SELECT id, senha, perfil, autenticacao_habilitada FROM usuarios WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['senha'])) {
            $_SESSION['userid'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['perfil'] = $user['perfil']; // Adiciona o perfil à sessão

            // Regenera o token CSRF após login bem-sucedido
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            log_activity("Usuário '$username' logado com sucesso.");

            // Redireciona para o local apropriado com base no perfil e autenticação em duas etapas
            if ($user['autenticacao_habilitada']) {
                header('Location: autenticacao.php');
            } else {
                $redirect_page = ($user['perfil'] === 'admin') ? 'dashboard.php' : 'dashboard_public.php';
                header('Location: ' . $redirect_page);
            }
            exit();
        } else {
            $_SESSION['error'] = "Credenciais incorretas.";
            log_activity("Tentativa de login falhou: Credenciais incorretas para usuário '$username'.");
        }
    } else {
        $_SESSION['error'] = "Usuário não encontrado.";
        log_activity("Tentativa de login falhou: Usuário '$username' não encontrado.");
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #E0E0E0;
            text-align: center;
            padding-top: 50px;
        }

        .login-container {
            max-width: 300px;
            margin: 0 auto;
            background-color: #1F1F1F;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #BB86FC;
        }

        .login-container form {
            text-align: left;
        }

        .login-container label {
            display: block;
            margin-bottom: 10px;
            color: #E0E0E0;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            background-color: #2C2C2C;
            border: 1px solid #BB86FC;
            border-radius: 5px;
            color: #E0E0E0;
        }

        .login-container input[type="submit"],
        .login-container .btn-back {
            width: 100%;
            padding: 10px;
            background-color: #BB86FC;
            color: #121212;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
            transition: background-color 0.3s ease;
        }

        .login-container .btn-back {
            background-color: #CF6679;
        }

        .login-container input[type="submit"]:hover,
        .login-container .btn-back:hover {
            opacity: 0.9;
        }

        .error-message {
            color: #CF6679;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error-message"><?php echo $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="username">Nome de Usuário:</label><br>
            <input type="text" id="username" name="username" required><br><br>
            <label for="senha">Senha:</label><br>
            <input type="password" id="senha" name="senha" required><br><br>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="submit" value="Login">
        </form>
        <form action="index.php">
            <button type="submit" class="btn-back">Voltar para Index</button>
        </form>
    </div>
</body>
</html>
