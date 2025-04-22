# PlantCare - Sistema de Gerenciamento de Plantas Domésticas

O PlantCare é um sistema web desenvolvido em PHP para ajudar no gerenciamento de plantas domésticas, permitindo o registro e acompanhamento de cuidados com as plantas.

## Funcionalidades

- Cadastro e gerenciamento de plantas
- Cadastro e gerenciamento de usuários
- Registro de cuidados (rega, poda, adubação, etc.)
- Sistema de alertas para próximos cuidados
- Recomendações automáticas de intervalos de rega
- Interface responsiva e amigável

## Tecnologias Utilizadas

- PHP 7.4+
- MySQL 5.7+
- HTML5
- CSS3
- JavaScript
- Font Awesome para ícones
- Bootstrap para estilos

## Requisitos

- Servidor web (Apache/Nginx)
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Extensão PDO PHP habilitada

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/plantcare.git
```

2. Importe o banco de dados:
```bash
mysql -u root -p < sql/schema.sql
mysql -u root -p < sql/seed.sql
```

3. Configure o banco de dados:
- Copie o arquivo `config/database.example.php` para `config/database.php`
- Edite as credenciais do banco no arquivo `config/database.php`

4. Configure o servidor web:
- Aponte o document root para a pasta do projeto
- Certifique-se que o mod_rewrite está habilitado (se usar Apache)

## Estrutura do Projeto

```
plantcare/
├── api/                # Endpoints da API
├── assets/            # Arquivos estáticos (CSS, JS, imagens)
├── config/            # Arquivos de configuração
├── sql/              # Scripts SQL (schema e seed)
└── *.php             # Páginas PHP principais
```

## Funcionalidades Principais

### Gerenciamento de Plantas
- Cadastro de novas plantas
- Registro de características (nome científico, nome popular, tipo)
- Definição de necessidades (luz, rega)

### Gerenciamento de Usuários
- Cadastro de usuários
- Definição de nível de experiência
- Associação com plantas e cuidados

### Registro de Cuidados
- Agendamento de cuidados
- Diferentes tipos de cuidados (rega, poda, adubação)
- Sistema inteligente de recomendação de intervalos
- Alertas para próximos cuidados

## Contribuição

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## Autor

Seu Nome - [seu-email@example.com](mailto:seu-email@example.com)

## Agradecimentos

- Font Awesome pela biblioteca de ícones
- Bootstrap pelo framework CSS
- Todos os contribuidores que participaram deste projeto
