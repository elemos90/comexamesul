<?php

namespace App\Controllers;

use App\Database\Connection;
use App\Http\Request;
use App\Http\Response;
use PDO;
use Exception;

class InstallController extends Controller
{
    public function masterData(): string
    {
        // Verificar se já foi instalado
        $installed = file_exists(base_path('.master_data_installed'));
        
        return $this->view('install/master_data', [
            'installed' => $installed
        ]);
    }
    
    public function executeMasterData(Request $request)
    {
        header('Content-Type: application/json');
        
        try {
            $db = Connection::getInstance();
            
            // Ler arquivo SQL
            $sqlFile = base_path('app/Database/migrations_master_data_simple.sql');
            
            if (!file_exists($sqlFile)) {
                Response::json([
                    'success' => false,
                    'message' => 'Arquivo SQL não encontrado.'
                ], 500);
            }
            
            $sql = file_get_contents($sqlFile);
            
            // Executar SQL completo
            $db->exec($sql);
            
            // Verificar tabelas criadas
            $disciplines = $db->query("SELECT COUNT(*) as count FROM disciplines")->fetch(PDO::FETCH_ASSOC);
            $locations = $db->query("SELECT COUNT(*) as count FROM exam_locations")->fetch(PDO::FETCH_ASSOC);
            $rooms = $db->query("SELECT COUNT(*) as count FROM exam_rooms")->fetch(PDO::FETCH_ASSOC);
            
            // Criar arquivo de flag
            file_put_contents(base_path('.master_data_installed'), date('Y-m-d H:i:s'));
            
            Response::json([
                'success' => true,
                'message' => 'Instalação concluída!',
                'counts' => [
                    'disciplines' => $disciplines['count'],
                    'locations' => $locations['count'],
                    'rooms' => $rooms['count']
                ]
            ]);
            
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
                'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
