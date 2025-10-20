<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Utils\Csrf;

class SettingsController extends Controller
{
    /**
     * Upload do logo da instituição
     */
    public function uploadLogo(Request $request)
    {
        try {
            // Validar CSRF
            if (!Csrf::validate($request)) {
                Response::json([
                    'success' => false,
                    'message' => 'Token CSRF inválido'
                ], 403);
                return;
            }
            
            // Verificar se o arquivo foi enviado
            if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                Response::json([
                    'success' => false,
                    'message' => 'Nenhum arquivo foi enviado'
                ], 400);
                return;
            }
            
            $file = $_FILES['logo'];
            
            // Validar tipo de arquivo
            $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                Response::json([
                    'success' => false,
                    'message' => 'Tipo de arquivo inválido. Apenas PNG e JPG são aceitos.'
                ], 400);
                return;
            }
            
            // Validar tamanho (máx 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                Response::json([
                    'success' => false,
                    'message' => 'Arquivo muito grande. Tamanho máximo: 2MB'
                ], 400);
                return;
            }
            
            // Criar diretório de uploads se não existir
            $uploadDir = public_path('/uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Nome fixo do arquivo
            $fileName = 'institution-logo.png';
            $targetPath = $uploadDir . '/' . $fileName;
            
            // Se é JPG, converter para PNG
            if (in_array($mimeType, ['image/jpeg', 'image/jpg'])) {
                $image = imagecreatefromjpeg($file['tmp_name']);
                imagepng($image, $targetPath);
                imagedestroy($image);
            } else {
                // Mover o arquivo
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    Response::json([
                        'success' => false,
                        'message' => 'Erro ao salvar arquivo'
                    ], 500);
                    return;
                }
            }
            
            Response::json([
                'success' => true,
                'message' => 'Logo carregado com sucesso!',
                'logoUrl' => '/uploads/' . $fileName
            ]);
            
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro ao fazer upload: ' . $e->getMessage()
            ], 500);
        }
    }
}
