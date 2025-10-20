<?php

namespace App\Controllers;

use App\Http\Request;

class GuideController extends Controller
{
    /**
     * Exibe um guia do utilizador
     */
    public function show(Request $request): string
    {
        $slug = $request->param('slug') ?? 'indice';
        
        // Mapeia slug para arquivo
        $slugToFile = [
            'indice' => 'GUIA_UTILIZADOR_INDICE.md',
            'parte1' => 'GUIA_UTILIZADOR_PARTE1.md',
            'parte2' => 'GUIA_UTILIZADOR_PARTE2.md',
            'parte3' => 'GUIA_UTILIZADOR_PARTE3.md',
            'referencia' => 'GUIA_RAPIDO_REFERENCIA.md'
        ];
        
        if (!isset($slugToFile[$slug])) {
            http_response_code(404);
            return $this->view('errors/404');
        }
        
        $file = $slugToFile[$slug];
        $filePath = base_path($file);
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            return $this->view('errors/404');
        }
        
        // Lê conteúdo do arquivo markdown
        $markdown = file_get_contents($filePath);
        
        // Converte markdown básico para HTML
        $html = $this->markdownToHtml($markdown);
        
        // Determina título baseado no slug
        $titles = [
            'indice' => 'Índice - Guia do Utilizador',
            'parte1' => 'Parte 1: Introdução e Vigilante',
            'parte2' => 'Parte 2: Membro da Comissão',
            'parte3' => 'Parte 3: Coordenador + FAQ',
            'referencia' => 'Guia Rápido de Referência'
        ];
        
        $title = $titles[$slug] ?? 'Guia do Utilizador';
        
        return $this->view('guides/show', [
            'title' => $title,
            'content' => $html,
            'currentSlug' => $slug
        ]);
    }
    
    /**
     * Converte markdown simples para HTML
     */
    private function markdownToHtml(string $markdown): string
    {
        // Headers
        $markdown = preg_replace('/^# (.+)$/m', '<h1 class="text-3xl font-bold text-gray-900 mb-4 mt-6">$1</h1>', $markdown);
        $markdown = preg_replace('/^## (.+)$/m', '<h2 class="text-2xl font-bold text-gray-800 mb-3 mt-5">$1</h2>', $markdown);
        $markdown = preg_replace('/^### (.+)$/m', '<h3 class="text-xl font-semibold text-gray-800 mb-2 mt-4">$1</h3>', $markdown);
        $markdown = preg_replace('/^#### (.+)$/m', '<h4 class="text-lg font-semibold text-gray-700 mb-2 mt-3">$1</h4>', $markdown);
        
        // Bold
        $markdown = preg_replace('/\*\*(.+?)\*\*/s', '<strong class="font-semibold">$1</strong>', $markdown);
        
        // Italic
        $markdown = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $markdown);
        
        // Code inline
        $markdown = preg_replace('/`([^`]+)`/', '<code class="bg-gray-100 px-1.5 py-0.5 rounded text-sm font-mono">$1</code>', $markdown);
        
        // Links [text](url)
        $markdown = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" class="text-blue-600 hover:text-blue-800 underline">$1</a>', $markdown);
        
        // Unordered lists
        $markdown = preg_replace('/^- (.+)$/m', '<li class="ml-4">$1</li>', $markdown);
        $markdown = preg_replace('/(<li class="ml-4">.*<\/li>\n)+/s', '<ul class="list-disc list-inside space-y-1 my-2">$0</ul>', $markdown);
        
        // Ordered lists
        $markdown = preg_replace('/^\d+\. (.+)$/m', '<li class="ml-4">$1</li>', $markdown);
        
        // Paragraphs
        $markdown = preg_replace('/^(?!<[hul]|```)(.+)$/m', '<p class="my-2">$1</p>', $markdown);
        
        // Code blocks
        $markdown = preg_replace('/```(.*?)\n(.*?)```/s', '<pre class="bg-gray-800 text-gray-100 p-4 rounded-lg overflow-x-auto my-4"><code>$2</code></pre>', $markdown);
        
        // Horizontal rules
        $markdown = preg_replace('/^---$/m', '<hr class="my-6 border-gray-300">', $markdown);
        
        // Tables (básico)
        $markdown = preg_replace('/^\|(.+)\|$/m', '<tr>$1</tr>', $markdown);
        $markdown = preg_replace('/\|([^\|]+)/','<td class="border px-4 py-2">$1</td>', $markdown);
        
        // Blockquotes
        $markdown = preg_replace('/^> (.+)$/m', '<blockquote class="border-l-4 border-blue-500 pl-4 italic text-gray-700 my-2">$1</blockquote>', $markdown);
        
        return $markdown;
    }
}
