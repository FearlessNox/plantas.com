USE plantas_db;

-- Inserir plantas de exemplo
INSERT INTO plantas (nome_cientifico, nome_popular, tipo_planta, nivel_luz, frequencia_rega) VALUES
('Spathiphyllum wallisii', 'Lírio da Paz', 'Interior', 'Média', 7),
('Monstera deliciosa', 'Costela de Adão', 'Interior', 'Média', 7),
('Strelitzia reginae', 'Ave do Paraíso', 'Exterior', 'Alta', 3),
('Lavandula angustifolia', 'Lavanda', 'Exterior', 'Alta', 2),
('Epipremnum aureum', 'Jiboia', 'Ambos', 'Baixa', 10);

-- Inserir usuários de exemplo
INSERT INTO usuarios (nome, email, telefone, nivel_experiencia) VALUES
('Maria Silva', 'maria@email.com', '(11) 98765-4321', 'Avançado'),
('João Santos', 'joao@email.com', '(11) 91234-5678', 'Iniciante'),
('Ana Oliveira', 'ana@email.com', '(11) 97777-8888', 'Intermediário');

-- Inserir registros de cuidados de exemplo
INSERT INTO cuidados (usuario_id, planta_id, tipo_cuidado, data_cuidado, intervalo_dias, observacoes) VALUES
(1, 1, 'Rega', CURDATE(), 7, 'Regar moderadamente'),
(2, 2, 'Poda', CURDATE(), 30, 'Podar folhas amareladas'),
(3, 3, 'Adubação', CURDATE(), 15, 'Usar adubo orgânico'),
(1, 4, 'Replantio', CURDATE(), NULL, 'Trocar para vaso maior'),
(2, 5, 'Outro', CURDATE(), NULL, 'Limpar folhas com pano úmido'); 