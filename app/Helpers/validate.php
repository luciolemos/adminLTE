<?php
/**
 * Helpers globais de validação e upload (centralizado).
 */

// String
function validate_string($val, $min = 1, $max = 255) {
    $val = trim($val ?? '');
    return (strlen($val) >= $min && strlen($val) <= $max) ? $val : false;
}

function validate_cpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return false;
    return $cpf;
}


// Email
function validate_email($email) {
    $email = trim($email ?? '');
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

// Enum
function validate_enum($value, $options) {
    return in_array($value, $options) ? $value : false;
}

// Data: retorna Y-m-d ou null
function validate_date($date) {
    $date = trim($date ?? '');
    if (!$date) return null;
    $d = date_create($date);
    return $d ? date_format($d, 'Y-m-d') : null;
}

// Upload de avatar de usuário
function validate_avatar_upload($file) {
    if (
        isset($file['error']) && $file['error'] === UPLOAD_ERR_OK &&
        $file['size'] > 0 && $file['size'] <= 2*1024*1024
    ) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
        if (isset($allowed[$mime])) {
            $ext = $allowed[$mime];
            $filename = uniqid('avatar_', true) . ".$ext";
            $relativeDir = '/uploads/avatars/';
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . $relativeDir;
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $destPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                return $relativeDir . $filename;
            }
        }
    }
    return null;
}


// Upload de imagem de ferramenta
function validate_img_upload($file, &$errors = [], $maxSize = 2*1024*1024)
{
    if (
        isset($file['error']) && $file['error'] === UPLOAD_ERR_OK &&
        $file['size'] > 0 && $file['size'] <= $maxSize
    ) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
        if (isset($allowed[$mime])) {
            $ext = $allowed[$mime];
            $filename = uniqid('tool_', true) . ".$ext";
            // Usar DOCUMENT_ROOT para caminho absoluto!
            $relativeDir = '/uploads/tools/';
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . $relativeDir;
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $destPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                return $relativeDir . $filename; // retorna caminho relativo para salvar no banco
            } else {
                $errors['img_tool'] = 'Falha ao mover o arquivo de upload!';
            }
        } else {
            $errors['img_tool'] = 'Formato de imagem inválido. Só JPG, PNG ou GIF.';
        }
    } elseif (isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors['img_tool'] = 'Erro no upload da imagem!';
    }
    // Caso não tenha arquivo, não adiciona erro (imagem é opcional)
    return null;
}

function validate_tool_categoria($cat) {
    $valid = [
        'Ferramentas elétricas',
        'Ferramentas hidráulicas',
        'Ferramentas de carpintaria e marcenaria',
        'Ferramentas de corte',
        'Ferramentas de medição',
        'Outras'
    ];
    return in_array($cat, $valid) ? $cat : false;
}

function validate_tool_estado($estado) {
    $valid = ['Nova','Usada','Restaurada','Danificada'];
    return in_array($estado, $valid) ? $estado : false;
}




