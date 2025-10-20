/**
 * Sistema de Sugestões Inteligentes "Top-3"
 * 
 * Ao clicar em um slot vazio (Vigilante/Supervisor), exibe popover
 * com os 3 melhores docentes ordenados por:
 * - Livre no horário
 * - Menor score (equilíbrio)
 * - Aptidão para o papel
 * - Proximidade (campus)
 * - Preferências
 * 
 * Integração com DnD: complementa o arrastar-soltar existente
 */

class SmartSuggestions {
    constructor() {
        this.currentPopover = null;
        this.isLoading = false;
        this.init();
    }

    init() {
        // Escutar cliques em slots vazios
        document.addEventListener('click', (e) => {
            const slot = e.target.closest('[data-suggest-slot]');
            if (slot) {
                e.preventDefault();
                e.stopPropagation();
                this.handleSlotClick(slot);
            }
        });

        // Fechar popover ao clicar fora
        document.addEventListener('click', (e) => {
            if (this.currentPopover && !e.target.closest('.suggestions-popover')) {
                this.closePopover();
            }
        });

        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.currentPopover) {
                this.closePopover();
            }
        });

        console.log('✅ SmartSuggestions inicializado');
    }

    /**
     * Handler de clique em slot vazio
     */
    async handleSlotClick(slot) {
        const juriId = parseInt(slot.dataset.juriId);
        const papel = slot.dataset.papel; // 'vigilante' ou 'supervisor'

        if (!juriId || !papel) {
            console.error('Slot sem juri_id ou papel');
            return;
        }

        // Fechar popover anterior
        this.closePopover();

        // Buscar sugestões
        await this.loadSuggestions(juriId, papel, slot);
    }

    /**
     * Carregar sugestões da API
     */
    async loadSuggestions(juriId, papel, slotElement) {
        if (this.isLoading) return;
        this.isLoading = true;

        try {
            // Mostrar loading no slot
            slotElement.classList.add('loading');

            const response = await fetch(`/api/suggest-top3?juri_id=${juriId}&papel=${papel}`);
            const data = await response.json();

            if (!data.ok) {
                throw new Error(data.error || 'Erro ao buscar sugestões');
            }

            // Renderizar popover
            this.renderPopover(data, slotElement, juriId, papel);

        } catch (error) {
            console.error('Erro ao carregar sugestões:', error);
            this.showError(slotElement, error.message);
        } finally {
            this.isLoading = false;
            slotElement.classList.remove('loading');
        }
    }

    /**
     * Renderizar popover com Top-3
     */
    renderPopover(data, slotElement, juriId, papel) {
        const { slot, top3, fallbacks } = data;

        // Criar popover
        const popover = document.createElement('div');
        popover.className = 'suggestions-popover';
        popover.innerHTML = `
            <div class="popover-header">
                <h4 class="popover-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Sugestões ${papel === 'supervisor' ? 'Supervisor' : 'Vigilante'}
                </h4>
                <button class="popover-close" onclick="window.smartSuggestions.closePopover()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="popover-info">
                <span class="info-badge">${slot.subject || 'Disciplina'}</span>
                <span class="info-badge">${slot.room || 'Sala'}</span>
                <span class="info-badge">${this.formatTime(slot.inicio)} - ${this.formatTime(slot.fim)}</span>
            </div>
            <div class="popover-content">
                ${top3.length === 0 ? this.renderNoSuggestions() : this.renderSuggestions(top3, juriId, papel)}
                ${fallbacks > 0 ? `<p class="fallback-warning">⚠️ Apenas ${top3.length} docente(s) disponível(is)</p>` : ''}
            </div>
        `;

        // Posicionar popover ao lado do slot
        this.positionPopover(popover, slotElement);

        // Adicionar ao DOM
        document.body.appendChild(popover);
        this.currentPopover = popover;

        // Animar entrada
        setTimeout(() => popover.classList.add('show'), 10);
    }

    /**
     * Renderizar lista de sugestões
     */
    renderSuggestions(top3, juriId, papel) {
        return `
            <div class="suggestions-list">
                ${top3.map((docente, index) => `
                    <div class="suggestion-card rank-${index + 1}">
                        <div class="suggestion-header">
                            <div class="suggestion-rank">#${index + 1}</div>
                            <div class="suggestion-name">${this.escapeHtml(docente.nome)}</div>
                        </div>
                        <div class="suggestion-metrics">
                            <span class="metric" title="Score de carga (1×vigia + 2×supervisor)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Score: ${docente.score}
                            </span>
                            ${papel === 'supervisor' ? `
                                <span class="metric" title="Aptidão para supervisão">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                    Aptidão: ${(docente.aptidao * 10).toFixed(1)}/10
                                </span>
                            ` : ''}
                            ${docente.dist === 0 ? `
                                <span class="metric metric-good" title="Mesmo campus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Mesmo campus
                                </span>
                            ` : ''}
                            ${docente.prefer === 1 ? `
                                <span class="metric metric-good" title="Preferência declarada">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Preferência
                                </span>
                            ` : ''}
                        </div>
                        <p class="suggestion-reason">${this.escapeHtml(docente.motivo)}</p>
                        <button 
                            class="btn-apply"
                            onclick="window.smartSuggestions.applySuggestion(${juriId}, ${docente.docente_id}, '${papel}')"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Aplicar
                        </button>
                    </div>
                `).join('')}
            </div>
        `;
    }

    /**
     * Renderizar mensagem de sem sugestões
     */
    renderNoSuggestions() {
        return `
            <div class="no-suggestions">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-600 font-medium">Nenhum docente disponível</p>
                <p class="text-sm text-gray-500">Todos os docentes têm conflitos de horário ou já atingiram sua capacidade.</p>
            </div>
        `;
    }

    /**
     * Aplicar sugestão (inserir alocação)
     */
    async applySuggestion(juriId, docenteId, papel) {
        try {
            const formData = new URLSearchParams();
            formData.append('juri_id', juriId);
            formData.append('docente_id', docenteId);
            formData.append('papel', papel);
            formData.append('_token', window.CSRF_TOKEN || '');

            const response = await fetch('/api/suggest-apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            });

            const data = await response.json();

            if (!data.ok) {
                throw new Error(data.error || 'Erro ao aplicar sugestão');
            }

            // Sucesso!
            this.showSuccess('Alocação aplicada com sucesso!');
            this.closePopover();

            // Recarregar página para atualizar interface
            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } catch (error) {
            console.error('Erro ao aplicar sugestão:', error);
            alert('❌ ' + error.message);
        }
    }

    /**
     * Posicionar popover
     */
    positionPopover(popover, slotElement) {
        const rect = slotElement.getBoundingClientRect();
        const popoverWidth = 400;
        const popoverHeight = 500;

        // Posicionar à direita do slot se houver espaço, senão à esquerda
        let left = rect.right + 10;
        if (left + popoverWidth > window.innerWidth) {
            left = rect.left - popoverWidth - 10;
        }

        // Posicionar verticalmente centralizado com o slot
        let top = rect.top - (popoverHeight / 2) + (rect.height / 2);
        
        // Ajustar se sair da tela
        if (top < 10) top = 10;
        if (top + popoverHeight > window.innerHeight) {
            top = window.innerHeight - popoverHeight - 10;
        }

        popover.style.left = `${left}px`;
        popover.style.top = `${top}px`;
    }

    /**
     * Fechar popover
     */
    closePopover() {
        if (this.currentPopover) {
            this.currentPopover.classList.remove('show');
            setTimeout(() => {
                if (this.currentPopover) {
                    this.currentPopover.remove();
                    this.currentPopover = null;
                }
            }, 200);
        }
    }

    /**
     * Mostrar erro
     */
    showError(element, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'suggestion-error';
        errorDiv.textContent = message;
        element.appendChild(errorDiv);
        setTimeout(() => errorDiv.remove(), 3000);
    }

    /**
     * Mostrar sucesso
     */
    showSuccess(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'suggestion-success';
        successDiv.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            ${message}
        `;
        document.body.appendChild(successDiv);
        setTimeout(() => successDiv.remove(), 3000);
    }

    /**
     * Formatar hora (YYYY-MM-DD HH:MM:SS -> HH:MM)
     */
    formatTime(datetime) {
        if (!datetime) return '';
        const parts = datetime.split(' ');
        if (parts.length === 2) {
            return parts[1].substring(0, 5); // HH:MM
        }
        return datetime;
    }

    /**
     * Escapar HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.smartSuggestions = new SmartSuggestions();
});
