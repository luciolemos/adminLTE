# AdminLTE MVC Starter

Sistema administrativo PHP 8.4 (MVC) com autenticação, CRUD de usuários, proteção de rotas, usando o AdminLTE3 integrado, flash messages com toasts, e fácil expansão para dashboards, relatórios e widgets.

## Sumário

- [Sobre o Projeto](#sobre-o-projeto)
- [Tecnologias e Recursos](#tecnologias-e-recursos)
- [Instalação Rápida](#instalação-rápida)
- [Estrutura de Diretórios](#estrutura-de-diretórios)
- [Configuração (.env)](#configuração-env)
- [Banco de Dados](#banco-de-dados)
- [Fluxo de Requisições](#fluxo-de-requisições)
- [Exemplos de Rotas](#exemplos-de-rotas)
- [Composer.json](#composerjson)
- [.htaccess](#htaccess)
- [Funcionalidades Prontas](#funcionalidades-prontas)
- [Toasts e Flash Messages](#toasts-e-flash-messages)
- [Expansão](#expansão)
- [Equipe & Créditos](#equipe--créditos)

## Sobre o Projeto

Este projeto implementa uma base moderna para sistemas administrativos PHP, usando:
- **MVC modular**
- **AdminLTE 3** como base visual em site e painel admin
- **Twig** para views
- **Slim-like roteamento** (dinâmico, com parâmetros)
- **Autenticação via sessão** (login/logout)
- **CRUD de usuários** completo (Admin)
- **Proteção de rotas com middleware**
- **Flash messages** (exibidos como toasts Bootstrap/AdminLTE)
- **Pronto para personalizar (widgets, relatórios, etc.)**

## Tecnologias e Recursos

- **PHP 8.4**
- **Composer** (autoload PSR-4)
- **Twig 3** (templates)
- **vlucas/phpdotenv** (variáveis de ambiente)
- **AdminLTE 3.2** (tema visual completo, com sidebar, navbar, widgets)
- **MySQL** (PDO, UTF8)
- **Bootstrap 4 (bundle)** (toast, grid, alerts, etc.)
- **jQuery** (AdminLTE depende)
- **Slim Router Custom** (roteamento dinâmico)
- **Flash Helper** (helpers PHP para mensagens flash)

## Instalação Rápida

```sh

git clone <este-repo>
cd site1
composer install
cp .env.example .env      # configure seu .env conforme abaixo
# Crie o banco de dados, depois a tabela "usuarios"
# Ajuste permissões do Apache/Nginx e hosts conforme seu ambiente
```

## Estrutura de Diretórios
```bash
site1/
├── app/
│   ├── Config/
│   │   └── Database.php
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Auth/
│   │   ├── Site/
│   ├── Core/
│   ├── Helpers/
│   │   └── flash.php
│   ├── Middlewares/
│   ├── Models/
│   │   └── User.php
│   ├── Routes/
│   ├── Views/
│   │   ├── Admin/
│   │   ├── Auth/
│   │   ├── Site/
│   │   ├── Partials/
│   │   ├── errors/
│   │   ├── base.twig
│   │   ├── layout_admin.twig
│   │   └── README.twig
├── composer.json
├── .env
├── public/
│   ├── assets/
│   │   └── adminlte/
│   ├── .htaccess
│   └── index.php
└──
```

## Configuração (.env)
```txt

    DB_DSN=mysql:host=localhost;dbname=sistema_admin;charset=utf8
    DB_USER=luciolemos
    DB_PASS=Lemos35@
    APP_DEBUG=true
    APP_TIMEZONE=America/Sao_Paulo
    APP_URL=http://site1.test
    APP_LOCALE=pt_BR
```

## Banco de Dados
```sql

-- Criação do banco de dados (se necessário)
CREATE DATABASE IF NOT EXISTS sistema_admin DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_admin;

-- Criação da tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    avatar VARCHAR(255) DEFAULT NULL,
    cargo ENUM('Admin', 'Editor', 'Moderador', 'Usuário') NOT NULL DEFAULT 'Usuário',
    status ENUM('Ativo', 'Inativo', 'Pendente', 'Banido') NOT NULL DEFAULT 'Ativo',
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME DEFAULT NULL,
    senha VARCHAR(255) NOT NULL COMMENT 'Hash da senha',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserção de dados fictícios
INSERT INTO usuarios (nome, email, avatar, cargo, status, data_cadastro, ultimo_login, senha) VALUES
('Administrador Principal', 'admin@exemplo.com', NULL, 'Admin', 'Ativo', '2023-01-01 10:00:00', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Editor Chefe', 'editor@exemplo.com', NULL, 'Editor', 'Ativo', '2023-01-15 11:30:00', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Moderador Sênior', 'moderador@exemplo.com', NULL, 'Moderador', 'Ativo', '2023-02-01 09:15:00', DATE_SUB(NOW(), INTERVAL 2 DAY), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Usuário Teste 1', 'usuario1@exemplo.com', NULL, 'Usuário', 'Ativo', '2023-02-10 14:20:00', DATE_SUB(NOW(), INTERVAL 5 DAY), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Usuário Teste 2', 'usuario2@exemplo.com', NULL, 'Usuário', 'Inativo', '2023-02-15 16:45:00', DATE_SUB(NOW(), INTERVAL 10 DAY), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Usuário Teste 3', 'usuario3@exemplo.com', NULL, 'Usuário', 'Pendente', '2023-03-01 08:00:00', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Usuário Banido', 'banido@exemplo.com', NULL, 'Usuário', 'Banido', '2023-03-05 13:10:00', DATE_SUB(NOW(), INTERVAL 15 DAY), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Editor Júnior', 'editor2@exemplo.com', NULL, 'Editor', 'Ativo', '2023-03-10 10:30:00', DATE_SUB(NOW(), INTERVAL 1 DAY), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Moderador Regional', 'moderador2@exemplo.com', NULL, 'Moderador', 'Ativo', '2023-03-15 11:45:00', DATE_SUB(NOW(), INTERVAL 3 DAY), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Usuário VIP', 'vip@exemplo.com', NULL, 'Usuário', 'Ativo', '2023-03-20 09:00:00', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserção de mais 40 usuários fictícios (totalizando 50)
INSERT INTO usuarios (nome, email, cargo, status, data_cadastro, ultimo_login, senha)
SELECT 
    CONCAT('Usuário ', n.n, ' ', 
           CASE WHEN n.n % 3 = 0 THEN 'Silva' WHEN n.n % 3 = 1 THEN 'Santos' ELSE 'Oliveira' END) AS nome,
    CONCAT('usuario', n.n + 10, '@exemplo.com') AS email,
    CASE 
        WHEN n.n % 10 = 0 THEN 'Admin'
        WHEN n.n % 5 = 0 THEN 'Editor'
        WHEN n.n % 3 = 0 THEN 'Moderador'
        ELSE 'Usuário'
    END AS cargo,
    CASE 
        WHEN n.n % 20 = 0 THEN 'Banido'
        WHEN n.n % 10 = 0 THEN 'Inativo'
        WHEN n.n % 7 = 0 THEN 'Pendente'
        ELSE 'Ativo'
    END AS status,
    DATE_SUB(NOW(), INTERVAL n.n DAY) AS data_cadastro,
    CASE 
        WHEN n.n % 6 = 0 THEN NULL
        ELSE DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) HOUR)
    END AS ultimo_login,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' AS senha
FROM (
    SELECT a.N + b.N * 10 + 1 AS n
    FROM 
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
        (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) b
    ORDER BY n
) n
WHERE n.n <= 40;

-- Atualiza alguns avatares com URLs fictícias
UPDATE usuarios SET avatar = CONCAT('https://i.pravatar.cc/150?img=', id) WHERE id <= 50;
```
## Fluxo de Requisições

```mermaid
flowchart TD
    A[Navegador solicita URL] --> B[index.php (Front Controller)]
    B --> C[App.php (Bootstrap)]
    C --> D[Router.php (despacha rota)]
    D -->|rota pública| E[Controllers/Site/*]
    D -->|rota auth| F[Controllers/Auth/*]
    D -->|rota /admin/*| G[AuthMiddleware verifica sessão]
    G -->|Autenticado| H[Controllers/Admin/*]
    G -->|Não autenticado| I[redirect para /login]
    E & F & H --> J[View Twig renderizada]
    J --> K[AdminLTE Layout & Toasts]
    K --> L[HTML entregue ao navegador]
```
## Exemplos de Rotas  
   
```bash

$router->add('GET', '/admin/dashboard', 'Admin\\DashboardController@index');
$router->add('GET', '/admin/users', 'Admin\\UserController@index');
$router->add('GET', '/admin/users/create', 'Admin\\UserController@create');
$router->add('POST', '/admin/users/create', 'Admin\\UserController@create');
$router->add('GET', '/admin/users/edit/{id}', 'Admin\\UserController@edit');
$router->add('POST', '/admin/users/edit/{id}', 'Admin\\UserController@edit');
$router->add('POST', '/admin/users/delete/{id}', 'Admin\\UserController@delete');

```
Para site público, exemplo em web.php:

```bash

 $router->add('GET', '/', 'Site\\HomeController@index');
 $router->add('GET', '/about', 'Site\\PageController@about');
 $router->add('GET', '/contact', 'Site\\PageController@contact');
 $router->add('GET', '/readme', 'Site\\PageController@readme');

```

## Composer.json
```json
    {
      "name": "luciolemos/my-app",
      "description": "My PHP MVC Application",
      "version": "1.0.0",
      "require": {
         "php": "^8.4",
         "slim/slim": "^4.0",
         "twig/twig": "^3.0",
         "vlucas/phpdotenv": "^5.6"
      },
      "autoload": {
         "psr-4": {
         "App\\": "app/"
         }
      },
      "config": {
         "optimize-autoloader": true,
         "preferred-install": "dist",
         "platform": {
         "php": "8.4"
         }
       }
     }
```     

## .htaccess
```txt
    <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteBase /

    # Redireciona tudo que não for arquivo real para index.php
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteRule ^ index.php [QSA,L]
    </IfModule>
```

## Funcionalidades
- [x] Estrutura MVC, roteamento dinâmico
- [x] Autenticação, sessão, logout/login
- [x] CRUD de usuários (Admin)
- [x] Middleware de proteção de rotas
- [x] Flash messages via sessão (toast/alerta)
- [x] Sidebar e navegação AdminLTE
- [x] Layouts e views customizáveis com Twig
- [x] Validação básica (server-side)
- [x] Página pública README (manual dentro do site)


## Toasts e Flash Messages
- Use set_flash('success', 'mensagem') e set_flash('error', 'mensagem') nos controllers.

- O layout_admin.twig já mostra as mensagens como toasts Bootstrap.

- Suporte tanto para alertas tradicionais quanto para toasts flutuantes.

## Expansão
- Fácil adicionar dashboard widgets, gráficos (chart.js, etc)

- Pronto para expandir com módulos (relatórios, exportação, multi-empresa)

- Permissões avançadas via RoleMiddleware (a implementar)

- Pronto para internacionalização (APP_LOCALE)

## Equipe & Créditos
- Base inicial por [luciolemos]

- Layout AdminLTE (https://adminlte.io)

- MVC inspirado em Slim Framework e Laravel (mas 100% autoral)

- Apoio e automação por Click here to try a new GPT!

## Quando você “renomeia um card” (ex.: de “Ferramentas de carpintaria e marcenaria” para “Ferramentas de marcenaria”), há alguns lugares típicos que precisam mudar para tudo ficar consistente.
### Guia rápido (ordem recomendada)

#### 1. Fonte da Verdade (validação)

- app/Helpers/validate.php
  - Atualize a lista do validate_tool_categoria() (ou função equivalente).
  -⚠️ Se você mantiver outra lista em qualquer lugar, você cria divergência. O ideal é que só exista essa lista (ou que ela venha de um único config).

#### 2. Banco de dados

- Se a coluna tools.cat_tool for ENUM:
  - ALTER TABLE para incluir o novo literal e remover o antigo.
  - (Opcional) UPDATE para migrar registros antigos para o novo rótulo.

- Se for VARCHAR:
  - Nada a mudar no schema, mas pode valer um UPDATE para padronizar rótulos legados.

#### 3. Model

- app/Models/Tool.php
  - create() já usa validate_tool_categoria() — ok.
  - update(): garanta que também use validate_tool_categoria() (e não validate_string()).
  - Se houver métodos como countByCategory($cat), certifique-se de que os lugares que o chamam passem o novo rótulo.

#### 4. Controller

- app/Controllers/Admin/ToolController.php
  - dashboard(): atualize $cat_options para refletir o novo rótulo (é o que alimenta contadores e links).
  - Em qualquer lugar que gere links com ?cat_tool=..., use o novo texto.

#### 5. Views (Twig)

- Formulário
  - Views/Admin/Tools/_form.twig: atualize a lista do `<select>` de categorias.

- Dashboard de Ferramentas
  - Views/Admin/Tools/dashboard.twig: atualize o rótulo exibido e o href dos cards:
    - Ex.: /admin/tools?cat_tool=Ferramentas de marcenaria.

- Listagem (index)
  - Views/Admin/Tools/index.twig: o JS já filtra por ?cat_tool=; não precisa mudar se você passar exatamente o novo texto no link do card.

- Outras views (se exibirem a categoria “hardcoded” em textos, badges, tooltips etc).

#### 6. Seeds/Migrations/Fixtures (se existirem)

- Atualize valores default/seed para a nova categoria.
- Garante que dados de demo não ressuscitem o rótulo antigo.

#### 7. Cache
- Limpe Twig cache e OPcache após as mudanças (se ativos).

#### 8. Traduções (se houver i18n)
- Atualize arquivos de tradução que referenciem o rótulo.

