<?php

namespace App\Http;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        // Limpar TODOS os buffers de saída
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Iniciar novo buffer limpo
        ob_start();

        // Prevenir cache
        if (!headers_sent()) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-Type: application/json; charset=utf-8');
            http_response_code($status);
        }

        // Gerar JSON
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($json === false) {
            error_log("ERRO JSON: " . json_last_error_msg());
            $json = json_encode([
                'success' => false,
                'message' => 'Erro ao gerar JSON: ' . json_last_error_msg()
            ]);
        }

        // Definir Content-Length para garantir integridade
        if (!headers_sent()) {
            header('Content-Length: ' . strlen($json));
        }

        // Limpar buffer e enviar apenas o JSON
        ob_clean();
        echo $json;
        ob_end_flush();

        // Garantir que nada mais seja enviado
        exit;
    }

    public static function redirect(string $url): void
    {
        \redirect($url);
    }

    public static function view(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data);
        include view_path($view . '.php');
    }
}
