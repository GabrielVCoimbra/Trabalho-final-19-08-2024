<?php
session_start();

require_once 'db.php';
require_once 'utils.php'; // Adiciona o arquivo utils.php

// Gera o token CSRF se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitiza e valida os dados do formulário
    $username = sanitize_input($mysqli, $_POST['username']);
    $email = sanitize_input($mysqli, $_POST['email']);
    $senha = sanitize_input($mysqli, $_POST['senha']);
    $confirm_senha = sanitize_input($mysqli, $_POST['confirm_senha']);
    $csrf_token = $_POST['csrf_token'];
    $concorda_lgpd = isset($_POST['concorda_lgpd']);

    // Verifica o token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error'] = "Token CSRF inválido.";
        header('Location: register.php');
        exit();
    }

    if (!$concorda_lgpd) {
        $_SESSION['error'] = "Você deve concordar com os termos da LGPD.";
        header('Location: register.php');
        exit();
    }

    if ($senha !== $confirm_senha) {
        $_SESSION['error'] = "As senhas não coincidem. Por favor, tente novamente.";
        header('Location: register.php');
        exit();
    }

    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result_check_user = $stmt->get_result();

    if ($result_check_user->num_rows > 0) {
        $_SESSION['error'] = "Usuário ou e-mail já registrado. Por favor, escolha outro.";
        header('Location: register.php');
        exit();
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO usuarios (username, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $senha_hash);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuário registrado com sucesso!";

        // Logando a atividade de registro de usuário
        $log_message = "Novo usuário registrado: $username, E-mail: $email";
        log_activity($log_message); // Chama a função de log

        if (isset($_POST['autenticacao_duas_etapas']) && $_POST['autenticacao_duas_etapas'] == 1) {
            $userid = $mysqli->insert_id;
            $codigo_autenticacao = rand(100000, 999999);

            $stmt = $mysqli->prepare("UPDATE usuarios SET autenticacao_habilitada=1, codigo_autenticacao=? WHERE id=?");
            $stmt->bind_param("ii", $codigo_autenticacao, $userid);
            $stmt->execute();

            $_SESSION['message'] = "Autenticação em duas etapas habilitada. Um código de autenticação foi enviado para você.";
            header('Location: autenticacao.php');
            exit();
        } else {
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Erro ao registrar o usuário: " . $stmt->error;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuário</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #E0E0E0;
            text-align: center;
            padding-top: 50px;
        }

        .register-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #1F1F1F;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #BB86FC;
        }

        .register-container form {
            text-align: left;
        }

        .register-container label {
            display: block;
            margin-bottom: 10px;
            color: #E0E0E0;
        }

        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"],
        .register-container input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #2C2C2C;
            border: 1px solid #BB86FC;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            color: #E0E0E0;
        }

        .register-container input[type="submit"],
        .register-container .btn-back {
            background-color: #BB86FC;
            color: #121212;
            padding: 15px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .register-container .btn-back {
            background-color: #CF6679;
        }

        .register-container input[type="submit"]:hover,
        .register-container .btn-back:hover {
            opacity: 0.9;
        }

        .register-container #mensagem-senha {
            display: block;
            margin-top: 5px;
            font-size: 14px;
        }

        .register-container #mensagem-senha.red {
            color: #CF6679;
        }

        .register-container #mensagem-senha.green {
            color: #03DAC6;
        }

        .register-container .terms-container {
            margin-top: 15px;
        }

        .register-container .terms-item {
            border: 1px solid #BB86FC;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            background-color: #2C2C2C;
        }

        .register-container .terms-item input[type="checkbox"] {
            margin-right: 10px;
        }

        .register-container .terms-item label {
            font-size: 14px;
            margin: 0;
            display: block;
            color: #E0E0E0;
        }
    </style>
    <script>
        function verificarSenha() {
            var senha = document.getElementById('senha').value;
            var confirmSenha = document.getElementById('confirm_senha').value;
            var mensagem = document.getElementById('mensagem-senha');
            var forte = /^(?=.[a-z])(?=.[A-Z])(?=.\d)(?=.[@$!%?&])[A-Za-z\d@$!%?&]{8,}$/;

            if (senha !== confirmSenha) {
                mensagem.className = 'red';
                mensagem.textContent = 'As senhas não coincidem.';
                return false;
            }

            if (forte.test(senha)) {
                mensagem.className = 'green';
                mensagem.textContent = 'Senha forte.';
                return true;
            } else {
                mensagem.className = 'red';
                mensagem.textContent = 'A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.';
                return false;
            }
        }
    </script>
</head>
<body>
    <div class="register-container">
        <h2>Registro de Usuário</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: #CF6679;"><?php echo $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p style="color: #03DAC6;"><?php echo $_SESSION['success']; ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <form action="register.php" method="post" onsubmit="return verificarSenha();">
            <label for="username">Nome de Usuário:</label><br>
            <input type="text" id="username" name="username" required><br><br>
            <label for="email">E-mail:</label><br>
            <input type="email" id="email" name="email" required><br><br>
            <label for="senha">Senha:</label><br>
            <input type="password" id="senha" name="senha" required oninput="verificarSenha();"><br><br>
            <label for="confirm_senha">Confirme a Senha:</label><br>
            <input type="password" id="confirm_senha" name="confirm_senha" required oninput="verificarSenha();"><br>
            <span id="mensagem-senha"></span><br><br>
            <div class="terms-container">
                <div class="terms-item">
                    <input type="checkbox" id="concorda_lgpd" name="concorda_lgpd">
                    <label for="concorda_lgpd">Eu concordo com os termos da LGPD e compreendo que o e-mail fornecido será utilizado exclusivamente para comunicação relacionada a esta aplicação.</label>
                </div>
                <div class="terms-item">
                    <input type="checkbox" id="autenticacao_duas_etapas" name="autenticacao_duas_etapas" value="1">
                    <label for="autenticacao_duas_etapas">Habilitar Autenticação em Duas Etapas</label>
                </div>
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="submit" value="Registrar">
        </form>
        <form action="index.php">
            <button type="submit" class="btn-back">Voltar para Index</button>
        </form>
    </div>
</body>
</html>