<?php

namespace App\Utils;

class FileUploader
{
    private const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    
    private const ALLOWED_MIMES = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    
    /**
     * Validar arquivo de upload
     * 
     * @param array $file Arquivo do $_FILES
     * @return array Lista de erros (vazio se válido)
     */
    public static function validate(array $file): array
    {
        $errors = [];
        
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erro no upload do arquivo.';
            return $errors;
        }
        
        // Validar tamanho
        if ($file['size'] > self::MAX_SIZE) {
            $errors[] = 'Arquivo muito grande. Tamanho máximo: 5MB.';
        }
        
        // Validar MIME type real (não apenas extensão)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, self::ALLOWED_MIMES)) {
                $errors[] = 'Tipo de arquivo não permitido. Use: PDF, JPG, PNG, DOC ou DOCX.';
            }
        }
        
        // Validar extensão
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED_MIMES[$extension])) {
            $errors[] = 'Extensão de arquivo não permitida.';
        }
        
        return $errors;
    }
    
    /**
     * Fazer upload de arquivo com validação robusta
     * 
     * @param array $file Arquivo do $_FILES
     * @param string $directory Diretório relativo (ex: 'storage/uploads/justifications')
     * @return string Path relativo do arquivo salvo
     * @throws \Exception Se validação falhar ou upload falhar
     */
    public static function upload(array $file, string $directory): string
    {
        // Validar primeiro
        $errors = self::validate($file);
        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }
        
        // Criar diretório se não existir
        $uploadDir = BASE_PATH . '/' . trim($directory, '/') . '/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new \Exception('Não foi possível criar o diretório de upload.');
            }
        }
        
        // Gerar nome seguro (evita path traversal)
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        // Mover arquivo
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('Falha ao salvar arquivo.');
        }
        
        // Retornar path relativo
        return trim($directory, '/') . '/' . $fileName;
    }
    
    /**
     * Deletar arquivo de upload
     * 
     * @param string $path Path relativo do arquivo
     * @return bool
     */
    public static function delete(string $path): bool
    {
        $fullPath = BASE_PATH . '/' . ltrim($path, '/');
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Verificar se arquivo existe
     * 
     * @param string $path Path relativo do arquivo
     * @return bool
     */
    public static function exists(string $path): bool
    {
        $fullPath = BASE_PATH . '/' . ltrim($path, '/');
        return file_exists($fullPath) && is_file($fullPath);
    }
}
