<?php
function log_activity($message) {
    $log_file = __DIR__ . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";

    // Abre o arquivo de log para anexar dados
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}
?>
