-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS plantas_db;
USE plantas_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de plantas
CREATE TABLE IF NOT EXISTS plantas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_popular VARCHAR(100) NOT NULL,
    nome_cientifico VARCHAR(100),
    descricao TEXT,
    necessidade_luz ENUM('baixa', 'media', 'alta'),
    necessidade_agua ENUM('baixa', 'media', 'alta'),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de cuidados
CREATE TABLE IF NOT EXISTS cuidados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    planta_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_cuidado ENUM('rega', 'poda', 'adubacao', 'transplante', 'controle_pragas') NOT NULL,
    descricao TEXT,
    data_cuidado DATE NOT NULL,
    intervalo_dias INT,
    observacoes TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (planta_id) REFERENCES plantas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Inserir alguns dados de exemplo
INSERT INTO usuarios (nome, email, senha) VALUES
('Administrador', 'admin@exemplo.com', '$2y$10$exemplo'),
('João Silva', 'joao@exemplo.com', '$2y$10$exemplo');

INSERT INTO plantas (nome_popular, nome_cientifico, necessidade_luz, necessidade_agua) VALUES
('Samambaia', 'Nephrolepis exaltata', 'media', 'alta'),
('Espada de São Jorge', 'Sansevieria trifasciata', 'baixa', 'baixa'),
('Orquídea', 'Phalaenopsis sp.', 'media', 'media'); 