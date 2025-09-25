/* Scrip para o console do PHP, que gera uma hash para admin123; php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"*/
/* tabela de usuários*/
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo ENUM('Admin','Editor','Moderador','Usuário') NOT NULL DEFAULT 'Usuário',
    status ENUM('Ativo','Inativo','Pendente','Banido') NOT NULL DEFAULT 'Ativo',
    avatar VARCHAR(255) DEFAULT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

/* Campos
id: Identificador único
nome: Nome do usuário
email: E-mail do usuário (único)
senha: Senha (hash)
cargo: Papel do usuário (Admin, Editor, etc.)
status: Status (Ativo, Inativo, etc.)
avatar: Caminho relativo do avatar (ou null)
data_cadastro: Data de cadastro (para estatísticas) */


/* script.sql de inseção de um usuário administrador, com senha admin123.*/
INSERT INTO usuarios (nome, email, senha, cargo, status, avatar)
VALUES (
  'Administrador',
  'admin@admin.com',
  '$2y$12$mvzFc.rgyGuH3t8DDjzp6u9TznVICc9Bev0B79mavMGzmzOhVpoEO',
  'Admin',
  'Ativo',
  NULL
);


/* tabela de ferramentas*/
CREATE TABLE tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    img_tool VARCHAR(255) DEFAULT '/assets/adminlte/dist/img/default_tool.png',
    desc_tool VARCHAR(255) NOT NULL,
    cat_tool VARCHAR(100) NOT NULL,
    type_tool VARCHAR(100) NOT NULL,
    data_aquisicao DATE DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

/* Campos
id: Identificador único
img_tool: Caminho relativo da imagem (/uploads/tools/... ou default)
desc_tool: Descrição
cat_tool: Categoria
type_tool: Tipo
data_aquisicao: Data de aquisição da ferramenta
created_at: Data/hora de criação
updated_at: Data/hora de atualização */


ALTER TABLE usuarios
  ADD COLUMN cpf VARCHAR(14) AFTER email,
  ADD COLUMN data_nascimento DATE AFTER cpf,
  ADD COLUMN cel VARCHAR(20) AFTER data_nascimento,
  ADD COLUMN logradouro VARCHAR(120) AFTER cel,
  ADD COLUMN cep VARCHAR(12) AFTER logradouro,
  ADD COLUMN cidade VARCHAR(80) AFTER cep,
  ADD COLUMN uf CHAR(2) AFTER cidade;


create table posts (
    id int auto_increment primary key,
    titulo varchar(255) not null,
    categoria ENUM('Apache','PHP','Java','Python','Javascript','MySQL','Twig','Bootstratp') NOT NULL DEFAULT 'PHP',
    slug varchar(255) not null,
    conteudo text not null,
    autor varchar(100) null,
    criado_em datetime default CURRENT_TIMESTAMP null,
    atualizado_em datetime default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP,
    constraint slug unique (slug)
);


INSERT INTO cargos_permissoes (nome, descricao) VALUES 
('post_view', 'Pode ver posts'),
('post_create', 'Pode criar posts'),
('post_edit', 'Pode editar posts'),
('post_delete', 'Pode deletar posts')
ON DUPLICATE KEY UPDATE nome=nome;


INSERT INTO permissoes (nome, descricao) VALUES 
('post_create', 'Pode criar posts'),
('post_edit', 'Pode editar posts'),
('post_delete', 'Pode deletar posts')
ON DUPLICATE KEY UPDATE nome=nome;



INSERT INTO cargos_permissoes (cargo, permissao_id) VALUES
('Admin', 9),  -- post_view
('Admin', 10), -- post_create
('Admin', 11), -- post_edit
('Admin', 12); -- post_delete