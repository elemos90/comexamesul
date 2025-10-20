/**
 * Sistema de Alocação Automática "Auto → Revisão Humana"
 * Fluxo em duas fases: PLANEJAR e APLICAR
 * 
 * Integração com API:
 * - POST /api/alocacao/plan-local-date
 * - POST /api/alocacao/apply-local-date
 * - GET /api/alocacao/kpis
 */

class AutoAllocationPlanner {
    constructor() {
        this.currentPlan = null;
        this.currentLocation = null;
        this.currentDate = null;
        this.init();
    }
    
    /**
     * Inicializar sistema
     */
    init() {
        this.attachEventListeners();
        console.log('✅ Auto Allocation Planner inicializado');
    }
    
    /**
     * Anexar event listeners
     */
    attachEventListeners() {
        // Botão "Gerar Plano (Auto)" por Local/Data
        const btnGeneratePlan = document.getElementById('btn-generate-plan');
        if (btnGeneratePlan) {
            btnGeneratePlan.addEventListener('click', () => this.openPlanModal());
        }
        
        // Botão dentro do modal para executar planejamento
        const btnExecutePlan = document.getElementById('btn-execute-plan');
        if (btnExecutePlan) {
            btnExecutePlan.addEventListener('click', () => this.generatePlan());
        }
        
        // Botão para aplicar plano
        const btnApplyPlan = document.getElementById('btn-apply-plan');
        if (btnApplyPlan) {
            btnApplyPlan.addEventListener('click', () => this.applyPlan());
        }
        
        // Fechar modal
        const btnClosePlan = document.querySelectorAll('[data-close-plan-modal]');
        btnClosePlan.forEach(btn => {
            btn.addEventListener('click', () => this.closePlanModal());
        });
    }
    
