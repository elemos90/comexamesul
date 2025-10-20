# 🚀 MELHORIAS: Candidaturas de Vigilantes - Parte 2

**Data:** 12/10/2025  
**Objetivo:** Elevar sistema de BOM para EXCELENTE

---

## 📋 Melhorias Propostas

### 🎯 PRIORIDADE ALTA

#### Melhoria #1: Modal de Rejeição com Motivo Obrigatório

**Objetivo:** Garantir feedback construtivo ao vigilante

**Implementação:**

```html
<!-- Modal de Rejeição -->
<div id="modal-reject" class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">🚫 Rejeitar Candidatura</h2>
        </div>
        
        <form id="form-reject" class="p-6">
            <input type="hidden" id="reject_app_id" name="application_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Vigilante:</label>
                <p class="font-semibold text-gray-900" id="reject_vigilante_name"></p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Motivo da Rejeição *</label>
                <select name="rejection_reason" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Selecione um motivo --</option>
                    <option value="perfil_incompleto">Perfil Incompleto</option>
                    <option value="documentos_pendentes">Documentos Pendentes</option>
                    <option value="experiencia_insuficiente">Experiência Insuficiente</option>
                    <option value="conflito_horario">Conflito de Horário</option>
                    <option value="nao_atende_requisitos">Não Atende Requisitos</option>
                    <option value="outro">Outro Motivo</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Detalhes Adicionais</label>
                <textarea name="rejection_details" rows="3" 
                          class="w-full border rounded px-3 py-2"
                          placeholder="Explique o motivo com mais detalhes..."></textarea>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button type="button" 
                        onclick="closeRejectModal()" 
                        class="px-4 py-2 border rounded">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Rejeitar Candidatura
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(appId, vigilanteName) {
    document.getElementById('reject_app_id').value = appId;
    document.getElementById('reject_vigilante_name').textContent = vigilanteName;
    document.getElementById('modal-reject').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('modal-reject').classList.add('hidden');
    document.getElementById('form-reject').reset();
}

document.getElementById('form-reject').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const appId = formData.get('application_id');
    
    showLoading();
    
    const response = await fetch(`/applications/${appId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            rejection_reason: formData.get('rejection_reason'),
            rejection_details: formData.get('rejection_details'),
            csrf: csrfToken
        })
    });
    
    const result = await response.json();
    
    if (result.success) {
        showSuccessToast('Candidatura rejeitada', 'Feedback Enviado');
        closeRejectModal();
        setTimeout(() => location.reload(), 1500);
    } else {
        showErrorToast(result.message, 'Erro');
    }
});
</script>
```

**Backend (Controller):**

```php
public function reject(Request $request)
{
    try {
        if (ob_get_length()) ob_clean();
        
        // Validar CSRF
        $csrf = $request->input('csrf');
        if (!Csrf::validate($csrf)) {
            Response::json(['success' => false, 'message' => 'Token inválido'], 403);
            return;
        }
        
        $applicationId = (int) $request->param('id');
        $rejectionReason = $request->input('rejection_reason');
        $rejectionDetails = $request->input('rejection_details');
        
        // VALIDAÇÃO OBRIGATÓRIA
        if (empty($rejectionReason)) {
            Response::json([
                'success' => false,
                'message' => 'Motivo de rejeição é obrigatório'
            ], 400);
            return;
        }
        
        $applicationModel = new VacancyApplication();
        $application = $applicationModel->find($applicationId);
        
        if (!$application || $application['status'] !== 'pendente') {
            Response::json(['success' => false, 'message' => 'Candidatura inválida'], 400);
            return;
        }
        
        // Combinar motivo + detalhes
        $fullReason = $rejectionReason;
        if (!empty($rejectionDetails)) {
            $fullReason .= ': ' . $rejectionDetails;
        }
        
        $applicationModel->reject($applicationId, Auth::id(), $fullReason);
        
        ActivityLogger::log('vacancy_applications', $applicationId, 'reject', [
            'rejection_reason' => $rejectionReason,
            'rejection_details' => $rejectionDetails
        ]);
        
        // Notificar vigilante
        $emailService = new EmailNotificationService();
        $emailService->notifyApplicationRejected($applicationId, $fullReason);
        
        Response::json([
            'success' => true,
            'message' => 'Candidatura rejeitada com sucesso'
        ]);
        
    } catch (\Exception $e) {
        if (ob_get_length()) ob_clean();
        Response::json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

**Esforço:** 4 horas  
**Impacto:** 🔴 ALTO  

---

#### Melhoria #2: Substituir Alerts por Toasts + AJAX

**Objetivo:** UX moderna e não-bloqueante

**Implementação:**

```javascript
// ===== HELPERS DE TOAST =====
function showToast(type, title, message, duration = 5000) {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: duration,
        extendedTimeOut: 2000
    };
    toastr[type](message, title);
}

function showSuccessToast(message, title = '✅ Sucesso') {
    showToast('success', title, message);
}

function showErrorToast(message, title = '❌ Erro') {
    showToast('error', title, message, 8000);
}

// ===== APROVAR CANDIDATURA =====
async function approveApplication(appId, vigilanteName) {
    showLoading();
    
    try {
        const response = await fetch(`/applications/${appId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ csrf: csrfToken })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessToast(
                `Candidatura de <strong>${vigilanteName}</strong> foi aprovada!`,
                '✅ Aprovada'
            );
            
            // Atualizar UI sem reload
            await refreshApplicationsList();
            await refreshStats();
        } else {
            showErrorToast(result.message);
        }
    } catch (error) {
        showErrorToast('Erro ao aprovar candidatura');
    }
}

