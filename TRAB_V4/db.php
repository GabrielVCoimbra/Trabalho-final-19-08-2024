<?php
// Configurações do banco de dados
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'seguro';
$port = 7306;

// Conexão com o banco de dados usando MySQLi
$mysqli = new mysqli($host, $username, $password, $database, $port);

// Verifica a conexão
if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Função para sanitizar entradas e prevenir SQL injection
if (!function_exists('sanitize_input')) {
    function sanitize_input($conn, $data) {
        return mysqli_real_escape_string($conn, $data);
    }
}
?>
