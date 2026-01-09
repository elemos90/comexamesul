/**
 * Sistema de Ajuda Contextual
 * Gerencia abertura/fechamento do modal e carregamento de conteúdo
 */

// Dados de ajuda (será preenchido via PHP)
let helpData = {};

/**
 * Define os dados de ajuda
 */
function setHelpData(data) {
    helpData = data;
}

/**
 * Abre o modal de ajuda com conteúdo específico da página
 */
function openHelp(pageId) {
    const modal = document.getElementById('helpModal');
    const titleElement = document.getElementById('helpTitle');
    const contentElement = document.getElementById('helpContent');
    const guideLinkElement = document.getElementById('helpGuideLink');
    
    if (!modal) {
        console.error('Help modal not found');
        return;
    }
    
    // Busca conteúdo da página
    const content = helpData[pageId] || helpData['default'] || {
        title: 'Ajuda',
        content: '<p>Conteúdo de ajuda não disponível para esta página.</p>',
        guide_link: null
    };
    
    // Preenche o modal
    titleElement.textContent = content.title;
    contentElement.innerHTML = content.content;
    
    // Configura link para guia completo
    if (content.guide_link) {
        // Usa window.appUrl() para construir URL com base path correto
        let guideUrl = content.guide_link;
        
        // Use appUrl function if available (defined in main.php)
        if (typeof window.appUrl === 'function') {
            guideUrl = window.appUrl(guideUrl);
        } else if (!guideUrl.startsWith('/')) {
            guideUrl = '/' + guideUrl;
        }
        
        guideLinkElement.href = guideUrl;
        guideLinkElement.style.display = 'inline-flex';
        
        // Debug
        console.log('Guide link configurado:', guideUrl);
    } else {
        guideLinkElement.style.display = 'none';
    }
    
    // Mostra modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Foco no modal para acessibilidade
    setTimeout(() => {
        const closeButton = modal.querySelector('.help-modal-close');
        if (closeButton) {
            closeButton.focus();
        }
    }, 100);
    
    // Listener para ESC
    document.addEventListener('keydown', handleEscapeKey);
}

/**
 * Fecha o modal de ajuda
 */
function closeHelp() {
    const modal = document.getElementById('helpModal');
    
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Remove listener do ESC
    document.removeEventListener('keydown', handleEscapeKey);
}

/**
 * Manipula tecla ESC para fechar modal
 */
function handleEscapeKey(event) {
    if (event.key === 'Escape') {
        closeHelp();
    }
}

/**
 * Inicialização quando DOM está pronto
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se modal existe
    const modal = document.getElementById('helpModal');
    
    if (modal) {
        // Click no overlay fecha modal
        const overlay = modal.querySelector('.help-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeHelp);
        }
        
        // Click nos botões de fechar
        const closeButtons = modal.querySelectorAll('[onclick="closeHelp()"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', closeHelp);
        });
    }
});