// ===== ATUALIZAR LISTA DINAMICAMENTE =====
async function refreshApplicationsList() {
    const vacancyId = new URLSearchParams(window.location.search).get('vacancy');
    if (!vacancyId) return;
    
    const response = await fetch(`/applications?vacancy=${vacancyId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    
    const html = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    const newList = doc.querySelector('.applications-list');
    const currentList = document.querySelector('.applications-list');
    
    if (newList && currentList) {
        currentList.innerHTML = newList.innerHTML;
    }
}

async function refreshStats() {
    const vacancyId = new URLSearchParams(window.location.search).get('vacancy');
    if (!vacancyId) return;
    
    const response = await fetch(`/api/applications/stats?vacancy=${vacancyId}`);
    const stats = await response.json();
    
    // Atualizar cards de estatísticas
    document.querySelector('[data-stat="pending"]').textContent = stats.pending;
    document.querySelector('[data-stat="approved"]').textContent = stats.approved;
    document.querySelector('[data-stat="rejected"]').textContent = stats.rejected;
    document.querySelector('[data-stat="total"]').textContent = stats.total;
}
```

**HTML Atualizado:**

```html
<!-- Trocar botão de submit por onclick -->
<button type="button" 
        onclick="approveApplication(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
        class="px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700">
    ✓ Aprovar
</button>

<button type="button"
        onclick="showRejectModal(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
        class="px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700">
    ✗ Rejeitar
</button>
```

**Backend - API de Stats:**

```php
// Route: GET /api/applications/stats
public function getStats(Request $request)
{
    if (ob_get_length()) ob_clean();
    
    $vacancyId = (int) $request->input('vacancy');
    $model = new VacancyApplication();
    $stats = $model->countByStatus($vacancyId);
    
    Response::json([
        'success' => true,
        'stats' => [
            'pending' => $stats['pendente'],
            'approved' => $stats['aprovada'],
            'rejected' => $stats['rejeitada'],
            'cancelled' => $stats['cancelada'],
            'total' => array_sum($stats)
        ]
    ]);
}
```

**Esforço:** 3 horas  
**Impacto:** 🟡 MÉDIO  

---

#### Melhoria #3: Busca e Filtros Inline

**Objetivo:** Encontrar candidaturas rapidamente

**Implementação:**

```html
<!-- Barra de Filtros -->
<div class="bg-white border rounded-lg p-4 mb-4">
    <div class="grid md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">🔍 Buscar Vigilante</label>
            <input type="search" 
                   id="search-vigilante" 
                   placeholder="Nome ou email..."
                   class="w-full border rounded px-3 py-2 text-sm"
                   onkeyup="filterApplications()">
        </div>
        
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
            <select id="filter-status" 
                    class="w-full border rounded px-3 py-2 text-sm"
                    onchange="filterApplications()">
                <option value="">Todos</option>
                <option value="pendente">Pendentes</option>
                <option value="aprovada">Aprovadas</option>
                <option value="rejeitada">Rejeitadas</option>
                <option value="cancelada">Canceladas</option>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Elegibilidade</label>
            <select id="filter-supervisor" 
                    class="w-full border rounded px-3 py-2 text-sm"
                    onchange="filterApplications()">
                <option value="">Todos</option>
                <option value="1">Elegíveis Supervisor</option>
                <option value="0">Não Elegíveis</option>
            </select>
        </div>
        
        <div class="flex items-end">
            <button onclick="clearFilters()" 
                    class="w-full px-3 py-2 border rounded text-sm hover:bg-gray-50">
                🔄 Limpar Filtros
            </button>
        </div>
    </div>
    
    <div id="filter-results" class="mt-3 text-xs text-gray-600">
        <!-- Mostrando X de Y candidaturas -->
    </div>
</div>

<script>
function filterApplications() {
    const search = document.getElementById('search-vigilante').value.toLowerCase();
    const statusFilter = document.getElementById('filter-status').value;
    const supervisorFilter = document.getElementById('filter-supervisor').value;
    
    const rows = document.querySelectorAll('[data-application-row]');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const name = row.dataset.name.toLowerCase();
        const email = row.dataset.email.toLowerCase();
        const status = row.dataset.status;
        const supervisor = row.dataset.supervisor;
        
        const matchSearch = !search || name.includes(search) || email.includes(search);
        const matchStatus = !statusFilter || status === statusFilter;
        const matchSupervisor = !supervisorFilter || supervisor === supervisorFilter;
        
        const show = matchSearch && matchStatus && matchSupervisor;
        
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    
    // Atualizar contador
    document.getElementById('filter-results').textContent = 
        `Mostrando ${visibleCount} de ${rows.length} candidaturas`;
}

function clearFilters() {
    document.getElementById('search-vigilante').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-supervisor').value = '';
    filterApplications();
}
</script>
```

**HTML das Linhas (adicionar data-attributes):**

```html
<div data-application-row
     data-name="<?= htmlspecialchars($app['vigilante_name']) ?>"
     data-email="<?= htmlspecialchars($app['vigilante_email']) ?>"
     data-status="<?= $app['status'] ?>"
     data-supervisor="<?= $app['supervisor_eligible'] ? '1' : '0' ?>"
     class="px-6 py-4 hover:bg-gray-50">
    <!-- Conteúdo da linha -->
</div>
```

**Esforço:** 2 horas  
**Impacto:** 🟡 MÉDIO  

---

### 🎨 PRIORIDADE MÉDIA

#### Melhoria #4: Dashboard com Gráficos (Chart.js)

**Objetivo:** Visualização de dados mais intuitiva

**Dependência:**

```html
<!-- No layout -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

**Gráfico 1: Candidaturas por Dia (Linha)**

```html
<div class="bg-white border rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">📈 Candidaturas nos Últimos 30 Dias</h3>
    <canvas id="chart-applications-by-day" style="max-height: 300px;"></canvas>
</div>

<script>
const applicationsData = <?= json_encode($applicationsByDay) ?>;

new Chart('chart-applications-by-day', {
    type: 'line',
    data: {
        labels: applicationsData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('pt-PT', { day: '2-digit', month: 'short' });
        }),
        datasets: [{
            label: 'Candidaturas',
            data: applicationsData.map(d => d.count),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>
```

**Gráfico 2: Distribuição de Status (Donut)**

```html
<div class="bg-white border rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">📊 Distribuição de Status</h3>
    <canvas id="chart-status-distribution" style="max-height: 300px;"></canvas>
</div>

<script>
new Chart('chart-status-distribution', {
    type: 'doughnut',
    data: {
        labels: ['Aprovadas', 'Pendentes', 'Rejeitadas', 'Canceladas'],
        datasets: [{
            data: [
                <?= $generalStats['approved_count'] ?>,
                <?= $generalStats['pending_count'] ?>,
                <?= $generalStats['rejected_count'] ?>,
                <?= $generalStats['cancelled_count'] ?>
            ],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
```

**Gráfico 3: Top Vigilantes (Barras)**

```html
<div class="bg-white border rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">🏆 Top 10 Vigilantes Mais Ativos</h3>
    <canvas id="chart-top-vigilantes" style="max-height: 300px;"></canvas>
</div>

<script>
const topVigilantes = <?= json_encode($topVigilantes) ?>;

new Chart('chart-top-vigilantes', {
    type: 'bar',
    data: {
        labels: topVigilantes.map(v => v.name.split(' ')[0]), // Primeiro nome
        datasets: [{
            label: 'Candidaturas',
            data: topVigilantes.map(v => v.application_count),
            backgroundColor: '#8b5cf6'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y', // Barras horizontais
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
```

**Esforço:** 4 horas  
**Impacto:** 🟡 MÉDIO  

---

## 📅 Roadmap de Implementação

### Fase 1: Correções Críticas (1-2 dias)

| # | Melhoria | Esforço | Impacto |
|---|----------|---------|---------|
| 1 | Modal de rejeição obrigatória | 4h | 🔴 Alto |
| 2 | Substituir alerts por toasts + AJAX | 3h | 🟡 Médio |
| 3 | Validação CSRF completa | 2h | 🔴 Alto |

**Total Fase 1:** ~9 horas (1-2 dias)

### Fase 2: Melhorias de UX (2-3 dias)

| # | Melhoria | Esforço | Impacto |
|---|----------|---------|---------|
| 4 | Busca e filtros inline | 2h | 🟡 Médio |
| 5 | Gráficos Chart.js (3 gráficos) | 4h | 🟡 Médio |
| 6 | Paginação | 3h | 🟡 Médio |

**Total Fase 2:** ~9 horas (2 dias)

### Fase 3: Recursos Avançados (3-4 dias)

| # | Melhoria | Esforço | Impacto |
|---|----------|---------|---------|
| 7 | Exportação avançada (XLSX + PDF) | 6h | 🟢 Baixo |
| 8 | Reverter decisões | 2h | 🟢 Baixo |
| 9 | Stats em tempo real | 3h | 🟢 Baixo |
| 10 | Comparação temporal | 4h | 🟢 Baixo |

**Total Fase 3:** ~15 horas (3-4 dias)

---

## 📊 Resumo Final

### Tempo Total Estimado
- **Fase 1 (Crítico):** 9 horas
- **Fase 2 (UX):** 9 horas  
- **Fase 3 (Avançado):** 15 horas
- **TOTAL:** ~33 horas (~5 dias úteis)

### Priorização Recomendada

```
1️⃣ IMPLEMENTAR JÁ (Fase 1):
   - Modal rejeição obrigatória
   - Toasts + AJAX
   - CSRF completo

2️⃣ IMPLEMENTAR DEPOIS (Fase 2):
   - Busca e filtros
   - Gráficos Chart.js
   - Paginação

3️⃣ IMPLEMENTAR SE HOUVER TEMPO (Fase 3):
   - Exportação avançada
   - Recursos extras
```

---

**FIM DA ANÁLISE**
