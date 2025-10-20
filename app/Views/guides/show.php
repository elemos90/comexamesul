<?php
$isPublic = true; // Guias são públicos
?>

<style>
/* Sobrescreve overflow:hidden do layout para permitir scroll nas páginas de guia */
body, html {
    overflow: auto !important;
    height: auto !important;
    position: relative !important;
}

/* Garante que o navbar fixo funcione */
body {
    padding-top: 0 !important;
    margin: 0 !important;
}

/* Fix para o main que contém o navbar */
main.min-h-screen {
    position: relative !important;
}

/* Garante que o navbar fique fixo mesmo dentro do flex container */
nav.fixed {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 50 !important;
}

/* Barra de progresso de leitura */
#readingProgress {
    position: fixed;
    top: 0;
    left: 0;
    width: 0%;
    height: 3px;
    background: linear-gradient(90deg, #3B82F6, #2563EB);
    z-index: 9999;
    transition: width 0.1s ease;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

/* Navbar fixo - sombra ao rolar */
nav.fixed {
    transition: box-shadow 0.3s ease;
    background-color: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(8px);
}

/* Header de navegação fixo */
.guide-nav-header {
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    background-color: rgba(255, 255, 255, 0.95);
}
</style>

<!-- Barra de progresso de leitura -->
<div id="readingProgress"></div>

<div class="min-h-screen bg-gray-50 pt-20 pb-20">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Header -->
        <div class="guide-nav-header rounded-lg shadow-sm border border-gray-200 p-6 mb-6 sticky top-16 z-40">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-900">
                    📚 Guia do Utilizador
                </h1>
                <a href="<?= \App\Utils\Auth::check() ? '/dashboard' : '/' ?>" 
                   class="text-sm text-blue-600 hover:text-blue-800">
                    ← Voltar
                </a>
            </div>
            
            <!-- Navegação entre guias -->
            <div class="flex flex-wrap gap-2">
                <?php
                $guides = [
                    'indice' => '📑 Índice',
                    'parte1' => '📘 Parte 1',
                    'parte2' => '📗 Parte 2',
                    'parte3' => '📕 Parte 3',
                    'referencia' => '⚡ Referência Rápida'
                ];
                
                foreach ($guides as $slug => $name):
                    $isActive = ($slug === $currentSlug);
                ?>
                    <a href="/guides/<?= $slug ?>" 
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors <?= $isActive ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        <?= htmlspecialchars($name) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Conteúdo do guia -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
            <div class="prose prose-blue max-w-none overflow-auto">
                <?= $content ?>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-6 mb-8 text-center text-sm text-gray-600">
            <p>Portal da Comissão de Exames de Admissão - UniLicungo</p>
            <p class="mt-1">Versão 2.1 | Outubro 2025</p>
        </div>
    </div>
</div>

<!-- Botão voltar ao topo -->
<button id="backToTop" class="fixed bottom-8 right-8 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-all opacity-0 pointer-events-none" aria-label="Voltar ao topo">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
    </svg>
</button>

<script>
// Debug: Verificar se navbar está fixo
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('nav');
    if (navbar) {
        const position = window.getComputedStyle(navbar).position;
        console.log('📌 Navbar position:', position);
        console.log('📌 Navbar classes:', navbar.className);
    }
});

// Atualizar barra de progresso, botão "Voltar ao topo" e sombra do navbar
window.addEventListener('scroll', function() {
    // Barra de progresso de leitura
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.getElementById('readingProgress').style.width = scrolled + '%';
    
    // Botão voltar ao topo
    const backToTop = document.getElementById('backToTop');
    if (window.scrollY > 300) {
        backToTop.style.opacity = '1';
        backToTop.style.pointerEvents = 'auto';
    } else {
        backToTop.style.opacity = '0';
        backToTop.style.pointerEvents = 'none';
    }
    
    // Adicionar sombra ao navbar ao rolar
    const navbar = document.querySelector('nav.fixed');
    const guideHeader = document.querySelector('.guide-nav-header');
    
    if (navbar) {
        if (window.scrollY > 10) {
            navbar.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        } else {
            navbar.style.boxShadow = '';
        }
    }
    
    if (guideHeader) {
        if (window.scrollY > 100) {
            guideHeader.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
        } else {
            guideHeader.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)';
        }
    }
});

// Scroll suave ao topo
document.getElementById('backToTop').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

<style>
/* Estilos adicionais para melhorar a leitura */
.prose {
    color: #374151;
    line-height: 1.75;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

.prose h1 {
    color: #111827;
    border-bottom: 2px solid #E5E7EB;
    padding-bottom: 0.5rem;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.prose h2 {
    color: #1F2937;
    border-bottom: 1px solid #E5E7EB;
    padding-bottom: 0.25rem;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.prose h3 {
    color: #374151;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}

.prose h4 {
    color: #4B5563;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.prose ul, .prose ol {
    padding-left: 1.5rem;
    margin: 1rem 0;
}

.prose li {
    margin: 0.5rem 0;
}

.prose p {
    margin: 1rem 0;
}

.prose code {
    background-color: #F3F4F6;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.prose pre {
    background-color: #1F2937;
    color: #F9FAFB;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}

.prose pre code {
    background-color: transparent;
    padding: 0;
}

.prose a {
    color: #2563EB;
    text-decoration: none;
}

.prose a:hover {
    text-decoration: underline;
}

.prose table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.prose table td {
    border: 1px solid #E5E7EB;
    padding: 0.5rem 1rem;
}

.prose table th {
    background-color: #F3F4F6;
    font-weight: 600;
    border: 1px solid #E5E7EB;
    padding: 0.5rem 1rem;
}

.prose blockquote {
    border-left: 4px solid #3B82F6;
    padding-left: 1rem;
    font-style: italic;
    color: #6B7280;
    margin: 1rem 0;
}

.prose hr {
    border: 0;
    border-top: 2px solid #E5E7EB;
    margin: 2rem 0;
}

/* Responsivo */
@media (max-width: 640px) {
    .prose {
        font-size: 0.875rem;
    }
    
    .prose h1 {
        font-size: 1.5rem;
        margin-top: 1rem;
    }
    
    .prose h2 {
        font-size: 1.25rem;
        margin-top: 1rem;
    }
    
    .prose h3 {
        font-size: 1.125rem;
    }
    
    /* Botão voltar ao topo menor em mobile */
    #backToTop {
        bottom: 1rem;
        right: 1rem;
        padding: 0.625rem;
    }
    
    #backToTop svg {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    /* Menos padding no conteúdo em mobile */
    .bg-white.rounded-lg {
        padding: 1rem;
    }
    
    /* Header de navegação ajustado em mobile */
    .guide-nav-header {
        top: 4rem !important;
        padding: 1rem !important;
    }
    
    .guide-nav-header h1 {
        font-size: 1.25rem !important;
    }
}

/* Scroll suave */
html {
    scroll-behavior: smooth;
}

/* Melhor visualização de tabelas em mobile */
@media (max-width: 768px) {
    .prose table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>
