<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.php';

$userid = $_SESSION['userid'];
$codigo = isset($_POST['codigo']) ? $_POST['codigo'] : null;

// Verificar se o formulário foi submetido para tentar novamente
if (isset($_POST['tentar_novamente'])) {
    // Limpar a mensagem de erro anterior e permitir nova tentativa
    unset($_SESSION['error']);
    header('Location: autenticacao.php'); // Redireciona para autenticacao.php para tentar novamente
    exit();
}

// Verificar se o formulário foi submetido para abortar
if (isset($_POST['abortar'])) {
    header('Location: index.php'); // Redireciona para index.php ao abortar
    exit();
}

// Verificar se o código foi submetido e realizar a verificação
if ($codigo !== null) {
    // Verifica código de autenticação no banco de dados de forma segura com prepared statement
    $sql = "SELECT codigo_autenticacao FROM usuarios WHERE id=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $codigo_autenticacao_bd = $row['codigo_autenticacao'];

        if ($codigo == $codigo_autenticacao_bd) {
            // Código correto, conclui autenticação em duas etapas de forma segura com prepared statement
            $sql_update = "UPDATE usuarios SET codigo_autenticacao=NULL WHERE id=?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("i", $userid);

            if ($stmt_update->execute()) {
                $_SESSION['message'] = "Autenticação em duas etapas concluída!";
                header('Location: dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = "Erro ao concluir autenticação em duas etapas: " . $mysqli->error;
            }

            $stmt_update->close();
        } else {
            $_SESSION['error'] = "Código de autenticação incorreto!";
            $_SESSION['show_retry_buttons'] = true; // Definir flag para mostrar botões de tentar novamente e abortar
        }
    } else {
        $_SESSION['error'] = "Erro ao verificar código de autenticação.";
    }

    $stmt->close();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Verificar Código de Autenticação</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #E0E0E0;
            text-align: center;
            padding-top: 50px;
        }

        .error-message {
            color: #CF6679;
            margin-bottom: 10px;
        }

        .success-message {
            color: #03DAC6;
            margin-bottom: 10px;
        }

        .retry-buttons {
            margin-top: 20px;
        }

        .retry-buttons input[type="submit"] {
            padding: 10px 20px;
            margin: 10px;
            background-color: #BB86FC;
            color: #121212;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .retry-buttons input[type="submit"]:hover {
            background-color: #A675E1;
        }
    </style>
</head>
<body>
    <h2>Verificar Código de Autenticação</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-message"><?php echo $_SESSION['error']; ?></p>
        <?php if (isset($_SESSION['show_retry_buttons']) && $_SESSION['show_retry_buttons']): ?>
            <form class="retry-buttons" method="post">
                <input type="submit" name="abortar" value="Abortar">
                <input type="submit" name="tentar_novamente" value="Tentar Novamente">
                <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($codigo); ?>">
            </form>
        <?php endif; ?>
        <?php unset($_SESSION['error']); ?>
        <?php unset($_SESSION['show_retry_buttons']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-message"><?php echo $_SESSION['message']; ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
</body>
</html>