# ✅ Guia de Teste - Drag-and-Drop no Planejamento de Júris

**Status**: Sistema ATIVO e FUNCIONAL  
**Última Verificação**: 11/10/2025  
**Localização**: `/juries/planning`

---

## 🎯 Funcionalidades Implementadas

### ✅ Drag-and-Drop
- [x] Arrastar vigilantes da lista para júris
- [x] Arrastar supervisores da lista para júris
- [x] Validação em tempo real (verde/âmbar/vermelho)
- [x] Prevenção de conflitos de horário
- [x] Verificação de capacidade
- [x] Feedback visual durante arrasto
- [x] Atualização dinâmica sem reload

### ✅ Remoção
- [x] Botão ✕ para remover vigilantes
- [x] Botão ✕ para remover supervisores
- [x] Confirmação antes de remover
- [x] Atualização automática de badges de carga
- [x] Atualização de métricas em tempo real

### ✅ Validações
- [x] Impede alocar em júris com horário conflitante
- [x] Impede exceder capacidade máxima
- [x] Valida disponibilidade do vigilante
- [x] Valida elegibilidade de supervisor
- [x] Cache de validações para performance

---

## 🚀 Como Testar

### Passo 1: Acessar o Sistema
```
URL: http://localhost/juries/planning
```

**Credenciais (seed):**
- **Coordenador**: `coordenador@unilicungo.ac.mz` / `password`
- **Membro**: `membro@unilicungo.ac.mz` / `password`

### Passo 2: Verificar Ambiente

**Checklist Inicial:**
1. ✅ PHP 8.2.12 está instalado
2. ✅ Scripts carregados:
   - `/js/planning-dnd.js` (sistema drag-and-drop)
   - `/js/jury-modals.js` (modais)
   - `/js/auto-allocation-planner.js` (auto-alocação)
   - `/js/smart-suggestions.js` (sugestões Top-3)
3. ✅ SortableJS carregado via CDN
4. ✅ Toastr carregado para notificações

### Passo 3: Testar Drag-and-Drop de Vigilantes

#### ✅ Teste 1: Alocação Simples
1. Localize a lista **"Vigilantes Disponíveis"** (coluna esquerda)
2. Clique e arraste um vigilante
3. Solte sobre a zona **"Vigilantes"** de um júri
4. **Esperado**:
   - Feedback visual verde durante arrasto
   - Notificação de sucesso após soltar
   - Vigilante aparece na zona do júri
   - Badge de carga atualizado
   - Contador (X/Y) atualizado

#### ✅ Teste 2: Validação de Capacidade
1. Arraste vigilantes até preencher a capacidade (geralmente 2)
2. Tente arrastar um terceiro vigilante
3. **Esperado**:
   - Feedback visual vermelho
   - Mensagem: "Capacidade máxima atingida"
   - Não permite soltar

#### ✅ Teste 3: Conflito de Horário
1. Aloque um vigilante no Júri A (ex: 08:00-11:00)
2. Tente alocar o MESMO vigilante no Júri B (mesmo horário)
3. **Esperado**:
   - Erro 409 (Conflict)
   - Notificação: "Vigilante já está alocado a um júri nesse horário"

### Passo 4: Testar Drag-and-Drop de Supervisores

#### ✅ Teste 4: Alocação de Supervisor
1. Localize a lista **"Supervisores"** (coluna esquerda, abaixo)
2. Arraste um supervisor
3. Solte sobre a zona **"Supervisor"** de um júri
4. **Esperado**:
   - Zona fica azul após alocação
   - Supervisor aparece com fundo azul
   - Badge de carga atualizado

#### ✅ Teste 5: Substituição de Supervisor
1. Arraste um segundo supervisor para o mesmo júri
2. **Esperado**:
   - Feedback âmbar: "Já tem supervisor (será substituído)"
   - Ao soltar, substitui o anterior
   - Apenas 1 supervisor por júri

### Passo 5: Testar Remoção

#### ✅ Teste 6: Remover Vigilante
1. Clique no botão **✕** ao lado do nome do vigilante
2. Confirme na popup
3. **Esperado**:
   - Vigilante removido da zona
   - Contador atualizado
   - Badge de carga do vigilante diminui
   - Notificação de sucesso

#### ✅ Teste 7: Remover Supervisor
1. Clique no botão **✕** ao lado do supervisor
2. Confirme na popup
3. **Esperado**:
   - Supervisor removido
   - Zona volta a cor cinza
   - Badge de carga atualizado

### Passo 6: Testar Busca

#### ✅ Teste 8: Filtrar Vigilantes
1. Digite um nome no campo **"Buscar vigilante..."**
2. **Esperado**:
   - Lista filtra em tempo real
   - Mostra apenas nomes que correspondem
   - Ainda é possível arrastar os filtrados

### Passo 7: Testar Métricas

#### ✅ Teste 9: Atualização de Métricas
1. Observe a barra de métricas no topo:
   - Total Júris
   - Slots Disponíveis
   - Alocados
   - Sem Supervisor
   - Desvio Carga
   - Equilíbrio
2. Aloque/remova vigilantes
3. **Esperado**:
   - Métricas atualizam automaticamente
   - "Equilíbrio" muda de cor conforme desvio padrão

---

## 🔧 Resolução de Problemas

### Problema 1: Drag não funciona
**Causas Possíveis:**
- SortableJS não carregado
- JavaScript com erros

