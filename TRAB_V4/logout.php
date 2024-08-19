<?php
session_start();
require_once 'utils.php';

// Registro de atividade de logout
$user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuário Desconhecido';
$log_message = "Usuário '$user' realizou logout.";
log_activity($log_message);

// Limpa todas as variáveis de sessão
session_unset();

// Destrói a sessão
session_destroy();

// Remove o cookie de sessão, se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redireciona para a página inicial
header('Location: index.php');
exit();
?>

