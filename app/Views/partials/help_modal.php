<!-- Modal de Ajuda Contextual -->
<div id="helpModal" class="help-modal" style="display: none;" role="dialog" aria-labelledby="helpModalTitle" aria-modal="true">
    <div class="help-modal-overlay" onclick="closeHelp()"></div>
    <div class="help-modal-content">
        <div class="help-modal-header">
            <h3 id="helpModalTitle" class="help-modal-title">
                <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span id="helpTitle">Ajuda</span>
            </h3>
            <button type="button" 
                    class="help-modal-close" 
                    onclick="closeHelp()"
                    aria-label="Fechar ajuda">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="help-modal-body" id="helpContent">
            <p>Carregando...</p>
        </div>
        
        <div class="help-modal-footer">
            <a href="#" id="helpGuideLink" class="help-guide-link" target="_blank" rel="noopener noreferrer" style="display: none;">
                📖 Ver Guia Completo
            </a>
            <button type="button" class="help-close-button" onclick="closeHelp()">
                Fechar
            </button>
        </div>
    </div>
</div>

<style>
/* Modal Overlay */
.help-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.help-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
    animation: fadeIn 0.2s ease-out;
}

/* Modal Content */
.help-modal-content {
    position: relative;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    max-width: 42rem;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease-out;
}

/* Header */
.help-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #E5E7EB;
}

.help-modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
}

.help-modal-close {
    background: none;
    border: none;
    color: #6B7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.help-modal-close:hover {
    background-color: #F3F4F6;
    color: #111827;
}

/* Body */
.help-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    color: #374151;
    line-height: 1.6;
}

.help-modal-body h4 {
    color: #111827;
    font-size: 1rem;
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.help-modal-body h4:first-child {
    margin-top: 0;
}

.help-modal-body h5 {
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    margin-top: 0.75rem;
    margin-bottom: 0.375rem;
}

.help-modal-body ul,
.help-modal-body ol {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}

.help-modal-body li {
    margin: 0.25rem 0;
}

.help-modal-body strong {
    color: #111827;
    font-weight: 600;
}

.help-modal-body p {
    margin: 0.5rem 0;
}

.help-modal-body pre {
    background-color: #F3F4F6;
    padding: 0.75rem;
    border-radius: 0.375rem;
    overflow-x: auto;
    font-size: 0.875rem;
    margin: 0.5rem 0;
}

/* Footer */
.help-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #E5E7EB;
}

.help-guide-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #3B82F6;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: color 0.2s;
}

.help-guide-link:hover {
    color: #2563EB;
    text-decoration: underline;
}

.help-close-button {
    padding: 0.625rem 1.25rem;
    background-color: #3B82F6;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.help-close-button:hover {
    background-color: #2563EB;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(1rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 640px) {
    .help-modal-content {
        max-height: 95vh;
        border-radius: 0.75rem;
    }
    
    .help-modal-header,
    .help-modal-body,
    .help-modal-footer {
        padding: 1rem;
    }
    
    .help-modal-footer {
        flex-direction: column-reverse;
        align-items: stretch;
    }
    
    .help-close-button {
        width: 100%;
    }
}

/* Scrollbar Styling */
.help-modal-body::-webkit-scrollbar {
    width: 8px;
}

.help-modal-body::-webkit-scrollbar-track {
    background: #F3F4F6;
    border-radius: 4px;
}

.help-modal-body::-webkit-scrollbar-thumb {
    background: #D1D5DB;
    border-radius: 4px;
}

.help-modal-body::-webkit-scrollbar-thumb:hover {
    background: #9CA3AF;
}
</style>