**Solução:**
```bash
# Abrir console do navegador (F12)
# Verificar erros de carregamento
# Verificar se SortableJS está disponível:
console.log(typeof Sortable);  # Deve retornar "function"
```

### Problema 2: CSRF Token inválido
**Causas:**
- Sessão expirou
- Token não definido

**Solução:**
```javascript
// No console, verificar:
console.log(CSRF_TOKEN);  // Deve mostrar um hash

// Se undefined, recarregar página
location.reload();
```

### Problema 3: API retorna 401/403
**Causas:**
- Usuário não autenticado
- Permissões insuficientes

**Solução:**
- Fazer login com coordenador ou membro
- Vigilantes NÃO têm acesso ao planejamento

### Problema 4: Validação sempre falha
**Causas:**
- Dados inconsistentes no BD
- Views SQL não criadas

**Solução:**
```bash
# Executar migrations/triggers:
php scripts/verify_allocation_system.php
```

---

## 📊 Endpoints Utilizados

### Alocação
- `POST /juries/{id}/assign` - Alocar vigilante
- `POST /juries/{id}/set-supervisor` - Alocar supervisor

### Remoção
- `POST /juries/{id}/unassign` - Remover vigilante
- `POST /juries/{id}/set-supervisor` (supervisor_id: null) - Remover supervisor

### Validação
- `POST /api/allocation/can-assign` - Verificar se pode alocar

### Métricas
- `GET /api/allocation/stats` - Estatísticas gerais

---

## 🎨 Feedback Visual

### Cores Durante Arrasto
- **Verde** (`.drag-over-valid`): Pode alocar sem problemas
- **Âmbar** (`.drag-over-warning`): Pode alocar mas com aviso
- **Vermelho** (`.drag-over-invalid`): Bloqueado, não pode alocar

### Badges de Carga (Workload)
- **Verde** (0 pontos): Sem alocações
- **Amarelo** (1-2 pontos): Carga moderada
- **Vermelho** (3+ pontos): Carga alta

**Score de Carga:**
- Vigilância = 1 ponto
- Supervisão = 2 pontos

---

## 🧪 Testes Avançados

### Teste 10: Auto-Alocação Individual
1. Clique no botão **"⚡ Auto"** de um júri específico
2. **Esperado**:
   - Sistema aloca automaticamente vigilantes disponíveis
   - Prioriza pessoas com menor carga
   - Respeita conflitos de horário

### Teste 11: Auto-Alocação Completa
1. Clique em **"⚡ Auto-Alocar Completo"** de uma disciplina
2. **Esperado**:
   - Aloca vigilantes e supervisores em TODOS os júris da disciplina
   - Distribui carga equilibradamente
   - Atualiza métricas

### Teste 12: Sugestões Top-3
1. Clique em **"Sugestões Top-3"** em um slot vazio
2. **Esperado**:
   - Modal com 3 melhores candidatos
   - Score de adequação exibido
   - Ao clicar em um, aloca automaticamente

---

## 📝 Arquivos Relacionados

### Controllers
- `app/Controllers/JuryController.php` (linhas 152-225)
  - `assign()` - Alocar vigilante
  - `unassign()` - Remover vigilante
  - `setSupervisor()` - Alocar/remover supervisor
  - `planning()` - Página principal (linha 727)

### Views
- `app/Views/juries/planning.php` (770 linhas)
  - HTML estrutural
  - Modais de criação
  - Integração de scripts

### JavaScript
- `public/js/planning-dnd.js` (812 linhas)
  - Lógica principal drag-and-drop
  - Validações em tempo real
  - Atualização dinâmica de UI

### Services
- `app/Services/AllocationService.php`
  - Lógica de negócio de alocação
  - Cálculo de slots e capacidade

---

## ✅ Checklist Final

### Funcionalidades Core
- [x] Arrastar vigilante para júri
- [x] Arrastar supervisor para júri
- [x] Remover vigilante
- [x] Remover supervisor
- [x] Validação de conflitos
- [x] Validação de capacidade
- [x] Feedback visual em tempo real

### Integrações
- [x] Auto-alocação individual
- [x] Auto-alocação completa por disciplina
- [x] Sugestões Top-3
- [x] Busca/filtro de vigilantes
- [x] Atualização de métricas
- [x] Logs de atividade

### UX
- [x] Notificações toast
- [x] Confirmações de remoção
- [x] Loading states
- [x] Badges de carga coloridos
- [x] Contadores atualizados
- [x] Sem reload de página

---

## 🎓 Dicas de Uso

### Melhor Fluxo de Trabalho
1. **Criar Júris**: Use "Criar Exames por Local" para criar vários de uma vez
2. **Auto-Alocar**: Clique em "Auto-Alocar Completo" para preencher automaticamente
3. **Ajustar**: Use drag-and-drop para trocar pessoas específicas
4. **Verificar**: Observe métricas de equilíbrio e ajuste se necessário

### Atalhos
- **Esc**: Fechar modais
- **Ctrl+F**: Buscar vigilante (foco no campo de busca)
- **Clique duplo**: Visualizar detalhes do júri (futuro)

---

## 📞 Suporte

**Se algo não funcionar:**
1. Verificar console do navegador (F12 → Console)
2. Verificar logs do servidor: `storage/logs/`
3. Executar verificação: `php scripts/verify_allocation_system.php`
4. Limpar cache: `Ctrl+Shift+R` no navegador

---

**Status**: ✅ Sistema PRONTO PARA USO  
**Última Atualização**: 11/10/2025  
**Testado em**: PHP 8.2.12, Chrome/Edge
