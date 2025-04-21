# Plantas.com

Um site dedicado ao mundo das plantas, desenvolvido em PHP.

## Descrição
Este projeto é um site que oferece informações sobre plantas, incluindo:
- Catálogo de plantas
- Dicas de cultivo
- Informações sobre cuidados básicos
- Sistema de busca de plantas
- Área de usuário para salvar plantas favoritas

## Tecnologias Utilizadas
- PHP 8.2
- MySQL
- HTML5
- CSS3
- Bootstrap 5

## Requisitos
- PHP >= 8.2
- MySQL >= 8.0
- Servidor web (Apache/Nginx)
- Composer

## Instalação
1. Clone o repositório
```bash
git clone https://github.com/FearlessNox/plantas.com.git
```

2. Instale as dependências
```bash
composer install
```

3. Configure o banco de dados
- Crie um banco de dados MySQL
- Copie o arquivo `.env.example` para `.env`
- Configure as credenciais do banco de dados no arquivo `.env`

4. Execute as migrações
```bash
php artisan migrate
```

## Estrutura do Projeto
```
plantas.com/
├── public/           # Arquivos públicos
├── src/              # Código fonte
├── templates/        # Templates do site
├── config/           # Arquivos de configuração
├── database/         # Migrações e seeds
└── vendor/           # Dependências (gerenciadas pelo Composer)
```

## Contribuição
Contribuições são bem-vindas! Por favor, leia o arquivo CONTRIBUTING.md para detalhes sobre nosso código de conduta e processo de envio de pull requests.

## Licença
Este projeto está licenciado sob a Licença MIT - veja o arquivo LICENSE para detalhes. 