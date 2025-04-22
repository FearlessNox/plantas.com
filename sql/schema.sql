-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS plantas_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE plantas_db;

-- Criar tabela de plantas
CREATE TABLE IF NOT EXISTS plantas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cientifico VARCHAR(100) NOT NULL,
    nome_popular VARCHAR(100) NOT NULL,
    tipo_planta ENUM('Interior', 'Exterior', 'Ambos') NOT NULL,
    nivel_luz ENUM('Baixa', 'Média', 'Alta') NOT NULL,
    frequencia_rega INT NOT NULL COMMENT 'Frequência de rega em dias',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    nivel_experiencia ENUM('Iniciante', 'Intermediário', 'Avançado') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de cuidados
CREATE TABLE IF NOT EXISTS cuidados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    planta_id INT NOT NULL,
    tipo_cuidado ENUM('Rega', 'Poda', 'Adubação', 'Replantio', 'Outro') NOT NULL,
    data_cuidado DATE NOT NULL,
    intervalo_dias INT COMMENT 'Intervalo em dias para repetir o cuidado',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (planta_id) REFERENCES plantas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de log de teste de email
CREATE TABLE IF NOT EXISTS email_test_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destinatario VARCHAR(100) NOT NULL,
    status ENUM('Sucesso', 'Erro') NOT NULL,
    data_teste DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 