    /**
     * Abrir modal de seleção de Local/Data
     */
    openPlanModal() {
        const modal = document.getElementById('modal-plan-selector');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    
    /**
     * Fechar modal
     */
    closePlanModal() {
        const modal = document.getElementById('modal-plan-selector');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        const modalReview = document.getElementById('modal-plan-review');
        if (modalReview) {
            modalReview.classList.add('hidden');
            modalReview.classList.remove('flex');
        }
    }
    
    /**
     * FASE 1: PLANEJAR
     * Gerar plano de alocação (não grava no BD)
     */
    async generatePlan() {
        const location = document.getElementById('plan_location')?.value;
        const date = document.getElementById('plan_date')?.value;
        
        // Validar inputs
        if (!location || !date) {
            toastr.error('Por favor, preencha Local e Data');
            return;
        }
        
        // Mostrar loading
        this.showLoading('Gerando plano automático...');
        
        try {
            const response = await fetch('/api/alocacao/plan-local-date', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({
                    location: location,
                    data: date,
                    csrf: CSRF_TOKEN
                })
            });
            
            const result = await response.json();
            
            if (!response.ok || !result.ok) {
                throw new Error(result.erro || 'Erro ao gerar plano');
            }
            
            // Guardar plano para revisão
            this.currentPlan = result.plan;
            this.currentLocation = location;
            this.currentDate = date;
            
            // Exibir plano para revisão
            this.displayPlanForReview(result);
            
            toastr.success(`Plano gerado: ${result.stats.total_acoes} ações propostas`);
            
        } catch (error) {
            console.error('Erro ao gerar plano:', error);
            toastr.error(error.message || 'Erro ao gerar plano');
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Exibir plano para revisão humana
     */
    displayPlanForReview(result) {
        // Fechar modal de seleção
        this.closePlanModal();
        
        // Abrir modal de revisão
        const modalReview = document.getElementById('modal-plan-review');
        if (!modalReview) {
            console.error('Modal de revisão não encontrado');
            return;
        }
        
        // Preencher estatísticas
        document.getElementById('plan-stat-janelas').textContent = result.janela_count || 0;
        document.getElementById('plan-stat-acoes').textContent = result.stats.total_acoes || 0;
        document.getElementById('plan-stat-incompletos').textContent = result.stats.juris_incompletos || 0;
        document.getElementById('plan-stat-desvio-pre').textContent = (result.stats.desvio_score_pre || 0).toFixed(2);
        document.getElementById('plan-stat-desvio-pos').textContent = (result.stats.desvio_score_pos || 0).toFixed(2);
        
        // Determinar cor do desvio (verde se melhorou)
        const desvioPre = result.stats.desvio_score_pre || 0;
        const desvioPos = result.stats.desvio_score_pos || 0;
        const desvioElement = document.getElementById('plan-stat-desvio-pos');
        
        if (desvioPos < desvioPre) {
            desvioElement.classList.add('text-green-600', 'font-bold');
        } else if (desvioPos > desvioPre) {
            desvioElement.classList.add('text-amber-600');
        }
        
        // Preencher avisos
        const avisosList = document.getElementById('plan-avisos-list');
        if (avisosList) {
            if (result.avisos && result.avisos.length > 0) {
                avisosList.innerHTML = result.avisos.map(aviso => 
                    `<li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm">${aviso}</span>
                    </li>`
                ).join('');
            } else {
                avisosList.innerHTML = '<li class="text-sm text-gray-500 italic">Nenhum aviso</li>';
            }
        }
        
        // Preencher lista de ações
        const actionsList = document.getElementById('plan-actions-list');
        if (actionsList) {
            actionsList.innerHTML = this.renderPlanActions(result.plan);
        }
        
        // Mostrar modal
        modalReview.classList.remove('hidden');
        modalReview.classList.add('flex');
    }
    
    /**
     * Renderizar ações do plano (editável)
     */
    renderPlanActions(plan) {
        if (!plan || plan.length === 0) {
            return '<div class="text-center text-gray-500 py-8">Nenhuma ação proposta</div>';
        }
        
        let html = '';
        
        plan.forEach((juryPlan, juryIndex) => {
            html += `
                <div class="border-2 border-gray-200 rounded-lg p-4 mb-3" data-jury-index="${juryIndex}">
                    <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                        ${juryPlan.juri_info || `Júri #${juryPlan.juri_id}`}
                    </h4>
                    
                    <div class="space-y-2">
                        ${juryPlan.acoes.map((acao, acaoIndex) => this.renderAction(acao, juryIndex, acaoIndex)).join('')}
                    </div>
                </div>
            `;
        });
        
        return html;
    }
    
    /**
     * Renderizar ação individual
     */
    renderAction(acao, juryIndex, acaoIndex) {
        const isInsert = acao.op === 'INSERT';
        const isPapelSupervisor = acao.papel === 'supervisor';
        
        // Determinar cor do badge
        let badgeColor = 'bg-green-100 text-green-800'; // Padrão: ação positiva
        
        const icon = isInsert 
            ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        
        return `
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded border border-gray-200 hover:border-blue-300 transition"
                 data-jury-index="${juryIndex}"
                 data-acao-index="${acaoIndex}">
                <div class="flex-shrink-0 mt-1">
                    <span class="inline-flex items-center justify-center w-6 h-6 ${badgeColor} rounded-full">
                        ${icon}
                    </span>
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-medium text-gray-900">${acao.docente_name}</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded ${isPapelSupervisor ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">
                            ${acao.papel}
                        </span>
                        <span class="text-xs text-gray-500">${acao.op}</span>
                    </div>
                    <p class="text-xs text-gray-600">${acao.racional}</p>
                </div>
                
                <button type="button" 
                        class="btn-remove-action flex-shrink-0 text-red-500 hover:text-red-700 p-1"
                        data-jury-index="${juryIndex}"
                        data-acao-index="${acaoIndex}"
                        title="Remover esta ação">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        `;
    }
    
    /**
     * FASE 2: APLICAR
     * Gravar plano no BD após revisão
     */
    async applyPlan() {
        if (!this.currentPlan || this.currentPlan.length === 0) {
            toastr.error('Nenhum plano para aplicar');
            return;
        }
        
        // Confirmar aplicação
        if (!confirm('Confirmar aplicação do plano? As alocações serão gravadas no banco de dados.')) {
            return;
        }
        
        // Mostrar loading
        this.showLoading('Aplicando plano...');
        
        try {
            const response = await fetch('/api/alocacao/apply-local-date', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({
                    location: this.currentLocation,
                    data: this.currentDate,
                    plan: this.currentPlan,
                    csrf: CSRF_TOKEN
                })
            });
            
            const result = await response.json();
            
            if (!response.ok || !result.ok) {
                throw new Error(result.erro || 'Erro ao aplicar plano');
            }
            
            // Mostrar resultado
            this.displayApplyResult(result);
            
            // Limpar plano atual
            this.currentPlan = null;
            this.currentLocation = null;
            this.currentDate = null;
            
            // Fechar modal após 2 segundos
            setTimeout(() => {
                this.closePlanModal();
                // Recarregar página para mostrar alocações
                location.reload();
            }, 2000);
            
        } catch (error) {
            console.error('Erro ao aplicar plano:', error);
            toastr.error(error.message || 'Erro ao aplicar plano');
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Exibir resultado da aplicação
     */
    displayApplyResult(result) {
        const aplicadas = result.aplicadas || 0;
        const falhas = result.falhas || [];
        
        if (falhas.length === 0) {
            toastr.success(`✅ Plano aplicado com sucesso! ${aplicadas} alocações realizadas.`, 'Sucesso', {
                timeOut: 5000
            });
        } else {
            toastr.warning(`⚠️ Plano aplicado: ${aplicadas} OK, ${falhas.length} falhas`, 'Concluído com avisos', {
                timeOut: 5000
            });
            
            // Mostrar detalhes das falhas
            console.warn('Falhas na aplicação:', falhas);
            
            // Opcional: Exibir modal com detalhes
            this.displayFailures(falhas);
        }
    }
    
    /**
     * Exibir falhas em modal ou console
     */
    displayFailures(falhas) {
        let mensagem = 'Algumas alocações falharam:\n\n';
        falhas.forEach((falha, index) => {
            mensagem += `${index + 1}. Júri #${falha.juri_id}, Docente #${falha.docente_id} (${falha.papel}): ${falha.erro}\n`;
        });
        
        console.error(mensagem);
        
        // Opcional: criar modal específico para falhas
        alert(mensagem);
    }
    
    /**
     * Remover ação do plano (edição humana)
     */
    removeAction(juryIndex, acaoIndex) {
        if (!this.currentPlan || !this.currentPlan[juryIndex]) {
            return;
        }
        
        // Remover ação
        this.currentPlan[juryIndex].acoes.splice(acaoIndex, 1);
        
        // Se júri ficou sem ações, remover júri
        if (this.currentPlan[juryIndex].acoes.length === 0) {
            this.currentPlan.splice(juryIndex, 1);
        }
        
        // Re-renderizar
        const actionsList = document.getElementById('plan-actions-list');
        if (actionsList) {
            actionsList.innerHTML = this.renderPlanActions(this.currentPlan);
            this.reattachActionListeners();
        }
        
        toastr.info('Ação removida do plano');
    }
    
    /**
     * Reattach listeners após re-render
     */
    reattachActionListeners() {
        const removeButtons = document.querySelectorAll('.btn-remove-action');
        removeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const juryIndex = parseInt(btn.dataset.juryIndex);
                const acaoIndex = parseInt(btn.dataset.acaoIndex);
                this.removeAction(juryIndex, acaoIndex);
            });
        });
    }
    
    /**
     * Mostrar loading
     */
    showLoading(message = 'Carregando...') {
        // Criar overlay de loading se não existir
        let overlay = document.getElementById('loading-overlay');
        
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white rounded-lg p-6 shadow-xl flex items-center gap-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="text-gray-700 font-medium" id="loading-message">${message}</span>
                </div>
            `;
            document.body.appendChild(overlay);
        } else {
            const messageEl = document.getElementById('loading-message');
            if (messageEl) messageEl.textContent = message;
            overlay.classList.remove('hidden');
        }
    }
    
    /**
     * Esconder loading
     */
    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }
    }
    
    /**
     * Obter KPIs de alocação
     */
    async getKPIs(location, date) {
        try {
            const response = await fetch(`/api/alocacao/kpis?location=${encodeURIComponent(location)}&data=${encodeURIComponent(date)}`);
            const result = await response.json();
            
            if (result.ok) {
                return result.kpis;
            }
            
            return null;
        } catch (error) {
            console.error('Erro ao obter KPIs:', error);
            return null;
        }
    }
}

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.autoAllocationPlanner = new AutoAllocationPlanner();
});
