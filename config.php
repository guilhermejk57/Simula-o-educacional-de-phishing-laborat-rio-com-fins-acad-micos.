<?php
// Configurações do banco de dados MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha_aqui');
define('DB_NAME', 'linkedin_db');

// Função para conectar ao banco de dados
function conectarBanco() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Erro de conexão: " . $conn->connect_error);
            return null;
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
        
    } catch (Exception $e) {
        error_log("Exceção na conexão: " . $e->getMessage());
        return null;
    }
}

// Função para sanitizar entrada
function sanitizarEntrada($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Função para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para log de atividades
function registrarLog($acao, $detalhes = '') {
    $logFile = __DIR__ . '/logs/atividades.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'DESCONHECIDO';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'DESCONHECIDO';
    
    $logEntry = "[$timestamp] IP: $ip | Ação: $acao | Detalhes: $detalhes | User-Agent: $userAgent\n";
    
    if (!file_exists(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>