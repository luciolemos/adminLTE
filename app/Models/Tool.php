<?php
namespace App\Models;

use PDO;
use Exception;

class Tool
{
    protected $pdo;

    public function __construct()
    {
        $dsn  = $_ENV['DB_DSN'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        if (!$dsn) throw new Exception('DSN do banco de dados não encontrado.');
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll()
    {
        return $this->pdo->query("SELECT * FROM tools ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tools WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data, &$errors = [])
    {
    require_once __DIR__ . '/../Helpers/validate.php';

    $desc_tool = validate_string($data['desc_tool'] ?? '', 3, 255);
    $caracteristica_tool = validate_string($data['caracteristica_tool'] ?? '', 0, 5000); // ou só trim($data['caracteristica_tool'])
    $cat_tool = validate_tool_categoria($data['cat_tool'] ?? '');
    $type_tool = validate_tool_estado($data['type_tool'] ?? '');
    $data_aquisicao = validate_date($data['data_aquisicao'] ?? '');
    $img_tool = $data['img_tool'] ?? '/assets/adminlte/dist/img/default_tool.png';

    if (!$desc_tool) $errors['desc_tool'] = 'Descrição obrigatória (mínimo 3 caracteres).';
    if (!$cat_tool) $errors['cat_tool'] = 'Selecione uma categoria válida.';
    if (!$type_tool) $errors['type_tool'] = 'Selecione um estado válido.';

    if (!empty($errors)) return false;

    $stmt = $this->pdo->prepare("
    INSERT INTO tools (
    img_tool, 
    desc_tool, 
    caracteristica_tool, 
    cat_tool, 
    type_tool, 
    data_aquisicao, 
    created_at, 
    updated_at
    )VALUES (
    :img_tool, 
    :desc_tool, 
    :caracteristica_tool, 
    :cat_tool, 
    :type_tool, 
    :data_aquisicao, 
    NOW(), 
    NOW()
    )
    ");
$stmt->execute([
    'img_tool' => $img_tool,
    'desc_tool' => $desc_tool,
    'caracteristica_tool' => $caracteristica_tool,
    'cat_tool' => $cat_tool,
    'type_tool' => $type_tool,
    'data_aquisicao' => $data_aquisicao
]);
    return true;
   }


    public function update($id, $data, &$errors = [])
    {
        require_once __DIR__ . '/../Helpers/validate.php';

        $desc_tool = validate_string($data['desc_tool'] ?? '', 3, 255);
        $caracteristica_tool = validate_string($data['caracteristica_tool'] ?? '', 0, 5000);
        $cat_tool = validate_string($data['cat_tool'] ?? '', 2, 100);
        $type_tool = validate_string($data['type_tool'] ?? '', 2, 100);
        $data_aquisicao = validate_date($data['data_aquisicao'] ?? '');
        $img_tool = $data['img_tool'] ?? '/assets/adminlte/dist/img/default_tool.png';

        if (!$desc_tool) $errors['desc_tool'] = 'Descrição obrigatória (mínimo 3 caracteres).';
        if (!$cat_tool) $errors['cat_tool'] = 'Categoria obrigatória (mínimo 2 caracteres).';
        if (!$type_tool) $errors['type_tool'] = 'Tipo obrigatório (mínimo 2 caracteres).';

        if (!empty($errors)) return false;

        $stmt = $this->pdo->prepare("
    UPDATE tools SET img_tool = :img_tool, desc_tool = :desc_tool, caracteristica_tool = :caracteristica_tool,
    cat_tool = :cat_tool, type_tool = :type_tool, data_aquisicao = :data_aquisicao, updated_at = NOW()
    WHERE id = :id
");
$stmt->execute([
    'img_tool' => $img_tool,
    'desc_tool' => $desc_tool,
    'caracteristica_tool' => $caracteristica_tool,
    'cat_tool' => $cat_tool,
    'type_tool' => $type_tool,
    'data_aquisicao' => $data_aquisicao,
    'id' => $id
]);
        return true;
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tools WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
